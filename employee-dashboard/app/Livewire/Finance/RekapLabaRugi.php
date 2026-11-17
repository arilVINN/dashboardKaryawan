<?php

namespace App\Livewire\Finance;

use App\Livewire\Finance\Concerns\HasMonthPicker;
use App\Helpers\FormatAkuntansi;
use App\Models\Divisi;
use Carbon\Carbon;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Rekap Laba / Rugi per Divisi untuk Kadiv Finance.
 *
 * Query, filter verifikasi, dan rumus margin/laba dipertahankan sama dengan
 * KeuanganController@rekapDivisi. Komponen ini hanya menambahkan navigasi
 * periode/minggu berbasis Livewire.
 */
class RekapLabaRugi extends Component
{
    use HasMonthPicker;

    #[Url(as: 'periode')]
    public string $periode = '';

    #[Url(as: 'minggu_ke')]
    public string $mingguKe = '';

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

    public function mount(?string $periode = null, ?string $mingguKe = null): void
    {
        $periode = $periode ?: request()->query('periode') ?: now()->format('Y-m');
        $this->periode = $this->normalizePeriode($periode);

        $mingguKe = $mingguKe ?? request()->query('minggu_ke', '');
        $this->mingguKe = $this->normalizeMinggu((string) $mingguKe);
    }

    public function previous(): void
    {
        $this->periode = $this->anchor()->subMonthNoOverflow()->format('Y-m');
    }

    public function next(): void
    {
        $this->periode = $this->anchor()->addMonthNoOverflow()->format('Y-m');
    }

    public function render(): View
    {
        // Lindungi request update Livewire dengan aturan yang sama seperti
        // middleware kadiv.finance pada route halaman.
        abort_unless(auth()->user()?->isKadivFinance(), 403);

        $query = Divisi::query()->unitNonFinance()
            ->where('status_aktif', 'Aktif')
            ->with(['laporanLabaRugi' => function ($q) {
                $q->where('periode', $this->periode)
                    ->where('status_verifikasi', '!=', 'menunggu');

                if ($this->mingguKe !== '') {
                    $q->where('minggu_ke', (int) $this->mingguKe);
                }
            }])
            ->get();

        // Baris tanpa laporan tidak ditampilkan; nilai Rp 0 / 0,0% / 0
        // laporan hanya akan muncul untuk divisi yang benar-benar punya laporan.
        $rekapDivisi = $query->map(function ($divisi) {
            $laporan = $divisi->laporanLabaRugi;

            $divisi->total_pendapatan_rekap = $laporan->sum('total_pendapatan');
            $divisi->total_pengeluaran_rekap = $laporan->sum('total_pengeluaran');
            $divisi->laba_bersih_rekap = $laporan->sum('laba_bersih');
            $divisi->laporan_list = $laporan;

            return $divisi;
        })->filter(fn ($divisi) => $divisi->laporan_list->isNotEmpty())->values();

        $grandPendapatan = 0;
        $grandPengeluaran = 0;
        $grandLaba = 0;

        foreach ($rekapDivisi as $row) {
            $grandPendapatan += (float) ($row->total_pendapatan_rekap ?? 0);
            $grandPengeluaran += (float) ($row->total_pengeluaran_rekap ?? 0);
            $grandLaba += (float) ($row->laba_bersih_rekap ?? 0);
        }

        $grandMargin = FormatAkuntansi::hitungMargin($grandPendapatan, $grandPengeluaran);

        return view('livewire.finance.rekap-laba-rugi', [
            'periodeLabel' => $this->periodeLabel(),
            'periodeSubtitle' => $this->periodeSubtitle(),
            'grandPendapatan' => (float) $grandPendapatan,
            'grandPengeluaran' => (float) $grandPengeluaran,
            'grandLaba' => (float) $grandLaba,
            'grandMargin' => $grandMargin,
            'rekapDivisi' => $rekapDivisi,
        ]);
    }

    private function periodeLabel(): string
    {
        [$tahun, $bulan] = explode('-', $this->periode);
        $namaBulan = self::BULAN[(int) $bulan] ?? $bulan;

        return $namaBulan.' '.$tahun;
    }

    private function periodeSubtitle(): string
    {
        if ($this->mingguKe !== '') {
            return 'Minggu ke-'.$this->mingguKe.', '.$this->periodeLabel();
        }

        return 'Akumulasi '.$this->periodeLabel();
    }

    private function normalizePeriode(string $periode): string
    {
        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periode) === 1) {
            return $periode;
        }

        return now()->format('Y-m');
    }

    private function normalizeMinggu(string $mingguKe): string
    {
        return in_array($mingguKe, ['1', '2', '3', '4', '5'], true) ? $mingguKe : '';
    }

    private function anchor(): Carbon
    {
        return Carbon::createFromFormat('Y-m-d', $this->periode.'-01')->startOfMonth();
    }
}
