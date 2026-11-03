<div>
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-4 font-medium whitespace-nowrap">Judul Pesan</th>
                        <th class="px-6 py-4 font-medium whitespace-nowrap">Pengirim / Penerima</th>
                        <th class="px-6 py-4 font-medium whitespace-nowrap">Jenis</th>
                        <th class="px-6 py-4 font-medium whitespace-nowrap">Tanggal</th>
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
                            <td class="px-6 py-4 text-slate-900 font-medium">
                                {{ $pesan->judul_pesan }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-slate-500">{{ $isIncoming ? 'Dari' : 'Kepada' }}:</span>
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

        {{-- Footer: Ringkasan & Baris per Halaman --}}
        <div class="border-t border-slate-200 px-6 pt-4 pb-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-[13px] text-[#4B5563]">
                    Menampilkan {{ $pesans->firstItem() ?? 0 }}-{{ $pesans->lastItem() ?? 0 }} dari {{ $pesans->total() }} data
                </p>
                <label class="flex items-center gap-2 text-[13px] text-[#4B5563]">
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

    {{-- Pagination di luar kartu tabel, rata tengah --}}
    @if ($pesans->hasPages())
        <div class="mt-3 flex justify-center pb-8">
            {{ $pesans->links('vendor.pagination.custom') }}
        </div>
    @endif
</div>
