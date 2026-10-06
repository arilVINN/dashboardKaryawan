@php
    $isCompact = $compact ?? false;
    $cellPadding = $isCompact ? 'px-4 py-3' : 'px-6 py-4';
@endphp

<div class="flex flex-col gap-3 w-full mt-2">
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold text-slate-800">Daftar Divisi</h2>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
        <div class="{{ $isCompact ? 'overflow-hidden' : 'overflow-x-auto' }}">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Kode Divisi</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Nama Divisi</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Status</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($divisis as $divisi)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="{{ $cellPadding }} font-semibold text-slate-800 whitespace-nowrap">
                                {{ $divisi->kode_divisi }}
                            </td>
                            <td class="{{ $cellPadding }} text-slate-700">
                                {{ $divisi->nama_divisi }}
                            </td>

                            <td class="{{ $cellPadding }} whitespace-nowrap">
                                @if (strtolower($divisi->status_aktif) === 'aktif')
                                    <span class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">
                                        {{ $divisi->status_aktif }}
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-500 rounded-full text-xs font-bold">
                                        {{ $divisi->status_aktif }}
                                    </span>
                                @endif
                            </td>

                            <td class="{{ $cellPadding }} whitespace-nowrap text-center text-xs">
                                <a href="{{ url('/hrd/detailDivisi/' . $divisi->id_divisi) }}"
                                   class="text-[#0097B2] hover:text-[#008199] font-medium hover:underline">
                                    Selengkapnya
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-slate-400">Belum ada divisi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>