<?php

namespace App\Livewire\Staff;

use App\Models\User2;
use App\Queries\StaffPesanQuery;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PesanTable extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $jenis = '';

    #[Url]
    public string $sort = '';

    #[Url]
    public string $dir = 'desc';

    public function mount(): void
    {
        $this->staff();
    }

    private function staff(): User2
    {
        $user = auth()->user();

        if (! $user instanceof User2
            || strtolower($user->role?->nama_role ?? '') !== 'staff'
            || ! $user->karyawan_id_karyawan) {
            abort(403);
        }

        return $user;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedJenis(): void
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
        if (in_array($name, ['search', 'jenis'], true)) {
            $this->{$name} = '';
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->jenis = '';
        $this->sort = '';
        $this->dir = 'desc';
        $this->resetPage();
    }

    public function render()
    {
        $items = StaffPesanQuery::forStaff($this->staff())->unified([
            'sort' => $this->sort,
            'dir' => $this->dir,
            'jenis' => $this->jenis,
            'q' => $this->search,
        ]);

        $page = (int) $this->getPage();
        $rows = new LengthAwarePaginator(
            $items->forPage($page, 10)->values(),
            $items->count(),
            10,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('livewire.staff.pesan-table', [
            'rows' => $rows,
            'chips' => $this->activeFilters(),
        ]);
    }

    private function activeFilters(): array
    {
        $chips = [];

        if ($this->search !== '') {
            $chips[] = ['name' => 'q', 'label' => 'Cari: "'.$this->search.'"'];
        }

        if ($this->jenis !== '') {
            $chips[] = [
                'name' => 'jenis',
                'label' => 'Jenis: '.($this->jenis === 'langsung' ? 'Pesan Langsung' : 'Pesan Tugas'),
            ];
        }

        return $chips;
    }
}
