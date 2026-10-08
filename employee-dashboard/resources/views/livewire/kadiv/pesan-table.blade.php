@php
    $indicator = fn (string $col) => $sort === $col ? ($dir === 'asc' ? '▲' : '▼') : '↕';
@endphp

<div>
<div class="flex flex-col gap-3 w-full mt-2">
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold text-slate-800">Pesan</h2>
        <button type="button" data-modal-open="modalKirimPesan"
            class="text-sm px-4 py-2 bg-[#0097B2] text-white rounded-lg font-medium hover:bg-[#008199] transition">
            Kirim Pesan
        </button>
    </div>

    @if (session('pesan_success'))
        <div class="text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-md px-3 py-2">
            {{ session('pesan_success') }}
        </div>
    @endif

    <div class="flex flex-wrap items-end gap-4 bg-white border border-slate-200 rounded-xl shadow-sm p-5">
        <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
            <label for="kadivPesanSearch" class="text-xs font-bold text-slate-500">Cari</label>
            <input id="kadivPesanSearch" type="text" wire:model.live.debounce.300ms="search" placeholder="Judul / isi pesan..."
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
                            <button type="button" wire:click="sortBy('judul')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Isi Pesan <span class="{{ $sort === 'judul' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('judul') }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-4 font-medium whitespace-nowrap text-center">
                            <button type="button" wire:click="sortBy('pengirim')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Pengirim <span class="{{ $sort === 'pengirim' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('pengirim') }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-4 font-medium whitespace-nowrap text-center">
                            <button type="button" wire:click="sortBy('tanggal')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Tanggal <span class="{{ $sort === 'tanggal' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('tanggal') }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-4 font-medium whitespace-nowrap text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($messages as $message)
                        <tr wire:key="pesan-{{ $message['id_pesan'] }}" class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-800 truncate max-w-[200px]">{{ $message['judul_pesan'] }}</td>
                            <td class="px-6 py-4 text-slate-700 text-center whitespace-nowrap">{{ $message['pengirim']['nama'] ?? $message['pengirim']['username'] ?? '-' }}</td>
                            <td class="px-6 py-4 text-slate-500 text-center whitespace-nowrap">{{ $message['tanggal_pesan'] ?? '-' }}</td>
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

    <div>{{ $messages->links() }}</div>
</div>

{{-- MODAL KIRIM PESAN --}}
<div id="modalKirimPesan" class="opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center transition-opacity duration-300">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-modal-close="modalKirimPesan"></div>
    <form wire:submit="sendMessage"
        class="transform scale-95 translate-y-4 relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden transition-all duration-300">
        <div class="flex items-center justify-between p-4 border-b border-dashed border-gray-300">
            <h3 class="text-base font-bold text-slate-900">Kirim Pesan</h3>
            <button type="button" data-modal-close="modalKirimPesan" class="text-slate-400 hover:text-red-500">&times;</button>
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
                @error('recipientUserId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">ISI PESAN</label>
                <textarea wire:model="isi" rows="4" placeholder="Tuliskan pesan..."
                    class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]"></textarea>
                @error('isi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex justify-between items-center px-5 py-4 border-t border-slate-100">
            <button type="button" data-modal-close="modalKirimPesan"
                class="px-5 py-1.5 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">Batal</button>
            <button type="submit"
                class="px-4 py-1.5 bg-[#004A65] text-white text-sm font-medium rounded-md hover:bg-[#003347] transition"
                wire:loading.attr="disabled">Kirim</button>
        </div>
    </form>
</div>
</div>
