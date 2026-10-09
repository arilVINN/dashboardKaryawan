@php
    $indicator = fn (string $col) => $sort === $col ? ($dir === 'asc' ? '▲' : '▼') : '↕';
@endphp

<div class="flex flex-col gap-3 w-full mt-4">
    <div class="flex flex-wrap items-end gap-4 bg-white border border-slate-200 rounded-xl shadow-sm p-5">
        <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
            <label for="kadivStaffSearch" class="text-xs font-bold text-slate-500">Cari Nama</label>
            <input id="kadivStaffSearch" type="text" wire:model.live.debounce.300ms="search" placeholder="Nama staff..."
                class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
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
                    <button type="button" wire:click="clearFilter('search')"
                        data-remove-filter="{{ $chip['name'] }}" title="Hapus filter"
                        class="inline-flex items-center justify-center w-4 h-4 rounded-full text-cyan-500 hover:bg-cyan-200 hover:text-cyan-900">&times;</button>
                </span>
            @endforeach
        </div>
    @endif

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[650px] text-left text-sm text-slate-600">
            <thead class="bg-slate-100 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">
                        <button type="button" wire:click="sortBy('nama')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                            Nama Staff <span class="{{ $sort === 'nama' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('nama') }}</span>
                        </button>
                    </th>
                    <th class="px-4 py-3 text-center font-medium">
                        <button type="button" wire:click="sortBy('tugas')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                            Tugas <span class="{{ $sort === 'tugas' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('tugas') }}</span>
                        </button>
                    </th>
                    <th class="px-4 py-3 text-center font-medium">
                        <button type="button" wire:click="sortBy('login')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                            Terakhir Login <span class="{{ $sort === 'login' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('login') }}</span>
                        </button>
                    </th>
                    <th class="px-4 py-3 text-center font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody id="kadiv-staff-rows" class="divide-y divide-slate-200">
                @forelse ($rows as $person)
                    <tr wire:key="staff-{{ $person->id_karyawan }}" class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-semibold text-slate-800">{{ $person->nama }}</td>
                        <td class="px-4 py-3 text-center">{{ $person->tugas_count }}</td>
                        <td class="px-4 py-3 text-center">
                            {{ $person->user?->last_login_at?->translatedFormat('d M Y, H:i') ?? 'Belum pernah login' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ url('/kadiv/manajemenStaff/' . $person->id_karyawan) }}"
                                class="font-semibold text-cyan-700 hover:underline">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-slate-400">Belum ada staff di divisi ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $rows->links() }}</div>
</div>
