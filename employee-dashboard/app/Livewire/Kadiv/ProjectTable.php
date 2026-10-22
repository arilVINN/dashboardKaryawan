<?php

namespace App\Livewire\Kadiv;

use App\Models\ProjectModel;
use App\Models\ProjectTask;
use App\Models\User2;
use Livewire\Attributes\On;
use Livewire\Component;

class ProjectTable extends Component
{
    public array $expanded = []; // id_project yang lagi dibuka detailnya

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

    #[On('tutup-modal-tugas')]
    public function refreshList(): void
    {
        // dipanggil otomatis setiap kali createProject() sukses di TugasTable
    }

    public function toggleExpand(string $idProject): void
    {
        if (in_array($idProject, $this->expanded)) {
            $this->expanded = array_values(array_diff($this->expanded, [$idProject]));
        } else {
            $this->expanded[] = $idProject;
        }
    }

    public function toggleTask(string $idTask): void
    {
        $task = ProjectTask::findOrFail($idTask);
        $task->selesai = ! $task->selesai;
        $task->save();
    }

    public function render()
    {
        $divisionId = $this->divisionId();

        $projects = ProjectModel::with(['tasks.karyawan'])
            ->whereHas('tasks.karyawan', fn ($q) => $q->where('divisi_id_divisi', $divisionId))
            ->latest()
            ->get();

        return view('livewire.kadiv.project-table', ['projects' => $projects]);
    }
}