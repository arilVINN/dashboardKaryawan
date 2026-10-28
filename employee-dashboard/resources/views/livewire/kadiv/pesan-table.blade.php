@php
    $indicator = fn(string $col) => $sort === $col ? ($dir === 'asc' ? '▲' : '▼') : '↕';
@endphp

<div>
    <div class="flex flex-col gap-3 w-full mt-2">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-slate-800">Daftar Pesan</h2>
            <div class="flex gap-2">
                <button type="button" data-modal-open="modalKirimPesan"
                    class="text-sm px-4 py-2 bg-[#0097B2] text-white rounded-lg font-medium hover:bg-[#008199] transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6 12L3.27 3.13a59.77 59.77 0 0118.45 8.87 59.77 59.77 0 01-18.45 8.87L6 12zm0 0h7.5">
                        </path>
                    </svg>
                    Buat Pesan
                </button>
                <button type="button" data-modal-open="modalSuratIzin"
                    class="text-sm px-4 py-2 bg-[#146C94] text-white rounded-lg font-medium hover:bg-[#0f5878] transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12h6m-6 4h6M9 8h1m5 12H7a2 2 0 01-2-2V6a2 2 0 012-2h6l5 5v11a2 2 0 01-2 2z" />
                    </svg>
                    Kirim Surat Izin
                </button>
            </div>
        </div>

        @if (session('pesan_success'))
            <div class="text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-md px-3 py-2">
                {{ session('pesan_success') }}
            </div>
        @endif

        <div class="flex flex-wrap items-end gap-4 bg-white border border-slate-200 rounded-xl shadow-sm p-5">
            <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
                <label for="kadivPesanSearch" class="text-xs font-bold text-slate-500">Cari</label>
                <input id="kadivPesanSearch" type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Judul / isi pesan..."
                    class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
            </div>
            <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
                <label for="kadivPesanTipe" class="text-xs font-bold text-slate-500">Tipe</label>
                <select id="kadivPesanTipe" wire:model.live="tipe"
                    class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                    <option value="">Semua</option>
                    <option value="pesan">Pesan</option>
                    <option value="surat">Surat</option>
                </select>
            </div>
            <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
                <label for="kadivPesanArah" class="text-xs font-bold text-slate-500">Arah</label>
                <select id="kadivPesanArah" wire:model.live="arah"
                    class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                    <option value="">Semua</option>
                    <option value="masuk">Masuk</option>
                    <option value="keluar">Keluar</option>
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
                                <button type="button" wire:click="sortBy('judul')"
                                    class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                    Isi Pesan <span
                                        class="{{ $sort === 'judul' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('judul') }}</span>
                                </button>
                            </th>
                            <th class="px-6 py-4 font-medium whitespace-nowrap text-center">
                                <button type="button" wire:click="sortBy('pengirim')"
                                    class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                    Pengirim <span
                                        class="{{ $sort === 'pengirim' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('pengirim') }}</span>
                                </button>
                            </th>
                            <th class="px-6 py-4 font-medium whitespace-nowrap text-center">
                                <button type="button" wire:click="sortBy('tanggal')"
                                    class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                    Tanggal <span
                                        class="{{ $sort === 'tanggal' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('tanggal') }}</span>
                                </button>
                            </th>
                            <th class="px-6 py-4 font-medium whitespace-nowrap text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($messages as $message)
                            <tr wire:key="pesan-{{ $message['id_pesan'] }}" class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 font-semibold text-slate-800 truncate max-w-[200px]">
                                    {{ $message['judul_pesan'] }}</td>
                                <td class="px-6 py-4 text-slate-700 text-center whitespace-nowrap">
                                    {{ $message['pengirim']['nama'] ?? ($message['pengirim']['username'] ?? '-') }}</td>
                                <td class="px-6 py-4 text-slate-500 text-center whitespace-nowrap">
                                    {{ $message['tanggal_pesan'] ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-xs">
                                    <a href="{{ url('/kadiv/detailPesan/' . $message['id_pesan']) }}"
                                        class="text-[#0e9dc3] hover:text-[#06627b] font-medium">Lihat</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-slate-400">Belum ada pesan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>{{ $messages->links('vendor.pagination.custom') }}</div>
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
            <div class="flex items-center justify-between rounded-t-xl border-b border-[#E2E8F0] bg-[#F8FAFC] px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#D9EEF5] text-[#146C94]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12h6m-6 4h6M9 8h1m5 12H7a2 2 0 01-2-2V6a2 2 0 012-2h6l5 5v11a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-black">Kirim Surat Izin</h3>
                </div>
                <button type="button" data-modal-close="modalSuratIzin"
                    class="cursor-pointer text-2xl leading-none text-[#565E74] hover:text-black">&times;</button>
            </div>

            <div class="flex-1 space-y-5 overflow-y-auto px-6 py-5">
                <div>
                    <label class="mb-2 block text-xs font-bold tracking-wide text-[#565E74]">JUDUL</label>
                    <input type="text" wire:model="judulSurat" required placeholder="Contoh: Izin Sakit"
                        class="h-10 w-full rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] outline-none placeholder:text-[#94A3B8] focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]">
                    @error('judulSurat')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold tracking-wide text-[#565E74]">TANGGAL</label>
                    <input type="date" wire:model="tanggalIzin" required
                        class="h-10 w-full cursor-pointer rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] outline-none focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]">
                    @error('tanggalIzin')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-lg bg-[#F1F5F9] p-4">
                    <label for="kadivFileSurat"
                        class="flex h-[90px] cursor-pointer flex-col items-center justify-center gap-1 rounded-lg border-2 border-dashed border-[#CBD5E1] bg-white text-center transition hover:border-[#19A7CE]">
                        <span id="kadivNamaFileSurat"
                            class="max-w-full truncate px-3 text-sm font-bold text-[#283044]">Pilih File (Bukti Surat)</span>
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
                    <textarea wire:model="keteranganSurat" rows="3" placeholder="Opsional"
                        class="w-full resize-none rounded-lg border border-[#CBD5E1] px-3 py-2.5 text-sm text-[#283044] outline-none placeholder:text-[#94A3B8] focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]"></textarea>
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
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('tutup-modal-pesan', () => {
                if (window.setModal) {
                    window.setModal('modalKirimPesan', false);
                }
            });
        });

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
