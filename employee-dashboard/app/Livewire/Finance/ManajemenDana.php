<?php

namespace App\Livewire\Finance;

use App\Exceptions\LaporanSudahDiverifikasiException;
use App\Helpers\FormatAkuntansi;
use App\Models\Divisi;
use App\Models\KLabaRugi;
use App\Models\KPermintDana;
use App\Services\Finance\VerifikasiLaporanService;
use App\Support\StatusPalette;
use Carbon\Carbon;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Halaman gabungan Permintaan Dana dan Verifikasi Laporan L/R untuk Kadiv
 * Finance. Filter, dialog, toast, dan pembaruan baris berjalan lewat Livewire.
 */
class ManajemenDana extends Component
{
    use WithPagination;

    #[Url(as: 'tab')]
    public string $tab = 'permintaan';

    #[Url(as: 'status')]
    public string $status = '';

    #[Url(as: 'divisi_id')]
    public string $divisiId = '';

    #[Url(as: 'periode')]
    public string $periode = '';

    public bool $showVerifikasiModal = false;

    public bool $showReportDetail = false;

    public string $verifikasiId = '';

    public string $catatanVerifikasi = '';

    /** @var array<string, mixed>|null */
    public ?array $verifikasiData = null;

    /** @var array<string, mixed>|null */
    public ?array $reportDetail = null;

    private const BULAN = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    private const BULAN_SINGKAT = [
        1 => 'Jan',
        2 => 'Feb',
        3 => 'Mar',
        4 => 'Apr',
        5 => 'Mei',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Agu',
        9 => 'Sep',
        10 => 'Okt',
        11 => 'Nov',
        12 => 'Des',
    ];

    public function mount(...$params): void
    {
        $this->ensureFinanceAccess();

        $tab = $params['tab'] ?? request()->query('tab', $this->tab);
        $periode = $params['periode'] ?? request()->query('periode', $this->periode ?: now()->format('Y-m'));
        $status = $params['status'] ?? request()->query('status', $this->status);
        $divisiId = $params['divisi_id'] ?? $params['divisiId'] ?? request()->query('divisi_id', $this->divisiId);

        $this->tab = $this->normalizeTab((string) $tab);
        $this->periode = $this->normalizePeriode((string) $periode);
        $this->status = $this->normalizeStatus((string) $status, $this->tab);
        $this->divisiId = (string) $divisiId;
    }

    public function setTab(string $tab): void
    {
        $this->tab = $this->normalizeTab($tab);
        $this->status = $this->normalizeStatus($this->status, $this->tab);
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->status = $this->normalizeStatus($this->status, $this->tab);
        $this->resetPage();
    }

    public function updatedDivisiId(): void
    {
        $this->resetPage();
    }

    public function updatedPeriode(): void
    {
        $this->periode = $this->normalizePeriode($this->periode);
        $this->resetPage();
    }

    public function previous(): void
    {
        $this->periode = $this->anchor()->subMonthNoOverflow()->format('Y-m');
        $this->resetPage();
    }

    public function next(): void
    {
        $this->periode = $this->anchor()->addMonthNoOverflow()->format('Y-m');
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->status = '';
        $this->divisiId = '';
        $this->periode = now()->format('Y-m');
        $this->resetPage();
    }

    public function openVerifikasi(string $id): void
    {
        $this->ensureFinanceAccess();

        $laporan = KLabaRugi::with(['divisi', 'pengirim.karyawan'])->findOrFail($id);
        $this->authorize('verify', $laporan);

        if (! $laporan->isMenunggu()) {
            $this->dispatch('laporan-toast', message: 'Laporan ini sudah diverifikasi', type: 'error');

            return;
        }

        $this->verifikasiId = $laporan->id_klr;
        $this->catatanVerifikasi = '';
        $this->verifikasiData = $this->reportData($laporan);
        $this->resetValidation();
        $this->showVerifikasiModal = true;
    }

    public function closeVerifikasi(): void
    {
        $this->showVerifikasiModal = false;
        $this->verifikasiId = '';
        $this->catatanVerifikasi = '';
        $this->verifikasiData = null;
        $this->resetValidation();
    }

