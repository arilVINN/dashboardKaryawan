@php
    $indicator = fn (string $col) => $sort === $col ? ($dir === 'asc' ? '▲' : '▼') : '↕';
    $cellPadding = 'px-6 py-4';
@endphp

<div class="flex flex-col gap-3 w-full mt-2">
    <div class="flex flex-wrap items-end gap-4 bg-white border border-slate-200 rounded-xl shadow-sm p-5">
        <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
            <label for="divisiSearch" class="text-xs font-bold text-slate-500">Cari Kode/Nama</label>
            <input id="divisiSearch" type="text" wire:model.live.debounce.300ms="search" placeholder="Kode atau nama divisi..."
                class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
        </div>
        <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
            <label for="divisiStatus" class="text-xs font-bold text-slate-500">Status</label>
            <select id="divisiStatus" wire:model.live="status"
                class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                <option value="">Semua</option>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
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

    <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold text-slate-800">{{ $manage ? 'Manajemen Divisi' : 'Daftar Divisi' }}</h2>
        @if ($manage)
            <button type="button" onclick="bukaModalDivisi()"
                class="text-sm px-4 py-2 bg-[#0097B2] text-white rounded-lg font-medium hover:bg-[#008199] transition">
                Tambah
            </button>
        @endif
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">
                            <button type="button" wire:click="sortBy('kode')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Kode Divisi <span class="{{ $sort === 'kode' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('kode') }}</span>
                            </button>
                        </th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">
                            <button type="button" wire:click="sortBy('nama')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Nama Divisi <span class="{{ $sort === 'nama' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('nama') }}</span>
                            </button>
                        </th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">
                            <button type="button" wire:click="sortBy('staff')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Jumlah Staff <span class="{{ $sort === 'staff' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('staff') }}</span>
                            </button>
                        </th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Status</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($rows as $divisi)
                        <tr wire:key="divisi-{{ $divisi->id_divisi }}" class="hover:bg-slate-50 transition">
                            <td class="{{ $cellPadding }} font-semibold text-slate-800 whitespace-nowrap">
                                {{ $divisi->kode_divisi }}
                            </td>
                            <td class="{{ $cellPadding }} text-slate-700">
                                {{ $divisi->nama_divisi }}
                            </td>
                            <td class="{{ $cellPadding }} text-slate-700">
                                {{ $divisi->karyawans_count ?? 0 }}
                            </td>
                            <td class="{{ $cellPadding }} whitespace-nowrap">
                                @if (strtolower($divisi->status_aktif ?? '') === 'aktif')
                                    <span class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">
                                        {{ $divisi->status_aktif }}
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-500 rounded-full text-xs font-bold">
                                        {{ $divisi->status_aktif }}
                                    </span>
                                @endif
                            </td>
                            <td class="{{ $cellPadding }} whitespace-nowrap text-center text-xs">
                                <a href="{{ url('/hrd/detailDivisi/' . $divisi->id_divisi) }}"
                                    class="text-[#0097B2] hover:text-[#008199] font-medium hover:underline">
                                    {{ $manage ? 'Detail' : 'Selengkapnya' }}
                                </a>
                                @if ($manage)
                                    <button type="button" onclick="hapusDivisi(this)" data-id="{{ $divisi->id_divisi }}"
                                        data-nama="{{ $divisi->nama_divisi }}"
                                        class="ml-3 text-red-600 hover:text-red-800 font-medium hover:underline">
                                        Hapus
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                                @if ($hasActiveFilters)
                                    <span>Tidak ada data yang cocok dengan filter.</span>
                                    <button type="button" wire:click="resetFilters"
                                        class="mt-1 inline-block text-sm text-[#0097B2] hover:underline">Reset filter</button>
                                @else
                                    {{ $manage ? 'Belum ada data divisi.' : 'Belum ada divisi.' }}
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $rows->links() }}</div>
</div>
