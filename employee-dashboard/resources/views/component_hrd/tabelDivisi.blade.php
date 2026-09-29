@php
    $isCompact = $compact ?? false;
    $cellPadding = $isCompact ? 'px-4 py-3' : 'px-6 py-4';

    $dummyDivisi = [
        [
            'id' => 1,
            'kode' => 'DIV-IT-01',
            'nama' => 'Teknologi Informasi & Komunikasi',
            'status' => 'Aktif', // Aktif / Non-aktif
        ],
        [
            'id' => 2,
            'kode' => 'DIV-HR-02',
            'nama' => 'Human Capital Management (HRD)',
            'status' => 'Aktif',
        ],
        [
            'id' => 3,
            'kode' => 'DIV-FIN-03',
            'nama' => 'Finance & Accounting',
            'status' => 'Aktif',
        ],
        [
            'id' => 4,
            'kode' => 'DIV-MKT-04',
            'nama' => 'Pemasaran & Kemitraan',
            'status' => 'Non-aktif',
        ],
    ];
@endphp

<div class="flex flex-col gap-3 w-full mt-2">
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold text-slate-800">Manajemen Divisi</h2>
        <button class="text-sm px-4 py-2 bg-[#0097B2] text-white rounded-lg font-medium hover:bg-[#008199]">Tambah</button>
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
                    @foreach ($dummyDivisi as $divisi)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="{{ $cellPadding }} font-semibold text-slate-800 whitespace-nowrap">
                                {{ $divisi['kode'] }}
                            </td>
                            <td class="{{ $cellPadding }} text-slate-700">
                                {{ $divisi['nama'] }}
                            </td>

                            <td class="{{ $cellPadding }} whitespace-nowrap">
                                @if($divisi['status'] == 'Aktif')
                                    <span class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">
                                        {{ $divisi['status'] }}
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-500 rounded-full text-xs font-bold">
                                        {{ $divisi['status'] }}
                                    </span>
                                @endif
                            </td>
                            {{-- Kolom Aksi --}}
                            <td class="{{ $cellPadding }} whitespace-nowrap text-center text-xs">
                                <a href="{{ url('/hrd/divisi/edit/' . $divisi['id']) }}" 
                                   class="text-[#0097B2] hover:text-[#008199] font-medium hover:underline mr-3">
                                    Edit
                                </a>
                                <button onclick="confirm('Yakin ingin hapus {{ $divisi['nama'] }}?')"
                                   class="text-red-600 hover:text-red-800 font-medium hover:underline">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>