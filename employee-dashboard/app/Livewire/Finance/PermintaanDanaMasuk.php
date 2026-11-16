<?php

namespace App\Livewire\Finance;

use App\Exceptions\PermintaanSudahDiprosesException;
use App\Helpers\FormatAkuntansi;
use App\Models\Divisi;
use App\Models\KPermintDana;
use App\Services\Finance\PermintaanDanaService;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar Permintaan Dana Masuk untuk Kadiv Finance.
 *
 * Filter, dialog keputusan, dan pembaruan baris/pill berjalan lewat Livewire.
 * Keputusan tetap memakai service transaksi yang mengunci baris dan menulis
 * audit log.
 */
class PermintaanDanaMasuk extends Component
{
    use WithPagination;

    #[Url(as: 'status')]
    public string $status = '';

    #[Url(as: 'divisi_id')]
    public string $divisiId = '';

    public bool $showApproveModal = false;

    public bool $showRejectModal = false;

    public string $approveId = '';

    public string $approveNomor = '';

    public string $approveJudul = '';

    public string $approveDivisi = '';

    public string $approveNominal = '';

    public string $rejectId = '';

    public string $rejectNomor = '';

    public string $rejectReason = '';

    public function mount(?string $status = null, ?string $divisiId = null): void
    {
        $status = $status ?? request()->query('status', '');
        $divisiId = $divisiId ?? request()->query('divisi_id', '');

        $this->status = $this->normalizeStatus((string) $status);
        $this->divisiId = (string) ($divisiId ?? '');
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedDivisiId(): void
    {
        $this->resetPage();
    }

    public function filterMenunggu(): void
    {
        $this->status = KPermintDana::STATUS_MENUNGGU;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->status = '';
        $this->divisiId = '';
        $this->resetPage();
    }

    public function openApproveModal(string $id): void
    {
        $this->ensureFinanceAccess();

        $permintaan = KPermintDana::with(['divisi', 'pemohon.karyawan'])->findOrFail($id);
        $this->authorize('approve', $permintaan);

        if (! $permintaan->isMenunggu()) {
            $this->dispatch('permintaan-toast', message: 'Pengajuan ini sudah diproses', type: 'error');

            return;
        }

        $this->approveId = $permintaan->id_kpd;
        $this->approveNomor = $permintaan->nomor_pengajuan;
        $this->approveJudul = $permintaan->judul_pengajuan;
        $this->approveDivisi = $permintaan->divisi?->nama_divisi ?? '-';
        $this->approveNominal = (string) (int) $permintaan->nominal_diajukan;
        $this->resetValidation();
        $this->showApproveModal = true;
    }

    public function closeApproveModal(): void
    {
        $this->showApproveModal = false;
        $this->resetValidation();
    }

    public function openRejectModal(string $id): void
    {
        $this->ensureFinanceAccess();

        $permintaan = KPermintDana::with(['divisi', 'pemohon.karyawan'])->findOrFail($id);
        $this->authorize('reject', $permintaan);

        if (! $permintaan->isMenunggu()) {
            $this->dispatch('permintaan-toast', message: 'Pengajuan ini sudah diproses', type: 'error');

            return;
        }

        $this->rejectId = $permintaan->id_kpd;
        $this->rejectNomor = $permintaan->nomor_pengajuan;
        $this->rejectReason = '';
        $this->resetValidation();
        $this->showRejectModal = true;
    }

    public function closeRejectModal(): void
    {
        $this->showRejectModal = false;
        $this->rejectReason = '';
        $this->resetValidation();
    }

    public function approve(): void
    {
        $this->ensureFinanceAccess();

        try {
            app(PermintaanDanaService::class)->approve(
                $this->approveId,
                auth()->user(),
                null
            );
        } catch (PermintaanSudahDiprosesException $e) {
            $this->closeApproveModal();
            $this->dispatch('permintaan-toast', message: $e->getMessage(), type: 'error');

            return;
        }

        $this->closeApproveModal();
        $this->dispatch('permintaan-toast', message: 'Pengajuan disetujui', type: 'success');
    }

    public function reject(): void
    {
        $this->ensureFinanceAccess();

        $this->validate([
            'rejectReason' => ['required', 'string', 'max:500'],
        ], [
            'rejectReason.required' => 'Alasan penolakan wajib diisi.',
            'rejectReason.max' => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        try {
            app(PermintaanDanaService::class)->reject(
                $this->rejectId,
                auth()->user(),
                $this->rejectReason
            );
        } catch (PermintaanSudahDiprosesException $e) {
            $this->closeRejectModal();
            $this->dispatch('permintaan-toast', message: $e->getMessage(), type: 'error');

            return;
        }

        $this->closeRejectModal();
        $this->dispatch('permintaan-toast', message: 'Pengajuan ditolak', type: 'success');
    }

    public function rupiah(float|int|null $nominal): string
    {
        return FormatAkuntansi::formatRupiah($nominal);
    }

    /**
     * @return array{label: string, bg: string, border: string, dot: string}
     */
    public function statusMeta(?string $status): array
    {
        return match ($status) {
            KPermintDana::STATUS_MENUNGGU => [
                'label' => 'Menunggu',
                'bg' => '#FEF0C3',
                'border' => '#F5C85C',
                'dot' => '#D97706',
            ],
            KPermintDana::STATUS_DISETUJUI => [
                'label' => 'Disetujui',
                'bg' => '#D5F3F0',
                'border' => '#7DD3C8',
                'dot' => '#0D9488',
            ],
            KPermintDana::STATUS_DITOLAK => [
                'label' => 'Ditolak',
                'bg' => '#FDE0E0',
                'border' => '#F5A3A3',
                'dot' => '#DC2626',
            ],
            default => [
                'label' => 'Dicairkan',
                'bg' => '#E2E8EC',
                'border' => '#D5DEE3',
                'dot' => '#4B5563',
            ],
        };
    }

    public function render(): View
    {
        $this->ensureFinanceAccess();

        $query = KPermintDana::with(['divisi', 'pemohon.karyawan', 'disetujuiOleh.karyawan'])
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->divisiId !== '', fn ($q) => $q->where('divisi_id_divisi', $this->divisiId))
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [KPermintDana::STATUS_MENUNGGU])
            ->orderByRaw('CASE WHEN status = ? THEN created_at END ASC', [KPermintDana::STATUS_MENUNGGU])
            ->orderByRaw('CASE WHEN status <> ? THEN created_at END DESC', [KPermintDana::STATUS_MENUNGGU]);

        $permintaan = $query->paginate(15);

        $pendingCount = KPermintDana::where('status', KPermintDana::STATUS_MENUNGGU)->count();
        $pendingSum = (float) KPermintDana::where('status', KPermintDana::STATUS_MENUNGGU)->sum('nominal_diajukan');

        $divisiList = Divisi::where('kode_divisi', '!=', 'FIN')
            ->where('status_aktif', 'Aktif')
            ->orderBy('nama_divisi')
            ->get();

        return view('livewire.finance.permintaan-dana-masuk', [
            'permintaan' => $permintaan,
            'pendingCount' => $pendingCount,
            'pendingSum' => $pendingSum,
            'divisiList' => $divisiList,
            'hasActiveFilter' => $this->status !== '' || $this->divisiId !== '',
        ]);
    }

    private function ensureFinanceAccess(): void
    {
        abort_unless(auth()->user()?->isKadivFinance(), 403);
    }

    private function normalizeStatus(string $status): string
    {
        $status = strtolower(trim($status));

        return in_array($status, [
            KPermintDana::STATUS_MENUNGGU,
            KPermintDana::STATUS_DISETUJUI,
            KPermintDana::STATUS_DITOLAK,
            KPermintDana::STATUS_DICAIRKAN,
        ], true) ? $status : '';
    }
}
