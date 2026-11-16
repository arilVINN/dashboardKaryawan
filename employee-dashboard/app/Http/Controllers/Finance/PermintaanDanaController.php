<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\KeuanganAldiv;
use App\Models\KPengeluaran;
use App\Models\KPermintDana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Manajemen Permintaan Dana:
 *   - Kadiv Finance: lihat semua pengajuan, approve / reject, dan cairkan.
 *   - Kadiv divisi lain: ajukan dana dan lihat riwayat divisinya.
 */
class PermintaanDanaController extends Controller
{
    // ═══════════════════════════════════════════════════════════════════════════
    // BAGIAN FINANCE — Melihat semua pengajuan & approval
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Daftar semua permintaan dana (Finance view).
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $status   = $request->input('status', '');
        $divisiId = $request->input('divisi_id', null);

        $query = KPermintDana::with('divisi', 'pemohon.karyawan')
            ->latest();

        if ($status && $status !== 'semua') {
            $query->where('status', $status);
        }

        if ($divisiId) {
            $query->where('divisi_id_divisi', $divisiId);
        }

        $permintaan     = $query->paginate(15);
        $jumlahMenunggu = KPermintDana::where('status', KPermintDana::STATUS_MENUNGGU)->count();
        $divisiList     = Divisi::where('kode_divisi', '!=', 'FIN')->where('status_aktif', 'Aktif')->get();

        return view('finance.permintaan.index', compact('permintaan', 'status', 'divisiId', 'divisiList', 'jumlahMenunggu'));
    }

    /**
     * Detail satu permintaan dana (Finance view).
     */
    public function show(string $id): \Illuminate\View\View
    {
        $permintaan = KPermintDana::with('divisi', 'pemohon.karyawan', 'disetujuiOleh.karyawan', 'pengeluaran')
            ->findOrFail($id);

        $periode       = now()->format('Y-m');
        [$tahun, $bln] = explode('-', $periode);
        $divisiId      = $permintaan->divisi_id_divisi;

        $totalAlokasi = KeuanganAldiv::where('divisi_id_divisi', $divisiId)
            ->where('periode', $periode)
            ->sum('nominal_alokasi');

        $totalTerpakai = KPengeluaran::where('divisi_id_divisi', $divisiId)
            ->whereYear('tanggal_cair', $tahun)
            ->whereMonth('tanggal_cair', $bln)
            ->sum('nominal');

        $sisaPagu = $totalAlokasi - $totalTerpakai;

        return view('finance.permintaan.show', compact('permintaan', 'totalAlokasi', 'totalTerpakai', 'sisaPagu'));
    }

    /**
     * Setujui permintaan dana.
     */
    public function approve(Request $request, string $id): \Illuminate\Http\RedirectResponse
    {
        $permintaan = KPermintDana::findOrFail($id);

        if (! $permintaan->isMenunggu()) {
            return back()->with('error', 'Hanya pengajuan berstatus Menunggu yang dapat disetujui.');
        }

        $permintaan->update([
            'status'                  => KPermintDana::STATUS_DISETUJUI,
            'tanggal_disetujui'       => now()->toDateString(),
            'disetujui_oleh_id_user'  => Auth::id(),
            'catatan_finance'         => $request->input('catatan_finance'),
        ]);

        return back()->with('success', 'Permintaan dana berhasil disetujui.');
    }

    /**
     * Tolak permintaan dana (wajib sertakan catatan alasan).
     */
    public function reject(Request $request, string $id): \Illuminate\Http\RedirectResponse
    {
        $permintaan = KPermintDana::findOrFail($id);

        if (! $permintaan->isMenunggu()) {
            return back()->with('error', 'Hanya pengajuan berstatus Menunggu yang dapat ditolak.');
        }

        $data = $request->validate([
            'catatan_finance' => ['required', 'string', 'max:500'],
        ], [
            'catatan_finance.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $permintaan->update([
            'status'                 => KPermintDana::STATUS_DITOLAK,
            'catatan_finance'        => $data['catatan_finance'],
            'disetujui_oleh_id_user' => Auth::id(),
        ]);

        return back()->with('success', 'Permintaan dana telah ditolak.');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // BAGIAN KADIV — Mengajukan dan melihat riwayat
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Form pengajuan dana (semua Kadiv).
     */
    public function create(): \Illuminate\View\View
    {
        $user       = Auth::user();
        $divisiId   = $user->karyawan?->divisi_id_divisi;
        $periode    = now()->format('Y-m');

        // Hitung sisa pagu divisi untuk periode ini
        $totalAlokasi   = KeuanganAldiv::where('divisi_id_divisi', $divisiId)
            ->where('periode', $periode)
            ->sum('nominal_alokasi');

        $totalTerpakai  = KPengeluaran::where('divisi_id_divisi', $divisiId)
            ->whereYear('tanggal_cair', now()->year)
            ->whereMonth('tanggal_cair', now()->month)
            ->sum('nominal');

        $sisaPagu = $totalAlokasi - $totalTerpakai;

        return view('kadiv.pengajuanDana', compact('sisaPagu', 'totalAlokasi', 'totalTerpakai', 'periode'));
    }

    /**
     * Simpan pengajuan dana baru (semua Kadiv).
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'judul_pengajuan'  => ['required', 'string', 'max:250'],
            'keperluan'        => ['required', 'string', 'max:250'],
            'nominal_diajukan' => ['required', 'numeric', 'min:1'],
            'lampiran_proposal' => ['nullable', 'file', 'mimes:pdf,xlsx,xls,doc,docx', 'max:5120'],
        ]);

        if ($request->hasFile('lampiran_proposal')) {
            $data['lampiran_proposal'] = $request->file('lampiran_proposal')
                ->store('keuangan/proposal', 'public');
        }

        $user  = Auth::user();
        $count = KPermintDana::count();

        $divisiKode = strtoupper($user->karyawan?->divisi?->kode_divisi ?? 'XX');
        $data['id_kpd']          = 'KPD' . str_pad($count + 1, 5, '0', STR_PAD_LEFT);
        $data['nomor_pengajuan'] = 'REQ' . now()->format('ym') . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        $data['status']          = KPermintDana::STATUS_MENUNGGU;
        $data['divisi_id_divisi'] = $user->karyawan?->divisi_id_divisi;
        $data['user_id_user']    = $user->id_user;

        KPermintDana::create($data);

        return redirect()->route('kadiv.pengajuanDana.riwayat')
            ->with('success', 'Pengajuan dana berhasil dikirim ke Finance.');
    }

    /**
     * Riwayat pengajuan dana untuk divisi user yang sedang login.
     */
    public function riwayat(): \Illuminate\View\View
    {
        $user     = Auth::user();
        $divisiId = $user->karyawan?->divisi_id_divisi;

        $riwayat = KPermintDana::where('divisi_id_divisi', $divisiId)
            ->with('disetujuiOleh.karyawan')
            ->latest()
            ->paginate(10);

        return view('kadiv.riwayatPengajuanDana', compact('riwayat'));
    }
}
