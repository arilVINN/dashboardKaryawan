@php
    $isCompact = $compact ?? false;
    $cellPadding = $isCompact ? 'px-4 py-3' : 'px-6 py-4';
@endphp

<div class="flex flex-col gap-3 w-full mt-2">
    <div class="flex justify-between items-center mb-2">
        <h2 class="text-xl font-bold text-slate-800">Daftar Pesan</h2>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
        <div class="{{ $isCompact ? 'overflow-hidden' : 'overflow-x-auto' }}">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">ID Pesan</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Judul Pesan</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Pengirim / Penerima</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Jenis</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Tanggal</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($pesans as $pesan)
                        @php
                            $isIncoming = $pesan->penerima_id_user === auth()->user()->id_user;
                            $contact = $isIncoming ? $pesan->pengirim : $pesan->penerima;
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="{{ $cellPadding }} font-semibold text-slate-800 whitespace-nowrap">
                                {{ $pesan->id_pesan }}
                            </td>
                            <td class="{{ $cellPadding }} text-slate-900 font-medium">
                                {{ $pesan->judul_pesan }}
                            </td>
                            <td class="{{ $cellPadding }} whitespace-nowrap">
                                <span class="text-slate-500">{{ $isIncoming ? 'Dari' : 'Kepada' }}:</span>
                                {{ $contact?->karyawan?->nama ?? $contact?->username ?? 'Pengguna tidak tersedia' }}
                            </td>
                            <td class="{{ $cellPadding }} whitespace-nowrap">
                                <span class="px-2.5 py-1 bg-cyan-50 text-cyan-700 rounded-full text-xs font-bold">
                                    {{ ucfirst($pesan->tipe ?? 'pesan') }}
                                </span>
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
                                Belum ada pesan masuk atau keluar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
