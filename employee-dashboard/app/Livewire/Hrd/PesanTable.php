<?php

namespace App\Livewire\Hrd;

use App\Models\Pesan;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PesanTable extends Component
{
    use WithPagination;

    public int $perPage = 5;

    #[Url]
    public string $search = '';

    #[Url]
    public string $sort = '';

    #[Url]
    public string $dir = 'desc';

    #[Url]
    public string $tipe = '';

    #[Url]
    public string $arah = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTipe(): void
    {
        $this->resetPage();
    }

    public function updatedArah(): void
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
        $this->tipe = '';
        $this->arah = '';
        $this->sort = 'tanggal';
        $this->dir = 'desc';
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        if (!in_array($this->perPage, [5, 10, 25, 50], true)) {
            $this->perPage = 5;
        }

        $this->resetPage();
    }

    public function render()
    {
        $query = Pesan::with(['pengirim.karyawan', 'penerima.karyawan']);

        if (in_array($this->tipe, ['pesan', 'surat'], true)) {
            $query->where('tipe', $this->tipe);
        }

        $userId = auth()->user()?->id_user;
        if ($this->arah === 'masuk') {
            $query->where('penerima_id_user', $userId);
        } elseif ($this->arah === 'keluar') {
            $query->where('pengirim_id_user', $userId);
        }

        if ($this->search !== '') {
            $search = mb_strtolower($this->search);
            $query->where(function ($builder) use ($search): void {
                $builder->whereRaw('LOWER(judul_pesan) LIKE ?', ['%' . $search . '%'])
                    ->orWhereRaw('LOWER(deskripsi) LIKE ?', ['%' . $search . '%']);
            });
        }

        $direction = $this->dir === 'asc' ? 'asc' : 'desc';
        switch ($this->sort) {
            case 'tanggal':
                $query->orderBy('tanggal_pesan', $direction)->orderBy('id_pesan', $direction);
                break;
            case 'judul':
                $query->orderBy('judul_pesan', $direction)->orderBy('id_pesan');
                break;
            case 'pengirim':
                $query->orderByRaw(
                    '(select k.nama from karyawans k inner join users2 u on u.karyawan_id_karyawan = k.id_karyawan where u.id_user = pesans.pengirim_id_user) ' . $direction
                );
                break;
            default:
                $query->orderByDesc('created_at')->orderByDesc('id_pesan');
        }

        $pesans = $query->paginate($this->perPage);

        return view('livewire.hrd.pesan-table', [
            'pesans' => $pesans,
        ]);
    }
}
