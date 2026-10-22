<div class="flex flex-col gap-3 w-full mt-6">
    <h2 class="text-xl font-bold text-slate-800">Daftar Project</h2>

    @if ($projects->isEmpty())
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-10 text-center text-slate-400">
            Belum ada project.
        </div>
    @else
        <div class="space-y-3">
            @foreach ($projects as $project)
                @php
                    $total = $project->tasks->count();
                    $selesai = $project->tasks->where('selesai', true)->count();
                    $pct = $total > 0 ? round(($selesai / $total) * 100) : 0;
                    $isOpen = in_array($project->id_project, $expanded);
                @endphp

                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <button type="button" wire:click="toggleExpand('{{ $project->id_project }}')"
                        class="w-full flex items-center justify-between px-5 py-4 hover:bg-slate-50 transition">
                        <div class="flex-1 text-left">
                            <p class="font-semibold text-slate-800">{{ $project->nama_project }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $selesai }} / {{ $total }} task selesai</p>
                        </div>

                        <div class="flex items-center gap-3 shrink-0">
                            <div class="w-28 h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-[#0E9DC3] transition-all" style="width: {{ $pct }}%"></div>
                            </div>
                            <span class="text-xs font-bold text-slate-500 w-9 text-right">{{ $pct }}%</span>
                            <svg class="w-4 h-4 text-slate-400 transition-transform {{ $isOpen ? 'rotate-180' : '' }}"
                                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>

                    @if ($isOpen)
                        <div class="border-t border-slate-100 divide-y divide-slate-100">
                            @if ($project->deskripsi || $project->file_path)
                                <div class="px-5 py-3 bg-slate-50">
                                    @if ($project->deskripsi)
                                        <p class="text-sm text-slate-600 whitespace-pre-line">{{ $project->deskripsi }}</p>
                                    @endif
                                    @if ($project->file_path)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($project->file_path) }}"
                                            target="_blank" rel="noopener noreferrer"
                                            class="inline-block mt-2 text-sm font-medium text-[#146C94] hover:underline">
                                            {{ $project->file_name ?: 'Lihat file project' }}
                                        </a>
                                    @endif
                                </div>
                            @endif

                            @foreach ($project->tasks as $task)
                                <label class="flex items-center gap-3 px-5 py-3 hover:bg-slate-50 transition cursor-pointer">
                                    <input type="checkbox" wire:click="toggleTask('{{ $task->id_task }}')"
                                        @checked($task->selesai)
                                        class="w-4 h-4 rounded border-slate-300 text-[#0E9DC3] focus:ring-[#0E9DC3] cursor-pointer">

                                    <span class="flex-1 text-sm {{ $task->selesai ? 'text-slate-400 line-through' : 'text-slate-700' }}">
                                        {{ $task->label }}
                                    </span>

                                    <span class="text-xs font-medium text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">
                                        {{ $task->karyawan->nama ?? '-' }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>