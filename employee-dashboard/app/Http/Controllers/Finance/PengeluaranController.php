<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Divisi;
use App\Models\KPengeluaran;
use App\Models\KPermintDana;
use App\Models\Notifikasi;
use App\Services\Finance\JurnalEntryService;
use App\Support\FileStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Manajemen Pengeluaran / Pencairan Dana — hanya untuk Kadiv Finance.
 */
class PengeluaranController extends Controller
{
    /**
     * Daftar pengeluaran, dapat difilter berdasarkan periode dan divisi.
     */
    public function index(Request $request): View
    {
        $periode = $request->input('periode', now()->format('Y-m'));
        $divisiId = $request->input('divisi_id_divisi', null);
        [$tahun, $bulan] = explode('-', $periode);

        $query = KPengeluaran::with('divisi', 'createdBy', 'permintaanDana')
            ->whereYear('tanggal_cair', $tahun)
            ->whereMonth('tanggal_cair', $bulan);

        if ($divisiId) {
            $query->where('divisi_id_divisi', $divisiId);
        }

        $pengeluaran = $query->latest('tanggal_cair')->paginate(15);
        $totalPeriode = $query->sum('nominal');
        $divisiList = Divisi::query()->unitNonFinance()->where('status_aktif', 'Aktif')->get();
        $permintaanSiapCair = KPermintDana::with('divisi', 'diajukanOleh.karyawan')
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
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nominal' => ['required', 'numeric', 'min:1'],
            'tanggal_cair' => ['required', 'date'],
            'metode_pembayaran' => ['required', 'string', 'in:'.implode(',', KPengeluaran::METODE_OPTIONS)],
            'bukti_pencairan' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'divisi_id_divisi' => ['required', 'string', 'exists:divisis,id_divisi'],
        ]);

        if ($request->hasFile('bukti_pencairan')) {
            $data['bukti_pencairan'] = $request->file('bukti_pencairan')
                ->store('keuangan/pengeluaran', 'private');
        }

        $count = KPengeluaran::count();
        $data['id_kpengeluaran'] = 'KO'.str_pad($count + 1, 6, '0', STR_PAD_LEFT);
        $data['kode_pengeluaran'] = 'OUT'.now()->format('ym').str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        $data['user_id_user'] = Auth::id();

        DB::transaction(function () use ($data) {
            $pengeluaran = KPengeluaran::create($data);

            app(JurnalEntryService::class)->createFromPengeluaran($pengeluaran);
        });

        return back()->with('success', 'Pengeluaran berhasil dicatat.');
    }

    /**
     * Cairkan permintaan dana yang sudah disetujui:
     * - Tandai status permintaan menjadi 'dicairkan'
     * - Buat record kpengeluaran baru secara otomatis
     */
    public function cairkan(Request $request, string $idKpd): RedirectResponse
    {
        $permintaan = KPermintDana::with('divisi')->findOrFail($idKpd);

        // The authoritative status check happens inside the transaction with
        // a row lock; see below. This early check only gives a fast response
        // for the common case and is intentionally not trusted.
        if (! $permintaan->isDisetujui()) {
            return back()->with('error', 'Hanya pengajuan berstatus Disetujui yang dapat dicairkan.');
        }

        $data = $request->validate([
            'tanggal_cair' => ['required', 'date'],
            'metode_pembayaran' => ['required', 'string', 'in:'.implode(',', KPengeluaran::METODE_OPTIONS)],
            'bukti_pencairan' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ]);

        $buktiBayar = null;
        if ($request->hasFile('bukti_pencairan')) {
            $buktiBayar = $request->file('bukti_pencairan')
                ->store('keuangan/pencairan', 'private');
        }

        $pengeluaran = null;
        $stale = false;

        DB::transaction(function () use ($idKpd, $data, $buktiBayar, &$permintaan, &$pengeluaran, &$stale) {
            // Lock the request row and re-check status *inside* the transaction
            // so two concurrent disbursements cannot both pass the check.
            /** @var KPermintDana $locked */
            $locked = KPermintDana::query()
                ->whereKey($idKpd)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->isDisetujui()) {
                $stale = true;

                return;
            }

            // Lock the whole pengeluaran table while allocating the next
            // sequence, so concurrent cash-outs cannot reuse the same ID.
            $max = 0;
            foreach (KPengeluaran::query()->lockForUpdate()->pluck('id_kpengeluaran') as $id) {
                if (preg_match('/^KO(\d+)$/', (string) $id, $matches) === 1) {
                    $max = max($max, (int) $matches[1]);
                }
            }
            $sequence = $max + 1;

            $pengeluaran = KPengeluaran::create([
                'id_kpengeluaran' => 'KO'.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT),
                'kode_pengeluaran' => 'OUT'.now()->format('ym').str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
                'kpd_id_kpd' => $locked->id_kpd,
                'nominal' => $locked->nominal_diajukan,
                'tanggal_cair' => $data['tanggal_cair'],
                'metode_pembayaran' => $data['metode_pembayaran'],
                'bukti_pencairan' => $buktiBayar,
                'keterangan' => $data['keterangan'] ?? $locked->judul_pengajuan,
                'divisi_id_divisi' => $locked->divisi_id_divisi,
                'user_id_user' => Auth::id(),
            ]);

            // Jurnal pencairan dana dibuat di transaksi yang sama.
            app(JurnalEntryService::class)->createFromPengeluaran($pengeluaran);

            // Update status permintaan
            $locked->update(['status' => KPermintDana::STATUS_DICAIRKAN]);

            $permintaan = $locked;
            $this->notifyPemohonPencairan($permintaan);
        });

        if ($stale) {
            FileStorage::delete($buktiBayar);

            return back()->with('error', 'Pengajuan ini sudah diproses atau tidak lagi berstatus Disetujui.');
        }

        // Audit log pencairan: user dan waktu direkam otomatis oleh AuditLog,
        // detail menyimpan nilai lama (status disetujui) dan nilai baru.
        $permintaan->loadMissing('divisi');
        AuditLog::catat(
            Auth::id(),
            'pencairan_dana',
            'pencairan_dana',
            (string) $permintaan->id_kpd,
            [
                'user_id' => Auth::id(),
                'user_nama' => Auth::user()?->karyawan?->nama ?? Auth::user()?->username,
                'waktu' => now()->toIso8601String(),
                'divisi_id' => $permintaan->divisi_id_divisi,
                'divisi_nama' => $permintaan->divisi?->nama_divisi,
                'nomor_pengajuan' => $permintaan->nomor_pengajuan,
                'nilai_lama' => [
                    'status' => KPermintDana::STATUS_DISETUJUI,
                    'nominal' => (float) $permintaan->nominal_diajukan,
                ],
                'nilai_baru' => [
                    'status' => KPermintDana::STATUS_DICAIRKAN,
                    'nominal' => (float) ($pengeluaran?->nominal ?? $permintaan->nominal_diajukan),
                    'id_kpengeluaran' => $pengeluaran?->id_kpengeluaran,
                    'tanggal_cair' => $pengeluaran?->tanggal_cair?->toDateString(),
                ],
            ]
        );

        return back()->with('success', 'Dana berhasil dicairkan dan pengeluaran tercatat.');
    }

    /**
     * Kabari Kadiv pemohon bahwa dananya sudah dicairkan.
     */
    private function notifyPemohonPencairan(KPermintDana $permintaan): void
    {
        $penerima = $permintaan->pemohon;

        if (! $penerima) {
            return;
        }

        $actor = Auth::user()?->karyawan?->nama ?? Auth::user()?->username ?? 'Finance';

        Notifikasi::create([
            'id_notifikasi' => Notifikasi::generateId(),
            'judul_notifikasi' => 'Dana Dicairkan',
            'isi_notif' => 'Pengajuan "'.$permintaan->judul_pengajuan.'" ('.$permintaan->nomor_pengajuan.') '
                .'telah dicairkan oleh '.$actor.'.',
            'tanggal_notifikasi' => now()->toDateString(),
            'user_id_user' => $penerima->id_user,
        ]);
    }
}
