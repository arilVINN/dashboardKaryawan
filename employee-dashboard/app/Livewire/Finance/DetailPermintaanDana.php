<?php

namespace App\Livewire\Finance;

use App\Exceptions\PermintaanSudahDiprosesException;
use App\Helpers\FormatAkuntansi;
use App\Models\KeuanganAldiv;
use App\Models\KPengeluaran;
use App\Models\KPermintDana;
use App\Support\FileStorage;
use App\Support\StatusPalette;
use App\Services\Finance\PermintaanDanaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Detail pengajuan dana untuk approver (Kadiv Finance).
 *
 * Perhitungan pagu, policy, transaksi keputusan, dan audit log tetap memakai
 * aturan yang sama dengan halaman daftar.
 */
class DetailPermintaanDana extends Component
{
    public string $permintaanId = '';

    public string $note = '';

    public bool $showApproveModal = false;

    public bool $showRejectModal = false;

    public string $rejectReason = '';

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

    public function mount(string $id): void
    {
        $this->permintaanId = $id;
    }

    public function openApproveModal(): void
    {
        $this->ensureFinanceAccess();

        $permintaan = $this->permintaan();
        $this->authorize('approve', $permintaan);

        if (! $permintaan->isMenunggu()) {
            $this->dispatch('permintaan-detail-toast', message: 'Pengajuan ini sudah diproses', type: 'error');

            return;
        }

        $this->resetValidation();
        $this->showApproveModal = true;
    }

    public function closeApproveModal(): void
    {
        $this->showApproveModal = false;
        $this->resetValidation();
    }

    public function openRejectModal(): void
    {
        $this->ensureFinanceAccess();

        $permintaan = $this->permintaan();
        $this->authorize('reject', $permintaan);

        if (! $permintaan->isMenunggu()) {
            $this->dispatch('permintaan-detail-toast', message: 'Pengajuan ini sudah diproses', type: 'error');

            return;
        }

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
                $this->permintaanId,
                auth()->user(),
                $this->note !== '' ? $this->note : null
            );
        } catch (PermintaanSudahDiprosesException $e) {
            $this->closeApproveModal();
            $this->dispatch('permintaan-detail-toast', message: $e->getMessage(), type: 'error');

            return;
        }

        $this->closeApproveModal();
        $this->dispatch('permintaan-detail-toast', message: 'Pengajuan disetujui', type: 'success');
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
                $this->permintaanId,
                auth()->user(),
                $this->rejectReason,
                $this->note !== '' ? $this->note : null
            );
        } catch (PermintaanSudahDiprosesException $e) {
            $this->closeRejectModal();
            $this->dispatch('permintaan-detail-toast', message: $e->getMessage(), type: 'error');

            return;
        }

        $this->closeRejectModal();
        $this->dispatch('permintaan-detail-toast', message: 'Pengajuan ditolak', type: 'success');
    }

    public function rupiah(float|int|null $nominal): string
    {
        return FormatAkuntansi::formatRupiah($nominal);
    }

    /**
     * Format tanggal Indonesia tanpa nama hari, dengan jam 24 jam.
     */
    public function tanggalIndonesia(mixed $date): string
    {
        if (! $date) {
            return '-';
        }

        $date = $date instanceof \DateTimeInterface ? $date : Carbon::parse($date);

        return $date->format('d').' '.self::BULAN_SINGKAT[(int) $date->format('n')].' '.$date->format('Y, H:i');
    }

    /**
     * @return array{label: string, bg: string, border: string, dot: string}
     */
    public function statusMeta(?string $status): array
    {
        return match ($status) {
            KPermintDana::STATUS_MENUNGGU => StatusPalette::pill('menunggu', 'Menunggu persetujuan'),
            KPermintDana::STATUS_DISETUJUI => StatusPalette::pill('disetujui', 'Disetujui'),
            KPermintDana::STATUS_DITOLAK => StatusPalette::pill('ditolak', 'Ditolak'),
            default => StatusPalette::pill('dicairkan', 'Dicairkan'),
        };
    }

    public function render(): View
    {
        $this->ensureFinanceAccess();

        $permintaan = $this->permintaan();
        $periode = now()->format('Y-m');
        [$tahun, $bulan] = explode('-', $periode);

        $totalAlokasi = (float) KeuanganAldiv::where('divisi_id_divisi', $permintaan->divisi_id_divisi)
            ->where('periode', $periode)
            ->sum('nominal_alokasi');

        $totalTerpakai = (float) KPengeluaran::where('divisi_id_divisi', $permintaan->divisi_id_divisi)
            ->whereYear('tanggal_cair', $tahun)
            ->whereMonth('tanggal_cair', $bulan)
            ->sum('nominal');

        $sisaPagu = $totalAlokasi - $totalTerpakai;
        $sisaSetelah = $sisaPagu - (float) $permintaan->nominal_diajukan;

        $currentPercent = $totalAlokasi > 0 ? ($totalTerpakai / $totalAlokasi) * 100 : 0;
        $afterPercent = $totalAlokasi > 0 ? (($totalTerpakai + (float) $permintaan->nominal_diajukan) / $totalAlokasi) * 100 : 0;

        $baseWidth = min(100, max(0, $currentPercent));
        $extraWidth = min(100 - $baseWidth, max(0, $afterPercent - $currentPercent));
        $overPercent = max(0, $afterPercent - 100);

        $currentPercentLabel = (int) round($currentPercent);
        $afterPercentLabel = (int) round($afterPercent);
        $progressLabel = "Terserap {$currentPercentLabel}% → {$afterPercentLabel}% jika disetujui";

        $lampiranList = collect();

        if (filled($permintaan->lampiran_proposal) && FileStorage::exists($permintaan->lampiran_proposal)) {
            $path = $permintaan->lampiran_proposal;
            $size = FileStorage::size($path);

            $lampiranList->push([
                'name' => basename($path),
                'type' => strtoupper(pathinfo($path, PATHINFO_EXTENSION)),
                'size' => $this->formatFileSize($size),
                'url' => \App\Support\FileStorage::url($path),
            ]);
        }

        return view('livewire.finance.detail-permintaan-dana', [
            'permintaan' => $permintaan,
            'totalAlokasi' => $totalAlokasi,
            'totalTerpakai' => $totalTerpakai,
            'sisaPagu' => $sisaPagu,
            'sisaSetelah' => $sisaSetelah,
            'baseWidth' => $baseWidth,
            'extraWidth' => $extraWidth,
            'overPercent' => $overPercent,
            'progressLabel' => $progressLabel,
            'afterPercentLabel' => $afterPercentLabel,
            'lampiranList' => $lampiranList,
            'periodeLabel' => self::BULAN[(int) $bulan].' '.$tahun,
            'canDecide' => $permintaan->isMenunggu() && auth()->user()?->can('approve', $permintaan),
            'status' => $this->statusMeta($permintaan->status),
            'actor' => $permintaan->disetujuiOleh?->karyawan?->nama
                ?? $permintaan->disetujuiOleh?->username
                ?? 'Finance',
        ]);
    }

    private function permintaan(): KPermintDana
    {
        return KPermintDana::with(['divisi', 'pemohon.karyawan', 'disetujuiOleh.karyawan', 'pengeluaran'])
            ->findOrFail($this->permintaanId);
    }

    private function ensureFinanceAccess(): void
    {
        abort_unless(auth()->user()?->isKadivFinance(), 403);
    }

    private function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1, ',', '.').' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0, ',', '.').' KB';
        }

        return $bytes.' B';
    }
}
