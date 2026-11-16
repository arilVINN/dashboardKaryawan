@php
    $indicator = fn (string $col) => $sort === $col ? ($dir === 'asc' ? '▲' : '▼') : '↕';
@endphp

<div class="flex flex-col gap-3 w-full mt-4">
    <div class="flex flex-wrap items-center gap-2">
        <div class="relative w-full sm:w-[280px]">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6B7280]"
                fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"></path>
            </svg>
            <input id="kadivStaffSearch" type="text" wire:model.live.debounce.300ms="search"
                value="{{ $search }}" placeholder="Cari nama staff..."
                class="h-10 w-full rounded-lg border bg-white pl-9 pr-3 text-sm text-[#172B36] outline-none transition placeholder:text-[#6B7280] focus:border-[#146C94] focus:ring-1 focus:ring-[#146C94] {{ $search !== '' ? 'border-[#146C94]' : 'border-[#D5DEE3]' }}">
        </div>

        @if ($search !== '')
            <button type="button" wire:click="resetFilters"
                class="inline-flex h-10 shrink-0 items-center gap-1 text-sm font-medium text-[#146C94] transition hover:text-[#0B4A66]">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                </svg>
                Reset
            </button>
        @endif
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[860px] text-left text-sm text-slate-600">
            <thead class="bg-[#EAF4F8] text-[#334155] text-xs">
                <tr>
                    <th class="px-4 py-3 font-medium text-left">
                        <button type="button" wire:click="sortBy('nama')"
                            class="inline-flex items-center gap-1 hover:text-[#146C94]">
                            Nama staff <span
                                class="{{ $sort === 'nama' ? 'text-[#334155]' : 'text-slate-300' }}">{{ $indicator('nama') }}</span>
                        </button>
                    </th>
                    <th class="px-4 py-3 font-medium text-left">Jabatan</th>
                    <th class="px-4 py-3 font-medium text-left">
                        <button type="button" wire:click="sortBy('tugas')"
                            class="inline-flex items-center gap-1 hover:text-[#146C94]">
                            Tugas <span
                                class="{{ $sort === 'tugas' ? 'text-[#334155]' : 'text-slate-300' }}">{{ $indicator('tugas') }}</span>
                        </button>
                    </th>
                    <th class="px-4 py-3 font-medium text-left">
                        <button type="button" wire:click="sortBy('login')"
                            class="inline-flex items-center gap-1 hover:text-[#146C94]">
                            Terakhir login <span
                                class="{{ $sort === 'login' ? 'text-[#334155]' : 'text-slate-300' }}">{{ $indicator('login') }}</span>
                        </button>
                    </th>
                    <th class="w-10 px-4 py-3"></th>
                </tr>
            </thead>
            <tbody id="kadiv-staff-rows" class="divide-y divide-[#EEF2F5]">
                @forelse ($rows as $person)
                    @php
                        $initials = collect(preg_split('/\s+/', trim((string) $person->nama)))
                            ->filter()
                            ->map(fn ($part) => mb_substr($part, 0, 1))
                            ->take(2)
                            ->implode('');
                        $jabatan = trim((string) $person->jabatan);
                        $detailUrl = url('/kadiv/manajemenStaff/' . $person->id_karyawan);
                    @endphp
                    <tr wire:key="staff-{{ $person->id_karyawan }}"
                        class="h-[54px] cursor-pointer hover:bg-[#F5FAFC] transition"
                        onclick="window.location.href='{{ $detailUrl }}'">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2.5">
                                <span
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#AFD3E2] text-[11px] font-semibold text-[#0B4A66]">{{ $initials ?: '?' }}</span>
                                <span class="text-[14px] font-medium text-[#172B36]">{{ $person->nama }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="block max-w-[220px] truncate text-[14px] font-normal {{ $jabatan !== '' ? 'text-[#172B36]' : 'text-[#4B5563]' }}"
                                title="{{ $jabatan !== '' ? $jabatan : '-' }}">{{ $jabatan !== '' ? $jabatan : '-' }}</span>
                        </td>
                        <td class="px-4 py-3 text-left text-[14px] text-[#172B36]">{{ $person->tugas_count }} tugas</td>
                        <td class="px-4 py-3 text-left text-[14px] whitespace-nowrap">
                            @if ($person->user?->last_login_at)
                                <span class="text-[#172B36]">{{ $person->user->last_login_at->translatedFormat('j M Y, H:i') }}</span>
                            @else
                                <span class="text-[#4B5563]">Belum pernah login</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ $detailUrl }}" onclick="event.stopPropagation()"
                                aria-label="Buka detail staff"
                                class="inline-flex text-[#5B6470] transition hover:text-[#146C94]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m8.25 4.5 7.5 7.5-7.5 7.5"></path>
                                </svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-slate-400">Belum ada staff di divisi ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($rows->total() > 0)
            <div class="border-t border-[#EEF2F5] px-4 pt-4 pb-6">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] sm:items-center">
                    <p class="text-[12px] text-[#4B5563] sm:text-[13px]">
                        Menampilkan {{ $rows->firstItem() }}-{{ $rows->lastItem() }} dari {{ $rows->total() }} staff
                    </p>
                    <div class="flex justify-center">
                        @if ($rows->hasPages())
                            {{ $rows->links('vendor.pagination.custom') }}
                        @endif
                    </div>
                    <div class="hidden sm:block" aria-hidden="true"></div>
                </div>
            </div>
        @endif
    </div>
</div>
