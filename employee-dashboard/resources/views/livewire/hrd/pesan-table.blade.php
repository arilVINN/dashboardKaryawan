<div>
    @php
        $indicator = fn(string $column) => $sort === $column ? ($dir === 'asc' ? '▲' : '▼') : '↕';
    @endphp

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <div class="relative min-w-0 flex-1">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input id="hrdPesanSearch" type="text" wire:model.live.debounce.300ms="search"
                placeholder="Cari pengirim atau topik..."
                class="h-10 w-full rounded-lg border border-slate-300 pl-9 pr-3 text-sm text-[#172B36] outline-none focus:border-[#146C94]">
        </div>

        <select id="hrdPesanTipe" wire:model.live="tipe"
            class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-[#172B36] outline-none focus:border-[#146C94] sm:w-40">
            <option value="">Semua tipe</option>
            <option value="pesan">Pesan</option>
            <option value="surat">Surat</option>
        </select>

        <select id="hrdPesanArah" wire:model.live="arah"
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

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-4 font-medium whitespace-nowrap">
                            <button type="button" wire:click="sortBy('judul')"
                                class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Judul Pesan
                                <span class="{{ $sort === 'judul' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('judul') }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-4 font-medium whitespace-nowrap">
                            <button type="button" wire:click="sortBy('pengirim')"
                                class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Pengirim / Penerima
                                <span class="{{ $sort === 'pengirim' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('pengirim') }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-4 font-medium whitespace-nowrap">Jenis</th>
                        <th class="px-6 py-4 font-medium whitespace-nowrap">
                            <button type="button" wire:click="sortBy('tanggal')"
                                class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Tanggal
                                <span class="{{ $sort === 'tanggal' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('tanggal') }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-4 font-medium whitespace-nowrap text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($pesans as $pesan)
                        @php
                            $isIncoming = $pesan->penerima_id_user === auth()->user()?->id_user;
                            $contact = $isIncoming ? $pesan->pengirim : $pesan->penerima;
                        @endphp
                        <tr wire:key="hrd-pesan-{{ $pesan->id_pesan }}"
                            onclick="window.location.href='{{ route('hrd.detailPesan', $pesan->id_pesan) }}'"
                            class="hover:bg-slate-50 cursor-pointer transition">
                            <td class="px-6 py-4 text-slate-500 font-medium">
                                {{ $pesan->judul_pesan }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-slate-900">{{ $isIncoming ? 'Dari' : 'Kepada' }}:</span>
                                {{ $contact?->karyawan?->nama ?? ($contact?->username ?? 'Pengguna tidak tersedia') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 bg-cyan-50 text-cyan-700 rounded-full text-xs font-bold">
                                    {{ ucfirst($pesan->tipe ?? 'pesan') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-600">
                                {{ $pesan->tanggal_pesan }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-xs">
                                <a href="{{ route('hrd.detailPesan', $pesan->id_pesan) }}"
                                    onclick="event.stopPropagation()"
                                    class="text-[#0097B2] hover:text-[#008199] font-medium hover:underline">
                                    Buka
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-slate-500">
                                Belum ada pesan masuk atau keluar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer: Ringkasan, Pagination & Baris per Halaman --}}
        <div class="border-t border-slate-200 px-6 py-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <p class="text-[13px] text-[#4B5563] lg:shrink-0">
                    Menampilkan {{ $pesans->firstItem() ?? 0 }}-{{ $pesans->lastItem() ?? 0 }} dari {{ $pesans->total() }} data
                </p>

                @if ($pesans->hasPages())
                    <div class="flex w-full justify-center lg:w-auto lg:flex-1">
                        {{ $pesans->links('vendor.pagination.custom') }}
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
</div>
