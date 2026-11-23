@php
    $indicator = fn (string $col) => $sort === $col ? ($dir === 'asc' ? '▲' : '▼') : '↕';
    $cellPadding = 'px-6 py-4';
@endphp

<div class="flex flex-col gap-3 w-full mt-2">
    <div class="flex flex-wrap items-end gap-4 bg-white border border-slate-200 rounded-xl shadow-sm p-5">
        <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
            <label for="hrdDivisiSearch" class="text-xs font-bold text-slate-500">Cari Kode/Nama</label>
            <input id="hrdDivisiSearch" type="text" wire:model.live.debounce.300ms="search" placeholder="Kode atau nama divisi..."
                class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
        </div>
        @if ($showHierarchy)
            <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
                <label for="divisiTipe" class="text-xs font-bold text-slate-500">Tingkatan</label>
                <select id="divisiTipe" wire:model.live="tipe"
                    class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                    <option value="">Semua</option>
                    @foreach (\App\Models\Divisi::TIPE_OPTIONS as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        @endif
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
        @if ($manage)
            <h2 class="text-xl font-bold text-slate-800">Daftar Divisi</h2>
        @else
            <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2.5">
                <span class="shrink-0 rounded-lg bg-[#E3F1F7] p-1.5 text-[#146C94]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M4 18h16M6 18v-7m4 7v-7m4 7v-7m4 7v-7M2 10l10-6 10 6"></path>
                    </svg>
                </span>
                <span>Struktur &amp; Daftar Divisi</span>
            </h2>
        @endif
        @if ($manage)
            <button type="button" onclick="bukaModalDivisi()"
                class="text-sm px-4 py-2 bg-[#0097B2] text-white rounded-lg font-medium hover:bg-[#008199] transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path></svg>
                Tambah
            </button>
        @endif
    </div>

    <div class="relative bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
        <x-table-loading-overlay target="search, status, parent, sortBy, resetFilters, previousPage, nextPage, gotoPage" />
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 {{ $showHierarchy ? '' : 'min-w-[720px] table-fixed' }}">
                <thead class="bg-[#EAF4F8] text-[#334155] text-xs">
                    <tr>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap {{ $showHierarchy ? '' : 'w-[12%]' }}">
                            <button type="button" wire:click="sortBy('kode')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Kode <span class="{{ $sort === 'kode' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('kode') }}</span>
                            </button>
                        </th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap {{ $showHierarchy ? '' : 'w-[32%]' }}">
                            <button type="button" wire:click="sortBy('nama')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                {{ $showHierarchy ? 'Struktur Divisi' : 'Nama Divisi' }}
                                <span class="{{ $sort === 'nama' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('nama') }}</span>
                            </button>
                        </th>
                        @if ($showHierarchy)
                            <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Kepala Divisi / Pimpinan</th>
                        @endif
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap {{ $showHierarchy ? '' : 'w-[14%]' }}">
                            <button type="button" wire:click="sortBy('staff')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                {{ $showHierarchy ? 'Anggota' : 'Jumlah Staff' }}
                                <span class="{{ $sort === 'staff' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('staff') }}</span>
                            </button>
                        </th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap {{ $showHierarchy ? '' : 'w-[14%]' }}">Status</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center {{ $showHierarchy ? '' : 'w-[28%]' }}">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($rows as $divisi)
                        @php
                            $memberCount = (int) ($divisi->karyawans_count ?? 0);
                            $head = $showHierarchy ? $divisi->penanggungJawab() : null;
                            $staffCount = $showHierarchy ? $divisi->anggotaStaff()->count() : 0;
                            $level = (int) ($divisi->level ?? 3);
                            $indent = max(0, ($level - 1) * 18);
                            $typePalette = \App\Support\StatusPalette::divisionType($divisi->tipe);
                            $capacity = $divisi->isGrouping()
                                ? 'Grup'
                                : ($divisi->kapasitas === null ? 'Tanpa batas' : $memberCount.'/'.$divisi->kapasitas);
                        @endphp
                        <tr wire:key="divisi-{{ $divisi->id_divisi }}" class="hover:bg-slate-50 transition">
                            <td class="{{ $cellPadding }} font-semibold text-slate-800 whitespace-nowrap">
                                {{ $divisi->kode_divisi }}
                            </td>
                            <td class="{{ $cellPadding }} text-slate-700">
                                @if ($showHierarchy)
                                    <div class="flex items-start gap-2" style="padding-left: {{ $indent }}px">
                                        <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded {{ $level > 1 ? 'text-[#146C94]' : 'text-amber-500' }}">
                                            @if ($level === 1)
                                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M10 2 2.5 6 10 10l7.5-4L10 2Zm-6 7.6L10 13l6-3.4V13l-6 3.4L4 13v-3.4Z"/></svg>
                                            @elseif ($level === 2)
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.09 9.09 0 0 0 3.74-.35 3 3 0 0 0-4.68-2.05M18 18.72a9.09 9.09 0 0 1-6 2.03 9.09 9.09 0 0 1-6-2.03M18 18.72v-1.5a3 3 0 0 0-2-2.83M6 18.72a9.09 9.09 0 0 1-3.74-.35 3 3 0 0 1 4.68-2.05M6 18.72v-1.5a3 3 0 0 1 2-2.83m8-4.14a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path></svg>
                                            @else
                                                <span aria-hidden="true">↳</span>
                                            @endif
                                        </span>
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="font-semibold text-slate-800">{{ $divisi->nama_divisi }}</span>
                                                <x-status-badge :status="$divisi->tipe" :label="$divisi->labelTipe()" :palette="$typePalette" dot />
                                            </div>
                                            <div class="mt-0.5 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                                @if ($divisi->parent)
                                                    <span>Induk: {{ $divisi->parent->nama_divisi }}</span>
                                                @endif
                                                <span>Kapasitas: {{ $capacity }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="block break-words font-medium text-slate-800">{{ $divisi->nama_divisi }}</span>
                                @endif
                            </td>
                            @if ($showHierarchy)
                                <td class="{{ $cellPadding }} text-slate-700">
                                    @if ($head)
                                        <div class="font-medium text-slate-800">{{ $head->nama }}</div>
                                        <div class="text-xs text-slate-500">{{ $head->jabatan ?: '-' }}</div>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                            @endif
                            <td class="{{ $cellPadding }} text-slate-700 whitespace-nowrap">
                                @if ($showHierarchy)
                                    <span class="font-semibold text-slate-800">{{ $memberCount }}</span>
                                    @if ($divisi->isGrouping())
                                        <span class="text-xs text-slate-500">anggota langsung</span>
                                    @elseif ($divisi->isSinglePerson() || $divisi->isDirekturUtama())
                                        <span class="text-xs text-slate-500">orang</span>
                                    @elseif ($memberCount > 0)
                                        <span class="text-xs text-slate-500">({{ $staffCount }} staff)</span>
                                    @endif
                                @else
                                    <span class="font-semibold text-slate-800">{{ $memberCount }}</span>
                                @endif
                            </td>
                            <td class="{{ $cellPadding }} whitespace-nowrap">
                                <x-status-badge :status="$divisi->status_aktif" :label="$divisi->status_aktif" dot />
                            </td>
                            <td class="{{ $cellPadding }} whitespace-nowrap text-center text-sm">
                                <a href="{{ url('/hrd/detailDivisi/' . $divisi->id_divisi) }}"
                                    class="text-[#0097B2] hover:text-[#008199] font-medium hover:underline">
                                    {{ $manage ? 'Detail' : 'Selengkapnya' }}
                                </a>
                                @if ($manage)
                                    <button type="button" onclick="bukaModalEditDivisi(this)"
                                        data-id="{{ $divisi->id_divisi }}"
                                        data-kode="{{ $divisi->kode_divisi }}"
                                        data-nama="{{ $divisi->nama_divisi }}"
                                        data-tipe="{{ $divisi->tipe }}"
                                        data-parent="{{ $divisi->parent_id }}"
                                        data-kapasitas="{{ $divisi->kapasitas }}"
                                        data-urutan="{{ $divisi->urutan }}"
                                        class="ml-3 text-amber-600 hover:text-amber-800 font-medium hover:underline">
                                        Edit
                                    </button>
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
                            <td colspan="{{ $showHierarchy ? 6 : 5 }}" class="px-6 py-10 text-center text-slate-400">
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

        @if ($rows->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                <div class="flex justify-center">
                    {{ $rows->links('vendor.pagination.custom') }}
                </div>
            </div>
        @endif
    </div>
</div>
