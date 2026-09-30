<?php

namespace App\Http\Controllers\Kadiv;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\Tugas;
use App\Models\User2;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KadivDashboardController extends Controller
{
    private const APPROVED_REVIEW_STATUSES = ['acc', 'approved', 'disetujui'];

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

        $approvedSubmission = fn ($query) => $query->whereIn(
            DB::raw('LOWER(status_review)'),
            self::APPROVED_REVIEW_STATUSES
        );

        $totalTasks = (clone $divisionTasks)->count();
        $notSubmitted = (clone $divisionTasks)->doesntHave('submitTugas')->count();
        $approved = (clone $divisionTasks)
            ->whereHas('submitTugas', $approvedSubmission)
            ->count();
        $awaitingApproval = (clone $divisionTasks)
            ->has('submitTugas')
            ->whereDoesntHave('submitTugas', $approvedSubmission)
            ->count();

        $staff = Karyawan::query()
            ->where('divisi_id_divisi', $divisionId)
            ->whereHas('user.role', fn ($query) => $query->whereRaw('LOWER(nama_role) = ?', ['staff']))
            ->with('user:id_user,karyawan_id_karyawan,last_login_at')
            ->withCount('tugas')
            ->orderBy('nama')
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
                    'tugas_belum_dikirim' => $notSubmitted,
                    'tugas_belum_di_acc' => $awaitingApproval,
                    'tugas_sudah_di_acc' => $approved,
                    'persentase_penyelesaian' => $totalTasks > 0
                        ? round(($approved / $totalTasks) * 100, 2)
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
