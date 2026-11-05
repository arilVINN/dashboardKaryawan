<div class="flex flex-col gap-3 w-full mt-6">
    <div>
        <h2 class="text-xl font-bold text-slate-800">Daftar Project</h2>
    </div>

    @if (session('tugas_success'))
        <div class="text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-md px-3 py-2">
            {{ session('tugas_success') }}
        </div>
    @endif
    @error('projectEdit')
        <div class="text-sm text-red-700 bg-red-50 border border-red-200 rounded-md px-3 py-2">
            {{ $message }}
        </div>
    @enderror

    @if ($projects->isEmpty())
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-10 text-center text-slate-400">
            Belum ada project.
        </div>
    @else
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[850px] text-left text-sm text-slate-600">
                    <thead class="bg-slate-100 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Nama Project</th>
                            <th class="px-5 py-4 font-semibold text-center">Status</th>
                            <th class="px-5 py-4 font-semibold text-center whitespace-nowrap">Tenggat</th>
                            <th class="px-5 py-4 font-semibold">Progress</th>
                            <th class="px-5 py-4 font-semibold text-center">Task Selesai</th>
                            <th class="px-5 py-4 font-semibold text-center">File Terkumpul</th>
                            <th class="px-5 py-4 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach ($projects as $project)
                            @php
                                $total = $project->tasks->count();
                                $selesai = $project->tasks->where('selesai', true)->count();
                                $terkumpul = $project->tasks->whereNotNull('file_hasil_path')->count();
                                $pct = $total > 0 ? round(($selesai / $total) * 100) : 0;
                            @endphp
                            <tr wire:key="project-{{ $project->id_project }}" class="hover:bg-slate-50 transition">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">{{ $project->nama_project }}</p>
                                    <p class="mt-1 max-w-md truncate text-xs text-slate-400">
                                        {{ $project->deskripsi ?: 'Tidak ada deskripsi project.' }}
                                    </p>
                                </td>
                                @php
                                    $status = $project->status_efektif;
                                    $badgeBg = match ($status) {
                                        \App\Models\Tugas::STATUS_BERJALAN => 'bg-emerald-100 text-emerald-700 border border-emerald-500',
                                        \App\Models\Tugas::STATUS_MENUNGGU_ACC => 'bg-amber-100 text-amber-700 border border-amber-500',
                                        \App\Models\Tugas::STATUS_SUDAH_ACC => 'bg-emerald-700 text-white border border-emerald-800',
                                        \App\Models\Tugas::STATUS_TERLAMBAT => 'bg-red-100 text-red-700 border border-red-500',
                                        default => 'bg-[#C0E7FF] text-blue-700 border border-blue-500',
                                    };
                                @endphp
                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $badgeBg }}">
                                        {{ $status }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center whitespace-nowrap text-xs {{ $project->tenggat && $project->tenggat->isPast() ? 'font-bold text-red-600' : 'text-slate-500' }}">
                                    {{ $project->tenggat?->format('d/m/Y H:i') ?? '-' }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex min-w-36 items-center gap-3">
                                        <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-200">
                                            <div class="h-full rounded-full bg-[#0E9DC3]" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="w-10 text-right text-xs font-bold text-slate-600">{{ $pct }}%</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    <span class="font-semibold text-slate-700">{{ $selesai }}</span>
                                    <span class="text-slate-400">/ {{ $total }}</span>
                                </td>
                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    <span class="font-semibold text-slate-700">{{ $terkumpul }}</span>
                                    <span class="text-slate-400">/ {{ $total }}</span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('kadiv.project.show', $project->id_project) }}"
                                            class="inline-flex items-center  text-blue-500 px-3 py-2 text-sm font-semibold hover:text-[#083c53] transition">
                                            Detail
                                        </a>
                                        @if ($project->approved_at)
                                            <span
                                                class="inline-flex items-center px-3 py-2 text-sm font-semibold text-slate-400 cursor-not-allowed"
                                                title="Project yang sudah di-ACC tidak dapat diedit">
                                                Edit
                                            </span>
                                        @else
                                            <button type="button" wire:click="editProject('{{ $project->id_project }}')"
                                                class="inline-flex items-center text-amber-500 px-3 py-2 text-sm font-semibold hover:text-amber-700 transition">
                                                Edit
                                            </button>
                                        @endif
                                        <button type="button" wire:click="deleteProject('{{ $project->id_project }}')"
                                            wire:confirm="Yakin ingin menghapus project ini beserta semua task-nya?"
                                            wire:loading.attr="disabled" wire:target="deleteProject('{{ $project->id_project }}')"
                                            class="inline-flex items-center  text-red-500 px-3 py-2 text-sm font-semibold hover:text-red-700 transition disabled:opacity-60">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 px-4">
            <div class="w-full max-w-lg rounded-xl bg-white shadow-xl">
                <form wire:submit="updateProject">
                    <div class="border-b border-slate-200 px-6 py-4">
                        <h3 class="text-lg font-bold text-slate-800">Edit Project</h3>
                    </div>
                    <div class="space-y-4 px-6 py-5">
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-600">NAMA PROJECT</label>
                            <input type="text" wire:model="namaProject" maxlength="150"
                                class="h-10 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-[#19A7CE]">
                            @error('namaProject') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-600">DESKRIPSI</label>
                            <textarea wire:model="deskripsiProject" rows="3" maxlength="5000"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#19A7CE]"></textarea>
                            @error('deskripsiProject') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-600">TENGGAT</label>
                            <input type="datetime-local" wire:model="tenggatProject"
                                class="h-10 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-[#19A7CE]">
                            @error('tenggatProject') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-600">GANTI FILE PROJECT (OPSIONAL)</label>
                            <input type="file" wire:model="projectFile"
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                                class="w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2">
                            @error('projectFile') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-slate-200 px-6 py-4">
                        <button type="button" wire:click="closeEditModal"
                            class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-700">Batal</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="updateProject"
                            class="rounded-lg bg-[#146C94] px-4 py-2 text-sm font-bold text-white disabled:opacity-60">
                            <span wire:loading.remove wire:target="updateProject">Simpan</span>
                            <span wire:loading wire:target="updateProject">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
