<?php

namespace App\Http\Controllers\Kadiv;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\Tugas;
use App\Models\User2;
use App\Queries\KadivStaffQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KadivDashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->authenticatedKadiv($request);

        if (! $user) {
            return response()->json([
                'message' => $request->user()
                    ? 'Akses hanya diberikan kepada Kadiv.'
                    : 'User Kadiv belum terautentikasi.',
            ], $request->user() ? 403 : 401);
        }

        $divisionId = $user->karyawan->divisi_id_divisi;
        $divisionTasks = Tugas::query()->whereHas(
            'karyawan',
            fn ($query) => $query->where('divisi_id_divisi', $divisionId)
        );

        $totalTasks = (clone $divisionTasks)->count();

        // Lima bucket status (status efektif, termasuk turunan "telat").
        $baru = (clone $divisionTasks)
            ->statusEfektif(Tugas::STATUS_BARU)
            ->count();
        $berjalan = (clone $divisionTasks)
            ->statusEfektif(Tugas::STATUS_BERJALAN)
            ->count();
        $menungguAcc = (clone $divisionTasks)
            ->statusEfektif(Tugas::STATUS_MENUNGGU_ACC)
            ->count();
        $sudahAcc = (clone $divisionTasks)
            ->statusEfektif(Tugas::STATUS_SUDAH_ACC)
            ->count();
        $telat = (clone $divisionTasks)
            ->statusEfektif(Tugas::STATUS_TELAT)
            ->count();

        $staff = KadivStaffQuery::forDivision($divisionId)
            ->apply($request->only(['sort', 'dir', 'q']))
            ->get()
            ->map(fn (Karyawan $employee) => [
                'id_karyawan' => $employee->id_karyawan,
                'nama' => $employee->nama,
                'jumlah_tugas_dikerjakan' => $employee->tugas_count,
                'terakhir_login' => $employee->user?->last_login_at?->toISOString(),
            ]);

        return response()->json([
            'data' => [
                'divisi' => [
                    'id_divisi' => $user->karyawan->divisi->id_divisi,
                    'nama_divisi' => $user->karyawan->divisi->nama_divisi,
                ],
                'metrics' => [
                    'tugas_baru' => $baru,
                    'tugas_berjalan' => $berjalan,
                    'tugas_menunggu_di_acc' => $menungguAcc,
                    'tugas_sudah_di_acc' => $sudahAcc,
                    'tugas_telat' => $telat,
                    'persentase_penyelesaian' => $totalTasks > 0
                        ? round(($sudahAcc / $totalTasks) * 100, 2)
                        : 0,
                ],
                'staff' => $staff,
            ],
        ]);
    }

    private function authenticatedKadiv(Request $request): ?User2
    {
        $user = $request->user();

        if (! $user instanceof User2) {
            return null;
        }

        $user->loadMissing(['role', 'karyawan.divisi']);

        if (strtolower($user->role?->nama_role ?? '') !== 'kadiv' || ! $user->karyawan?->divisi) {
            return null;
        }

        return $user;
    }
}
