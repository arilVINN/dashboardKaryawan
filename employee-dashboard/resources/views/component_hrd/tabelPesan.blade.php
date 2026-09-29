@php
    $isCompact = $compact ?? false;
    $cellPadding = $isCompact ? 'px-4 py-3' : 'px-6 py-4';

    $dummyPesan = [
        [
            'id_pesan' => 'MSG01', // Maksimal 5 karakter (VARCHAR2 5)
            'judul_pesan' => 'Revisi Layout Dashboard', // Maksimal 35 karakter
            'deskripsi' =>
                'Tolong rapikan margin pada topbar dan pastikan grafik donat sudah responsive di layar kecil.', // Maksimal 250 karakter
            'tanggal_pesan' => '2026-09-28', // Format Date standar (YYYY-MM-DD)
            'Tugas_id_tugas' => 'TGS01', // Foreign Key (VARCHAR2 5)
            'Tugas_Karyawan_id_karyawan' => 'KRY12', // Foreign Key (VARCHAR2 5)
        ],
        [
            'id_pesan' => 'MSG02',
            'judul_pesan' => 'Integrasi API Autentikasi',
            'deskripsi' =>
                'Endpoint JWT token sudah ready. Tolong segera integrasikan ke halaman login dan pastikan session tersimpan dengan aman.',
            'tanggal_pesan' => '2026-09-29',
            'Tugas_id_tugas' => 'TGS04',
            'Tugas_Karyawan_id_karyawan' => 'KRY05',
        ],
        [
            'id_pesan' => 'MSG03',
            'judul_pesan' => 'Update Asset Logo Baru',
            'deskripsi' =>
                'Gunakan file SVG terbaru untuk logo instansi di bagian header sidebar. File sudah saya upload di folder assets.',
            'tanggal_pesan' => '2026-09-29',
            'Tugas_id_tugas' => 'TGS07',
            'Tugas_Karyawan_id_karyawan' => 'KRY02',
        ],
        [
            'id_pesan' => 'MSG04',
            'judul_pesan' => 'Perbaikan Relasi Tabel',
            'deskripsi' =>
                'Ada error saat menghapus data pesan. Tolong cek kembali konfigurasi foreign key on delete cascade di migration.',
            'tanggal_pesan' => '2026-09-30',
            'Tugas_id_tugas' => 'TGS12',
            'Tugas_Karyawan_id_karyawan' => 'KRY08',
        ],
    ];
@endphp
<div class="flex flex-col gap-3 w-full mt-2">
    <div class="flex justify-end items-right">
        <button class="text-sm px-4 py-2 bg-[#0097B2] text-white rounded-lg font-medium hover:bg-[#008199]">Tambah
            Pesan</button>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
        <div class="{{ $isCompact ? 'overflow-hidden' : 'overflow-x-auto' }}">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Id Pesan</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Judul Pesan</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Deskripsi</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">Tanggal Pesan</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">Tugas_id_tugas</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">
                            Tugas_Karyawan_id_tugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($dummyPesan as $pesan)
                        <tr class="hover:bg-slate-50 transition">
                            {{-- Kolom ID Pesan --}}
                            <td class="{{ $cellPadding }} font-semibold text-slate-800 whitespace-nowrap">
                                {{ $pesan['id_pesan'] }}
                            </td>

                            {{-- Kolom Judul Pesan --}}
                            <td class="{{ $cellPadding }} text-slate-900 font-medium whitespace-nowrap">
                                {{ $pesan['judul_pesan'] }}
                            </td>

                            {{-- Kolom Deskripsi (Gunakan truncate agar teks panjang tidak merusak tabel) --}}
                            <td class="{{ $cellPadding }} max-w-[200px] truncate text-slate-500">
                                {{ $pesan['deskripsi'] }}
                            </td>

                            {{-- Kolom Tanggal Pesan --}}
                            <td class="{{ $cellPadding }} whitespace-nowrap text-slate-600">
                                {{ $pesan['tanggal_pesan'] }}
                            </td>

                            {{-- Kolom Aksi --}}
                            <td class="{{ $cellPadding }} whitespace-nowrap text-center text-xs">
                                <!-- Menggunakan id_pesan untuk URL -->
                                <a href="{{ url('/hrd/pesan/edit/' . $pesan['id_pesan']) }}"
                                    class="text-[#0097B2] hover:text-[#008199] font-medium hover:underline mr-3">
                                    Edit
                                </a>
                                <button onclick="confirm('Yakin ingin hapus pesan: {{ $pesan['judul_pesan'] }}?')"
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
