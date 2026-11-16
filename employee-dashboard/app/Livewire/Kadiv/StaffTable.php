<?php

namespace App\Livewire\Kadiv;

use App\Models\User2;
use App\Queries\KadivStaffQuery;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class StaffTable extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $sort = 'nama';

    #[Url]
    public string $dir = 'asc';

    public function mount(): void
    {
        $this->divisionId();
    }

    private function divisionId(): string
    {
        $user = auth()->user();

        if (! $user instanceof User2
            || strtolower($user->role?->nama_role ?? '') !== 'kadiv'
            || ! $user->karyawan?->divisi_id_divisi) {
            abort(403);
        }

        return (string) $user->karyawan->divisi_id_divisi;
    }

    public function updatedSearch(): void
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
        $this->sort = 'nama';
        $this->dir = 'asc';
        $this->resetPage();
    }

    public function render()
    {
        $rows = KadivStaffQuery::forDivision($this->divisionId())
            ->apply(['sort' => $this->sort, 'dir' => $this->dir, 'q' => $this->search])
            ->paginate(10);

        return view('livewire.kadiv.staff-table', ['rows' => $rows]);
    }
}
