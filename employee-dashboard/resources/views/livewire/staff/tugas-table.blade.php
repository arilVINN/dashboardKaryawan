@php
    $indicator = fn (string $col) => $sort === $col ? ($dir === 'asc' ? '▲' : '▼') : '↕';

    $statusClass = [
        'baru' => 'bg-[#C0E7FF] text-blue-700',
        'berjalan' => 'bg-[#DFE4EA] text-slate-700',
        'menunggu di-acc' => 'bg-[#FFF7ED] text-[#C2410C]',
        'sudah di-acc' => 'bg-[#CCF4DB] text-emerald-700',
        'telat' => 'bg-red-100 text-red-700',
    ];
@endphp

<div class="flex flex-col gap-3 w-full mt-2">
    <div class="flex flex-wrap items-end gap-4 bg-white border border-slate-200 rounded-xl shadow-sm p-5">
        <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
            <label for="staffTugasSearch" class="text-xs font-bold text-slate-500">Cari Judul</label>
            <input id="staffTugasSearch" type="text" wire:model.live.debounce.300ms="search" placeholder="Judul tugas..."
                class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
        </div>
        <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
            <label for="staffTugasStatus" class="text-xs font-bold text-slate-500">Status</label>
            <select id="staffTugasStatus" wire:model.live="status"
                class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                <option value="">Semua</option>
                <option value="baru">Baru</option>
                <option value="berjalan">Berjalan</option>
                <option value="menunggu di-acc">Menunggu di-acc</option>
                <option value="sudah di-acc">Sudah di-acc</option>
                <option value="telat">Telat</option>
            </select>
        </div>
        <div class="flex shrink-0 gap-2 sm:ml-auto">
            <button type="button" wire:click="resetFilters"
                class="h-10 inline-flex items-center px-4 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">Reset</button>
        </div>
    </div>

    @if (!empty($chips))
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-bold text-slate-500 uppercase">Filter aktif:</span>
            @foreach ($chips as $chip)
                <span data-filter-chip="{{ $chip['name'] }}"
                    class="inline-flex items-center gap-1.5 pl-2.5 pr-1.5 py-1 rounded-full bg-cyan-50 text-cyan-700 text-xs font-semibold border border-cyan-100">
                    {{ $chip['label'] }}
                    <button type="button" wire:click="clearFilter('{{ $chip['name'] === 'q' ? 'search' : $chip['name'] }}')"
                        data-remove-filter="{{ $chip['name'] }}" title="Hapus filter"
                        class="inline-flex items-center justify-center w-4 h-4 rounded-full text-cyan-500 hover:bg-cyan-200 hover:text-cyan-900">&times;</button>
                </span>
            @endforeach
        </div>
    @endif

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-4 font-medium whitespace-nowrap">
                            <button type="button" wire:click="sortBy('judul')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Judul <span class="{{ $sort === 'judul' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('judul') }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-4 font-medium whitespace-nowrap">
                            <button type="button" wire:click="sortBy('tenggat')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Tenggat <span class="{{ $sort === 'tenggat' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('tenggat') }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-4 font-medium whitespace-nowrap">
                            <button type="button" wire:click="sortBy('status')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Status <span class="{{ $sort === 'status' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('status') }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-4 font-medium whitespace-nowrap text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($rows as $row)
                        @php $st = $row->status_efektif; @endphp
                        <tr wire:key="tugas-{{ $row->id_tugas }}" class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-800 whitespace-nowrap">{{ $row->judul_tugas }}</td>
                            <td class="px-6 py-4 text-slate-500 whitespace-nowrap">{{ $row->deadline ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusClass[$st] ?? 'bg-slate-100 text-slate-700' }}">
                                    {{ $st }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <a href="{{ url('/tugas/detail/' . $row->id_tugas) }}"
                                    class="text-[#0097B2] hover:text-[#008199] font-medium hover:underline">Lihat</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-slate-400">Belum ada tugas yang ditugaskan kepada Anda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $rows->links() }}</div>
</div>
