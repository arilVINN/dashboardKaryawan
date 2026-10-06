@php
    $isCompact = $compact ?? false;
    $cellPadding = $isCompact ? 'px-4 py-3' : 'px-6 py-4';
@endphp

<div class="flex flex-col gap-3 w-full mt-2">

    <div class="flex justify-between items-center mb-2">
        <h2 class="text-xl font-bold text-slate-800">Daftar Karyawan</h2>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
        <div class="{{ $isCompact ? 'overflow-hidden' : 'overflow-x-auto' }}">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">ID Karyawan</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Nama Karyawan</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Divisi</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Jabatan</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Status</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($karyawan as $k)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="{{ $cellPadding }} font-semibold text-slate-800 whitespace-nowrap">
                                {{ $k->id_karyawan }}
                            </td>

                            <td class="{{ $cellPadding }} text-slate-900 font-medium whitespace-nowrap">
                                {{ $k->nama }}
                            </td>

                            <td class="{{ $cellPadding }} text-slate-500 whitespace-nowrap">
                                {{ $k->divisi->nama_divisi ?? '-' }}
                            </td>

                            <td class="{{ $cellPadding }} text-slate-500 whitespace-nowrap">
                                {{ $k->jabatan }}
                            </td>

                            <td class="{{ $cellPadding }} whitespace-nowrap">
                                @if ($k->user)
                                    <span
                                        class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">Aktif</span>
                                @else
                                    <span
                                        class="px-2.5 py-1 bg-slate-100 text-slate-500 rounded-full text-xs font-bold">Belum
                                        ada akun</span>
                                @endif
                            </td>

                            <td class="{{ $cellPadding }} whitespace-nowrap text-center text-xs">
                                <a href="{{ url('/hrd/detailKaryawan/' . $k->id_karyawan) }}"
                                    class="text-[#0097B2] hover:text-[#008199] font-medium hover:underline">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400">Belum ada karyawan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
