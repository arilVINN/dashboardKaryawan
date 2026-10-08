<?php

namespace App\Queries;

use App\Models\Karyawan;
use App\Models\Tugas;
use Illuminate\Database\Eloquent\Builder;

class KadivTugasQuery
{
    private function __construct(private string $divisiId)
    {
    }

    public static function forDivision(string $divisiId): self
    {
        return new self($divisiId);
    }

    public function apply(array $filters): Builder
    {
        $karyawanIds = Karyawan::where('divisi_id_divisi', $this->divisiId)->pluck('id_karyawan');

        $query = Tugas::with(['karyawan', 'submitTugas'])
            ->whereIn('karyawan_id_karyawan', $karyawanIds);

        $staff = $filters['staff'] ?? null;
        if ($staff && $karyawanIds->contains($staff)) {
            $query->where('karyawan_id_karyawan', $staff);
        }

        $status = $filters['status'] ?? null;
        if (in_array($status, Tugas::ALL_STATUSES, true)) {
            $query->statusEfektif($status);
        }

        $q = $filters['q'] ?? null;
        if ($q !== null && $q !== '') {
            $query->whereRaw('LOWER(judul_tugas) LIKE ?', ['%' . mb_strtolower($q) . '%']);
        }

        $dir = ($filters['dir'] ?? null) === 'asc' ? 'asc' : 'desc';
        switch ($filters['sort'] ?? null) {
            case 'judul':
                $query->orderBy('judul_tugas', $dir);
                break;
            case 'status':
                $query->orderBy('status', $dir);
                break;
            case 'tanggal':
                $query->orderBy('deadline', $dir);
                break;
            default:
                $query->orderBy('tanggal_dibuat', 'desc');
        }

        return $query;
    }
}
