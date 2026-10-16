@php
    $indicator = fn (string $col) => $sort === $col ? ($dir === 'asc' ? '▲' : '▼') : '↕';
    $badge = fn (string $status) => match ($status) {
        'berjalan' => 'bg-emerald-100 text-emerald-700',
        'menunggu di-acc' => 'bg-amber-100 text-amber-700',
        'sudah di-acc' => 'bg-[#CCF4DB] text-emerald-700',
        \App\Models\Tugas::STATUS_TERLAMBAT => 'bg-red-100 text-red-700',
        default => 'bg-[#C0E7FF] text-blue-700',
    };
@endphp

<div>
    <div class="flex flex-col gap-3 w-full mt-2">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-slate-800">Monitor Tugas</h2>
        </div>

        <div class="flex flex-wrap items-end gap-4 bg-white border border-slate-200 rounded-xl shadow-sm p-5">
            <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
                <label for="direksiTaskSearch" class="text-xs font-bold text-slate-500">Cari Judul</label>
                <input id="direksiTaskSearch" type="text" wire:model.live.debounce.300ms="search" placeholder="Judul tugas..."
                    class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
            </div>
            <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
                <label for="direksiTaskDivisi" class="text-xs font-bold text-slate-500">Divisi</label>
                <select id="direksiTaskDivisi" wire:model.live="divisi"
                    class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                    <option value="">Semua Divisi</option>
                    @foreach ($daftarDivisi as $divisiItem)
                        <option value="{{ $divisiItem->id_divisi }}">{{ $divisiItem->nama_divisi }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
                <label for="direksiTaskStatus" class="text-xs font-bold text-slate-500">Status</label>
                <select id="direksiTaskStatus" wire:model.live="status"
                    class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                    <option value="">Semua</option>
                    <option value="baru">Baru</option>
                    <option value="berjalan">Berjalan</option>
                    <option value="menunggu di-acc">Menunggu di-acc</option>
                    <option value="sudah di-acc">Sudah di-acc</option>
                    <option value="terlambat">Terlambat</option>
                </select>
            </div>
            <div class="flex shrink-0 gap-2 sm:ml-auto">
                <button type="button" wire:click="resetFilters"
                    class="h-10 inline-flex items-center px-4 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">Reset</button>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-4 font-medium whitespace-nowrap">
                                <button type="button" wire:click="sortBy('judul')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                    Nama Tugas <span class="{{ $sort === 'judul' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('judul') }}</span>
                                </button>
                            </th>
                            <th class="px-6 py-4 font-medium whitespace-nowrap">Divisi</th>
                            <th class="px-6 py-4 font-medium whitespace-nowrap">Staff</th>
                            <th class="px-6 py-4 font-medium whitespace-nowrap text-center">
                                <button type="button" wire:click="sortBy('status')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                    Keterangan <span class="{{ $sort === 'status' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('status') }}</span>
                                </button>
                            </th>
                            <th class="px-6 py-4 font-medium whitespace-nowrap text-center">
                                <button type="button" wire:click="sortBy('tanggal')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                    Tenggat <span class="{{ $sort === 'tanggal' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('tanggal') }}</span>
                                </button>
                            </th>
                            <th class="px-6 py-4 font-medium whitespace-nowrap text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($rows as $task)
                            @php $status = $task->status_efektif; @endphp
                            <tr wire:key="direksi-tugas-{{ $task->id_tugas }}" class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 font-semibold text-slate-800 whitespace-nowrap">{{ $task->judul_tugas }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $task->karyawan?->divisi?->nama_divisi ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $task->karyawan?->nama ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="inline-flex gap-1">
                                        @if ($task->is_terlambat && in_array($task->status, [\App\Models\Tugas::STATUS_MENUNGGU_ACC, \App\Models\Tugas::STATUS_SUDAH_ACC], true))
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $task->status === \App\Models\Tugas::STATUS_SUDAH_ACC ? 'bg-[#CCF4DB] text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ $task->status }}</span>
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">Terlambat</span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $badge($status) }}">{{ $status }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-500 text-center whitespace-nowrap">{{ $task->deadline ? \Illuminate\Support\Carbon::parse($task->deadline)->format('d/m/Y H:i') : '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-xs">
                                    <a href="{{ url('/direksi/tugas/' . $task->id_tugas) }}"
                                        class="text-[#0c88a9] hover:text-[#104958] transition font-medium">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-slate-400">Belum ada tugas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>{{ $rows->links('vendor.pagination.custom') }}</div>
    </div>
</div>
