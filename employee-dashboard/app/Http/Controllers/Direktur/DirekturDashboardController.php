<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Helpers\FormatAkuntansi;
use App\Models\BookingRequest;
use App\Models\Carousel;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\KPemasukan;
use App\Models\KPengeluaran;
use App\Models\KPermintDana;
use App\Models\KLabaRugi;
use App\Models\SuratIzin;
use App\Models\Tugas;
use Illuminate\Http\Request;

class DirekturDashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. HRD Metrics
        $totalKaryawan = Karyawan::where('status_aktif', 'Aktif')
            ->where(function ($q) {
                $q->where('aktif', true)->orWhereNull('aktif');
            })
            ->whereNull('tanggal_keluar')
            ->count();

        $totalDivisi = Divisi::count();

        $suratIzinPending = SuratIzin::where('status', 'menunggu')->count();
        $suratIzinList = SuratIzin::with('karyawan.divisi')
            ->where('status', 'menunggu')
            ->latest()
            ->take(5)
            ->get();

        $bookingPending = BookingRequest::where('status', 'menunggu')->count();
        $bookingList = BookingRequest::with(['pemohon.karyawan.divisi'])
            ->where('status', 'menunggu')
            ->latest()
            ->take(5)
            ->get();

        // 2. Finance Metrics
        $totalPemasukan = (float) KPemasukan::sum('nominal');
        $totalPengeluaran = (float) KPengeluaran::sum('nominal');
        $saldoKas = $totalPemasukan - $totalPengeluaran;

        $pemasukanBulanIni = (float) KPemasukan::whereMonth('tanggal_masuk', now()->month)
            ->whereYear('tanggal_masuk', now()->year)
            ->sum('nominal');

        $pengeluaranBulanIni = (float) KPengeluaran::whereMonth('tanggal_cair', now()->month)
            ->whereYear('tanggal_cair', now()->year)
            ->sum('nominal');

        $permintaanDanaPending = KPermintDana::where('status', KPermintDana::STATUS_MENUNGGU)->count();
        $permintaanDanaList = KPermintDana::with(['divisi', 'user.karyawan'])
            ->where('status', KPermintDana::STATUS_MENUNGGU)
            ->latest()
            ->take(5)
            ->get();

        $laporanLrPending = KLabaRugi::where('status_verifikasi', 'menunggu')->count();

        // 3. Tugas / Operasional Kadiv
        $totalTugas = Tugas::count();
        $tugasBerjalan = Tugas::whereIn('status', ['baru', 'proses', 'dikerjakan', 'revisi'])->count();
        $tugasSelesai = Tugas::where('status', 'selesai')->count();
        $tugasRevisi = Tugas::where('status', 'revisi')->count();

        $tugasTerbaru = Tugas::with('karyawan.divisi')
            ->latest('tanggal_dibuat')
            ->take(6)
            ->get();

        // 4. Ringkasan per Divisi
        $divisiList = Divisi::withCount(['karyawans as jumlah_karyawan' => function ($q) {
            $q->where('status_aktif', 'Aktif')->whereNull('tanggal_keluar');
        }])->get();

        // 5. Carousels
        $carousels = Carousel::query()
            ->with(['user.karyawan', 'divisi'])
            ->latest()
            ->take(5)
            ->get();

        return view('direktur.dashboard', compact(
            'totalKaryawan',
            'totalDivisi',
            'suratIzinPending',
            'suratIzinList',
            'bookingPending',
            'bookingList',
            'totalPemasukan',
            'totalPengeluaran',
            'saldoKas',
            'pemasukanBulanIni',
            'pengeluaranBulanIni',
            'permintaanDanaPending',
            'permintaanDanaList',
            'laporanLrPending',
            'totalTugas',
            'tugasBerjalan',
            'tugasSelesai',
            'tugasRevisi',
            'tugasTerbaru',
            'divisiList',
            'carousels'
        ));
    }
}
