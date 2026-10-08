<?php

namespace App\Livewire\Hrd;

use App\Models\User2;
use App\Queries\HrdDivisiQuery;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class DivisiTable extends Component
{
    use WithPagination;

    public bool $manage = false;

    // The legacy GET filter form used `q`; keep that query-string key so old
    // deep links keep working while the property stays `search`.
    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $sort = '';

    #[Url]
    public string $dir = 'asc';

    public function mount(bool $manage = false): void
    {
        $this->hrd();
        $this->manage = $manage;
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
        if (in_array($name, ['search', 'status'], true)) {
            $this->{$name} = '';
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->sort = '';
        $this->dir = 'asc';
        $this->resetPage();
    }

    public function render()
    {
        $this->hrd();

        $rows = (new HrdDivisiQuery)->apply([
            'status' => $this->status,
            'q' => $this->search,
            'sort' => $this->sort,
            'dir' => $this->dir,
        ])->paginate($this->manage ? 10 : 5);

        return view('livewire.hrd.divisi-table', [
            'rows' => $rows,
            'chips' => $this->activeFilters(),
            'hasActiveFilters' => $this->hasActiveFilters(),
        ]);
    }

    private function hasActiveFilters(): bool
    {
        return $this->search !== '' || $this->status !== '';
    }

    private function activeFilters(): array
    {
        $chips = [];

        if ($this->search !== '') {
            $chips[] = ['name' => 'q', 'label' => 'Cari: "'.$this->search.'"'];
        }

        if ($this->status !== '') {
            $chips[] = ['name' => 'status', 'label' => 'Status: '.ucfirst($this->status)];
        }

        return $chips;
    }
}
