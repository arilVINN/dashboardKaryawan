<div class="flex flex-col gap-3 w-full mt-6">
    <div>
        <h2 class="text-xl font-bold text-slate-800">Daftar Project</h2>
        <p class="text-sm text-slate-500 mt-1">Pilih project untuk melihat detail task dan pengumpulan.</p>
    </div>

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
                                    <a href="{{ route('kadiv.project.show', $project->id_project) }}"
                                        class="inline-flex items-center rounded-lg bg-[#146C94] px-3 py-2 text-xs font-semibold text-white hover:bg-[#0f5878] transition">
                                        Lihat detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
