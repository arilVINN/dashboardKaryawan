<?php

namespace App\Queries;

use App\Models\Tugas;
use Illuminate\Database\Eloquent\Builder;

/**
 * Read-only task listing across every division, used by the Direksi
 * (super-admin) monitoring page. Unlike KadivTugasQuery it is not scoped to a
 * single division; the `divisi` filter narrows it down on demand.
 */
class DireksiTugasQuery
{
    public function apply(array $filters): Builder
    {
        $query = Tugas::with(['karyawan.divisi', 'latestSubmitTugas']);

        $divisi = $filters['divisi'] ?? null;
        if ($divisi) {
            $query->whereHas('karyawan', fn ($employee) => $employee->where('divisi_id_divisi', $divisi));
        }

        $staff = $filters['staff'] ?? null;
        if ($staff) {
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
