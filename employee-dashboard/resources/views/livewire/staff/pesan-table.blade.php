@php
    $indicator = fn(string $col) => $sort === $col ? ($dir === 'asc' ? '▲' : '▼') : '↕';
@endphp

<div class="w-full">
    {{-- Compact filters --}}
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <div class="relative min-w-0 flex-1">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input id="staffPesanSearch" type="text" wire:model.live.debounce.300ms="search"
                placeholder="Cari pengirim atau topik..."
                class="h-10 w-full rounded-lg border border-slate-300 pl-9 pr-3 text-sm text-[#172B36] outline-none focus:border-[#146C94]">
        </div>

        <select id="staffPesanJenis" wire:model.live="jenis"
            class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-[#172B36] outline-none focus:border-[#146C94] sm:w-48">
            <option value="">Semua jenis</option>
            <option value="tugas">Pesan Tugas</option>
            <option value="langsung">Pesan Langsung</option>
        </select>

        @if ($search !== '' || $jenis !== '')
            <button type="button" wire:click="resetFilters"
                class="h-10 shrink-0 text-sm font-medium text-[#146C94] hover:underline">
                Reset
            </button>
        @endif
    </div>

    <div class="overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] table-fixed text-left text-sm text-[#475569]">
                <thead class="bg-[#EAF4F8] text-[#334155] text-xs">
                    <tr>
                        <th class="w-[45%] px-4 py-3 text-left font-medium whitespace-nowrap">
                            <button type="button" wire:click="sortBy('judul')"
                                class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Topik / judul
                                <span class="{{ $sort === 'judul' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('judul') }}</span>
                            </button>
                        </th>
                        <th class="w-[28%] px-4 py-3 text-left font-medium whitespace-nowrap">
                            <button type="button" wire:click="sortBy('pengirim')"
                                class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Pengirim
                                <span class="{{ $sort === 'pengirim' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('pengirim') }}</span>
                            </button>
                        </th>
                        <th class="w-[18%] px-4 py-3 text-left font-medium whitespace-nowrap">
                            <button type="button" wire:click="sortBy('tanggal')"
                                class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Tanggal
                                <span class="{{ $sort === 'tanggal' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('tanggal') }}</span>
                            </button>
                        </th>
                        <th class="w-20 px-4 py-3 text-right font-medium whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EEF2F5]">
                    @forelse ($rows as $row)
                        @php
                            $url = url('/pesan/detail/' . $row['link_id']);
                            $date = !empty($row['tanggal_raw'])
                                ? \Illuminate\Support\Carbon::parse($row['tanggal_raw'])
                                : null;
                            $dateLabel = $date
                                ? ($date->isToday()
                                    ? 'Hari ini'
                                    : ($date->isYesterday()
                                        ? 'Kemarin'
                                        : $date->locale('id')->translatedFormat('j M Y')))
                                : '-';
                            $hasUnread = !empty($row['unread']);
                        @endphp
                        <tr wire:key="pesan-{{ $row['jenis'] }}-{{ $row['link_id'] }}"
                            role="link" tabindex="0"
                            onclick="window.location.href='{{ $url }}'"
                            onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location.href='{{ $url }}'; }"
                            class="h-16 cursor-pointer hover:bg-[#F5FAFC] focus:bg-[#F5FAFC] focus:outline-none">
                            <td class="px-4 py-2">
                                <div class="flex min-w-0 items-center gap-2">
                                    @if ($hasUnread)
                                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-[#146C94]"></span>
                                    @endif
                                    <span class="truncate text-sm text-[#172B36] {{ $hasUnread ? 'font-semibold' : 'font-medium' }}">
                                        {{ $row['judul'] }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-2">
                                @if (($row['arah'] ?? 'masuk') === 'keluar')
                                    <div class="text-sm text-[#172B36]">Anda</div>
                                    <div class="text-xs text-[#4B5563]">Ke: {{ $row['pengirim'] }}</div>
                                @else
                                    <div class="text-sm text-[#172B36]">{{ $row['pengirim'] }}</div>
                                    @if (!empty($row['pengirim_username']))
                                        <div class="text-xs text-[#4B5563]">{{ $row['pengirim_username'] }}</div>
                                    @endif
                                @endif
                            </td>
                            <td class="px-4 py-2 text-sm text-[#4B5563]">{{ $dateLabel }}</td>
                            <td class="px-4 py-2 text-right">
                                <a href="{{ $url }}" onclick="event.stopPropagation()"
                                    class="text-sm font-medium text-[#146C94] hover:underline">
                                    Baca
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12">
                                <div class="flex flex-col items-center justify-center gap-2 text-center">
                                    <svg class="h-10 w-10 text-slate-300" fill="none" stroke="currentColor"
                                        stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                    </svg>
                                    <p class="text-sm font-medium text-[#4B5563]">Tidak ada pesan ditemukan</p>
                                    <button type="button" wire:click="resetFilters"
                                        class="text-sm font-medium text-[#146C94] hover:underline">
                                        Reset filter
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-[#EEF2F5] px-4 py-3">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-[13px] text-[#4B5563]">
                    Menampilkan {{ $rows->firstItem() ?? 0 }}-{{ $rows->lastItem() ?? 0 }} dari {{ $rows->total() }} pesan
                </p>

                @if ($rows->hasPages())
                    <div>{{ $rows->links('vendor.pagination.custom') }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
