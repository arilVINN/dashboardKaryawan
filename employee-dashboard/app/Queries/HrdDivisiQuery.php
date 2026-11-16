<?php

namespace App\Queries;

use App\Models\Divisi;
use Illuminate\Database\Eloquent\Builder;

class HrdDivisiQuery
{
    public function apply(array $filters): Builder
    {
        $sort = $filters['sort'] ?? null;
        $dir = ($filters['dir'] ?? null) === 'desc' ? 'desc' : 'asc';

        $query = Divisi::query()
            ->withCount('karyawans')
            ->with([
                'parent:id_divisi,kode_divisi,nama_divisi,tipe,level',
                'karyawans.user.role',
            ]);

        $q = $filters['q'] ?? null;
        if ($this->hasFilter($q)) {
            // LOWER(...) so the search is case-insensitive on Postgres too.
            $kata = mb_strtolower((string) $q);
            $query->where(function ($query) use ($kata): void {
                $query->whereRaw('LOWER(kode_divisi) LIKE ?', ['%'.$kata.'%'])
                    ->orWhereRaw('LOWER(nama_divisi) LIKE ?', ['%'.$kata.'%']);
            });
        }

        $status = $filters['status'] ?? null;
        if (in_array($status, ['aktif', 'nonaktif'], true)) {
            $query->whereRaw('LOWER(status_aktif) = ?', [$status]);
        }

        $tipe = $filters['tipe'] ?? null;
        if (is_string($tipe) && array_key_exists($tipe, Divisi::TIPE_OPTIONS)) {
            $query->where('tipe', $tipe);
        }

        switch ($sort) {
            case 'kode':
                $query->orderBy('kode_divisi', $dir)->orderBy('id_divisi');
                break;
            case 'nama':
                $query->orderBy('nama_divisi', $dir)->orderBy('id_divisi');
                break;
            case 'staff':
                $query->orderBy('karyawans_count', $dir)->orderBy('id_divisi');
                break;
            default:
                // Hierarchy order: Direktur Utama, Manajemen, then divisions.
                $query->ordered();
        }

        return $query;
    }

    /**
     * Mirrors Request::filled(): a string filter is absent when it is null or blank.
     */
    private function hasFilter(mixed $value): bool
    {
        return $value !== null && trim((string) $value) !== '';
    }
}
