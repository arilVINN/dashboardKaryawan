@php
    $isCompact = $compact ?? false;
    $cellPadding = $isCompact ? 'px-4 py-3' : 'px-6 py-4';

    $divisis = $divisis ?? [];
    $sortable = $sortable ?? false;
@endphp

<div class="flex flex-col gap-3 w-full mt-2">
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold text-slate-800">Manajemen Divisi</h2>
        <button onclick="bukaModalDivisi()"
            class="text-sm px-4 py-2 bg-[#0097B2] text-white rounded-lg font-medium hover:bg-[#008199] transition">
            Tambah
        </button>
    </div>

    <!-- Tabel Divisi -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
        <div class="{{ $isCompact ? 'overflow-hidden' : 'overflow-x-auto' }}">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                    <tr>
                        @if ($sortable)
                            <x-sort-th column="kode" label="Kode Divisi" :padding="$cellPadding" />
                            <x-sort-th column="nama" label="Nama Divisi" :padding="$cellPadding" />
                            <x-sort-th column="staff" label="Jumlah Staff" :padding="$cellPadding" />
                        @else
                            <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Kode Divisi</th>
                            <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Nama Divisi</th>
                        @endif
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
                            @if ($sortable)
                                <td class="{{ $cellPadding }} text-slate-700">
                                    {{ $divisi->karyawans_count ?? 0 }}
                                </td>
                            @endif
                            <td class="{{ $cellPadding }} whitespace-nowrap">
                                @if (($divisi->status_aktif ?? 'Aktif') === 'Aktif')
                                    <span
                                        class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">
                                        Aktif
                                    </span>
                                @else
                                    <span
                                        class="px-2.5 py-1 bg-slate-100 text-slate-500 rounded-full text-xs font-bold">
                                        {{ $divisi->status_aktif }}
                                    </span>
                                @endif
                            </td>
                            <td
                                class="{{ $cellPadding }} whitespace-nowrap text-center text-xs gap-2 flex justify-center items-center">
                                <a href="{{ url('/hrd/detailDivisi/' . $divisi->id_divisi) }}"
                                    class="text-[#0097B2] hover:text-[#008199] font-medium hover:underline">
                                    Detail
                                </a>
                                <button type="button" onclick="hapusDivisi(this)" data-id="{{ $divisi->id_divisi }}"
                                    data-nama="{{ $divisi->nama_divisi }}"
                                    class="ml-3 text-red-600 hover:text-red-800 font-medium hover:underline">
                                    Hapus
                                </button>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $sortable ? 5 : 4 }}" class="p-4 text-center text-slate-500">
                                @if (collect(request()->query())->except(['page', 'sort', 'dir'])->filter()->isNotEmpty())
                                    <span>Tidak ada data yang cocok dengan filter.</span>
                                    <a href="{{ url()->current() }}"
                                        class="mt-1 inline-block text-sm text-[#0097B2] hover:underline">Reset filter</a>
                                @else
                                    Belum ada data divisi.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('component_hrd.divisiModal')
