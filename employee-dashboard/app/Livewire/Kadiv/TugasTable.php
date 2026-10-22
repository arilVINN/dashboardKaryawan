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
use App\Models\ProjectModel;
use App\Models\ProjectTask;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

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

    public string $activeTab = 'tugas'; // tugas | project

    public string $namaProject = '';
    public string $deskripsiProject = '';
    public $projectFile = null;
    public array $taskLabels = [''];
    public array $taskKaryawan = [''];

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function addTaskRow(): void
    {
        $this->taskLabels[] = '';
        $this->taskKaryawan[] = '';
    }

    public function removeTaskRow(int $index): void
    {
        if (count($this->taskLabels) <= 1) {
            return;
        }
        unset($this->taskLabels[$index], $this->taskKaryawan[$index]);
        $this->taskLabels = array_values($this->taskLabels);
        $this->taskKaryawan = array_values($this->taskKaryawan);
    }

    public function mount(): void
    {
        $this->divisionId();
    }

    private function divisionId(): string
    {
        $user = auth()->user();

        if (
            ! $user instanceof User2
            || strtolower($user->role?->nama_role ?? '') !== 'kadiv'
            || ! $user->karyawan?->divisi_id_divisi
        ) {
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

        $this->validate([
            'judul'      => 'required|string|max:100',
            'deskripsi'  => 'required|string',
            'tenggat'    => 'required|date',
            'recipient'  => 'required|string',
            'attachment' => 'nullable|file|max:204800|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
        ]);

        $staffIds = Karyawan::where('divisi_id_divisi', $divisionId)
            ->whereHas('user.role', fn($q) => $q->whereRaw('LOWER(nama_role) = ?', ['staff']))
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


    public function createProject(): void
    {
        $this->divisionId();

        $this->validate([
            'namaProject' => 'required|string|max:150',
            'deskripsiProject' => 'nullable|string|max:5000',
            'projectFile' => 'nullable|file|max:204800|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
            'taskLabels' => 'required|array|min:1',
            'taskLabels.*' => 'required|string|max:200',
            'taskKaryawan' => 'required|array|min:1',
            'taskKaryawan.*' => 'required|string',
        ]);

        $karyawanIds = $this->projectKaryawanQuery()
            ->pluck('id_karyawan');

        foreach ($this->taskKaryawan as $i => $karyawanId) {
            if (! $karyawanIds->contains($karyawanId)) {
                $this->addError("taskKaryawan.$i", 'Karyawan tidak valid atau tidak dapat dipilih.');
                return;
            }
        }

        $filePath = null;

        try {
            $filePath = $this->projectFile
                ? $this->projectFile->store('project_files', 'public')
                : null;

            if ($this->projectFile && ! $filePath) {
                throw new \RuntimeException('File project gagal disimpan.');
            }

            \Illuminate\Support\Facades\DB::transaction(function () use ($filePath) {
                $project = ProjectModel::create([
                    'id_project' => 'PRJ' . strtoupper(substr(uniqid('', true), -10)),
                    'nama_project' => $this->namaProject,
                    'deskripsi' => $this->deskripsiProject ?: null,
                    'file_path' => $filePath,
                    'file_name' => $this->projectFile?->getClientOriginalName(),
                    'dibuat_oleh_id_user' => auth()->user()->id_user,
                ]);

                foreach ($this->taskLabels as $i => $label) {
                    ProjectTask::create([
                        'id_task' => 'TSK' . strtoupper(substr(uniqid('', true), -10)),
                        'project_id_project' => $project->id_project,
                        'label' => $label,
                        'karyawan_id_karyawan' => $this->taskKaryawan[$i],
                        'urutan' => $i,
                    ]);
                }
            });

            $this->reset(['namaProject', 'deskripsiProject', 'projectFile', 'taskLabels', 'taskKaryawan']);
            $this->taskLabels = [''];
            $this->taskKaryawan = [''];

            session()->flash('tugas_success', 'Project berhasil dibuat.');
            $this->dispatch('tutup-modal-tugas');
            $this->resetPage();
        } catch (\Throwable $e) {
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }

            \Illuminate\Support\Facades\Log::error('createProject error: ' . $e->getMessage());
            $this->addError('namaProject', 'Gagal membuat project: ' . $e->getMessage());
        }
    }

    private function projectKaryawanQuery(): Builder
    {
        return Karyawan::query()
            ->whereDoesntHave('user.role', fn ($query) => $query
                ->whereRaw('LOWER(nama_role) IN (?, ?)', ['hrd', 'kadiv']));
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
        $projectKaryawanOptions = $this->projectKaryawanQuery()
            ->with('divisi')
            ->orderBy('nama')
            ->get();

        return view('livewire.kadiv.tugas-table', [
            'rows' => $rows,
            'staffOptions' => $staffOptions,
            'projectKaryawanOptions' => $projectKaryawanOptions,
        ]);
    }
}