    public function verifikasi(): void
    {
        $this->ensureFinanceAccess();

        $this->validate([
            'catatanVerifikasi' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            app(VerifikasiLaporanService::class)->verifikasi(
                $this->verifikasiId,
                auth()->user(),
                filled($this->catatanVerifikasi) ? $this->catatanVerifikasi : null
            );
        } catch (LaporanSudahDiverifikasiException $e) {
            $this->closeVerifikasi();
            $this->dispatch('laporan-toast', message: $e->getMessage(), type: 'error');

            return;
        }

        $this->closeVerifikasi();
        $this->dispatch('laporan-toast', message: 'Laporan diverifikasi', type: 'success');
    }

    public function openReportDetail(string $id): void
    {
        $this->ensureFinanceAccess();

        $laporan = KLabaRugi::with(['divisi', 'pengirim.karyawan', 'diverifikasiOleh.karyawan'])->findOrFail($id);
        $this->reportDetail = $this->reportData($laporan);
        $this->showReportDetail = true;
    }

    public function closeReportDetail(): void
    {
        $this->showReportDetail = false;
        $this->reportDetail = null;
    }

    public function periodeLabel(): string
    {
        [$tahun, $bulan] = explode('-', $this->periode);

        return (self::BULAN[(int) $bulan] ?? $bulan).' '.$tahun;
    }

    public function rupiah(float|int|null $nominal): string
    {
        return FormatAkuntansi::formatRupiah($nominal);
    }

    public function margin(float|int|null $margin): string
    {
        return FormatAkuntansi::formatMargin($margin);
    }

    public function tanggalPengajuan(?Carbon $tanggal): string
    {
        if (! $tanggal) {
            return '-';
        }

        return $tanggal->format('d').' '
            .(self::BULAN_SINGKAT[(int) $tanggal->month] ?? $tanggal->month).' '
            .$tanggal->format('Y, H:i');
    }

    /**
     * @return array{label: string, bg: string, border: string, dot: string, text: string}
     */
    public function statusMetaPermintaan(?string $status): array
    {
        return match ($status) {
            KPermintDana::STATUS_MENUNGGU => StatusPalette::pill('menunggu', 'Menunggu'),
            KPermintDana::STATUS_DISETUJUI => StatusPalette::pill('disetujui', 'Disetujui'),
            KPermintDana::STATUS_DITOLAK => StatusPalette::pill('ditolak', 'Ditolak'),
            default => StatusPalette::pill('dicairkan', 'Dicairkan'),
        };
    }

    /**
     * @return array{label: string, bg: string, border: string, dot: string, text: string}
     */
    public function statusMetaLaporan(?string $status): array
    {
        return match ($status) {
            KLabaRugi::STATUS_DISETUJUI => StatusPalette::pill('terverifikasi', 'Terverifikasi'),
            KLabaRugi::STATUS_PERLU_REVISI => StatusPalette::pill('perlu_revisi', 'Perlu revisi'),
            default => StatusPalette::pill('menunggu', 'Menunggu'),
        };
    }

    public function render(): View
    {
        $this->ensureFinanceAccess();

        $divisiList = Divisi::query()->unitNonFinance()
            ->where('status_aktif', 'Aktif')
            ->ordered()
            ->get();

        $permintaan = null;
        $laporan = null;

        if ($this->tab === 'permintaan') {
            $permintaan = KPermintDana::with(['divisi', 'diajukanOleh.karyawan'])
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->when($this->divisiId !== '', fn ($query) => $query->where('divisi_id_divisi', $this->divisiId))
                ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [KPermintDana::STATUS_MENUNGGU])
                ->orderByRaw('CASE WHEN status = ? THEN created_at END ASC', [KPermintDana::STATUS_MENUNGGU])
                ->orderByRaw('CASE WHEN status <> ? THEN created_at END DESC', [KPermintDana::STATUS_MENUNGGU])
                ->paginate(15);
        } else {
            $laporan = KLabaRugi::with(['divisi', 'pengirim.karyawan', 'diverifikasiOleh.karyawan'])
                ->where('periode', $this->periode)
                ->when($this->status !== '', fn ($query) => $query->where('status_verifikasi', $this->status))
                ->when($this->divisiId !== '', fn ($query) => $query->where('divisi_id_divisi', $this->divisiId))
                ->orderByRaw('CASE WHEN status_verifikasi = ? THEN 0 ELSE 1 END', [KLabaRugi::STATUS_MENUNGGU])
                ->orderByRaw('CASE WHEN status_verifikasi = ? THEN created_at END ASC', [KLabaRugi::STATUS_MENUNGGU])
                ->orderByRaw('CASE WHEN status_verifikasi <> ? THEN created_at END DESC', [KLabaRugi::STATUS_MENUNGGU])
                ->paginate(15);
        }

        return view('livewire.finance.manajemen-dana', [
            'permintaan' => $permintaan,
            'laporan' => $laporan,
            'divisiList' => $divisiList,
            'jumlahMenungguPermintaan' => KPermintDana::where('status', KPermintDana::STATUS_MENUNGGU)->count(),
            'jumlahMenungguLaporan' => KLabaRugi::where('status_verifikasi', KLabaRugi::STATUS_MENUNGGU)->count(),
            'hasActiveFilter' => $this->hasActiveFilter(),
        ]);
    }

