@php
    $isCompact = $compact ?? false;
    $cellPadding = $isCompact ? 'px-4 py-3' : 'px-6 py-4';
@endphp

<section class="flex flex-col gap-3 w-full mt-2 p-5">
    <div class="flex justify-between items-center pr-4 sm:pr-6 lg:pr-8 gap-3">
        <h3 class="text-lg font-semibold text-slate-800">Anggota Divisi</h3>
        <button onclick="bukaModalKaryawan()" type="button"
            class="px-6 py-2.5 bg-[#0097B2] hover:bg-[#008199] text-white text-sm font-semibold rounded-lg shadow-sm transition">
            Tambah Karyawan
        </button>
    </div>

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
                    @forelse ($anggotaDivisi as $anggota)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="{{ $cellPadding }} font-semibold text-slate-800 whitespace-nowrap">
                                {{ $anggota->id_karyawan }}
                            </td>
                            <td class="{{ $cellPadding }} text-slate-900 font-medium whitespace-nowrap">
                                {{ $anggota->nama }}
                            </td>
                            <td class="{{ $cellPadding }} text-slate-700 whitespace-nowrap">
                                <span class="bg-slate-100 text-slate-600 px-2.5 py-1 rounded-md text-xs font-medium border border-slate-200">
                                    {{ $anggota->jabatan }}
                                </span>
                            </td>
                            <td class="{{ $cellPadding }} text-slate-500 whitespace-nowrap">
                                {{ $anggota->email ?? '-' }}
                            </td>
                            <td class="{{ $cellPadding }} text-slate-500 whitespace-nowrap">
                                {{ $anggota->no_telepon ?? '-' }}
                            </td>
                            <td class="{{ $cellPadding }} whitespace-nowrap text-center">
                                <button type="button" onclick="bukaEditKaryawan(this)"
                                    data-id="{{ $anggota->id_karyawan }}"
                                    data-nama="{{ $anggota->nama }}"
                                    data-gender="{{ $anggota->jenis_kelamin }}"
                                    data-lahir="{{ $anggota->tanggal_lahir }}"
                                    data-email="{{ $anggota->email }}"
                                    data-telepon="{{ $anggota->no_telepon }}"
                                    data-jabatan="{{ $anggota->jabatan }}"
                                    data-divisi="{{ $divisi->id_divisi }}"
                                    data-username="{{ $anggota->user->username ?? '' }}"
                                    class="text-[#0097B2] hover:text-[#008199] font-medium hover:underline mr-3">
                                    Edit
                                </button>
                                <button type="button" onclick="hapusKaryawan(this)"
                                    data-id="{{ $anggota->id_karyawan }}"
                                    data-nama="{{ $anggota->nama }}"
                                    class="text-red-600 hover:text-red-800 font-medium hover:underline">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-4 text-center text-slate-500">
                                Belum ada staff di divisi ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

