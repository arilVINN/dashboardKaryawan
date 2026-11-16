<?php

namespace App\Livewire\Finance;

use App\Livewire\Finance\Concerns\HasMonthPicker;
use App\Helpers\FormatAkuntansi;
use App\Models\Divisi;
use App\Models\KeuanganAldiv;
use App\Services\Finance\AlokasiPaguService;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Halaman Alokasi Pagu Anggaran untuk Kadiv Finance.
 *
 * Query dan aturan data tetap mengikuti AlokasiController@index/store/update.
 * Komponen ini menambahkan navigasi periode Livewire serta dialog create/edit.
 */
class AlokasiPagu extends Component
{
    use HasMonthPicker;

    #[Url(as: 'periode')]
    public string $periode = '';

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public string $createDivisiId = '';

    public string $createNominal = '';

    public string $createKeterangan = '';

    public string $editId = '';

    public string $editDivisiNama = '';

    public string $editDivisiKode = '';

    public string $editPeriodeLabel = '';

    public string $editNominal = '';

    public string $editNominalLama = '';

    public string $editKeterangan = '';

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

    public function openCreateModal(): void
    {
        $this->ensureFinanceAccess();
        $this->resetValidation();
        $this->createDivisiId = '';
        $this->createNominal = '';
        $this->createKeterangan = '';
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }

    public function openEditModal(string $id): void
    {
        $this->ensureFinanceAccess();

        $alokasi = KeuanganAldiv::with('divisi')
            ->where('periode', $this->periode)
            ->findOrFail($id);

        $this->editId = $alokasi->id_kad;
        $this->editDivisiNama = $alokasi->divisi?->nama_divisi ?? '-';
        $this->editDivisiKode = $alokasi->divisi?->kode_divisi ?? '-';
        $this->editPeriodeLabel = $this->periodeLabel();
        $this->editNominalLama = (string) (int) $alokasi->nominal_alokasi;
        $this->editNominal = (string) (int) $alokasi->nominal_alokasi;
        $this->editKeterangan = '';
        $this->resetValidation();
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->reset(['editId', 'editDivisiNama', 'editDivisiKode', 'editPeriodeLabel', 'editNominal', 'editNominalLama', 'editKeterangan']);
        $this->resetValidation();
    }

    public function saveCreate(): void
    {
        $this->ensureFinanceAccess();

        $this->validate([
            'createDivisiId' => [
                'required',
                'string',
                'exists:divisis,id_divisi',
                Rule::unique('keuangan_aldiv', 'divisi_id_divisi')->where(fn ($q) => $q
                    ->where('divisi_id_divisi', $this->createDivisiId)
                    ->where('periode', $this->periode)
                ),
            ],
            'createNominal' => ['required', 'integer', 'gt:0'],
            'createKeterangan' => ['nullable', 'string', 'max:250'],
        ], [
            'createDivisiId.required' => 'Divisi wajib dipilih.',
            'createDivisiId.unique' => 'Divisi ini sudah memiliki alokasi untuk periode tersebut.',
            'createNominal.required' => 'Nominal pagu wajib diisi.',
            'createNominal.integer' => 'Nominal pagu harus berupa angka bulat.',
            'createNominal.gt' => 'Nominal pagu harus lebih dari 0.',
            'createKeterangan.max' => 'Keterangan maksimal 250 karakter.',
        ]);

        app(AlokasiPaguService::class)->create([
            'periode' => $this->periode,
            'divisi_id_divisi' => $this->createDivisiId,
            'nominal_alokasi' => (int) $this->createNominal,
            'keterangan' => $this->createKeterangan !== '' ? $this->createKeterangan : null,
        ], auth()->user());

        $this->showCreateModal = false;
        $this->reset(['createDivisiId', 'createNominal', 'createKeterangan']);
        $this->resetValidation();
        $this->dispatch('alokasi-toast', message: 'Pagu ditetapkan', type: 'success');
    }

    public function saveEdit(): void
    {
        $this->ensureFinanceAccess();

        $this->validate([
            'editNominal' => ['required', 'integer', 'gt:0'],
            'editKeterangan' => ['required', 'string', 'max:250'],
        ], [
            'editNominal.required' => 'Nominal pagu wajib diisi.',
            'editNominal.integer' => 'Nominal pagu harus berupa angka bulat.',
            'editNominal.gt' => 'Nominal pagu harus lebih dari 0.',
            'editKeterangan.required' => 'Alasan perubahan wajib diisi.',
            'editKeterangan.max' => 'Alasan perubahan maksimal 250 karakter.',
        ]);

        $alokasi = KeuanganAldiv::where('periode', $this->periode)->findOrFail($this->editId);

        app(AlokasiPaguService::class)->update($alokasi, [
            'nominal_alokasi' => (int) $this->editNominal,
            'keterangan' => $this->editKeterangan,
        ], auth()->user());

        $this->closeEditModal();
        $this->dispatch('alokasi-toast', message: 'Pagu diperbarui', type: 'success');
    }

    public function rupiah(float|int|null $nominal): string
    {
        return FormatAkuntansi::formatRupiah($nominal);
    }

    public function render(): View
    {
        $this->ensureFinanceAccess();

        $alokasi = KeuanganAldiv::with(['divisi', 'createdBy.karyawan'])
            ->where('periode', $this->periode)
            ->latest()
            ->get();

        $totalPagu = (float) $alokasi->sum('nominal_alokasi');
        $divisiDenganAlokasi = $alokasi->pluck('divisi_id_divisi')->all();

        $divisiTersedia = Divisi::query()->unitNonFinance()
            ->where('status_aktif', 'Aktif')
            ->whereNotIn('id_divisi', $divisiDenganAlokasi)
            ->ordered()
            ->get();

        $totalDivisi = Divisi::query()->unitNonFinance()
            ->where('status_aktif', 'Aktif')
            ->count();

        return view('livewire.finance.alokasi-pagu', [
            'periodeLabel' => $this->periodeLabel(),
            'alokasi' => $alokasi,
            'totalPagu' => $totalPagu,
            'divisiTersedia' => $divisiTersedia,
            'totalDivisi' => $totalDivisi,
        ]);
    }

    private function ensureFinanceAccess(): void
    {
        // Samakan dengan middleware kadiv.finance pada route halaman.
        abort_unless(auth()->user()?->isKadivFinance(), 403);
    }

    private function periodeLabel(): string
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
        return Carbon::createFromFormat('Y-m-d', $this->periode.'-01')->startOfMonth();
    }
}
