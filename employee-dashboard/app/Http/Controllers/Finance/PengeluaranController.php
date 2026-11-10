<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\KPengeluaran;
use App\Models\KPermintDana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Manajemen Pengeluaran / Pencairan Dana — hanya untuk Kadiv Finance.
 */
class PengeluaranController extends Controller
{
    /**
     * Daftar pengeluaran, dapat difilter berdasarkan periode dan divisi.
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $periode  = $request->input('periode', now()->format('Y-m'));
        $divisiId = $request->input('divisi_id_divisi', null);
        [$tahun, $bulan] = explode('-', $periode);

        $query = KPengeluaran::with('divisi', 'createdBy', 'permintaanDana')
            ->whereYear('tanggal_cair', $tahun)
            ->whereMonth('tanggal_cair', $bulan);

        if ($divisiId) {
            $query->where('divisi_id_divisi', $divisiId);
        }

        $pengeluaran         = $query->latest('tanggal_cair')->paginate(15);
        $totalPeriode        = $query->sum('nominal');
        $divisiList          = Divisi::where('kode_divisi', '!=', 'FIN')->where('status_aktif', 'Aktif')->get();
        $permintaanSiapCair  = KPermintDana::with('divisi', 'diajukanOleh.karyawan')
            ->where('status', KPermintDana::STATUS_DISETUJUI)
            ->latest('tanggal_disetujui')
            ->get();

        return view('finance.pengeluaran.index', compact(
            'pengeluaran',
            'periode',
            'divisiId',
            'totalPeriode',
            'divisiList',
            'permintaanSiapCair',
        ));
    }

    /**
     * Simpan pengeluaran baru secara langsung (tanpa permintaan dana).
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'nominal'           => ['required', 'numeric', 'min:1'],
            'tanggal_cair'      => ['required', 'date'],
            'metode_pembayaran' => ['required', 'string', 'in:' . implode(',', KPengeluaran::METODE_OPTIONS)],
            'bukti_pencairan'   => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'keterangan'        => ['nullable', 'string', 'max:500'],
            'divisi_id_divisi'  => ['required', 'string', 'exists:divisis,id_divisi'],
        ]);

        if ($request->hasFile('bukti_pencairan')) {
            $data['bukti_pencairan'] = $request->file('bukti_pencairan')
                ->store('keuangan/pengeluaran', 'public');
        }

        $count = KPengeluaran::count();
        $data['id_kpengeluaran']  = 'KO' . str_pad($count + 1, 6, '0', STR_PAD_LEFT);
        $data['kode_pengeluaran'] = 'OUT' . now()->format('ym') . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        $data['user_id_user']     = Auth::id();

        KPengeluaran::create($data);

        return back()->with('success', 'Pengeluaran berhasil dicatat.');
    }

    /**
     * Cairkan permintaan dana yang sudah disetujui:
     * - Tandai status permintaan menjadi 'dicairkan'
     * - Buat record kpengeluaran baru secara otomatis
     */
    public function cairkan(Request $request, string $idKpd): \Illuminate\Http\RedirectResponse
    {
        $permintaan = KPermintDana::with('divisi')->findOrFail($idKpd);

        if (! $permintaan->isDisetujui()) {
            return back()->with('error', 'Hanya pengajuan berstatus Disetujui yang dapat dicairkan.');
        }

        $data = $request->validate([
            'tanggal_cair'      => ['required', 'date'],
            'metode_pembayaran' => ['required', 'string', 'in:' . implode(',', KPengeluaran::METODE_OPTIONS)],
            'bukti_pencairan'   => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'keterangan'        => ['nullable', 'string', 'max:500'],
        ]);

        $buktiBayar = null;
        if ($request->hasFile('bukti_pencairan')) {
            $buktiBayar = $request->file('bukti_pencairan')
                ->store('keuangan/pencairan', 'public');
        }

        DB::transaction(function () use ($permintaan, $data, $buktiBayar) {
            $count = KPengeluaran::count();

            // Buat record pengeluaran
            KPengeluaran::create([
                'id_kpengeluaran'  => 'KO' . str_pad($count + 1, 6, '0', STR_PAD_LEFT),
                'kode_pengeluaran' => 'OUT' . now()->format('ym') . str_pad($count + 1, 4, '0', STR_PAD_LEFT),
                'kpd_id_kpd'       => $permintaan->id_kpd,
                'nominal'          => $permintaan->nominal_diajukan,
                'tanggal_cair'     => $data['tanggal_cair'],
                'metode_pembayaran' => $data['metode_pembayaran'],
                'bukti_pencairan'  => $buktiBayar,
                'keterangan'       => $data['keterangan'] ?? $permintaan->judul_pengajuan,
                'divisi_id_divisi' => $permintaan->divisi_id_divisi,
                'user_id_user'     => Auth::id(),
            ]);

            // Update status permintaan
            $permintaan->update(['status' => KPermintDana::STATUS_DICAIRKAN]);
        });

        return back()->with('success', 'Dana berhasil dicairkan dan pengeluaran tercatat.');
    }
}
