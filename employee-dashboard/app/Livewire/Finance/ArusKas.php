<?php

namespace App\Livewire\Finance;

use App\Models\Divisi;
use App\Models\KPemasukan;
use App\Models\KPengeluaran;
use App\Models\KPermintDana;
use Carbon\Carbon;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Arus Kas untuk Kadiv Finance.
 *
 * Sumber data, total, dan aturan akses mengikuti KeuanganController@arusKas.
 * Komponen ini hanya memindahkan tampilan ke Livewire agar periode, kategori,
 * pencarian, dan tab dapat berubah tanpa memuat ulang halaman penuh.
 */
class ArusKas extends Component
{
    use WithPagination;

    #[Url(as: 'tab')]
    public string $tab = 'masuk';

    #[Url(as: 'periode')]
    public string $periode = '';

    #[Url(as: 'kategori')]
    public string $kategori = '';

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'divisi_id_divisi')]
    public string $divisiId = '';

    public bool $showTransactionDetail = false;

    /** @var array<string, mixed>|null */
    public ?array $transactionDetail = null;

    /** Nama bulan Indonesia untuk label periode. */
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
        $periode = $periode ?: $this->periode ?: request()->query('periode') ?: now()->format('Y-m');

        $this->periode = $this->normalizePeriode($periode);
        $this->tab = in_array($this->tab, ['masuk', 'keluar'], true) ? $this->tab : 'masuk';
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

    public function updatedTab(): void
    {
        $this->resetPage();
    }

    public function updatedKategori(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDivisiId(): void
    {
        $this->resetPage();
    }

    public function openTransactionDetail(string $type, string $id): void
    {
        if ($type === 'masuk') {
            $item = KPemasukan::with('divisi')->find($id);

            if (! $item) {
                return;
            }

            $this->transactionDetail = [
                'type' => 'masuk',
                'kode' => $item->kode_transaksi,
                'tanggal' => Carbon::parse($item->tanggal_masuk)->locale('id')->isoFormat('D MMMM Y'),
                'kategori' => $item->kategori_pemasukan,
                'metode' => null,
                'divisi' => $item->divisi?->nama_divisi ?? 'Umum / Pusat',
                'keterangan' => $item->keterangan ?: '-',
                'nominal' => (float) $item->nominal,
                'bukti' => $item->bukti_dokumen,
                'referensi' => null,
            ];
        } else {
            $item = KPengeluaran::with('divisi', 'permintaanDana')->find($id);

            if (! $item) {
                return;
            }

            $this->transactionDetail = [
                'type' => 'keluar',
                'kode' => $item->kode_pengeluaran,
                'tanggal' => Carbon::parse($item->tanggal_cair)->locale('id')->isoFormat('D MMMM Y'),
                'kategori' => null,
                'metode' => $item->metode_pembayaran,
                'divisi' => $item->divisi?->nama_divisi ?? '-',
                'keterangan' => $item->keterangan ?: '-',
                'nominal' => (float) $item->nominal,
                'bukti' => $item->bukti_pencairan,
                'referensi' => $item->permintaanDana?->nomor_pengajuan,
            ];
        }

        $this->showTransactionDetail = true;
    }

    public function closeTransactionDetail(): void
    {
        $this->showTransactionDetail = false;
        $this->transactionDetail = null;
    }

    public function render(): View
    {
        // Lindungi request update Livewire dengan aturan yang sama seperti
        // middleware kadiv.finance pada route halaman.
        abort_unless(auth()->user()?->isKadivFinance(), 403);

        [$tahun, $bulan] = explode('-', $this->periode);

        $divisiList = Divisi::query()->unitNonFinance()->where('status_aktif', 'Aktif')->get();
        $kategoriList = KPemasukan::KATEGORI_OPTIONS;

        $qMasuk = KPemasukan::with('divisi')
            ->whereYear('tanggal_masuk', $tahun)
            ->whereMonth('tanggal_masuk', $bulan);

        if ($this->tab === 'masuk' && $this->kategori) {
            $qMasuk->where('kategori_pemasukan', $this->kategori);
        }

        if ($this->search !== '') {
            $like = '%'.$this->search.'%';
            $qMasuk->where(function ($query) use ($like) {
                $query->where('kode_transaksi', 'like', $like)
                    ->orWhere('keterangan', 'like', $like);
            });
        }

        $pemasukan = $qMasuk->latest('tanggal_masuk')->paginate(15);
        $totalMasuk = KPemasukan::whereYear('tanggal_masuk', $tahun)
            ->whereMonth('tanggal_masuk', $bulan)->sum('nominal');

        $qKeluar = KPengeluaran::with('divisi', 'permintaanDana')
            ->whereYear('tanggal_cair', $tahun)
            ->whereMonth('tanggal_cair', $bulan);

        if ($this->tab === 'keluar' && $this->divisiId) {
            $qKeluar->where('divisi_id_divisi', $this->divisiId);
        }

        if ($this->search !== '') {
            $like = '%'.$this->search.'%';
            $qKeluar->where(function ($query) use ($like) {
                $query->where('kode_pengeluaran', 'like', $like)
                    ->orWhere('keterangan', 'like', $like);
            });
        }

        $pengeluaran = $qKeluar->latest('tanggal_cair')->paginate(15);
        $totalKeluar = KPengeluaran::whereYear('tanggal_cair', $tahun)
            ->whereMonth('tanggal_cair', $bulan)->sum('nominal');

        $permintaanSiapCair = KPermintDana::with('divisi', 'diajukanOleh.karyawan')
            ->where('status', KPermintDana::STATUS_DISETUJUI)
            ->latest('tanggal_disetujui')
            ->get();

        return view('livewire.finance.arus-kas', [
            'tab' => $this->tab,
            'periode' => $this->periode,
            'periodeLabel' => $this->periodeLabel(),
            'pemasukan' => $pemasukan,
            'pengeluaran' => $pengeluaran,
            'totalMasuk' => (float) $totalMasuk,
            'totalKeluar' => (float) $totalKeluar,
            'saldo' => (float) $totalMasuk - (float) $totalKeluar,
            'divisiList' => $divisiList,
            'kategoriList' => $kategoriList,
            'permintaanSiapCair' => $permintaanSiapCair,
            'kategori' => $this->kategori,
            'divisiId' => $this->divisiId,
            'search' => $this->search,
        ]);
    }

    public function periodeLabel(): string
    {
        [$tahun, $bulan] = explode('-', $this->periode);
        $namaBulan = self::BULAN[(int) $bulan] ?? $bulan;

        return $namaBulan.' '.$tahun;
    }

    private function normalizePeriode(string $periode): string
    {
        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periode) === 1) {
            return $periode;
        }

        return now()->format('Y-m');
    }

    private function anchor(): Carbon
    {
        return Carbon::createFromFormat('Y-m', $this->periode)->startOfMonth();
    }
}
