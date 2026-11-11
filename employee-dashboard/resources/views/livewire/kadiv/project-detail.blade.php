@php
    $total = $project->tasks->count();
    $selesai = $project->tasks->where('selesai', true)->count();
    $terkumpul = $project->tasks->whereNotNull('file_hasil_path')->count();
    $pct = $total > 0 ? round(($selesai / $total) * 100) : 0;
    $status = $project->status_efektif;
    $badgeBg = match ($status) {
        \App\Models\Tugas::STATUS_BERJALAN => 'bg-emerald-100 text-emerald-700 border border-emerald-500',
        \App\Models\Tugas::STATUS_MENUNGGU_ACC => 'bg-amber-100 text-amber-700 border border-amber-500',
        \App\Models\Tugas::STATUS_SUDAH_ACC => 'bg-emerald-700 text-white border border-emerald-800',
        \App\Models\Tugas::STATUS_TERLAMBAT => 'bg-red-100 text-red-700 border border-red-500',
        default => 'bg-[#C0E7FF] text-blue-700 border border-blue-500',
    };
    $semuaTerkumpul = $total > 0 && $terkumpul === $total;
@endphp

<div class="space-y-5">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="mt-3 text-2xl font-bold text-slate-800">{{ $project->nama_project }}</h1>
            <p class="mt-2 whitespace-pre-line text-sm text-slate-600">
                {{ $project->deskripsi ?: 'Tidak ada deskripsi project.' }}</p>
            <p
                class="mt-2 text-xs {{ !$project->approved_at && $project->tenggat && $project->tenggat->isPast() ? 'font-bold text-red-600' : 'text-slate-500' }}">
                Tenggat: {{ $project->tenggat?->format('d/m/Y H:i') ?? '-' }}
            </p>
            @if ($project->approved_at)
                <span class="mt-3 inline-flex rounded-full px-3 py-1.5 text-xs font-bold {{ $badgeBg }}">{{ $status }}</span>
            @elseif ($semuaTerkumpul)
                <button type="button" wire:click="approveProject" wire:confirm="Yakin ingin meng-ACC seluruh project ini?"
                    wire:loading.attr="disabled" wire:target="approveProject"
                    class="mt-3 inline-flex items-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-800 disabled:cursor-wait disabled:opacity-60">
                    <span wire:loading.remove wire:target="approveProject">ACC Project</span>
                    <span wire:loading wire:target="approveProject">Memproses...</span>
                </button>
            @endif
            @error('approval')
                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror
        </div>
        @if ($project->file_path)
            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($project->file_path) }}"
                target="_blank" rel="noopener noreferrer"
                class="inline-flex shrink-0 rounded-lg bg-white px-4 py-2 text-sm font-medium text-[#146C94] shadow-sm ring-1 ring-slate-200 hover:bg-slate-50">
                {{ $project->file_name ?: 'Lihat file project' }}
            </a>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-slate-500">Progress</p>
            <p class="mt-1 text-2xl font-bold text-slate-800">{{ $pct }}%</p>
            <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
                <div class="h-full rounded-full bg-[#0E9DC3]" style="width: {{ $pct }}%"></div>
            </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-slate-500">Task selesai</p>
            <p class="mt-1 text-2xl font-bold text-slate-800">{{ $selesai }} <span
                    class="text-base font-normal text-slate-400">/ {{ $total }}</span></p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-slate-500">File terkumpul</p>
            <p class="mt-1 text-2xl font-bold text-slate-800">{{ $terkumpul }} <span
                    class="text-base font-normal text-slate-400">/ {{ $total }}</span></p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="font-semibold text-slate-800">Daftar Task</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="bg-[#EAF4F8] text-[#334155] text-xs">
                    <tr>
                        <th class="px-5 py-3 font-medium">Task</th>
                        <th class="px-5 py-3 font-medium">Dikerjakan oleh</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium">Pengumpulan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($project->tasks as $task)
                        <tr wire:key="project-task-{{ $task->id_task }}" class="hover:bg-slate-50 transition">
                            <td class="px-5 py-4 align-top">
                                <label class="flex items-start gap-3">
                                    <input type="checkbox" wire:click="toggleTask('{{ $task->id_task }}')"
                                        @checked($task->selesai)
                                        class="mt-0.5 h-4 w-4 rounded border-slate-300 text-[#0E9DC3] focus:ring-[#0E9DC3] cursor-pointer">
                                    <span
                                        class="{{ $task->selesai ? 'text-slate-400 line-through' : 'text-slate-700' }}">{{ $task->label }}</span>
                                </label>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <span
                                    class="inline-flex rounded-full bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600">{{ $task->karyawan->nama ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <span
                                    class="inline-flex rounded-full px-3 py-1.5 text-xs font-medium {{ $task->selesai ? 'bg-emerald-100 text-emerald-700 border border-emerald-500' : 'bg-amber-100 text-amber-700 border border-amber-500' }}">
                                    {{ $task->selesai ? 'Selesai' : 'Belum selesai' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 align-top">
                                @if ($task->file_hasil_path)
                                    <div class="flex flex-col gap-1">
                                        <span
                                            class="inline-flex w-fit rounded-full border border-emerald-500 bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700">Sudah
                                            dikumpulkan</span>
                                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($task->file_hasil_path) }}"
                                            target="_blank" rel="noopener noreferrer"
                                            class="max-w-56 truncate text-xs text-[#146C94] hover:underline">
                                            {{ $task->file_hasil_name ?: 'Lihat file hasil' }}
                                        </a>
                                        @if ($task->tanggal_submit)
                                            <span
                                                class="text-xs text-slate-400">{{ $task->tanggal_submit->format('d/m/Y H:i') }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span
                                        class="inline-flex rounded-full border border-amber-500 bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700">Belum
                                        dikumpulkan</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-slate-400">Belum ada task pada project
                                ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        (function () {
            const crumb = document.querySelector('[data-breadcrumb-current]');
            if (crumb) crumb.textContent = @json($project->nama_project);
        })();
    </script>
</div>
