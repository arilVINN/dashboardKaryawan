@php
    $indicator = fn(string $col) => $sort === $col ? ($dir === 'asc' ? '▲' : '▼') : '↕';
    $cellPadding = 'px-6 py-4';
@endphp

<div class="flex flex-col gap-4 w-full">
    {{-- Header Judul & Tombol Tambah --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Direktori Arsip & Data</h2>
            
        </div>

        @if ($canManage)
            <button type="button" wire:click="bukaModalTambah"
                class="text-sm px-4 py-2 bg-[#0097B2] text-white rounded-lg font-medium hover:bg-[#008199] transition flex items-center justify-center gap-1.5 shrink-0 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                </svg>
                <span>Tambah Data</span>
            </button>
        @endif
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

    {{-- Filter Card (Konsisten dengan KaryawanTable & TugasTable) --}}
    <div class="flex flex-wrap items-end gap-4 bg-white border border-slate-200 rounded-xl shadow-sm p-5">
        {{-- Search Input --}}
        <div class="flex flex-1 min-w-[12rem] flex-col gap-1.5">
            <label for="towerSearch" class="text-xs font-bold text-slate-500 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <span>Pencarian</span>
            </label>
            <input id="towerSearch" type="text" wire:model.live.debounce.300ms="search"
                placeholder="Cari nama dokumen, mitra, nomor, atau tautan..."
                class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
        </div>

        {{-- Dropdown Jenis --}}
        <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
            <label for="towerJenis" class="text-xs font-bold text-slate-500">Jenis</label>
            <select id="towerJenis" wire:model.live="jenis"
                class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65] bg-white cursor-pointer">
                <option value="">Semua Jenis ({{ $counts['total'] }})</option>
                <option value="file">File & Template ({{ $counts['file'] }})</option>
                <option value="nomor">Kontak & Mitra ({{ $counts['nomor'] }})</option>
                <option value="link">Tautan Link ({{ $counts['link'] }})</option>
            </select>
        </div>

        {{-- Tombol Reset --}}
        <div class="flex shrink-0 gap-2 sm:ml-auto">
            <button type="button" wire:click="resetFilters"
                class="h-10 inline-flex items-center px-4 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">
                Reset
            </button>
        </div>
    </div>

    {{-- Tabel List Data --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap min-w-[16rem]">
                            <button type="button" wire:click="sortBy('nama')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Nama <span class="{{ $sort === 'nama' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('nama') }}</span>
                            </button>
                        </th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center w-36">
                            <button type="button" wire:click="sortBy('jenis')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Jenis <span class="{{ $sort === 'jenis' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('jenis') }}</span>
                            </button>
                        </th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap min-w-[16rem]">
                            Detail / Nilai
                        </th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center w-48">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($items as $item)
                        <tr class="hover:bg-slate-50/80 transition" wire:key="tower-item-{{ $item->id }}">
                            {{-- Kolom 1: Nama --}}
                            <td class="{{ $cellPadding }}">
                                <span class="font-bold text-slate-900 text-sm leading-snug">{{ $item->nama }}</span>
                            </td>

                            {{-- Kolom 2: Jenis (Badge) --}}
                            <td class="{{ $cellPadding }} text-center">
                                @if ($item->jenis === 'file')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <span>File</span>
                                    </span>
                                @elseif ($item->jenis === 'nomor')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        <span>Kontak</span>
                                    </span>
                                @elseif ($item->jenis === 'link')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                        <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                        <span class="font-medium text-slate-800 text-xs truncate max-w-xs">{{ $item->file_name ?: 'Dokumen File' }}</span>
                                        @if ($item->file_size)
                                            <span class="text-[11px] text-slate-400 mt-0.5">{{ $item->file_size }}</span>
                                        @endif
                                    </div>
                                @elseif ($item->jenis === 'nomor')
                                    <div class="flex flex-col gap-0.5">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-mono font-bold text-slate-800 text-xs select-all">{{ $item->nilai_kontak }}</span>
                                        </div>
                                        @if ($item->email_kontak)
                                            <span class="text-[11px] text-slate-400 truncate max-w-xs">{{ $item->email_kontak }}</span>
                                        @endif
                                    </div>
                                @elseif ($item->jenis === 'link')
                                    <div class="flex items-center gap-1.5">
                                        <a href="{{ $item->url_link }}" target="_blank" rel="noopener noreferrer"
                                           class="text-xs text-blue-600 hover:text-blue-800 hover:underline truncate max-w-xs font-mono"
                                           title="{{ $item->url_link }}">
                                            {{ $item->url_link }}
                                        </a>
                                    </div>
                                @endif
                            </td>

                            {{-- Kolom 4: Aksi Langsung --}}
                            <td class="{{ $cellPadding }}">
                                <div class="flex items-center justify-center gap-1.5">
                                    {{-- Aksi Utama Berdasarkan Jenis --}}
                                    @if ($item->jenis === 'file')
                                        @if ($item->file_path)
                                            <button type="button" wire:click="downloadFile({{ $item->id }})"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#004A65] text-white text-xs font-medium rounded-lg hover:bg-[#003347] transition shadow-sm"
                                                title="Unduh File">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                </svg>
                                                <span>Unduh</span>
                                            </button>
                                        @else
                                            <span class="text-xs text-slate-400 italic">File Kosong</span>
                                        @endif
                                    @elseif ($item->jenis === 'nomor')
                                        {{-- Tombol Salin Nomor --}}
                                        <button type="button"
                                            onclick="salinNomor('{{ $item->nilai_kontak }}', this)"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-slate-100 text-slate-700 text-xs font-medium rounded-lg hover:bg-slate-200 border border-slate-300 transition"
                                            title="Salin Nomor">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                            <span>Salin</span>
                                        </button>

                                        {{-- Tombol Buka WhatsApp --}}
                                        @php
                                            $waClean = preg_replace('/[^0-9]/', '', $item->nilai_kontak ?? '');
                                            if (str_starts_with($waClean, '0')) {
                                                $waClean = '62' . substr($waClean, 1);
                                            }
                                        @endphp
                                        @if ($waClean)
                                            <a href="https://wa.me/{{ $waClean }}" target="_blank" rel="noopener noreferrer"
                                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#004A65] text-white text-xs font-medium rounded-lg hover:bg-[#003347] transition shadow-sm"
                                               title="Buka Chat WhatsApp">
                                                <span>Buka</span>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                </svg>
                                            </a>
                                        @endif
                                    @elseif ($item->jenis === 'link')
                                        <a href="{{ $item->url_link }}" target="_blank" rel="noopener noreferrer"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#004A65] text-white text-xs font-medium rounded-lg hover:bg-[#003347] transition shadow-sm"
                                           title="Buka Tautan di Tab Baru">
                                            <span>Buka</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            </svg>
                                        </a>
                                    @endif

                                    {{-- Aksi Edit & Hapus (Hanya Kadiv / HRD / Admin) --}}
                                    @if ($canManage)
                                        <button type="button" wire:click="bukaModalEdit({{ $item->id }})"
                                            class="inline-flex items-center justify-center p-1.5 text-slate-500 hover:text-blue-600 hover:bg-slate-100 rounded-lg transition"
                                            title="Edit Data">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button type="button"
                                            wire:click="hapus({{ $item->id }})"
                                            wire:confirm="Yakin ingin menghapus data '{{ $item->nama }}'?"
                                            class="inline-flex items-center justify-center p-1.5 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                            title="Hapus Data">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
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

        {{-- Pagination --}}
        @if ($items->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $items->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Tambah / Edit Data (Konsisten dengan Modal Tugas) --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-lg overflow-hidden transform transition-all my-8"
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
</div>

{{-- Script Kecil untuk Fitur Salin Nomor ke Clipboard --}}
<script>
    function salinNomor(teks, btn) {
        if (!teks) return;
        navigator.clipboard.writeText(teks).then(() => {
            const originalHtml = btn.innerHTML;
            btn.innerHTML = `<span class="text-emerald-600 font-bold">✓ Tersalin</span>`;
            setTimeout(() => {
                btn.innerHTML = originalHtml;
            }, 1800);
        }).catch(err => {
            console.error('Gagal menyalin nomor:', err);
        });
    }
</script>
