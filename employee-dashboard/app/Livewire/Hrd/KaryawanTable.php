<?php

namespace App\Livewire\Hrd;

use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\User2;
use App\Queries\HrdKaryawanQuery;
use Livewire\Attributes\On;
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

    public int $perPage = 5;

    public function mount(): void
    {
        $this->hrd();
    }

    private function hrd(): User2
    {
        $user = auth()->user();

        if (! $user instanceof User2
            || (! $user instanceof User2 || ! $user->hasRole('hrd') && ! $user->isDirektur())) {
            abort(403);
        }

        return $user;
    }

    #[On('karyawan-disimpan')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDivisi(): void
    {
        // A jabatan name only makes sense together with its division.
        if ($this->jabatan !== '' && $this->divisi !== '') {
            $masihAda = Jabatan::where('division_id', $this->divisi)
                ->where('nama', $this->jabatan)
                ->exists();
            if (! $masihAda) {
                $this->jabatan = '';
            }
        }

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

    public function updatedPerPage(): void
    {
        if (! in_array($this->perPage, [5, 10, 25, 50], true)) {
            $this->perPage = 5;
        }

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
        ])->paginate($this->perPage);

        $daftarDivisi = Divisi::query()->ordered()->get();

        // Jabatan options follow the selected division (all jabatan when no
        // division filter is active).
        $jabatanQuery = Jabatan::query()->orderBy('nama');
        if ($this->divisi !== '') {
            $jabatanQuery->where('division_id', $this->divisi);
        }
        $daftarJabatan = $jabatanQuery->pluck('nama')->unique()->values();

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
                'label' => 'Status: '.$this->status,
            ];
        }

        if ($this->search !== '') {
            $chips[] = ['name' => 'q', 'label' => 'Cari: "'.$this->search.'"'];
        }

        return $chips;
    }
}
