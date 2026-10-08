@php
    $indicator = fn (string $col) => $sort === $col ? ($dir === 'asc' ? '▲' : '▼') : '↕';
@endphp

<div class="flex flex-col gap-3 w-full mt-2">
    <div class="flex flex-wrap items-end gap-4 bg-white border border-slate-200 rounded-xl shadow-sm p-5">
        <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
            <label for="staffPesanSearch" class="text-xs font-bold text-slate-500">Cari</label>
            <input id="staffPesanSearch" type="text" wire:model.live.debounce.300ms="search" placeholder="Pengirim / topik..."
                class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
        </div>
        <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
            <label for="staffPesanJenis" class="text-xs font-bold text-slate-500">Jenis</label>
            <select id="staffPesanJenis" wire:model.live="jenis"
                class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                <option value="">Semua</option>
                <option value="tugas">Pesan Tugas</option>
                <option value="langsung">Pesan Langsung</option>
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
                            <button type="button" wire:click="sortBy('pengirim')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Pengirim <span class="{{ $sort === 'pengirim' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('pengirim') }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-4 font-medium whitespace-nowrap">
                            <button type="button" wire:click="sortBy('judul')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Topik / Judul <span class="{{ $sort === 'judul' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('judul') }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-4 font-medium whitespace-nowrap">
                            <button type="button" wire:click="sortBy('tanggal')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Tanggal <span class="{{ $sort === 'tanggal' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('tanggal') }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-4 font-medium whitespace-nowrap text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($rows as $row)
                        <tr wire:key="pesan-{{ $row['jenis'] }}-{{ $row['link_id'] }}" class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-800 whitespace-nowrap">{{ $row['pengirim'] }}</td>
                            <td class="px-6 py-4 max-w-[160px] truncate text-slate-500">{{ $row['judul'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-500">{{ $row['tanggal'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <a href="{{ url('/pesan/detail/' . $row['link_id']) }}"
                                    class="text-[#0097B2] hover:text-[#008199] font-medium hover:underline">Baca</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-slate-400">Belum ada pesan masuk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $rows->links() }}</div>
</div>
