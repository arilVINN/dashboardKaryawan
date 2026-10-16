<?php

namespace App\Livewire\Direksi;

use App\Models\Divisi;
use App\Models\User2;
use App\Queries\DireksiTugasQuery;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Read-only, company-wide task monitor for the Direksi/Pemimpin super-admin.
 * There are intentionally no create/edit/delete/review actions here.
 */
class TugasTable extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $divisi = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $sort = '';

    #[Url]
    public string $dir = 'desc';

    public function mount(): void
    {
        $this->guard();
    }

    private function guard(): User2
    {
        $user = auth()->user();

        if (! $user instanceof User2 || ! $user->isSuperAdmin()) {
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
        $this->divisi = '';
        $this->status = '';
        $this->sort = '';
        $this->dir = 'desc';
        $this->resetPage();
    }

    public function render()
    {
        $this->guard();

        $rows = (new DireksiTugasQuery)->apply([
            'divisi' => $this->divisi,
            'status' => $this->status,
            'q' => $this->search,
            'sort' => $this->sort,
            'dir' => $this->dir,
        ])->paginate(10);

        return view('livewire.direksi.tugas-table', [
            'rows' => $rows,
            'daftarDivisi' => Divisi::orderBy('nama_divisi')->get(),
        ]);
    }
}
