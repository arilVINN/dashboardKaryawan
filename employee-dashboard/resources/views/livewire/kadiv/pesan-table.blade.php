@php
    $indicator = fn(string $col) => $sort === $col ? ($dir === 'asc' ? '▲' : '▼') : '↕';
@endphp

<div>
    <div class="w-full">
        @if (session('pesan_success'))
            <div class="mb-4 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-md px-3 py-2">
                {{ session('pesan_success') }}
            </div>
        @endif

        {{-- Compact filters --}}
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <div class="relative min-w-0 flex-1">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input id="kadivPesanSearch" type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Cari pengirim atau topik..."
                    class="h-10 w-full rounded-lg border border-slate-300 pl-9 pr-3 text-sm text-[#172B36] outline-none focus:border-[#146C94]">
            </div>

            <select id="kadivPesanTipe" wire:model.live="tipe"
                class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-[#172B36] outline-none focus:border-[#146C94] sm:w-40">
                <option value="">Semua tipe</option>
                <option value="pesan">Pesan</option>
                <option value="surat">Surat</option>
            </select>

            <select id="kadivPesanArah" wire:model.live="arah"
                class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-[#172B36] outline-none focus:border-[#146C94] sm:w-40">
                <option value="">Semua arah</option>
                <option value="masuk">Masuk</option>
                <option value="keluar">Keluar</option>
            </select>

            @if ($search !== '' || $tipe !== '' || $arah !== '')
                <button type="button" wire:click="resetFilters"
                    class="h-10 shrink-0 text-sm font-medium text-[#146C94] hover:underline">
                    Reset
                </button>
            @endif
        </div>

        <div class="overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] table-fixed text-left text-sm text-[#475569]">
                    <thead class="bg-slate-100">
                        <tr>
                            <th class="w-[42%] px-4 py-3 text-left text-[13px] font-semibold text-slate-500 whitespace-nowrap">
                                <button type="button" wire:click="sortBy('judul')"
                                    class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                    Topik / judul
                                    <span class="{{ $sort === 'judul' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('judul') }}</span>
                                </button>
                            </th>
                            <th class="w-[28%] px-4 py-3 text-left text-[13px] font-semibold text-slate-500 whitespace-nowrap">
                                <button type="button" wire:click="sortBy('pengirim')"
                                    class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                    Pengirim
                                    <span class="{{ $sort === 'pengirim' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('pengirim') }}</span>
                                </button>
                            </th>
                            <th class="w-[18%] px-4 py-3 text-left text-[13px] font-semibold text-slate-500 whitespace-nowrap">
                                <button type="button" wire:click="sortBy('tanggal')"
                                    class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                    Tanggal
                                    <span class="{{ $sort === 'tanggal' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('tanggal') }}</span>
                                </button>
                            </th>
                            <th class="w-20 px-4 py-3 text-right text-[13px] font-semibold text-slate-500 whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EEF2F5]">
                        @forelse ($messages as $message)
                            @php
                                $url = url('/kadiv/detailPesan/' . $message['id_pesan']);
                                $date = !empty($message['tanggal_pesan'])
                                    ? \Illuminate\Support\Carbon::parse($message['tanggal_pesan'])
                                    : null;
                                $dateLabel = $date
                                    ? ($date->isToday()
                                        ? 'Hari ini'
                                        : ($date->isYesterday()
                                            ? 'Kemarin'
                                            : $date->locale('id')->translatedFormat('j M Y')))
                                    : '-';
                                $isOutgoing = ($message['arah'] ?? 'masuk') === 'keluar';
                                $contact = $isOutgoing ? ($message['penerima'] ?? null) : ($message['pengirim'] ?? null);
                                $contactName = $contact['nama'] ?? $contact['username'] ?? '-';
                                $contactUsername = $contact['username'] ?? null;
                            @endphp
                            <tr wire:key="pesan-{{ $message['id_pesan'] }}"
                                role="link" tabindex="0"
                                onclick="window.location.href='{{ $url }}'"
                                onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location.href='{{ $url }}'; }"
                                class="h-16 cursor-pointer hover:bg-[#F5FAFC] focus:bg-[#F5FAFC] focus:outline-none">
                                <td class="px-4 py-2">
                                    <span class="truncate text-sm font-medium text-[#172B36]">
                                        {{ $message['judul_pesan'] ?: 'Pesan' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2">
                                    @if ($isOutgoing)
                                        <div class="text-sm text-[#172B36]">Anda</div>
                                        <div class="text-xs text-[#4B5563]">Ke: {{ $contactName }}</div>
                                    @else
                                        <div class="text-sm text-[#172B36]">{{ $contactName }}</div>
                                        @if ($contactUsername)
                                            <div class="text-xs text-[#4B5563]">{{ $contactUsername }}</div>
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
                        Menampilkan {{ $messages->firstItem() ?? 0 }}-{{ $messages->lastItem() ?? 0 }} dari {{ $messages->total() }} pesan
                    </p>

                    @if ($messages->hasPages())
                        <div>{{ $messages->links('vendor.pagination.custom') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL KIRIM PESAN --}}
    <div id="modalKirimPesan"
        class="opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center transition-opacity duration-150">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-modal-close="modalKirimPesan"></div>
        <form wire:submit="sendMessage" data-modal-panel
            class="transform scale-95 translate-y-4 relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden transition-all duration-150">
            <div class="flex items-center justify-between p-4 border-b border-dashed border-gray-300">
                <h3 class="text-base font-bold text-slate-900">Kirim Pesan</h3>
                <button type="button" data-modal-close="modalKirimPesan"
                    class="text-slate-400 hover:text-red-500">&times;</button>
            </div>

            <div class="p-5 flex flex-col gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">PENERIMA</label>
                    <select wire:model="recipientUserId"
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm text-slate-700 outline-none focus:border-[#004A65]">
                        <option value="">-- Pilih Penerima --</option>
                        @foreach ($recipients as $r)
                            <option value="{{ $r->id_user }}">{{ $r->karyawan?->nama ?? $r->username }}</option>
                        @endforeach
                    </select>
                    @error('recipientUserId')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">ISI PESAN</label>
                    <textarea wire:model="isi" rows="4" placeholder="Tuliskan pesan..."
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]"></textarea>
                    @error('isi')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-between items-center px-5 py-4 border-t border-slate-100">
                <button type="button" data-modal-close="modalKirimPesan"
                    class="px-5 py-1.5 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">Batal</button>
                <button type="submit"
                    class="px-4 py-1.5 bg-[#004A65] text-white text-sm font-medium rounded-md hover:bg-[#003347] transition"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove>Kirim</span>
                    <span wire:loading>Mengirim...</span>
                </button>
            </div>
        </form>
    </div>


    {{-- MODAL KIRIM SURAT IZIN --}}
    <div id="modalSuratIzin"
        class="opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center p-4 bg-[rgba(86,94,116,0.4)] backdrop-blur-sm transition-opacity duration-150 ease-in-out">
        <form wire:submit="kirimSuratIzin" data-modal-panel
            class="relative flex max-h-[90vh] w-full max-w-[420px] flex-col overflow-hidden rounded-xl bg-white shadow-[0_8px_16px_rgba(0,0,0,0.12)] scale-95 translate-y-2 transition-all duration-150 ease-in-out">

            <div
                class="flex items-center justify-between rounded-t-xl border-b border-[#E2E8F0] bg-[#F8FAFC] px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#D9EEF5] text-[#146C94]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12h6m-6 4h6M9 8h1m5 12H7a2 2 0 01-2-2V6a2 2 0 012-2h6l5 5v11a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-black">Kirim Surat Izin / Cuti</h3>
                </div>
                <button type="button" data-modal-close="modalSuratIzin"
                    class="cursor-pointer text-2xl leading-none text-[#565E74] hover:text-black">&times;</button>
            </div>

            <div class="flex-1 space-y-4 overflow-y-auto px-6 py-5">

                {{-- JENIS IZIN / CUTI --}}
                <div>
                    <label class="mb-2 block text-xs font-bold tracking-wide text-[#565E74]">JENIS IZIN / CUTI</label>
                    <select wire:model="jenisIzin" required
                        class="h-10 w-full rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] bg-white outline-none focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]">
                        <option value="" disabled selected>Pilih Jenis Izin / Cuti</option>
                        <option value="Cuti Tahunan">Cuti Tahunan</option>
                        <option value="Izin Sakit">Izin Sakit</option>
                        <option value="Izin Keperluan Pribadi">Izin Keperluan Pribadi</option>
                        <option value="Cuti Melahirkan">Cuti Melahirkan</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                    @error('jenisIzin')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold tracking-wide text-[#565E74]">JUDUL SURAT</label>
                    <input type="text" wire:model="judulSurat" required
                        placeholder="Contoh: Pengajuan Cuti Tahunan"
                        class="h-10 w-full rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] outline-none placeholder:text-[#94A3B8] focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]">
                    @error('judulSurat')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- TANGGAL MULAI & SELESAI --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-2 block text-xs font-bold tracking-wide text-[#565E74]">TGL MULAI</label>
                        <input type="date" wire:model="tanggalMulai" required
                            class="h-10 w-full cursor-pointer rounded-lg border border-[#CBD5E1] px-2 text-xs text-[#283044] outline-none focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]">
                        @error('tanggalMulai')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold tracking-wide text-[#565E74]">TGL SELESAI</label>
                        <input type="date" wire:model="tanggalSelesai" required
                            class="h-10 w-full cursor-pointer rounded-lg border border-[#CBD5E1] px-2 text-xs text-[#283044] outline-none focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]">
                        @error('tanggalSelesai')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="rounded-lg bg-[#F1F5F9] p-4">
                    <label for="kadivFileSurat"
                        class="flex h-[80px] cursor-pointer flex-col items-center justify-center gap-1 rounded-lg border-2 border-dashed border-[#CBD5E1] bg-white text-center transition hover:border-[#19A7CE]">
                        <span id="kadivNamaFileSurat"
                            class="max-w-full truncate px-3 text-sm font-bold text-[#283044]">Pilih File (Bukti
                            Surat)</span>
                        <span class="text-xs text-[#94A3B8]">Maks 20 MB</span>
                    </label>
                    <input id="kadivFileSurat" type="file" wire:model="fileSurat" class="hidden">
                    <div wire:loading wire:target="fileSurat" class="mt-1 text-xs text-slate-400">Mengunggah...</div>
                    @error('fileSurat')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold tracking-wide text-[#565E74]">KETERANGAN</label>
                    <textarea wire:model="keteranganSurat" rows="2" placeholder="Opsional"
                        class="w-full resize-none rounded-lg border border-[#CBD5E1] px-3 py-2 text-sm text-[#283044] outline-none placeholder:text-[#94A3B8] focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]"></textarea>
                    @error('keteranganSurat')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-between border-t border-[#E2E8F0] px-6 py-4">
                <button type="button" data-modal-close="modalSuratIzin"
                    class="h-9 cursor-pointer rounded-lg bg-[#E8E7E9] px-4 text-sm text-[#333335] transition hover:bg-[#D9D9D9]">
                    Batal
                </button>
                <button type="submit"
                    class="flex h-9 cursor-pointer items-center gap-2 rounded-lg bg-[#146C94] px-4 text-sm font-bold text-white transition hover:bg-[#0f5878] disabled:cursor-not-allowed disabled:opacity-70"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="kirimSuratIzin">Kirim Surat</span>
                    <span wire:loading wire:target="kirimSuratIzin">Mengirim...</span>
                    <svg wire:loading.remove wire:target="kirimSuratIzin" class="h-4 w-4" fill="currentColor"
                        viewBox="0 0 24 24">
                        <path d="M2 21l21-9L2 3v7l15 2-15 2z" />
                    </svg>
                </button>
            </div>
        </form>

        <script>
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('tutup-modal-pesan', () => {
                    if (window.setModal) window.setModal('modalKirimPesan', false);
                });
                Livewire.on('tutup-modal-surat', () => {
                    if (window.setModal) window.setModal('modalSuratIzin', false);
                });
            });

            const fileInput = document.getElementById('kadivFileSurat');
            const fileLabel = document.getElementById('kadivNamaFileSurat');
            fileInput?.addEventListener('change', () => {
                fileLabel.textContent = fileInput.files?.[0]?.name || 'Pilih File (Bukti Surat)';
            });
        </script>
    </div>
