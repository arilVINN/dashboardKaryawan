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

    <div class="flex justify-end pr-4 sm:pr-6 lg:pr-8 gap-3">
        <button onclick="bukaModalAnggota()" type="button" id="btn-toggle-balas"
            class="px-6 py-2.5 bg-[#0097B2] hover:bg-[#008199] text-white text-sm font-semibold rounded-lg shadow-sm transition">
            Tambah
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
                                <a onclick="bukaEditAnggota('{{ $anggota['id_karyawan'] }}')"
                                    class="text-[#0097B2] hover:text-[#008199] font-medium hover:underline mr-3 cursor-pointer">
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


<!-- ini untuk nambah anggota divisi -->

<div id="modalTambahAnggota"
    class="opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center transition-opacity duration-300">

    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupModalAnggota()"></div>

    <div id="modalBox"
        class="transform scale-95 translate-y-4 relative bg-white rounded-xl shadow-xl w-full max-w-md max-h-[90vh] flex flex-col overflow-hidden transition-all duration-300">

        <div class="flex items-center justify-between p-4 border-b border-dashed border-gray-300 shrink-0">
            <div class="flex items-center gap-3">
                <div class="bg-cyan-100 p-1.5 rounded text-cyan-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900">Tambah Anggota Divisi</h3>
            </div>
            <button onclick="tutupModalAnggota()" class="text-slate-400 hover:text-red-500 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form action="#" method="POST" class="flex flex-col overflow-hidden">
            @csrf

            <div class="p-5 overflow-y-auto">
                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-500 mb-2">1. NAMA STAFF</label>
                    <input type="text" name="nama_staff"
                        class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-[#004A65] focus:border-[#004A65] outline-none text-sm placeholder-gray-400"
                        placeholder="Masukkan nama" required>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-500 mb-2">2. DIVISI</label>
                    <input type="text" name="divisi"
                        class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-[#004A65] focus:border-[#004A65] outline-none text-sm placeholder-gray-400"
                        placeholder="Pilih divisi" required>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-500 mb-2">3. KETERANGAN</label>
                    <input type="text" name="keterangan"
                        class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-[#004A65] focus:border-[#004A65] outline-none text-sm placeholder-gray-400"
                        placeholder="Keterangan tambahan" required>
                </div>

                <div class="mb-5 border-b border-slate-100 pb-5">
                    <label class="block text-xs font-bold text-slate-500 mb-2">4. UPLOAD FOTO PROFILE</label>
                    <input type="file" name="foto_profile"
                        class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-[#004A65] focus:border-[#004A65] outline-none text-sm placeholder-gray-400 text-slate-600"
                        required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-3">5. PENGATURAN AKUN</label>

                    <div class="space-y-4">
                        <div>
                            <label
                                class="block text-[10px] font-semibold text-slate-700 uppercase mb-1">username</label>
                            <input type="text" name="username" placeholder="username" { }}
                                class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm text-slate-800 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-700 uppercase mb-1">Password
                                    Baru</label>
                                <input type="password" name="password" placeholder="********"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm text-slate-800 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                            </div>
                            <div>
                                <label
                                    class="block text-[10px] font-semibold text-slate-700 uppercase mb-1">Konfirmasi</label>
                                <input type="password" name="password_confirmation" placeholder="********"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm text-slate-800 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center p-4 bg-slate-50 border-t border-slate-200 shrink-0">
                <button type="button" onclick="tutupModalAnggota()"
                    class="px-5 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">
                    Batal
                </button>
                <button type="submit"
                    class="px-5 py-2 bg-[#004A65] text-white text-sm font-medium rounded-md hover:bg-[#003347] transition flex items-center gap-1">
                    Tambah
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>


<!-- ini untuk edit anggota divisi -->
<div id="modalEditAnggota"
    class="opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center transition-opacity duration-300">

    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupModalAnggota()"></div>

    <div id="modalBox"
        class="transform scale-95 translate-y-4 relative bg-white rounded-xl shadow-xl w-full max-w-md max-h-[90vh] flex flex-col overflow-hidden transition-all duration-300">

        <div class="flex items-center justify-between p-4 border-b border-dashed border-gray-300 shrink-0">
            <div class="flex items-center gap-3">
                <div class="bg-cyan-100 p-1.5 rounded text-cyan-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900">Edit Anggota Divisi</h3>
            </div>
            <button onclick="tutupModalEdit()" class="text-slate-400 hover:text-red-500 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form action="#" method="POST" class="flex flex-col overflow-hidden">
            @csrf

            <div class="p-5 overflow-y-auto">
                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-500 mb-2">1. NAMA STAFF</label>
                    <input type="text" name="nama_staff"
                        class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-[#004A65] focus:border-[#004A65] outline-none text-sm placeholder-gray-400"
                        placeholder="Masukkan nama" required>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-500 mb-2">2. DIVISI</label>
                    <input type="text" name="divisi"
                        class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-[#004A65] focus:border-[#004A65] outline-none text-sm placeholder-gray-400"
                        placeholder="Pilih divisi" required>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-500 mb-2">3. KETERANGAN</label>
                    <input type="text" name="keterangan"
                        class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-[#004A65] focus:border-[#004A65] outline-none text-sm placeholder-gray-400"
                        placeholder="Keterangan tambahan" required>
                </div>

                <div class="mb-5 border-b border-slate-100 pb-5">
                    <label class="block text-xs font-bold text-slate-500 mb-2">4. UPLOAD FOTO PROFILE</label>
                    <input type="file" name="foto_profile"
                        class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-[#004A65] focus:border-[#004A65] outline-none text-sm placeholder-gray-400 text-slate-600"
                        required>
                </div>
            </div>

            <div class="flex justify-between items-center p-4 bg-slate-50 border-t border-slate-200 shrink-0">
                <button type="button" onclick="tutupModalEdit()"
                    class="px-5 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">
                    Batal
                </button>
                <button type="submit"
                    class="px-5 py-2 bg-[#004A65] text-white text-sm font-medium rounded-md hover:bg-[#003347] transition flex items-center gap-1">
                    Simpan
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>


<script>
    const modalTambah = document.getElementById('modalTambahAnggota');
    const modalBoxTambah = document.getElementById('modalBoxTambah');
    const modalEdit = document.getElementById('modalEditAnggota');
    const modalBoxEdit = document.getElementById('modalBoxEdit');

    function bukaModal(modal, box) {
        modal.classList.remove('opacity-0', 'pointer-events-none');
        box.classList.remove('scale-95', 'translate-y-4');
        box.classList.add('scale-100', 'translate-y-0');
    }

    function tutupModal(modal, box) {
        modal.classList.add('opacity-0', 'pointer-events-none');
        box.classList.remove('scale-100', 'translate-y-0');
        box.classList.add('scale-95', 'translate-y-4');
    }

    function bukaModalAnggota() {
        bukaModal(modalTambah, modalBoxTambah);
    }

    function tutupModalAnggota() {
        tutupModal(modalTambah, modalBoxTambah);
    }

    function bukaEditAnggota(idKaryawan) {
        bukaModal(modalEdit, modalBoxEdit);
    }

    function tutupModalEdit() {
        tutupModal(modalEdit, modalBoxEdit);
    }
</script>
