<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ProjectModel;
use App\Models\ProjectTask;
use App\Queries\StaffTugasQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $karyawanId = $request->user()->karyawan_id_karyawan;

        $projects = ProjectModel::with(['tasks.karyawan'])
            ->whereHas('tasks', fn ($q) => $q->where('karyawan_id_karyawan', $karyawanId))
            ->latest()
            ->get()
            ->map(fn ($project) => self::formatProject($project, $karyawanId));

        return response()->json(['data' => $projects]);
    }

    public function overview(Request $request)
    {
        $user = $request->user();
        $karyawanId = $user->karyawan_id_karyawan;

        $tasks = StaffTugasQuery::forStaff($user)
            ->apply($request->only(['sort', 'dir', 'status', 'q']))
            ->get();

        $projects = ProjectModel::with(['tasks.karyawan'])
            ->whereHas('tasks', fn ($q) => $q->where('karyawan_id_karyawan', $karyawanId))
            ->latest()
            ->get()
            ->map(fn ($project) => self::formatProject($project, $karyawanId));

        return response()->json([
            'data' => [
                'tasks' => $tasks,
                'projects' => $projects,
            ],
        ]);
    }

    public function show(Request $request, string $id)
    {
        $karyawanId = $request->user()->karyawan_id_karyawan;
        $project = ProjectModel::with(['tasks.karyawan'])
            ->whereKey($id)
            ->whereHas('tasks', fn ($query) => $query->where('karyawan_id_karyawan', $karyawanId))
            ->firstOrFail();

        return response()->json(['data' => self::formatProject($project, $karyawanId)]);
    }

    public static function formatProject(ProjectModel $project, string $karyawanId): array
    {
        return [
            'id_project' => $project->id_project,
            'nama_project' => $project->nama_project,
            'deskripsi' => $project->deskripsi,
            'status' => $project->status_efektif,
            'tenggat' => $project->tenggat?->toIso8601String(),
            'file_url' => $project->file_path
                ? Storage::disk('public')->url($project->file_path)
                : null,
            'file_name' => $project->file_name,
            'tasks' => $project->tasks->map(fn ($task) => [
                'id_task' => $task->id_task,
                'label' => $task->label,
                'selesai' => $task->selesai,
                'karyawan' => $task->karyawan->nama ?? '-',
                'milik_saya' => $task->karyawan_id_karyawan === $karyawanId,
                'file_hasil_url' => $task->file_hasil_path
                    ? Storage::disk('public')->url($task->file_hasil_path)
                    : null,
                'file_hasil_name' => $task->file_hasil_name,
                'tanggal_submit' => $task->tanggal_submit?->toIso8601String(),
            ]),
        ];
    }

    public function submitTask(Request $request, string $id)
    {
        $task = ProjectTask::where('id_task', $id)
            ->where('karyawan_id_karyawan', $request->user()->karyawan_id_karyawan)
            ->firstOrFail();

        $validated = $request->validate([
            'file_hasil' => [
                'required',
                'file',
                'max:204800',
                'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip',
            ],
        ]);

        $oldPath = $task->file_hasil_path;
        $newPath = $validated['file_hasil']->store('project_submissions', 'public');

        if (! $newPath) {
            throw ValidationException::withMessages([
                'file_hasil' => 'File gagal disimpan. Silakan coba lagi.',
            ]);
        }

        try {
            $task->update([
                'file_hasil_path' => $newPath,
                'file_hasil_name' => $validated['file_hasil']->getClientOriginalName(),
                'tanggal_submit' => now(),
            ]);
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($newPath);
            throw $exception;
        }

        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return response()->json([
            'message' => 'Pengumpulan task berhasil.',
            'data' => [
                'file_hasil_url' => Storage::disk('public')->url($task->file_hasil_path),
                'file_hasil_name' => $task->file_hasil_name,
                'tanggal_submit' => $task->tanggal_submit?->toIso8601String(),
            ],
        ]);
    }

    public function toggleTask(Request $request, $id)
    {
        $task = ProjectTask::findOrFail($id);

        if ($task->karyawan_id_karyawan !== $request->user()->karyawan_id_karyawan) {
            return response()->json(['message' => 'Bukan task Anda.'], 403);
        }

        $task->selesai = ! $task->selesai;
        $task->save();

        return response()->json(['success' => true, 'selesai' => $task->selesai]);
    }
}