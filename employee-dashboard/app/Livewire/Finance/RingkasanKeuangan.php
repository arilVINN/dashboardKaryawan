<?php

namespace App\Livewire\Finance;

use App\Livewire\Finance\Concerns\HasMonthPicker;
use App\Models\Divisi;
use App\Models\KPemasukan;
use App\Models\KPengeluaran;
use App\Models\KPermintDana;
use Carbon\Carbon;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Ringkasan Keuangan untuk Kadiv Finance.
 *
 * Tanggung jawab komponen ini hanya menyajikan ulang data dashboard yang sama
 * dengan KeuanganController@index. Sumber data, filter, dan hak akses tidak
 * diubah; periode dapat digeser tanpa memuat ulang halaman penuh.
 */
class RingkasanKeuangan extends Component
{
    use HasMonthPicker;

    #[Url(as: 'periode')]
    public string $periode = '';

    /** Nama bulan Indonesia, dipakai untuk label periode. */
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

    public function mount(?string $periode = null): void
    {
        $periode = $periode ?: request()->query('periode') ?: now()->format('Y-m');
        $this->periode = $this->normalizePeriode($periode);
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

        [$tahun, $bulan] = explode('-', $this->periode);

        // ── KPI Cards ────────────────────────────────────────────────────
        $totalKasMasuk = KPemasukan::whereYear('tanggal_masuk', $tahun)
            ->whereMonth('tanggal_masuk', $bulan)
            ->sum('nominal');

        $totalKasKeluar = KPengeluaran::whereYear('tanggal_cair', $tahun)
            ->whereMonth('tanggal_cair', $bulan)
            ->sum('nominal');

        $sisaKas = $totalKasMasuk - $totalKasKeluar;

        $permintaanMenunggu = KPermintDana::where('status', KPermintDana::STATUS_MENUNGGU)->count();

        // ── Rekap Pagu Per Divisi ─────────────────────────────────────────
        // Hanya divisi yang memiliki pagu pada periode ini yang ditampilkan.
        // Baris tanpa pagu (Rp 0 / 0%) tidak masuk tabel, melainkan memicu
        // empty state di view.
        $rekapDivisi = Divisi::query()->unitNonFinance()
            ->where('status_aktif', 'Aktif')
            ->withSum(['alokasiAnggaran as total_alokasi' => function ($q) {
                $q->where('periode', $this->periode);
            }], 'nominal_alokasi')
            ->withSum(['pengeluaran as total_terpakai' => function ($q) use ($tahun, $bulan) {
                $q->whereYear('tanggal_cair', $tahun)->whereMonth('tanggal_cair', $bulan);
            }], 'nominal')
            ->get()
            ->map(function ($divisi) {
                $divisi->sisa_pagu = ($divisi->total_alokasi ?? 0) - ($divisi->total_terpakai ?? 0);

                return $divisi;
            })
            ->filter(fn ($divisi) => (float) ($divisi->total_alokasi ?? 0) > 0)
            ->values();

        return view('livewire.finance.ringkasan-keuangan', [
            'periodeLabel' => $this->periodeLabel(),
            'totalKasMasuk' => (float) $totalKasMasuk,
            'totalKasKeluar' => (float) $totalKasKeluar,
            'sisaKas' => (float) $sisaKas,
            'permintaanMenunggu' => $permintaanMenunggu,
            'rekapDivisi' => $rekapDivisi,
        ]);
    }

    /**
     * Format rupiah untuk tampilan dashboard: "Rp 48.200.000" dan
     * "-Rp 2.000.000" untuk nilai negatif (bukan tanda kurung).
     */
    public function rupiah(float|int|null $nominal): string
    {
        $nominal = (float) ($nominal ?? 0);
        $prefix = $nominal < 0 ? '-Rp ' : 'Rp ';

        return $prefix.number_format(abs($nominal), 0, ',', '.');
    }

    /**
     * Persentase penyerapan dibulatkan ke bilangan bulat. Nilai di atas 100%
     * tetap ditampilkan apa adanya; lebar bar dibatasi di view.
     */
    public function persenPenyerapan(float|int|null $pagu, float|int|null $terpakai): int
    {
        $pagu = (float) ($pagu ?? 0);
        $terpakai = (float) ($terpakai ?? 0);

        if ($pagu <= 0) {
            return 0;
        }

        return (int) round(($terpakai / $pagu) * 100);
    }

    public function periodeLabel(): string
    {
        [$tahun, $bulan] = explode('-', $this->periode);
        $namaBulan = self::BULAN[(int) $bulan] ?? $bulan;

        return $namaBulan.' '.$tahun;
    }

    /**
     * Kembalikan periode ke format Y-m yang valid. Nilai yang tidak valid
     * jatuh ke bulan berjalan agar halaman tidak error.
     */
    private function normalizePeriode(string $periode): string
    {
        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periode) === 1) {
            return $periode;
        }

        return now()->format('Y-m');
    }

    private function anchor(): Carbon
    {
        return Carbon::createFromFormat('Y-m-d', $this->periode.'-01')->startOfMonth();
    }
}
