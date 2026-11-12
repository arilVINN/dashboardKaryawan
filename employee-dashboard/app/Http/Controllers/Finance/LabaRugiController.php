<?php

namespace App\Http\Controllers\Finance;

use App\Helpers\FormatAkuntansi;
use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\KLabaRugi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Manajemen Laporan Laba/Rugi:
 *   - Kadiv divisi lain: submit laporan mingguan beserta bukti file.
 *   - Kadiv Finance: lihat semua laporan & verifikasi.
 */
class LabaRugiController extends Controller
{
    // ═══════════════════════════════════════════════════════════════════════════
    // BAGIAN KADIV — Mengirim laporan laba/rugi divisi
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Halaman form laporan & riwayat laporan divisi user saat ini.
     */
    public function index(): \Illuminate\View\View
    {
        $user     = Auth::user();
        $divisiId = $user->karyawan?->divisi_id_divisi;

        $riwayat = KLabaRugi::where('divisi_id_divisi', $divisiId)
            ->with('diverifikasiOleh.karyawan')
            ->latest()
            ->paginate(10);

        return view('kadiv.laporanLabaRugi', compact('riwayat'));
    }

    /**
     * Simpan laporan laba/rugi mingguan dari Kadiv divisi.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user     = Auth::user();
        $divisiId = $user->karyawan?->divisi_id_divisi;

        $data = $request->validate([
            'periode'           => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'minggu_ke'         => ['required', 'integer', 'min:1', 'max:5'],
            'tanggal_mulai'     => ['required', 'date'],
            'tanggal_selesai'   => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'total_pendapatan'  => ['required', 'numeric', 'min:0'],
            'total_pengeluaran' => ['required', 'numeric', 'min:0'],
            'bukti_laporan'     => ['required', 'file', 'mimes:pdf,xlsx,xls,jpg,jpeg,png', 'max:5120'],
            'catatan'           => ['nullable', 'string', 'max:500'],
        ], [
            'periode.regex'       => 'Format periode harus YYYY-MM, contoh: 2026-11.',
            'minggu_ke.min'       => 'Minggu ke harus antara 1 sampai 5.',
            'minggu_ke.max'       => 'Minggu ke harus antara 1 sampai 5.',
            'bukti_laporan.required' => 'File bukti laporan wajib diunggah.',
        ]);

        // Cek laporan minggu tersebut belum ada
        $existing = KLabaRugi::where('divisi_id_divisi', $divisiId)
            ->where('periode', $data['periode'])
            ->where('minggu_ke', $data['minggu_ke'])
            ->first();

        if ($existing) {
            return back()
                ->withInput()
                ->with('error', "Laporan Minggu ke-{$data['minggu_ke']} periode {$data['periode']} sudah pernah dikirim.");
        }

        // Upload file bukti laporan
        $data['bukti_laporan'] = $request->file('bukti_laporan')
            ->store('keuangan/laporan', 'public');

        // Hitung laba bersih dan margin
        $pendapatan  = (float) $data['total_pendapatan'];
        $pengeluaran = (float) $data['total_pengeluaran'];

        $data['laba_bersih']   = FormatAkuntansi::hitungLaba($pendapatan, $pengeluaran);
        $data['margin_persen'] = FormatAkuntansi::hitungMargin($pendapatan, $pengeluaran);

        $count = KLabaRugi::count();
        $data['id_klr']           = 'KLR' . str_pad($count + 1, 5, '0', STR_PAD_LEFT);
        $data['status_verifikasi'] = KLabaRugi::STATUS_MENUNGGU;
        $data['tanggal_kirim']     = now()->toDateString();
        $data['divisi_id_divisi']  = $divisiId;
        $data['user_id_user']      = $user->id_user;

        KLabaRugi::create($data);

        return back()->with('success', "Laporan Laba/Rugi Minggu ke-{$data['minggu_ke']} berhasil dikirim ke Finance.");
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // BAGIAN FINANCE — Melihat semua laporan & verifikasi
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Daftar semua laporan laba/rugi masuk (Finance view).
     */
    public function daftarLaporan(Request $request): \Illuminate\View\View
    {
        $periode  = $request->input('periode', now()->format('Y-m'));
        $mingguKe = $request->input('minggu_ke', null);
        $status   = $request->input('status', 'semua');
        $divisiId = $request->input('divisi_id_divisi', null);

        $query = KLabaRugi::with('divisi', 'pelapor.karyawan', 'diverifikasiOleh.karyawan')
            ->where('periode', $periode)
            ->latest();

        if ($mingguKe) {
            $query->where('minggu_ke', $mingguKe);
        }

        if ($divisiId) {
            $query->where('divisi_id_divisi', $divisiId);
        }

        if ($status && $status !== 'semua') {
            $query->where('status_verifikasi', $status);
        }

        $laporan    = $query->paginate(15);
        $divisiList = Divisi::where('kode_divisi', '!=', 'FIN')->where('status_aktif', 'Aktif')->get();

        return view('finance.laporan.index', compact('laporan', 'periode', 'mingguKe', 'status', 'divisiId', 'divisiList'));
    }

    /**
     * Verifikasi laporan laba/rugi (setujui atau minta revisi).
     */
    public function verifikasi(Request $request, string $id): \Illuminate\Http\RedirectResponse
    {
        $laporan = KLabaRugi::findOrFail($id);

        $data = $request->validate([
            'status_verifikasi'  => ['required', 'string', 'in:disetujui,perlu_revisi'],
            'catatan_verifikasi' => ['nullable', 'string', 'max:500'],
        ]);

        $laporan->update([
            'status_verifikasi'         => $data['status_verifikasi'],
            'catatan_verifikasi'        => $data['catatan_verifikasi'],
            'diverifikasi_oleh_id_user' => Auth::id(),
        ]);

        $statusLabel = $data['status_verifikasi'] === 'disetujui' ? 'disetujui' : 'diminta untuk direvisi';

        return back()->with('success', "Laporan berhasil {$statusLabel}.");
    }
}
