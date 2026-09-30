<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Karyawan;
use App\Models\Divisi;
use App\Models\Tugas;
use App\Models\Pesan;

class HrdDashboardController extends Controller
{
    /**
     * GET /api/hrd/dashboard
     * Metrics tingkat perusahaan (Total staff, divisi, % tugas, total pesan) & widget summary.
     */
    public function index(Request $request)
    {
        $totalStaff = Karyawan::count();
        $totalDivisi = Divisi::count();
        
        $totalTugas = Tugas::count();
        $tugasSelesai = Tugas::where('status', 'selesai')->count();
        $persentaseTugas = $totalTugas > 0 ? round(($tugasSelesai / $totalTugas) * 100, 2) : 0;

        $user = $request->user();
        
        // Pesan global (bisa berdasarkan seluruh sistem atau yang melibatkan HRD ini)
        // Menurut instruksi, metrics "tingkat perusahaan" mungkin total keseluruhan.
        $totalPesanPerusahaan = Pesan::count();
        
        // Widget summary divisi (contoh: 3 divisi teraktif)
        $divisiSummary = Divisi::withCount('karyawans')
            ->orderByDesc('karyawans_count')
            ->take(5)
            ->get()
            ->map(function ($div) {
                return [
                    'nama_divisi' => $div->nama_divisi,
                    'total_staff' => $div->karyawans_count
                ];
            });

        return response()->json([
            'message' => 'Berhasil mengambil data dashboard HRD',
            'data' => [
                'metrics' => [
                    'total_staff' => $totalStaff,
                    'total_divisi' => $totalDivisi,
                    'tugas_keseluruhan' => $totalTugas,
                    'tugas_selesai' => $tugasSelesai,
                    'persentase_tugas_selesai' => $persentaseTugas . '%',
                    'total_pesan_perusahaan' => $totalPesanPerusahaan
                ],
                'widgets' => [
                    'divisi_terbesar' => $divisiSummary
                ]
            ]
        ]);
    }
}
