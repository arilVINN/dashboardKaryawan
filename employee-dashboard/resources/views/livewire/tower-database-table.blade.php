@php
    $indicator = fn(string $col) => $sort === $col ? ($dir === 'asc' ? '▲' : '▼') : '↕';
    $cellPadding = 'px-6 py-3';
    $headPadding = 'px-6 py-3';
    $primaryActionClass = 'inline-flex h-9 w-[104px] items-center justify-center gap-1.5 rounded-lg border border-slate-300 bg-white text-xs font-medium text-slate-700 hover:bg-slate-50 transition';
@endphp

<div class="flex flex-col gap-4 w-full"
     x-data="{ confirmOpen: false, confirmId: null, confirmName: '' }"
     @confirm-delete.window="confirmId = $event.detail.id; confirmName = $event.detail.name; confirmOpen = true">
    <style>
        [x-cloak] { display: none !important; }

        @keyframes arsip-modal-fade-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes arsip-modal-panel-in {
            from { opacity: 0; transform: translateY(16px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .arsip-modal-backdrop {
            animation: arsip-modal-fade-in 0.18s ease-out both;
        }
        .arsip-modal-panel {
            animation: arsip-modal-panel-in 0.24s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        @media (prefers-reduced-motion: reduce) {
            .arsip-modal-backdrop,
            .arsip-modal-panel {
                animation: none;
            }
        }
    </style>

    {{-- Header Judul --}}
    <div class="mb-2 flex items-center justify-between gap-3">
        <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-2.5">
            <span class="p-2 rounded-lg bg-[#E3F1F7] text-[#146C94]">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                </svg>
            </span>
            <span>Direktori Arsip & Data</span>
        </h2>
    </div>

    {{-- Flash Notifications --}}
    @if (session('tower_success'))
        <div class="text-sm text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <span class="font-medium">{{ session('tower_success') }}</span>
            </div>
        </div>
    @endif

    @if (session('tower_error'))
        <div class="text-sm text-red-800 bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            <span class="font-medium">{{ session('tower_error') }}</span>
        </div>
    @endif

    {{-- Filter Ringkas: Pencarian + Toggle Jenis + Reset --}}
    <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
        {{-- Search Input --}}
        <div class="relative w-full sm:w-80 shrink-0">
            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input id="towerSearch" type="text" wire:model.live.debounce.300ms="search"
                placeholder="Cari nama dokumen, mitra, nomor, atau tautan..."
                class="h-10 w-full pl-9 pr-3 border border-slate-300 rounded-lg text-sm outline-none focus:border-[#146C94]">
        </div>

        {{-- Toggle Group Jenis (segmented, seperti switcher di halaman kalender) --}}
        @php
            $jenisTabs = [
                ''      => ['label' => 'Semua',  'count' => $counts['total']],
                'nomor' => ['label' => 'Kontak', 'count' => $counts['nomor']],
                'file'  => ['label' => 'File',   'count' => $counts['file']],
                'link'  => ['label' => 'Tautan', 'count' => $counts['link']],
            ];
        @endphp
        <div class="inline-flex items-center" role="group" aria-label="Filter jenis">
            @foreach ($jenisTabs as $value => $tab)
                <button type="button" wire:click="$set('jenis', @js($value))"
                    class="inline-flex h-10 items-center gap-1.5 border border-slate-300 px-3.5 text-[13px] font-medium transition
                        {{ $loop->first ? 'rounded-l-lg' : '' }}
                        {{ $loop->last ? 'rounded-r-lg' : '' }}
                        {{ ! $loop->first ? '-ml-px' : '' }}
                        {{ $jenis === $value
                            ? 'relative z-10 border-[#146C94] bg-[#146C94] text-white'
                            : 'bg-white text-slate-700 hover:bg-slate-50' }}">
                    <span>{{ $tab['label'] }}</span>
                    <span class="text-xs {{ $jenis === $value ? 'text-white/80' : 'text-slate-400' }}">{{ $tab['count'] }}</span>
                </button>
            @endforeach
        </div>

        {{-- Aksi kanan: Reset (saat filter aktif) + Tambah arsip --}}
        <div class="ml-auto flex items-center gap-3">
            @if ($search !== '' || $jenis !== '')
                <button type="button" wire:click="resetFilters"
                    class="text-sm font-medium text-[#146C94] hover:underline">
                    Reset
                </button>
            @endif

            @if ($canManage)
                <button type="button" wire:click="bukaModalTambah"
                    class="inline-flex h-10 items-center justify-center gap-1.5 shrink-0 rounded-lg bg-[#0097B2] px-4 text-sm font-medium text-white shadow-sm transition hover:bg-[#008199]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                    </svg>
                    <span>Tambah arsip</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Tabel List Data --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-[#EAF4F8] text-[#334155] text-xs">
                    <tr>
                        <th class="{{ $headPadding }} text-left font-medium whitespace-nowrap min-w-[16rem]">
                            <button type="button" wire:click="sortBy('nama')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Nama <span class="{{ $sort === 'nama' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('nama') }}</span>
                            </button>
                        </th>
                        <th class="{{ $headPadding }} text-left font-medium whitespace-nowrap w-36">
                            <button type="button" wire:click="sortBy('jenis')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Jenis <span class="{{ $sort === 'jenis' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('jenis') }}</span>
                            </button>
                        </th>
                        <th class="{{ $headPadding }} text-left font-medium whitespace-nowrap min-w-[16rem]">
                            Detail / Nilai
                        </th>
                        <th class="{{ $headPadding }} text-right font-medium whitespace-nowrap w-48">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EEF2F5]">
                    @forelse ($items as $item)
                        <tr class="h-16 cursor-pointer hover:bg-[#F5FAFC] transition" wire:key="tower-item-{{ $item->id }}">
                            {{-- Kolom 1: Nama --}}
                            <td class="{{ $cellPadding }}">
                                <span class="block max-w-[22rem] truncate font-bold text-slate-900 text-sm leading-snug" title="{{ $item->nama }}">{{ $item->nama }}</span>
                            </td>

                            {{-- Kolom 2: Jenis (Badge netral) --}}
                            <td class="{{ $cellPadding }}">
                                @if ($item->jenis === 'file')
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-[#93C5FD] bg-[#DCEEFE] px-2 py-0.5 text-[11px] font-medium text-[#172B36]">
                                        <svg class="w-3.5 h-3.5 text-[#2563EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <span>File</span>
                                    </span>
                                @elseif ($item->jenis === 'nomor')
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-[#86D9AE] bg-[#D9F5E5] px-2 py-0.5 text-[11px] font-medium text-[#172B36]">
                                        <svg class="w-3.5 h-3.5 text-[#059669]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        <span>Kontak</span>
                                    </span>
                                @elseif ($item->jenis === 'link')
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-[#C4B5FD] bg-[#EDE9FE] px-2 py-0.5 text-[11px] font-medium text-[#172B36]">
                                        <svg class="w-3.5 h-3.5 text-[#8B5CF6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                        </svg>
                                        <span>Tautan</span>
                                    </span>
                                @endif
                            </td>

                            {{-- Kolom 3: Detail / Nilai --}}
                            <td class="{{ $cellPadding }}">
                                @if ($item->jenis === 'file')
                                    <div class="flex flex-col">
                                        <span class="text-[13px] font-medium text-[#334155] truncate max-w-[22rem]" title="{{ $item->file_name ?: 'Dokumen File' }}">{{ $item->file_name ?: 'Dokumen File' }}</span>
                                        @if ($item->file_size)
                                            <span class="text-[13px] text-[#4B5563]">{{ $item->file_size }}</span>
                                        @endif
                                    </div>
                                @elseif ($item->jenis === 'nomor')
                                    <div class="flex flex-col">
                                        <span class="font-mono font-bold text-slate-800 text-[13px] select-all">{{ $item->nilai_kontak }}</span>
                                        @if ($item->email_kontak)
                                            <span class="text-[13px] text-[#4B5563] truncate max-w-[22rem]" title="{{ $item->email_kontak }}">{{ $item->email_kontak }}</span>
                                        @endif
                                    </div>
                                @elseif ($item->jenis === 'link')
                                    @php
                                        $linkHost = parse_url($item->url_link ?? '', PHP_URL_HOST);
                                        $linkHost = $linkHost ? preg_replace('/^www\./i', '', $linkHost) : ($item->url_link ?? '');
                                    @endphp
                                    <a href="{{ $item->url_link }}" target="_blank" rel="noopener noreferrer"
                                       class="block max-w-[22rem] truncate text-[13px] font-medium text-[#146C94] hover:underline"
                                       title="{{ $item->url_link }}">
                                        {{ $linkHost }}
                                    </a>
                                @endif
                            </td>

                            {{-- Kolom 4: Aksi --}}
                            <td class="{{ $cellPadding }}">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Slot 1: Aksi Utama Berdasarkan Jenis --}}
                                    @if ($item->jenis === 'file')
                                        @if ($item->file_path)
                                            <button type="button" wire:click="downloadFile({{ $item->id }})"
                                                class="{{ $primaryActionClass }}" title="Unduh File">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                </svg>
                                                <span>Unduh</span>
                                            </button>
                                        @else
                                            <button type="button" disabled
                                                class="{{ $primaryActionClass }} opacity-50 cursor-not-allowed" title="File kosong">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                </svg>
                                                <span>Unduh</span>
                                            </button>
                                        @endif
                                    @elseif ($item->jenis === 'nomor')
                                        <button type="button"
                                            onclick="salinNomor(@js($item->nilai_kontak), this)"
                                            class="{{ $primaryActionClass }}" title="Salin Nomor">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                            <span>Salin</span>
                                        </button>
                                    @elseif ($item->jenis === 'link')
                                        <a href="{{ $item->url_link }}" target="_blank" rel="noopener noreferrer"
                                           class="{{ $primaryActionClass }}" title="Buka Tautan di Tab Baru">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            </svg>
                                            <span>Buka</span>
                                        </a>
                                    @endif

                                    {{-- Slot 2: Menu Aksi (Edit / Hapus) --}}
                                    @if ($canManage)
                                        <div class="relative" x-data="{ open: false, top: 0, right: 0 }" @click.outside="open = false">
                                            <button type="button"
                                                @click="
                                                    open = !open;
                                                    if (open) {
                                                        const r = $el.getBoundingClientRect();
                                                        top = r.bottom + 6;
                                                        right = Math.max(8, window.innerWidth - r.right);
                                                    }"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 transition"
                                                aria-label="Aksi lainnya" title="Aksi lainnya">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                </svg>
                                            </button>

                                            <div x-show="open" x-cloak
                                                 :style="`position: fixed; top: ${top}px; right: ${right}px;`"
                                                 class="z-50 w-36 overflow-hidden rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                                                <button type="button" wire:click="bukaModalEdit({{ $item->id }})"
                                                    @click="open = false"
                                                    class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50">
                                                    Edit
                                                </button>
                                                <div class="my-1 border-t border-slate-100"></div>
                                                <button type="button"
                                                    @click="open = false; $dispatch('confirm-delete', { id: {{ $item->id }}, name: @js($item->nama) })"
                                                    class="block w-full px-4 py-2 text-left text-sm font-medium text-red-600 hover:bg-red-50">
                                                    Hapus
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <span class="text-sm font-medium">Belum ada data arsip yang cocok.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer: Ringkasan, Pagination & Baris per Halaman --}}
        <div class="border-t border-slate-100 px-6 py-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <p class="text-[13px] text-[#4B5563] lg:shrink-0">
                    Menampilkan {{ $items->firstItem() ?? 0 }}-{{ $items->lastItem() ?? 0 }} dari {{ $items->total() }} data
                </p>

                @if ($items->hasPages())
                    <div class="flex w-full justify-center lg:w-auto lg:flex-1">
                        {{ $items->links('vendor.pagination.custom') }}
                    </div>
                @endif

                <label class="flex items-center gap-2 text-[13px] text-[#4B5563] lg:shrink-0">
                    <span>Baris per halaman</span>
                    <select wire:model.live="perPage"
                        class="h-9 rounded-lg border border-slate-300 bg-white px-2 text-sm text-[#334155] outline-none focus:border-[#146C94]">
                        @foreach ([5, 10, 25, 50] as $size)
                            <option value="{{ $size }}">{{ $size }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        </div>
    </div>

    {{-- Modal Tambah / Edit Data (Konsisten dengan Modal Tugas) --}}
    @if ($showModal)
        <div class="arsip-modal-backdrop fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
            <div class="arsip-modal-panel bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-lg overflow-hidden transform transition-all my-8"
                 onclick="event.stopPropagation()">
                {{-- Header Modal --}}
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-800">
                        {{ $editingId ? 'Edit Data Arsip' : 'Tambah Data Baru' }}
                    </h3>
                    <button type="button" wire:click="tutupModal" class="text-slate-400 hover:text-slate-600 transition p-1 rounded-lg hover:bg-slate-200/60">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Form Body --}}
                <form wire:submit="simpan">
                    <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                        {{-- Pilihan Jenis (Pills Selector) --}}
                        <div>
                            <label class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">PILIH JENIS DATA</label>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="flex items-center justify-center gap-1.5 p-2.5 rounded-lg border cursor-pointer transition text-xs font-semibold
                                    {{ $formJenis === 'file' ? 'bg-blue-50 border-blue-500 text-blue-700' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                                    <input type="radio" wire:model.live="formJenis" value="file" class="hidden">
                                    <span>File / Dokumen</span>
                                </label>
                                <label class="flex items-center justify-center gap-1.5 p-2.5 rounded-lg border cursor-pointer transition text-xs font-semibold
                                    {{ $formJenis === 'nomor' ? 'bg-emerald-50 border-emerald-500 text-emerald-700' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                                    <input type="radio" wire:model.live="formJenis" value="nomor" class="hidden">
                                    <span>Kontak / Nomor</span>
                                </label>
                                <label class="flex items-center justify-center gap-1.5 p-2.5 rounded-lg border cursor-pointer transition text-xs font-semibold
                                    {{ $formJenis === 'link' ? 'bg-purple-50 border-purple-500 text-purple-700' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                                    <input type="radio" wire:model.live="formJenis" value="link" class="hidden">
                                    <span>Tautan / Link</span>
                                </label>
                            </div>
                        </div>

                        {{-- Nama Item --}}
                        <div>
                            <label class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">NAMA ITEM / JUDUL</label>
                            <input type="text" wire:model="formNama"
                                placeholder="{{ $formJenis === 'nomor' ? 'Contoh: Pak Hendra - Vendor Cloud' : 'Contoh: Template Surat Tugas Resmi' }}"
                                class="w-full h-10 rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] outline-none focus:border-[#19A7CE]">
                            @error('formNama')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Input Khusus: FILE --}}
                        @if ($formJenis === 'file')
                            <div>
                                <label class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">UNGGAH FILE DOKUMEN</label>
                                <div class="bg-[#F1F5F9] rounded-lg p-3">
                                    <label for="towerUploadFile"
                                        class="flex flex-col items-center justify-center text-center gap-1 h-[100px] rounded-lg border-2 border-dashed border-[#CBD5E1] bg-white cursor-pointer hover:border-[#19A7CE] transition">
                                        <span class="text-xs font-bold text-[#283044] px-3 truncate max-w-full">
                                            {{ $formFile ? $formFile->getClientOriginalName() : 'Pilih File (PDF, DOCX, XLSX, ZIP, dll)' }}
                                        </span>
                                        <span class="text-[11px] text-[#94A3B8]">Maks 50 MB</span>
                                    </label>
                                    <input id="towerUploadFile" type="file" wire:model="formFile" class="hidden">
                                </div>
                                <div wire:loading wire:target="formFile" class="text-xs text-blue-600 mt-1">Mengunggah file...</div>
                                @error('formFile')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        {{-- Input Khusus: NOMOR / KONTAK --}}
                        @if ($formJenis === 'nomor')
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">NOMOR TELEPON / WHATSAPP</label>
                                    <input type="text" wire:model="formNilaiKontak"
                                        placeholder="Contoh: 081234567890 atau (021) 555-1234"
                                        class="w-full h-10 rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] outline-none focus:border-[#19A7CE]">
                                    @error('formNilaiKontak')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">EMAIL KONTAK (OPSIONAL)</label>
                                    <input type="email" wire:model="formEmailKontak"
                                        placeholder="Contoh: mitra@perusahaan.com"
                                        class="w-full h-10 rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] outline-none focus:border-[#19A7CE]">
                                    @error('formEmailKontak')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        @endif

                        {{-- Input Khusus: LINK / TAUTAN --}}
                        @if ($formJenis === 'link')
                            <div>
                                <label class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">URL TAUTAN WEB</label>
                                <input type="url" wire:model="formUrlLink"
                                    placeholder="Contoh: https://drive.google.com/drive/folders/..."
                                    class="w-full h-10 rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] outline-none focus:border-[#19A7CE]">
                                @error('formUrlLink')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    </div>

                    {{-- Footer Modal --}}
                    <div class="flex items-center justify-between px-6 py-4 border-t border-[#E2E8F0] bg-slate-50">
                        <button type="button" wire:click="tutupModal"
                            class="h-9 px-4 bg-[#E8E7E9] rounded-lg text-sm text-[#333335] hover:bg-[#D9D9D9] transition font-medium">
                            Batal
                        </button>
                        <button type="submit"
                            class="h-9 px-5 bg-[#146C94] rounded-lg text-sm text-white font-bold hover:bg-[#0f5878] transition shadow-sm flex items-center gap-1.5"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="simpan">Simpan Data</span>
                            <span wire:loading wire:target="simpan">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Dialog Konfirmasi Hapus --}}
    <div x-show="confirmOpen" x-cloak
         class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/40 p-4 backdrop-blur-sm"
         @click.self="confirmOpen = false"
         @keydown.escape.window="confirmOpen = false">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl"
             role="dialog" aria-modal="true" aria-labelledby="hapus-arsip-title">
            <h3 id="hapus-arsip-title" class="text-lg font-semibold text-slate-800">Hapus arsip?</h3>
            <p class="mt-2 text-sm leading-relaxed text-[#4B5563]">
                Data <span class="font-semibold text-slate-800" x-text="confirmName"></span> akan dihapus permanen.
                Tindakan ini tidak dapat dibatalkan.
            </p>
            <div class="mt-6 flex items-center justify-end gap-3">
                <button type="button" @click="confirmOpen = false"
                    class="h-10 rounded-lg border border-slate-300 bg-white px-4 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
                    Batal
                </button>
                <button type="button"
                    @click="confirmOpen = false; $wire.hapus(confirmId)"
                    class="h-10 rounded-lg bg-red-600 px-4 text-sm font-semibold text-white transition hover:bg-red-700">
                    Hapus
                </button>
            </div>
        </div>
    </div>

    {{-- Script Kecil untuk Fitur Salin Nomor ke Clipboard --}}
    <script>
        function salinNomor(teks, btn) {
            if (!teks || btn.dataset.copied === '1') return;

            const showCopied = () => {
                btn.dataset.copied = '1';
                const originalHtml = btn.innerHTML;
                btn.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><span>Tersalin</span>`;
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.dataset.copied = '0';
                }, 2000);
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(teks).then(showCopied).catch(err => {
                    console.error('Gagal menyalin nomor:', err);
                });
                return;
            }

            // Fallback untuk konteks non-secure (http)
            const textarea = document.createElement('textarea');
            textarea.value = teks;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                showCopied();
            } catch (err) {
                console.error('Gagal menyalin nomor:', err);
            } finally {
                document.body.removeChild(textarea);
            }
        }
    </script>
</div>
