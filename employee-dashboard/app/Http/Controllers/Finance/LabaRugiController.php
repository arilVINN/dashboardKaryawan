<?php

namespace App\Http\Controllers\Finance;

use App\Actions\NotifyFinanceUsers;
use App\Exceptions\LaporanSudahDiverifikasiException;
use App\Helpers\FormatAkuntansi;
use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\KLabaRugi;
use App\Services\Finance\VerifikasiLaporanService;
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
    public function index(Request $request): \Illuminate\View\View
    {
        $user     = Auth::user();
        $divisiId = $user->karyawan?->divisi_id_divisi;

        $periode = $request->input('periode', now()->format('Y-m'));
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periode)) {
            $periode = now()->format('Y-m');
        }

        $mingguKe = (string) $request->input('minggu_ke', '');
        if (! in_array($mingguKe, ['1', '2', '3', '4', '5'], true)) {
            $mingguKe = '';
        }

        $query = KLabaRugi::where('divisi_id_divisi', $divisiId)
            ->with('diverifikasiOleh.karyawan')
            ->where('periode', $periode)
            ->latest();

        if ($mingguKe !== '') {
            $query->where('minggu_ke', (int) $mingguKe);
        }

        $riwayat = $query->paginate(10)->withQueryString();

        return view('kadiv.laporanLabaRugi', compact('riwayat', 'periode', 'mingguKe'));
    }

    /**
     * Simpan laporan laba/rugi mingguan dari Kadiv divisi.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user     = Auth::user();
        $divisiId = $user->karyawan?->divisi_id_divisi;

        $data = $request->validate([
            'periode'                    => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'minggu_ke'                  => ['required', 'integer', 'min:1', 'max:5'],
            'tanggal_mulai'              => ['required', 'date'],
            'tanggal_selesai'            => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'pendapatan_operasional'     => ['nullable', 'numeric', 'min:0'],
            'pendapatan_non_operasional' => ['nullable', 'numeric', 'min:0'],
            'beban_operasional'          => ['nullable', 'numeric', 'min:0'],
            'beban_non_operasional'      => ['nullable', 'numeric', 'min:0'],
            'total_pendapatan'           => ['nullable', 'numeric', 'min:0'],
            'total_pengeluaran'          => ['nullable', 'numeric', 'min:0'],
            'bukti_laporan'              => ['required', 'file', 'mimes:pdf,xlsx,xls,jpg,jpeg,png', 'max:5120'],
            'catatan'                    => ['nullable', 'string', 'max:500'],
        ], [
            'periode.regex'              => 'Format periode harus YYYY-MM, contoh: 2026-11.',
            'minggu_ke.min'              => 'Minggu ke harus antara 1 sampai 5.',
            'minggu_ke.max'              => 'Minggu ke harus antara 1 sampai 5.',
            'bukti_laporan.required'     => 'File bukti laporan wajib diunggah.',
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

        // Hitung pengelompokan aktivitas bisnis: Operasional & Non-Operasional
        $pendOp = (float) ($data['pendapatan_operasional'] ?? 0);
        $pendNonOp = (float) ($data['pendapatan_non_operasional'] ?? 0);
        $totalPendapatan = isset($data['pendapatan_operasional'])
            ? ($pendOp + $pendNonOp)
            : (float) ($data['total_pendapatan'] ?? 0);
        if (!isset($data['pendapatan_operasional'])) {
            $pendOp = $totalPendapatan;
        }

        $bebanOp = (float) ($data['beban_operasional'] ?? 0);
        $bebanNonOp = (float) ($data['beban_non_operasional'] ?? 0);
        $totalPengeluaran = isset($data['beban_operasional'])
            ? ($bebanOp + $bebanNonOp)
            : (float) ($data['total_pengeluaran'] ?? 0);
        if (!isset($data['beban_operasional'])) {
            $bebanOp = $totalPengeluaran;
        }

        $data['pendapatan_operasional']     = $pendOp;
        $data['pendapatan_non_operasional'] = $pendNonOp;
        $data['beban_operasional']          = $bebanOp;
        $data['beban_non_operasional']      = $bebanNonOp;
        $data['total_pendapatan']           = $totalPendapatan;
        $data['total_pengeluaran']          = $totalPengeluaran;
        $data['laba_operasional']           = $pendOp - $bebanOp;
        $data['laba_bersih']                = FormatAkuntansi::hitungLaba($totalPendapatan, $totalPengeluaran);
        $data['margin_persen']              = FormatAkuntansi::hitungMargin($totalPendapatan, $totalPengeluaran);

        $count = KLabaRugi::count();
        $data['id_klr']           = 'KLR' . str_pad($count + 1, 5, '0', STR_PAD_LEFT);
        $data['status_verifikasi'] = KLabaRugi::STATUS_MENUNGGU;
        $data['tanggal_kirim']     = now()->toDateString();
        $data['divisi_id_divisi']  = $divisiId;
        $data['user_id_user']      = $user->id_user;

        $laporan = KLabaRugi::create($data);

        $pengirim = $user->karyawan?->nama ?? $user->username;
        app(NotifyFinanceUsers::class)(
            'Laporan L/R Baru',
            'Laporan laba/rugi periode '.$laporan->periode.' minggu ke-'.$laporan->minggu_ke
                .' dari '.$pengirim.' menunggu verifikasi Finance.'
        );

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
        $divisiList = Divisi::query()->unitNonFinance()->where('status_aktif', 'Aktif')->get();

        return view('finance.laporan.index', compact('laporan', 'periode', 'mingguKe', 'status', 'divisiId', 'divisiList'));
    }

    /**
     * Verifikasi laporan laba/rugi (setujui atau minta revisi).
     */
    public function verifikasi(Request $request, string $id): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'status_verifikasi'  => ['required', 'string', 'in:disetujui,perlu_revisi'],
            'catatan_verifikasi' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            app(VerifikasiLaporanService::class)->putuskan(
                $id,
                $request->user(),
                $data['status_verifikasi'],
                $data['catatan_verifikasi'] ?? null
            );
        } catch (LaporanSudahDiverifikasiException $e) {
            return back()->with('error', $e->getMessage());
        }

        $statusLabel = $data['status_verifikasi'] === 'disetujui' ? 'disetujui' : 'diminta untuk direvisi';

        return back()->with('success', "Laporan berhasil {$statusLabel}.");
    }
}
