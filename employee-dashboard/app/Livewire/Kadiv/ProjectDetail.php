<?php

namespace App\Livewire\Kadiv;

use App\Models\ProjectModel;
use App\Models\ProjectTask;
use App\Models\User2;
use Illuminate\Support\Facades\DB;
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

    public function approveProject(): void
    {
        $user = auth()->user();
        $this->divisionId();

        DB::transaction(function () use ($user): void {
            $project = ProjectModel::query()
                ->whereKey($this->projectId)
                ->where('dibuat_oleh_id_user', $user->id_user)
                ->lockForUpdate()
                ->firstOrFail();

            if ($project->approved_at) {
                return;
            }

            $hasIncompleteTask = $project->tasks()
                ->whereNull('file_hasil_path')
                ->exists();

            if ($hasIncompleteTask) {
                $this->addError('approval', 'Project belum dapat di-acc karena masih ada task yang belum dikumpulkan.');
                return;
            }

            if (! $project->tasks()->exists()) {
                $this->addError('approval', 'Project belum memiliki task.');
                return;
            }

            $project->forceFill([
                'approved_at' => now(),
                'approved_by_id_user' => $user->id_user,
            ])->save();
        });
    }

    public function render()
    {
        return view('livewire.kadiv.project-detail', ['project' => $this->project()]);
    }
}
