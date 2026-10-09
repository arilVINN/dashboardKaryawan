<?php

namespace App\Livewire\Staff;

use App\Models\User2;
use App\Queries\StaffTugasQuery;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TugasTable extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $sort = '';

    #[Url]
    public string $dir = 'desc';

    #[Url]
    public string $status = '';

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

    public function resetFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->sort = '';
        $this->dir = 'desc';
        $this->resetPage();
    }

    public function clearFilter(string $name): void
    {
        if (in_array($name, ['search', 'status'], true)) {
            $this->{$name} = '';
            $this->resetPage();
        }
    }

    public function render()
    {
        $rows = StaffTugasQuery::forStaff($this->staff())
            ->apply([
                'sort' => $this->sort,
                'dir' => $this->dir,
                'status' => $this->status,
                'q' => $this->search,
            ])
            ->paginate(10);

        return view('livewire.staff.tugas-table', [
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

        if ($this->status !== '') {
            $chips[] = ['name' => 'status', 'label' => 'Status: '.ucfirst($this->status)];
        }

        return $chips;
    }
}
