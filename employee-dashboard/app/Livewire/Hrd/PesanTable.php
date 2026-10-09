<?php

namespace App\Livewire\Hrd;

use App\Models\User2;
use App\Queries\HrdPesanQuery;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PesanTable extends Component
{
    use WithPagination;

    // The legacy GET filter form used `q`; keep that query-string key so old
    // deep links keep working while the property stays `search`.
    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $arah = '';

    #[Url]
    public string $tipe = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $sort = '';

    #[Url]
    public string $dir = 'desc';

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

    public function updatedArah(): void
    {
        $this->resetPage();
    }

    public function updatedTipe(): void
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
        if (in_array($name, ['search', 'arah', 'tipe', 'status'], true)) {
            $this->{$name} = '';
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->arah = '';
        $this->tipe = '';
        $this->status = '';
        $this->sort = '';
        $this->dir = 'desc';
        $this->resetPage();
    }

    public function render()
    {
        $hrd = $this->hrd();

        $rows = HrdPesanQuery::forHrd($hrd)->apply([
            'q' => $this->search,
            'arah' => $this->arah,
            'tipe' => $this->tipe,
            'status' => $this->status,
            'sort' => $this->sort,
            'dir' => $this->dir,
        ])->paginate(10);

        return view('livewire.hrd.pesan-table', [
            'rows' => $rows,
            'chips' => $this->activeFilters(),
            'hasActiveFilters' => $this->hasActiveFilters(),
        ]);
    }

    private function hasActiveFilters(): bool
    {
        return $this->search !== ''
            || $this->arah !== ''
            || $this->tipe !== ''
            || $this->status !== '';
    }

    private function activeFilters(): array
    {
        $chips = [];

        if ($this->search !== '') {
            $chips[] = ['name' => 'q', 'label' => 'Cari: "'.$this->search.'"'];
        }

        if ($this->arah !== '') {
            $chips[] = ['name' => 'arah', 'label' => 'Arah: '.ucfirst($this->arah)];
        }

        if ($this->tipe !== '') {
            $chips[] = ['name' => 'tipe', 'label' => 'Tipe: '.ucfirst($this->tipe)];
        }

        if ($this->status !== '') {
            $chips[] = [
                'name' => 'status',
                'label' => 'Status: '.($this->status === 'dibaca' ? 'Dibaca' : 'Belum dibaca'),
            ];
        }

        return $chips;
    }
}
