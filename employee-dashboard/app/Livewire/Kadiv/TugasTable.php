<?php

namespace App\Livewire\Kadiv;

use App\Models\Karyawan;
use App\Models\Tugas;
use App\Models\User2;
use App\Queries\KadivStaffQuery;
use App\Queries\KadivTugasQuery;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class TugasTable extends Component
{
    use WithPagination;
    use WithFileUploads;

    #[Url]
    public string $search = '';

    #[Url]
    public string $sort = '';

    #[Url]
    public string $dir = 'desc';

    #[Url]
    public string $status = '';

    #[Url]
    public string $staff = '';

    public string $judul = '';
    public string $deskripsi = '';
    public string $tenggat = '';
    public string $recipient = '';
    public $attachment = null;

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

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedStaff(): void
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
        $this->staff = '';
        $this->sort = 'tanggal';
        $this->dir = 'desc';
        $this->resetPage();
    }

    public function clearFilter(string $name): void
    {
        if (in_array($name, ['search', 'status', 'staff'], true)) {
            $this->{$name} = '';
            $this->resetPage();
        }
    }

    public function createTask(): void
    {
        $divisionId = $this->divisionId();

        $this->validate([
            'judul' => 'required|string|max:100',
            'deskripsi' => 'required|string',
            'tenggat' => 'required|date',
            'recipient' => 'required|string',
            'attachment' => 'nullable|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
        ]);

        $staffIds = Karyawan::where('divisi_id_divisi', $divisionId)
            ->whereHas('user.role', fn ($q) => $q->whereRaw('LOWER(nama_role) = ?', ['staff']))
            ->pluck('id_karyawan');

        if ($this->recipient === 'semua') {
            $recipients = $staffIds->all();
            if (empty($recipients)) {
                $this->addError('recipient', 'Belum ada staff di divisi ini.');
                return;
            }
        } else {
            if (! $staffIds->contains($this->recipient)) {
                $this->addError('recipient', 'Penerima tidak valid atau beda divisi.');
                return;
            }
            $recipients = [$this->recipient];
        }

        $filePath = $this->attachment ? $this->attachment->store('tugas_pendukung', 'public') : null;

        foreach ($recipients as $karyawanId) {
            $last = Tugas::orderBy('id_tugas', 'desc')->first();
            $newId = $last
                ? 'TG' . str_pad(intval(substr($last->id_tugas, 2)) + 1, 3, '0', STR_PAD_LEFT)
                : 'TG001';

            Tugas::create([
                'id_tugas' => $newId,
                'karyawan_id_karyawan' => $karyawanId,
                'judul_tugas' => $this->judul,
                'deskripsi' => $this->deskripsi,
                'file_pendukung' => $filePath,
                'deadline' => $this->tenggat,
                'progress' => '0',
                'status' => Tugas::STATUS_BARU,
                'tanggal_dibuat' => now(),
                'tanggal_update' => now(),
            ]);
        }

        $this->reset(['judul', 'deskripsi', 'tenggat', 'recipient', 'attachment']);
        session()->flash('tugas_success', 'Tugas berhasil dibuat.');
        $this->resetPage();
    }

    public function render()
    {
        $divisionId = $this->divisionId();

        $rows = KadivTugasQuery::forDivision($divisionId)
            ->apply([
                'sort' => $this->sort,
                'dir' => $this->dir,
                'status' => $this->status,
                'staff' => $this->staff,
                'q' => $this->search,
            ])
            ->paginate(10);

        $staffOptions = KadivStaffQuery::forDivision($divisionId)->apply([])->get();

        return view('livewire.kadiv.tugas-table', [
            'rows' => $rows,
            'staffOptions' => $staffOptions,
            'chips' => $this->activeFilters($staffOptions),
        ]);
    }

    private function activeFilters($staffOptions): array
    {
        $chips = [];

        if ($this->search !== '') {
            $chips[] = ['name' => 'q', 'label' => 'Cari: "'.$this->search.'"'];
        }

        if ($this->status !== '') {
            $chips[] = ['name' => 'status', 'label' => 'Status: '.ucfirst($this->status)];
        }

        if ($this->staff !== '') {
            $nama = $staffOptions->firstWhere('id_karyawan', $this->staff)?->nama ?? $this->staff;
            $chips[] = ['name' => 'staff', 'label' => 'Staff: '.$nama];
        }

        return $chips;
    }
}