    private function hasActiveFilter(): bool
    {
        if ($this->tab === 'permintaan') {
            return $this->status !== '' || $this->divisiId !== '';
        }

        return $this->status !== ''
            || $this->divisiId !== ''
            || $this->periode !== now()->format('Y-m');
    }

    /**
     * @return array<string, mixed>
     */
    private function reportData(KLabaRugi $laporan): array
    {
        $start = $laporan->tanggal_mulai ? Carbon::parse($laporan->tanggal_mulai) : null;
        $end = $laporan->tanggal_selesai ? Carbon::parse($laporan->tanggal_selesai) : null;

        return [
            'id' => $laporan->id_klr,
            'divisi' => $laporan->divisi?->nama_divisi ?? '-',
            'pengirim' => $laporan->pengirim?->karyawan?->nama ?? $laporan->pengirim?->username ?? '-',
            'minggu' => $laporan->minggu_ke,
            'range' => $this->formatRange($start, $end),
            'periode' => $this->formatPeriode($laporan->periode),
            'pendapatan' => FormatAkuntansi::formatRupiah($laporan->total_pendapatan),
            'pengeluaran' => FormatAkuntansi::formatRupiah($laporan->total_pengeluaran),
            'laba' => FormatAkuntansi::formatRupiah($laporan->laba_bersih),
            'laba_negatif' => $laporan->laba_bersih < 0,
            'margin' => FormatAkuntansi::formatMargin($laporan->margin_persen),
            'margin_negatif' => $laporan->margin_persen < 0,
            'status' => $laporan->status_verifikasi,
            'status_meta' => $this->statusMetaLaporan($laporan->status_verifikasi),
            'bukti' => $laporan->bukti_laporan,
            'catatan' => $laporan->catatan_verifikasi,
            'diverifikasi_oleh' => $laporan->diverifikasiOleh?->karyawan?->nama
                ?? $laporan->diverifikasiOleh?->username,
        ];
    }

    public function formatRange(?Carbon $start, ?Carbon $end): string
    {
        if (! $start || ! $end) {
            return '-';
        }

        $bulanAwal = self::BULAN_SINGKAT[(int) $start->month] ?? $start->month;
        $bulanAkhir = self::BULAN_SINGKAT[(int) $end->month] ?? $end->month;

        if ($start->isSameMonth($end) && $start->isSameYear($end)) {
            return $start->format('j').'–'.$end->format('j').' '.$bulanAwal.' '.$start->format('Y');
        }

        if ($start->isSameYear($end)) {
            return $start->format('j').' '.$bulanAwal.' – '.$end->format('j').' '.$bulanAkhir.' '.$end->format('Y');
        }

        return $start->format('j').' '.$bulanAwal.' '.$start->format('Y')
            .' – '.$end->format('j').' '.$bulanAkhir.' '.$end->format('Y');
    }

    private function formatPeriode(?string $periode): string
    {
        if (! $periode || ! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periode)) {
            return '-';
        }

        [$tahun, $bulan] = explode('-', $periode);

        return (self::BULAN[(int) $bulan] ?? $bulan).' '.$tahun;
    }

    private function anchor(): Carbon
    {
        return Carbon::createFromFormat('Y-m', $this->periode)->startOfMonth();
    }

    private function normalizeTab(string $tab): string
    {
        return in_array($tab, ['permintaan', 'laporan'], true) ? $tab : 'permintaan';
    }

    private function normalizePeriode(string $periode): string
    {
        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periode) === 1) {
            return $periode;
        }

        return now()->format('Y-m');
    }

    private function normalizeStatus(string $status, string $tab): string
    {
        $status = strtolower(trim($status));

        $allowed = $tab === 'laporan'
            ? [KLabaRugi::STATUS_MENUNGGU, KLabaRugi::STATUS_DISETUJUI, KLabaRugi::STATUS_PERLU_REVISI]
            : [
                KPermintDana::STATUS_MENUNGGU,
                KPermintDana::STATUS_DISETUJUI,
                KPermintDana::STATUS_DITOLAK,
                KPermintDana::STATUS_DICAIRKAN,
            ];

        return in_array($status, $allowed, true) ? $status : '';
    }

    private function ensureFinanceAccess(): void
    {
        abort_unless(auth()->user()?->isKadivFinance(), 403);
    }
}
