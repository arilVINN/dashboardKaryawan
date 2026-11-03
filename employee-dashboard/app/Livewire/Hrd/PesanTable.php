<?php

namespace App\Livewire\Hrd;

use App\Models\Pesan;
use Livewire\Component;
use Livewire\WithPagination;

class PesanTable extends Component
{
    use WithPagination;

    public int $perPage = 5;

    public function updatedPerPage(): void
    {
        if (!in_array($this->perPage, [5, 10, 25, 50], true)) {
            $this->perPage = 5;
        }

        $this->resetPage();
    }

    public function render()
    {
        $pesans = Pesan::with(['pengirim.karyawan', 'penerima.karyawan'])
            ->orderByDesc('created_at')
            ->orderByDesc('id_pesan')
            ->paginate($this->perPage);

        return view('livewire.hrd.pesan-table', [
            'pesans' => $pesans,
        ]);
    }
}
