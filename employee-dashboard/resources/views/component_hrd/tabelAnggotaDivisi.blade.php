@php
    $isCompact = $compact ?? false;
    $cellPadding = $isCompact ? 'px-4 py-3' : 'px-6 py-4';

    // Data dummy berdasarkan tabel Karyawan pada ERD
    $dummyAnggota = [
        [
            'id_karyawan' => 'KRY01',
            'nama' => 'Budi Santoso',
            'jabatan' => 'Head of IT',
            'email' => 'budi.s@silindo.com',
            'no_telepon' => '081234567890',
        ],
        [
            'id_karyawan' => 'KRY05',
            'nama' => 'Siti Aminah',
            'jabatan' => 'Senior Backend Developer',
            'email' => 'siti.a@silindo.com',
            'no_telepon' => '081298765432',
        ],
        [
            'id_karyawan' => 'KRY12',
            'nama' => 'Andi Wijaya',
            'jabatan' => 'UI/UX Designer',
            'email' => 'andi.w@silindo.com',
            'no_telepon' => '085712349876',
        ],
        [
            'id_karyawan' => 'KRY18',
            'nama' => 'Rina Melati',
            'jabatan' => 'QA Engineer',
            'email' => 'rina.m@silindo.com',
            'no_telepon' => '081933445566',
        ],
    ];
@endphp

<div class="flex flex-col gap-3 w-full mt-2 p-5">

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full mt-2">
        <div class="{{ $isCompact ? 'overflow-hidden' : 'overflow-x-auto' }}">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">ID Karyawan</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Nama Karyawan</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Jabatan</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Email</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">No Telepon</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($dummyAnggota as $anggota)
                        <tr class="hover:bg-slate-50 transition">
                            {{-- Kolom ID --}}
                            <td class="{{ $cellPadding }} font-semibold text-slate-800 whitespace-nowrap">
                                {{ $anggota['id_karyawan'] }}
                            </td>

                            {{-- Kolom Nama --}}
                            <td class="{{ $cellPadding }} text-slate-900 font-medium whitespace-nowrap">
                                {{ $anggota['nama'] }}
                            </td>

                            {{-- Kolom Jabatan --}}
                            <td class="{{ $cellPadding }} text-slate-700 whitespace-nowrap">
                                <span
                                    class="bg-slate-100 text-slate-600 px-2.5 py-1 rounded-md text-xs font-medium border border-slate-200">
                                    {{ $anggota['jabatan'] }}
                                </span>
                            </td>

                            {{-- Kolom Email --}}
                            <td class="{{ $cellPadding }} text-slate-500 whitespace-nowrap">
                                {{ $anggota['email'] }}
                            </td>

                            {{-- Kolom Telepon --}}
                            <td class="{{ $cellPadding }} text-slate-500 whitespace-nowrap">
                                {{ $anggota['no_telepon'] }}
                            </td>

                            {{-- Kolom Aksi --}}
                            <td class="{{ $cellPadding }} whitespace-nowrap text-center text-xs">
                                <a href="{{ url('/hrd/karyawan/edit/' . $anggota['id_karyawan']) }}"
                                    class="text-[#0097B2] hover:text-[#008199] font-medium hover:underline mr-3">
                                    Edit
                                </a>
                                <button onclick="confirm('Keluarkan {{ $anggota['nama'] }} dari divisi ini?')"
                                    class="text-red-500 hover:text-red-700 font-medium hover:underline">
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
