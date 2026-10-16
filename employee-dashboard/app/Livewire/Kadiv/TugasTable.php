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
            || ! $user->hasAnyRole(['kadiv'])
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

    public function createTask(): void
    {
        $divisionId = $this->divisionId();

        // Direksi/Pemimpin is a monitoring-only (read-only) role.
        if (auth()->user()?->isSuperAdmin()) {
            abort(403, 'Direksi hanya dapat memantau tugas (read-only).');
        }

        $this->validate([
            'judul'      => 'required|string|max:100',
            'deskripsi'  => 'required|string',
            'tenggat'    => 'required|date',
            'recipient'  => 'required|string',
            'attachment' => 'nullable|file|max:204800|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
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

        try {
            $filePath = $this->attachment ? $this->attachment->store('tugas_pendukung', 'public') : null;
            $deadline = \Illuminate\Support\Carbon::parse($this->tenggat)->format('Y-m-d H:i:s');

            foreach ($recipients as $karyawanId) {
                // Generate ID aman dari race condition
                $newId = \Illuminate\Support\Facades\DB::transaction(function () {
                    $last = Tugas::lockForUpdate()->orderByRaw("CAST(SUBSTRING(id_tugas, 3) AS INTEGER) DESC NULLS LAST")->first();
                    if ($last && preg_match('/^TG(\d+)$/', $last->id_tugas, $m)) {
                        return 'TG' . str_pad((int)$m[1] + 1, 3, '0', STR_PAD_LEFT);
                    }
                    return 'TG' . str_pad((Tugas::count() + 1), 3, '0', STR_PAD_LEFT);
                });

                Tugas::create([
                    'id_tugas'            => $newId,
                    'karyawan_id_karyawan' => $karyawanId,
                    'judul_tugas'         => $this->judul,
                    'deskripsi'           => $this->deskripsi,
                    'file_pendukung'      => $filePath,
                    'deadline'            => $deadline,
                    'progress'            => '0',
                    'status'              => Tugas::STATUS_BARU,
                    'tanggal_dibuat'      => now(),
                    'tanggal_update'      => now(),
                ]);
            }

            $this->reset(['judul', 'deskripsi', 'tenggat', 'recipient', 'attachment']);
            session()->flash('tugas_success', 'Tugas berhasil dibuat.');
            $this->dispatch('tutup-modal-tugas');
            $this->resetPage();

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('createTask error: ' . $e->getMessage());
            $this->addError('judul', 'Gagal membuat tugas: ' . $e->getMessage());
        }
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

        return view('livewire.kadiv.tugas-table', ['rows' => $rows, 'staffOptions' => $staffOptions]);
    }
}
