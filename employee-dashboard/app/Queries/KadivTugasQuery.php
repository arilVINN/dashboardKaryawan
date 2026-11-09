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

        $query = Tugas::with(['karyawan.divisi', 'submitTugas', 'latestSubmitTugas'])
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
            case 'terbaru':
                $query->orderByDesc('created_at')->orderByDesc('id_tugas');
                break;
            default:
                // Default: waiting for approval first, then new/in-progress,
                // then approved. Each group uses the nearest deadline.
                $query->orderByRaw(
                    'CASE
                        WHEN status = ? THEN 0
                        WHEN status IN (?, ?) THEN 1
                        WHEN status = ? THEN 2
                        ELSE 3
                    END',
                    [
                        Tugas::STATUS_MENUNGGU_ACC,
                        Tugas::STATUS_BARU,
                        Tugas::STATUS_BERJALAN,
                        Tugas::STATUS_SUDAH_ACC,
                    ]
                )
                    ->orderByRaw('CASE WHEN deadline IS NULL THEN 1 ELSE 0 END')
                    ->orderBy('deadline', 'asc')
                    ->orderBy('tanggal_dibuat', 'desc')
                    ->orderBy('id_tugas', 'asc');
        }

        return $query;
    }
}
