<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Karyawan;
use App\Models\Divisi;
use App\Models\Tugas;
use App\Models\Pesan;
use App\Models\SuratIzin;
use Illuminate\Validation\Rule;

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

        $totalTugas       = Tugas::count();
        $tugasSelesai     = Tugas::statusEfektif(Tugas::STATUS_SUDAH_ACC)->count();
        $persentaseTugas = $totalTugas > 0 ? round(($tugasSelesai / $totalTugas) * 100, 2) : 0;

        $totalPesanPerusahaan = Pesan::count();

        $totalKaryawanAktif = Karyawan::where('aktif', true)->count();
        $totalKeluarTahunIni = Karyawan::whereNotNull('tanggal_keluar')
            ->whereYear('tanggal_keluar', now()->year)
            ->count();

        // 5 divisi terbesar (untuk widget dan JSON)
        $divisiTerbesar = Divisi::withCount('karyawans')
            ->orderByDesc('karyawans_count')
            ->take(5)
            ->get();

        // Menghitung total karyawan nonaktif (berdasarkan kolom aktif false atau tanggal_keluar terisi)
        $totalKaryawanTidakAktif = Karyawan::where(function ($query) {
            $query->where('aktif', false)
                ->orWhereNotNull('tanggal_keluar');
        })->count();

        // JSON (API), hanya kalau diminta lewat Accept: application/json
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Berhasil mengambil data dashboard HRD',
                'data' => [
                    'metrics' => [
                        'total_staff'              => $totalStaff,
                        'total_divisi'             => $totalDivisi,
                        'total_karyawan_aktif'     => $totalKaryawanAktif,
                        'total_karyawan_nonaktif'  => $totalKaryawanTidakAktif,
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
            'divisiTerbesar',
            'totalKaryawanAktif',
            'totalKaryawanTidakAktif',
            'totalKeluarTahunIni'
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

    //untuk histori izin karyawan
    public function getHistoriKaryawan($id)
    {
        // Mengambil data karyawan beserta surat izins, diurutkan dari yang terbaru
        $karyawan = Karyawan::with(['suratIzins' => function ($query) {
            $query->latest();
        }])->where('id_karyawan', $id)->first();

        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $karyawan
        ]);
    }

    public function analitikKaryawan()
    {
        $totalDivisi = Divisi::count();
        $totalKaryawanAktif = Karyawan::where('aktif', true)->count();

        $totalKaryawanTidakAktif = Karyawan::where(function ($query) {
            $query->where('aktif', false)
                ->orWhereNotNull('tanggal_keluar');
        })->count();

        // Mengambil data divisi beserta relasi karyawannya yang aktif
        $divisiList = Divisi::with(['karyawans' => function ($q) {
            $q->where('aktif', true);
        }])->get();

        $gender = Karyawan::where('aktif', true)
            ->selectRaw('jenis_kelamin, COUNT(*) as jumlah')
            ->groupBy('jenis_kelamin')
            ->pluck('jumlah', 'jenis_kelamin');

        // Mengambil data pendidikan langsung dari kolom baru
        $pendidikan = Karyawan::where('aktif', true)
            ->selectRaw('pendidikan, COUNT(*) as jumlah')
            ->groupBy('pendidikan')
            ->pluck('jumlah', 'pendidikan');

        // Mengambil data job_level langsung dari kolom baru
        $jobLevel = Karyawan::where('aktif', true)
            ->selectRaw('job_level, COUNT(*) as jumlah')
            ->groupBy('job_level')
            ->pluck('jumlah', 'job_level');

        // Mengambil data status_karyawan langsung dari kolom baru di migration
        $statusKaryawan = Karyawan::where('aktif', true)
            ->selectRaw('status_karyawan, COUNT(*) as jumlah')
            ->groupBy('status_karyawan')
            ->pluck('jumlah', 'status_karyawan');

        return view('hrd.analitikKaryawan', compact(
            'totalDivisi',
            'totalKaryawanAktif',
            'totalKaryawanTidakAktif',
            'gender',
            'pendidikan',
            'jobLevel',
            'statusKaryawan',
            'divisiList'
        ));
    }

    public function turnoverKaryawan()
    {
        $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $turnoverRaw = Karyawan::whereNotNull('tanggal_keluar')
            ->where('tanggal_keluar', '>=', now()->subMonths(11)->startOfMonth())
            ->select('tanggal_keluar')
            ->get()
            ->reduce(function (array $carry, $karyawan) {
                $key = \Carbon\Carbon::parse($karyawan->tanggal_keluar)->format('Y-m');
                $carry[$key] = ($carry[$key] ?? 0) + 1;

                return $carry;
            }, []);

        $turnoverLabel = [];
        $turnoverData = [];
        for ($i = 11; $i >= 0; $i--) {
            $bulan = now()->subMonths($i);
            $key = $bulan->format('Y-m');
            // Diperbaiki agar tidak terpotong
            $turnoverLabel[] = $namaBulan[$bulan->month - 1] . ' ' . $bulan->format('y');
            $turnoverData[] = $turnoverRaw[$key] ?? 0;
        }

        $totalKeluarTahunIni = Karyawan::whereNotNull('tanggal_keluar')
            ->whereYear('tanggal_keluar', now()->year)
            ->count();

        $totalKaryawanAktif = Karyawan::where('aktif', true)->count();

        // Diperbaiki dari query 'tidak aktif' yang salah menjadi query nonaktif yang benar
        $totalKaryawanTidakAktif = Karyawan::where(function ($query) {
            $query->where('aktif', false)
                ->orWhereNotNull('tanggal_keluar');
        })->count();

        $daftarKeluar = Karyawan::whereNotNull('tanggal_keluar')
            ->with('divisi:id_divisi,nama_divisi')
            ->orderByDesc('tanggal_keluar')
            ->limit(20)
            ->get();

        return view('hrd.turnoverKaryawan', compact(
            'turnoverLabel',
            'turnoverData',
            'totalKeluarTahunIni',
            'totalKaryawanAktif',
            'totalKaryawanTidakAktif',
            'daftarKeluar'
        ));
    }
}
