<?php

namespace App\Livewire\Hrd;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\User2;
use App\Queries\HrdKaryawanQuery;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class KaryawanTable extends Component
{
    use WithPagination;

    // The legacy GET filter form used `q`; keep that query-string key so old
    // deep links keep working while the property stays `search`.
    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $divisi = '';

    #[Url]
    public string $jabatan = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $sort = '';

    #[Url]
    public string $dir = 'asc';

    public function mount(): void
    {
        $this->hrd();
    }

    private function hrd(): User2
    {
        $user = auth()->user();

        if (! $user instanceof User2
            || strtolower($user->role?->nama_role ?? '') !== 'hrd') {
            abort(403);
        }

        return $user;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDivisi(): void
    {
        $this->resetPage();
    }

    public function updatedJabatan(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        if ($this->sort === $column) {
            $this->dir = $this->dir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $column;
            $this->dir = 'asc';
        }

        $this->resetPage();
    }

    public function clearFilter(string $name): void
    {
        if (in_array($name, ['search', 'divisi', 'jabatan', 'status'], true)) {
            $this->{$name} = '';
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->divisi = '';
        $this->jabatan = '';
        $this->status = '';
        $this->sort = '';
        $this->dir = 'asc';
        $this->resetPage();
    }

    public function render()
    {
        $this->hrd();

        $rows = (new HrdKaryawanQuery)->apply([
            'divisi' => $this->divisi,
            'jabatan' => $this->jabatan,
            'status' => $this->status,
            'q' => $this->search,
            'sort' => $this->sort,
            'dir' => $this->dir,
        ])->paginate(6);

        $daftarDivisi = Divisi::orderBy('nama_divisi')->get();
        $daftarJabatan = Karyawan::query()
            ->whereNotNull('jabatan')
            ->distinct()
            ->orderBy('jabatan')
            ->pluck('jabatan');

        return view('livewire.hrd.karyawan-table', [
            'rows' => $rows,
            'daftarDivisi' => $daftarDivisi,
            'daftarJabatan' => $daftarJabatan,
            'chips' => $this->activeFilters($daftarDivisi),
            'hasActiveFilters' => $this->hasActiveFilters(),
        ]);
    }

    private function hasActiveFilters(): bool
    {
        return $this->search !== ''
            || $this->divisi !== ''
            || $this->jabatan !== ''
            || $this->status !== '';
    }

    private function activeFilters($daftarDivisi): array
    {
        $chips = [];

        if ($this->divisi !== '') {
            $namaDivisi = $daftarDivisi->firstWhere('id_divisi', $this->divisi)?->nama_divisi ?? $this->divisi;
            $chips[] = ['name' => 'divisi', 'label' => 'Divisi: '.$namaDivisi];
        }

        if ($this->jabatan !== '') {
            $chips[] = ['name' => 'jabatan', 'label' => 'Jabatan: '.$this->jabatan];
        }

        if ($this->status !== '') {
            $chips[] = [
                'name' => 'status',
                'label' => 'Status Akun: '.($this->status === 'aktif' ? 'Aktif' : 'Belum ada akun'),
            ];
        }

        if ($this->search !== '') {
            $chips[] = ['name' => 'q', 'label' => 'Cari: "'.$this->search.'"'];
        }

        return $chips;
    }
}
