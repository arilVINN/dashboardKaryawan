<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\KPemasukan;
use App\Models\KPengeluaran;
use App\Models\KPermintDana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard utama Keuangan — hanya untuk Kadiv Finance.
 */
class KeuanganController extends Controller
{
    /**
     * Halaman dashboard keuangan: ringkasan KPI + rekap saldo per divisi.
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $periode = $request->input('periode', now()->format('Y-m'));
        [$tahun, $bulan] = explode('-', $periode);

        // ── KPI Cards ────────────────────────────────────────────────────
        $totalKasMasuk = KPemasukan::whereYear('tanggal_masuk', $tahun)
            ->whereMonth('tanggal_masuk', $bulan)
            ->sum('nominal');

        $totalKasKeluar = KPengeluaran::whereYear('tanggal_cair', $tahun)
            ->whereMonth('tanggal_cair', $bulan)
            ->sum('nominal');

        $sisaKas = $totalKasMasuk - $totalKasKeluar;

        $permintaanMenunggu = KPermintDana::where('status', KPermintDana::STATUS_MENUNGGU)->count();

        // ── Rekap Saldo Per Divisi ─────────────────────────────────────
        $rekapDivisi = Divisi::where('kode_divisi', '!=', 'FIN')
            ->where('status_aktif', 'Aktif')
            ->withSum(['alokasiAnggaran as total_alokasi' => function ($q) use ($periode) {
                $q->where('periode', $periode);
            }], 'nominal_alokasi')
            ->withSum(['pengeluaran as total_terpakai' => function ($q) use ($tahun, $bulan) {
                $q->whereYear('tanggal_cair', $tahun)->whereMonth('tanggal_cair', $bulan);
            }], 'nominal')
            ->get()
            ->map(function ($divisi) {
                $divisi->sisa_pagu = ($divisi->total_alokasi ?? 0) - ($divisi->total_terpakai ?? 0);
                return $divisi;
            });

        return view('finance.dashboard', compact(
            'periode',
            'totalKasMasuk',
            'totalKasKeluar',
            'sisaKas',
            'permintaanMenunggu',
            'rekapDivisi',
        ));
    }

    /**
     * Halaman rekap laba/rugi per divisi (semua periode & minggu).
     */
    public function rekapDivisi(Request $request): \Illuminate\View\View
    {
        $periode  = $request->input('periode', now()->format('Y-m'));
        $mingguKe = $request->input('minggu_ke', null); // null = semua minggu

        $query = Divisi::where('kode_divisi', '!=', 'FIN')
            ->where('status_aktif', 'Aktif')
            ->with(['laporanLabaRugi' => function ($q) use ($periode, $mingguKe) {
                $q->where('periode', $periode)
                  ->where('status_verifikasi', '!=', 'menunggu');
                if ($mingguKe) {
                    $q->where('minggu_ke', $mingguKe);
                }
            }])
            ->get();

        $rekapDivisi = $query->map(function ($divisi) {
            $laporan = $divisi->laporanLabaRugi;
            $divisi->total_pendapatan_rekap  = $laporan->sum('total_pendapatan');
            $divisi->total_pengeluaran_rekap = $laporan->sum('total_pengeluaran');
            $divisi->laba_bersih_rekap       = $laporan->sum('laba_bersih');
            $divisi->laporan_list            = $laporan;
            return $divisi;
        });

        return view('finance.rekapDivisi', compact('periode', 'mingguKe', 'rekapDivisi'));
    }

    /**
     * Export rekap laba/rugi divisi ke Excel (.xlsx).
     */
    public function export(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $periode  = $request->input('periode', now()->format('Y-m'));
        $mingguKe = $request->input('minggu_ke');
        $divisiId = $request->input('divisi_id');

        $fileName = 'Rekap_Laba_Rugi_' . $periode . ($mingguKe ? '_M' . $mingguKe : '') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\RekapLabaRugiExport($periode, $mingguKe, $divisiId),
            $fileName
        );
    }
}
