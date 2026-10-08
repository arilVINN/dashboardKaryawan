<?php

namespace App\Queries;

use App\Models\Karyawan;
use Illuminate\Database\Eloquent\Builder;

class HrdKaryawanQuery
{
    /**
     * Kolom yang boleh dipakai untuk ORDER BY (whitelist, anti SQL injection).
     */
    private const SORT_COLUMNS = [
        'id' => 'id_karyawan',
        'nama' => 'nama',
        'jabatan' => 'jabatan',
    ];

    public function apply(array $filters): Builder
    {
        $dir = ($filters['dir'] ?? null) === 'desc' ? 'desc' : 'asc';

        $query = Karyawan::with(['divisi', 'user.role'])
            ->withCount('tugas');

        $divisi = $filters['divisi'] ?? null;
        if ($this->hasFilter($divisi)) {
            $query->where('divisi_id_divisi', $divisi);
        }

        $jabatan = $filters['jabatan'] ?? null;
        if ($this->hasFilter($jabatan)) {
            $query->where('jabatan', $jabatan);
        }

        $q = $filters['q'] ?? null;
        if ($this->hasFilter($q)) {
            $query->whereRaw('LOWER(nama) LIKE ?', ['%'.mb_strtolower($q).'%']);
        }

        $status = $filters['status'] ?? null;
        if ($status === 'aktif') {
            $query->whereHas('user');
        } elseif ($status === 'belum') {
            $query->whereDoesntHave('user');
        }

        if (($filters['sort'] ?? null) === 'divisi') {
            $query->orderByRaw(
                "(select nama_divisi from divisis where divisis.id_divisi = karyawans.divisi_id_divisi) {$dir}"
            )->orderBy('id_karyawan');
        } else {
            $query->orderBy(self::SORT_COLUMNS[$filters['sort'] ?? null] ?? 'nama', $dir);
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
