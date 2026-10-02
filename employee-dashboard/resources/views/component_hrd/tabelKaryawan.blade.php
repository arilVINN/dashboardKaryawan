@php
    $isCompact = $compact ?? false;
    $cellPadding = $isCompact ? 'px-4 py-3' : 'px-6 py-4';

    $dummyKaryawan = [
        [
            'id_karyawan' => 'KRY01',
            'nama' => 'Samuel',
            'divisi' => 'Teknologi Informasi & Komunikasi',
            'jabatan' => 'Frontend Developer Intern',
            'status' => 'Aktif',
        ],
        [
            'id_karyawan' => 'KRY02',
            'nama' => 'Aril',
            'divisi' => 'Teknologi Informasi & Komunikasi',
            'jabatan' => 'Backend Developer',
            'status' => 'Aktif',
        ],
        [
            'id_karyawan' => 'KRY03',
            'nama' => 'Ariel2',
            'divisi' => 'Teknologi Informasi & Komunikasi',
            'jabatan' => 'Fullstack Developer',
            'status' => 'Aktif',
        ],
        [
            'id_karyawan' => 'KRY04',
            'nama' => 'Rina Melati',
            'divisi' => 'Human Capital Management (HRD)',
            'jabatan' => 'HR Staff',
            'status' => 'Non-aktif',
        ],
    ];
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
                    @foreach ($dummyKaryawan as $karyawan)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="{{ $cellPadding }} font-semibold text-slate-800 whitespace-nowrap">
                                {{ $karyawan['id_karyawan'] }}
                            </td>

                            <td class="{{ $cellPadding }} text-slate-900 font-medium whitespace-nowrap">
                                {{ $karyawan['nama'] }}
                            </td>
                            
                            <td class="{{ $cellPadding }} text-slate-500 whitespace-nowrap">
                                {{ $karyawan['divisi'] }}
                            </td>

                            <td class="{{ $cellPadding }} text-slate-500 whitespace-nowrap">
                                {{ $karyawan['jabatan'] }}
                            </td>

                            <td class="{{ $cellPadding }} whitespace-nowrap">
                                @if($karyawan['status'] == 'Aktif')
                                    <span class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">
                                        {{ $karyawan['status'] }}
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-500 rounded-full text-xs font-bold">
                                        {{ $karyawan['status'] }}
                                    </span>
                                @endif
                            </td>

                            <td class="{{ $cellPadding }} whitespace-nowrap text-center text-xs">
                                <a href="{{ url('/hrd/detailKaryawan/' . $karyawan['id_karyawan']) }}"
                                    class="text-[#0097B2] hover:text-[#008199] font-medium hover:underline mr-3">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>