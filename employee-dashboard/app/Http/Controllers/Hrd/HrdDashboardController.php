<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Karyawan;
use App\Models\Divisi;
use App\Models\Tugas;
use App\Models\Pesan;
use App\Models\SuratIzin;

class HrdDashboardController extends Controller
{
    /**
     * GET /api/hrd/dashboard
     * Metrics tingkat perusahaan (Total staff, divisi, % tugas, total pesan) & widget summary.
     */
    public function index(Request $request)
    {
        $totalStaff  = Karyawan::count();
        $totalDivisi = Divisi::count();

        $totalTugas      = Tugas::count();
        $tugasSelesai    = Tugas::statusEfektif(Tugas::STATUS_SUDAH_ACC)->count();
        $persentaseTugas = $totalTugas > 0 ? round(($tugasSelesai / $totalTugas) * 100, 2) : 0;

        $totalPesanPerusahaan = Pesan::count();

        // 5 divisi terbesar (untuk widget dan JSON)
        $divisiTerbesar = Divisi::withCount('karyawans')
            ->orderByDesc('karyawans_count')
            ->take(5)
            ->get();

        // JSON (API), hanya kalau diminta lewat Accept: application/json
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Berhasil mengambil data dashboard HRD',
                'data' => [
                    'metrics' => [
                        'total_staff'              => $totalStaff,
                        'total_divisi'             => $totalDivisi,
                        'tugas_keseluruhan'        => $totalTugas,
                        'tugas_selesai'            => $tugasSelesai,
                        'persentase_tugas_selesai' => $persentaseTugas . '%',
                        'total_pesan_perusahaan'   => $totalPesanPerusahaan,
                    ],
                    'widgets' => [
                        'divisi_terbesar' => $divisiTerbesar->map(fn($d) => [
                            'nama_divisi' => $d->nama_divisi,
                            'total_staff' => $d->karyawans_count,
                        ]),
                    ],
                ],
            ]);
        }

        // Revisi = tugas "berjalan" yang submission terakhirnya diminta revisi.
        $ongoingBase = Tugas::statusEfektif(Tugas::STATUS_BERJALAN);

        $revisiTugas = (clone $ongoingBase)
            ->whereHas('submitTugas', fn($q) => $q->where('status_review', 'revisi'))
            ->count();

        $ongoingTugas = (clone $ongoingBase)
            ->whereDoesntHave('submitTugas', fn($q) => $q->where('status_review', 'revisi'))
            ->count();

        $pendingTugas = Tugas::statusEfektif(Tugas::STATUS_BARU)->count()
            + Tugas::statusEfektif(Tugas::STATUS_MENUNGGU_ACC)->count();

        $total = [
            'karyawan' => $totalStaff,
            'divisi' => $totalDivisi,
            'pesan' => $totalPesanPerusahaan,
        ];

        $tugasBuckets = [
            'ongoing' => $ongoingTugas,
            'pending' => $pendingTugas,
            'revisi' => $revisiTugas,
        ];

        // Halaman Blade
        $divisis = Divisi::withCount('karyawans')
            ->orderBy('id_divisi')
            ->get();

        $pesan = Pesan::orderBy('tanggal_pesan', 'desc')
            ->orderBy('id_pesan', 'desc')
            ->limit(5)
            ->get();

        return view('hrd.dashboard', compact(
            'total',
            'tugasBuckets',
            'totalStaff',
            'totalDivisi',
            'persentaseTugas',
            'totalPesanPerusahaan',
            'divisis',
            'pesan',
            'divisiTerbesar'
        ));
    }

    public function suratIzinPage()
    {
        $suratIzin = SuratIzin::with('karyawan.user.role')
            ->latest()
            ->paginate(5);

        return view('hrd.suratIzin', compact('suratIzin'));
    }

    public function approveSuratIzin(SuratIzin $suratIzin)
    {
        abort_unless($suratIzin->status === 'Pending', 409, 'Surat izin sudah diproses.');

        $suratIzin->update(['status' => 'Disetujui']);

        return back()->with('success', 'Surat izin berhasil disetujui.');
    }

    public function rejectSuratIzin(SuratIzin $suratIzin)
    {
        abort_unless($suratIzin->status === 'Pending', 409, 'Surat izin sudah diproses.');

        $suratIzin->update(['status' => 'Ditolak']);

        return back()->with('success', 'Surat izin berhasil ditolak.');
    }

    public function analitikKaryawan()
    {
        $totalDivisi = Divisi::count();
        $totalKaryawanAktif = Karyawan::where('aktif', true)->count();

        $gender = Karyawan::where('aktif', true)
            ->selectRaw('jenis_kelamin, COUNT(*) as jumlah')
            ->groupBy('jenis_kelamin')
            ->pluck('jumlah', 'jenis_kelamin');

        $pendidikan = Karyawan::where('aktif', true)
            ->selectRaw('pendidikan, COUNT(*) as jumlah')
            ->groupBy('pendidikan')
            ->pluck('jumlah', 'pendidikan');

        $jobLevel = Karyawan::where('aktif', true)
            ->selectRaw('job_level, COUNT(*) as jumlah')
            ->groupBy('job_level')
            ->pluck('jumlah', 'job_level');

        $statusKaryawan = Karyawan::where('aktif', true)
            ->selectRaw("
                CASE
                    WHEN tanggal_akhir_kontrak IS NULL THEN 'Tetap'
                    ELSE 'Tidak Tetap'
                END as status_karyawan,
                COUNT(*) as jumlah
            ")
            ->groupByRaw("CASE WHEN tanggal_akhir_kontrak IS NULL THEN 'Tetap' ELSE 'Tidak Tetap' END")
            ->pluck('jumlah', 'status_karyawan');

        $divisiList = Divisi::withCount(['karyawans' => fn($q) => $q->where('aktif', true)])->get();

        return view('hrd.analitikKaryawan', compact(
            'totalDivisi',
            'totalKaryawanAktif',
            'gender',
            'pendidikan',
            'jobLevel',
            'statusKaryawan',
            'divisiList'
        ));
    }
}
