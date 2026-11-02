<?php

namespace App\Livewire\Kadiv;

use App\Models\ProjectModel;
use App\Models\ProjectTask;
use App\Models\User2;
use Livewire\Component;

class ProjectDetail extends Component
{
    public string $projectId;

    public function mount(string $id): void
    {
        $this->projectId = $id;
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

    private function project(): ProjectModel
    {
        $project = ProjectModel::with(['tasks.karyawan'])
            ->where('id_project', $this->projectId)
            ->where('dibuat_oleh_id_user', auth()->user()->id_user)
            ->firstOrFail();

        return $project;
    }

    public function toggleTask(string $idTask): void
    {
        $project = $this->project();
        $task = $project->tasks->firstWhere('id_task', $idTask);

        abort_unless($task instanceof ProjectTask, 404);

        $task->update(['selesai' => ! $task->selesai]);
    }

    public function render()
    {
        return view('livewire.kadiv.project-detail', ['project' => $this->project()]);
    }
}
