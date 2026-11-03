<?php

namespace App\Livewire\Kadiv;

use App\Models\ProjectModel;
use App\Models\Karyawan;
use App\Models\User2;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProjectTable extends Component
{
    use WithFileUploads;

    public bool $showEditModal = false;
    public string $editingProjectId = '';
    public string $namaProject = '';
    public string $deskripsiProject = '';
    public string $tenggatProject = '';
    public $projectFile = null;

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

    private function projectForOwner(string $id): ProjectModel
    {
        return ProjectModel::query()
            ->whereKey($id)
            ->where('dibuat_oleh_id_user', auth()->user()->id_user)
            ->firstOrFail();
    }

    private function projectKaryawanQuery(): Builder
    {
        return Karyawan::query()
            ->where('aktif', true)
            ->whereNull('tanggal_keluar')
            ->where('status_aktif', 'Aktif')
            ->whereDoesntHave('user.role', fn ($query) => $query
                ->whereRaw('LOWER(nama_role) IN (?, ?)', ['hrd', 'kadiv']));
    }

    public function editProject(string $id): void
    {
        $this->divisionId();
        $project = $this->projectForOwner($id);

        if ($project->approved_at) {
            $this->addError('projectEdit', 'Project yang sudah di-ACC tidak dapat diedit.');
            return;
        }

        $this->editingProjectId = $project->id_project;
        $this->namaProject = $project->nama_project;
        $this->deskripsiProject = $project->deskripsi ?? '';
        $this->tenggatProject = $project->tenggat?->format('Y-m-d\TH:i') ?? '';
        $this->projectFile = null;
        $this->resetErrorBag();
        $this->showEditModal = true;
    }

    public function updateProject(): void
    {
        $this->divisionId();
        $project = $this->projectForOwner($this->editingProjectId);

        if ($project->approved_at) {
            $this->closeEditModal();
            $this->addError('projectEdit', 'Project yang sudah di-ACC tidak dapat diedit.');
            return;
        }

        $this->validate([
            'namaProject' => ['required', 'string', 'max:150'],
            'deskripsiProject' => ['nullable', 'string', 'max:5000'],
            'tenggatProject' => ['required', 'date', 'after:now'],
            'projectFile' => ['nullable', 'file', 'max:204800', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png'],
        ]);

        $oldPath = $project->file_path;
        $newPath = null;

        try {
            if ($this->projectFile) {
                $newPath = $this->projectFile->store('project_files', 'public');
                if (! $newPath) {
                    throw new \RuntimeException('File project gagal disimpan.');
                }
            }

            DB::transaction(function () use ($project, $newPath): void {
                $project->update([
                    'nama_project' => $this->namaProject,
                    'deskripsi' => $this->deskripsiProject ?: null,
                    'tenggat' => \Illuminate\Support\Carbon::parse($this->tenggatProject),
                    'file_path' => $newPath ?: $project->file_path,
                    'file_name' => $newPath
                        ? $this->projectFile?->getClientOriginalName()
                        : $project->file_name,
                    'approved_at' => null,
                    'approved_by_id_user' => null,
                ]);
            });

            if ($newPath && $oldPath) {
                Storage::disk('public')->delete($oldPath);
            }

            $this->closeEditModal();
            session()->flash('tugas_success', 'Project berhasil diperbarui.');
        } catch (\Throwable $e) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }

            report($e);
            $this->addError('namaProject', 'Gagal memperbarui project.');
        }
    }

    public function deleteProject(string $id): void
    {
        $this->divisionId();
        $project = $this->projectForOwner($id);
        $filePath = $project->file_path;

        DB::transaction(function () use ($project): void {
            $project->delete();
        });

        if ($filePath) {
            Storage::disk('public')->delete($filePath);
        }

        session()->flash('tugas_success', 'Project berhasil dihapus.');
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->reset(['editingProjectId', 'namaProject', 'deskripsiProject', 'tenggatProject', 'projectFile']);
        $this->resetErrorBag();
    }

    public function render()
    {
        $this->divisionId();
        $user = auth()->user();

        $projects = ProjectModel::with(['tasks.karyawan'])
            ->where('dibuat_oleh_id_user', $user->id_user)
            ->latest()
            ->get();

        return view('livewire.kadiv.project-table', [
            'projects' => $projects,
            'projectKaryawanOptions' => $this->projectKaryawanQuery()->with('divisi')->orderBy('nama')->get(),
        ]);
    }
}