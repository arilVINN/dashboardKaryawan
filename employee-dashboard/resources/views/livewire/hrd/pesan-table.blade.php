@php
    $indicator = fn (string $col) => $sort === $col ? ($dir === 'asc' ? '▲' : '▼') : '↕';
    $cellPadding = 'px-6 py-4';
    $viewerId = auth()->user()->id_user;
@endphp

<div class="flex flex-col gap-3 w-full mt-2">
    <div class="flex flex-wrap items-end gap-4 bg-white border border-slate-200 rounded-xl shadow-sm p-5">
        <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
            <label for="hrdPesanSearch" class="text-xs font-bold text-slate-500">Cari</label>
            <input id="hrdPesanSearch" type="text" wire:model.live.debounce.300ms="search" placeholder="Judul / pengirim / penerima..."
                class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
        </div>
        <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
            <label for="hrdPesanArah" class="text-xs font-bold text-slate-500">Arah</label>
            <select id="hrdPesanArah" wire:model.live="arah"
                class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                <option value="">Semua</option>
                <option value="masuk">Masuk</option>
                <option value="keluar">Keluar</option>
            </select>
        </div>
        <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
            <label for="hrdPesanTipe" class="text-xs font-bold text-slate-500">Jenis</label>
            <select id="hrdPesanTipe" wire:model.live="tipe"
                class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                <option value="">Semua</option>
                <option value="pesan">Pesan</option>
                <option value="surat">Surat</option>
            </select>
        </div>
        <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
            <label for="hrdPesanStatus" class="text-xs font-bold text-slate-500">Status</label>
            <select id="hrdPesanStatus" wire:model.live="status"
                class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                <option value="">Semua</option>
                <option value="belum_dibaca">Belum dibaca</option>
                <option value="dibaca">Dibaca</option>
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

    <div class="flex justify-between items-center mb-2">
        <h2 class="text-xl font-bold text-slate-800">Daftar Pesan</h2>
        <button type="button" onclick="bukaModalPesanHrd()"
                class="text-sm px-4 py-2 bg-[#0097B2] text-white rounded-lg font-medium hover:bg-[#008199] transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
            </svg>
            Kirim Pesan
        </button>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">ID Pesan</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">
                            <button type="button" wire:click="sortBy('judul')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Judul Pesan <span class="{{ $sort === 'judul' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('judul') }}</span>
                            </button>
                        </th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">
                            <button type="button" wire:click="sortBy('pengirim')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Pengirim / Penerima <span class="{{ $sort === 'pengirim' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('pengirim') }}</span>
                            </button>
                        </th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Arah</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">
                            <button type="button" wire:click="sortBy('tanggal')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Tanggal <span class="{{ $sort === 'tanggal' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('tanggal') }}</span>
                            </button>
                        </th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($rows as $pesan)
                        @php
                            $isIncoming = $pesan->penerima_id_user === $viewerId;
                            $isOutgoing = $pesan->pengirim_id_user === $viewerId;
                            $contact = $isIncoming ? $pesan->pengirim : $pesan->penerima;
                        @endphp
                        <tr wire:key="pesan-{{ $pesan->id_pesan }}" class="hover:bg-slate-50 transition">
                            <td class="{{ $cellPadding }} font-semibold text-slate-800 whitespace-nowrap">
                                {{ $pesan->id_pesan }}
                            </td>
                            <td class="{{ $cellPadding }} text-slate-900 font-medium">
                                {{ $pesan->judul_pesan }}
                            </td>
                            <td class="{{ $cellPadding }} whitespace-nowrap">
                                @if ($isIncoming)
                                    <span class="text-slate-500">Dari:</span>
                                    {{ $pesan->pengirim?->karyawan?->nama ?? $pesan->pengirim?->username ?? 'Pengguna tidak tersedia' }}
                                @elseif ($isOutgoing)
                                    <span class="text-slate-500">Kepada:</span>
                                    {{ $pesan->penerima?->karyawan?->nama ?? $pesan->penerima?->username ?? 'Pengguna tidak tersedia' }}
                                @else
                                    <span class="text-slate-500">Dari {{ $pesan->pengirim?->karyawan?->nama ?? $pesan->pengirim?->username ?? '?' }} ke</span>
                                    {{ $pesan->penerima?->karyawan?->nama ?? $pesan->penerima?->username ?? '?' }}
                                @endif
                            </td>
                            <td class="{{ $cellPadding }} whitespace-nowrap">
                                @if ($isIncoming)
                                    <span data-arah="masuk"
                                        class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">
                                        Masuk
                                    </span>
                                @elseif ($isOutgoing)
                                    <span data-arah="keluar"
                                        class="px-2.5 py-1 bg-[#C0E7FF] text-blue-700 rounded-full text-xs font-bold">
                                        Keluar
                                    </span>
                                @else
                                    <span data-arah="lainnya"
                                        class="px-2.5 py-1 bg-slate-100 text-slate-500 rounded-full text-xs font-bold">
                                        Lainnya
                                    </span>
                                @endif
                            </td>
                            <td class="{{ $cellPadding }} whitespace-nowrap text-slate-600">
                                {{ $pesan->tanggal_pesan }}
                            </td>
                            <td class="{{ $cellPadding }} whitespace-nowrap text-center text-xs">
                                <a href="{{ route('hrd.detailPesan', $pesan->id_pesan) }}"
                                    class="text-[#0097B2] hover:text-[#008199] font-medium hover:underline">
                                    Buka
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-slate-500">
                                @if ($hasActiveFilters)
                                    <span>Tidak ada data yang cocok dengan filter.</span>
                                    <button type="button" wire:click="resetFilters"
                                        class="mt-1 inline-block text-sm text-[#0097B2] hover:underline">Reset filter</button>
                                @else
                                    Belum ada pesan masuk atau keluar.
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
