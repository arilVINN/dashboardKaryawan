<?php

namespace App\Livewire\Kadiv;

use App\Models\ProjectModel;
use App\Models\User2;
use Livewire\Component;

class ProjectTable extends Component
{
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

    public function render()
    {
        $this->divisionId();
        $user = auth()->user();

        $projects = ProjectModel::with(['tasks.karyawan'])
            ->where('dibuat_oleh_id_user', $user->id_user)
            ->latest()
            ->get();

        return view('livewire.kadiv.project-table', ['projects' => $projects]);
    }
}