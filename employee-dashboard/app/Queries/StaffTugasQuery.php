<?php

namespace App\Queries;

use App\Models\Tugas;
use App\Models\User2;
use Illuminate\Database\Eloquent\Builder;

class StaffTugasQuery
{
    private function __construct(private string $karyawanId)
    {
    }

    public static function forStaff(User2 $staff): self
    {
        return new self($staff->karyawan_id_karyawan);
    }

    public function apply(array $filters): Builder
    {
        $query = Tugas::where('karyawan_id_karyawan', $this->karyawanId);

        $q = $filters['q'] ?? null;
        if ($q !== null && trim((string) $q) !== '') {
            $query->whereRaw('LOWER(judul_tugas) LIKE ?', ['%' . mb_strtolower($q) . '%']);
        }

        $status = $filters['status'] ?? null;
        if (in_array($status, Tugas::ALL_STATUSES, true)) {
            $query->statusEfektif($status);
        }

        $dir = ($filters['dir'] ?? null) === 'asc' ? 'asc' : 'desc';
        switch ($filters['sort'] ?? null) {
            case 'judul':
                $query->orderBy('judul_tugas', $dir);
                break;
            case 'tenggat':
                $query->orderBy('deadline', $dir);
                break;
            case 'status':
                $query->orderBy('status', $dir);
                break;
            default:
                $query->orderBy('tanggal_update', 'desc')->orderBy('tanggal_dibuat', 'desc');
        }

        return $query;
    }
}
