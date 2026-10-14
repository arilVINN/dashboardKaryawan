@php
    $isCompact = $compact ?? false;
    $cellPadding = $isCompact ? 'px-4 py-3' : 'px-6 py-4';
@endphp

<section class="flex flex-col gap-3 w-full mt-2 p-5">
    <div class="flex justify-between items-center pr-4 sm:pr-6 lg:pr-8 gap-3">
        <h3 class="text-lg font-semibold text-slate-800">Anggota Divisi</h3>
        <button onclick="bukaModalAnggota()" type="button"
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
                                <button type="button" onclick="bukaEditAnggota(this)"
                                    data-id="{{ $anggota->id_karyawan }}"
                                    data-nama="{{ $anggota->nama }}"
                                    data-jenis-kelamin="{{ $anggota->jenis_kelamin }}"
                                    data-tanggal-lahir="{{ $anggota->tanggal_lahir }}"
                                    data-email="{{ $anggota->email }}"
                                    data-no-telepon="{{ $anggota->no_telepon }}"
                                    data-jabatan="{{ $anggota->jabatan }}"
                                    data-username="{{ $anggota->user->username ?? '' }}"
                                    class="text-[#0097B2] hover:text-[#008199] font-medium hover:underline mr-3">
                                    Edit
                                </button>
                                <button type="button" onclick="hapusAnggota(this)"
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

<div id="modalTambahAnggota"
    class="opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center transition-opacity duration-300">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupModalAnggota()"></div>

    <div id="modalBoxTambah"
        class="transform scale-95 translate-y-4 relative bg-white rounded-xl shadow-xl w-full max-w-md max-h-[90vh] flex flex-col overflow-hidden transition-all duration-300">
        <div class="flex items-center justify-between p-4 border-b border-dashed border-gray-300 shrink-0">
            <div class="bg-cyan-100 p-1.5 rounded text-cyan-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z">
                        </path>
                    </svg>
                </div>
            <h3 class="text-base font-bold text-slate-900">Tambah Karyawan ke {{ $divisi->nama_divisi }}</h3>
            <button type="button" onclick="tutupModalAnggota()" class="text-slate-400 hover:text-red-500 transition"
                aria-label="Tutup">
                &times;
            </button>
        </div>

        <form id="formTambahAnggota" onsubmit="submitFormAnggota(event)" class="flex flex-col overflow-hidden">
            @csrf
            <div class="p-5 overflow-y-auto space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2">NAMA KARYAWAN</label>
                    <input type="text" name="nama" maxlength="100" required
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm"
                        placeholder="Masukkan nama">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2">JENIS KELAMIN</label>
                    <select name="jenis_kelamin" required class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                        <option value="">Pilih jenis kelamin</option>
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2">TANGGAL LAHIR</label>
                    <input type="date" name="tanggal_lahir"
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2">EMAIL</label>
                    <input type="email" name="email" maxlength="100"
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm"
                        placeholder="nama@contoh.com">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2">NO. TELEPON</label>
                    <input type="tel" name="no_telepon" maxlength="20"
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm"
                        placeholder="08xxxxxxxxxx">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2">JABATAN</label>
                    <select name="jabatan" required
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                        <option value="">Pilih jabatan</option>
                        <option value="Kepala Divisi">Kepala Divisi</option>
                        <option value="Wakil Kepala Divisi">Wakil Kepala Divisi</option>
                        <option value="Staff">Staff</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2">USERNAME AKUN</label>
                    <input type="text" name="username" maxlength="50" required
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2">PASSWORD (MIN. 6 KARAKTER)</label>
                    <input type="password" name="password" minlength="6" required
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                </div>
                <input type="hidden" name="divisi_id_divisi" value="{{ $divisi->id_divisi }}">
            </div>

            <div class="flex justify-between items-center p-4 bg-slate-50 border-t border-slate-200 shrink-0">
                <button type="button" onclick="tutupModalAnggota()"
                    class="px-5 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">
                    Batal
                </button>
                <button type="submit" id="btnSubmitAnggota"
                    class="px-5 py-2 bg-[#004A65] text-white text-sm font-medium rounded-md hover:bg-[#003347] transition">
                    Tambah
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modalEditAnggota"
    class="opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center transition-opacity duration-300">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupEditAnggota()"></div>
    <div id="modalBoxEditAnggota"
        class="transform scale-95 translate-y-4 relative bg-white rounded-xl shadow-xl w-full max-w-md max-h-[90vh] flex flex-col overflow-hidden transition-all duration-300">
        <div class="flex items-center justify-between p-4 border-b border-dashed border-gray-300 shrink-0">
            <h3 class="text-base font-bold text-slate-900">Edit Data Staff</h3>
            <button type="button" onclick="tutupEditAnggota()" class="text-slate-400 hover:text-red-500"
                aria-label="Tutup">&times;</button>
        </div>
        <form id="formEditAnggota" onsubmit="submitEditAnggota(event)" class="flex flex-col overflow-hidden">
            @csrf
            <input type="hidden" name="id_karyawan">
            <div class="p-5 overflow-y-auto space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2">NAMA KARYAWAN</label>
                    <input type="text" name="nama" maxlength="100" required
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2">JENIS KELAMIN</label>
                    <select name="jenis_kelamin" required class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2">TANGGAL LAHIR</label>
                    <input type="date" name="tanggal_lahir"
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2">EMAIL</label>
                    <input type="email" name="email" maxlength="100"
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2">NO. TELEPON</label>
                    <input type="tel" name="no_telepon" maxlength="20"
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2">JABATAN</label>
                    <select name="jabatan" required
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                        <option value="">Pilih jabatan</option>
                        <option value="Kepala Divisi">Kepala Divisi</option>
                        <option value="Wakil Kepala Divisi">Wakil Kepala Divisi</option>
                        <option value="Staff">Staff</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2">USERNAME</label>
                    <input type="text" name="username" maxlength="50"
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                    <p class="text-xs text-slate-500 mt-1">Kosongkan jika staff belum memiliki akun login.</p>
                </div>
            </div>
            <div class="flex justify-between items-center p-4 bg-slate-50 border-t border-slate-200 shrink-0">
                <button type="button" onclick="tutupEditAnggota()"
                    class="px-5 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300">
                    Batal
                </button>
                <button type="submit" id="btnSimpanEditAnggota"
                    class="px-5 py-2 bg-[#004A65] text-white text-sm font-medium rounded-md hover:bg-[#003347]">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const modalTambahAnggota = document.getElementById('modalTambahAnggota');
    const modalBoxTambahAnggota = document.getElementById('modalBoxTambah');
    const modalEditAnggota = document.getElementById('modalEditAnggota');
    const modalBoxEditAnggota = document.getElementById('modalBoxEditAnggota');

    function showModal(modal, box) {
        modal.classList.remove('opacity-0', 'pointer-events-none');
        box.classList.remove('scale-95', 'translate-y-4');
        box.classList.add('scale-100', 'translate-y-0');
    }

    function hideModal(modal, box) {
        modal.classList.add('opacity-0', 'pointer-events-none');
        box.classList.remove('scale-100', 'translate-y-0');
        box.classList.add('scale-95', 'translate-y-4');
    }

    function bukaModalAnggota() {
        showModal(modalTambahAnggota, modalBoxTambahAnggota);
    }

    function tutupModalAnggota() {
        hideModal(modalTambahAnggota, modalBoxTambahAnggota);
        document.getElementById('formTambahAnggota').reset();
    }

    function bukaEditAnggota(button) {
        const form = document.getElementById('formEditAnggota');
        const fields = ['id', 'nama', 'jenisKelamin', 'tanggalLahir', 'email', 'noTelepon', 'jabatan', 'username'];
        for (const field of fields) {
            const name = field === 'id' ? 'id_karyawan' : field.replace(/[A-Z]/g, letter => `_${letter.toLowerCase()}`);
            const value = button.dataset[field] || '';
            const input = form.elements[name];
            if (name === 'jabatan' && value && !Array.from(input.options).some(option => option.value === value)) {
                input.add(new Option(value, value), 1);
            }
            input.value = value;
        }
        showModal(modalEditAnggota, modalBoxEditAnggota);
    }

    function tutupEditAnggota() {
        hideModal(modalEditAnggota, modalBoxEditAnggota);
    }

    function getCsrfToken(form) {
        return form.querySelector('input[name="_token"]').value;
    }

    async function sendStaffRequest(url, method, body, form) {
        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(form),
            },
            body: body ? JSON.stringify(body) : undefined,
        });
        const result = await response.json();
        if (!response.ok) {
            const errors = result.errors ? Object.values(result.errors).flat().join('\n') : result.message;
            throw new Error(errors || 'Permintaan gagal.');
        }
        return result;
    }

    async function submitFormAnggota(event) {
        event.preventDefault();
        const form = event.currentTarget;
        const button = document.getElementById('btnSubmitAnggota');
        const formData = Object.fromEntries(new FormData(form).entries());
        button.disabled = true;

        try {
            const result = await sendStaffRequest('{{ url('/hrd/staff') }}', 'POST', formData, form);
            alert(result.message || 'Staff berhasil ditambahkan.');
            window.location.reload();
        } catch (error) {
            alert(error.message || 'Gagal terhubung ke server.');
        } finally {
            button.disabled = false;
        }
    }

    async function submitEditAnggota(event) {
        event.preventDefault();
        const form = event.currentTarget;
        const button = document.getElementById('btnSimpanEditAnggota');
        const formData = Object.fromEntries(new FormData(form).entries());
        const id = formData.id_karyawan;
        delete formData._token;
        delete formData.id_karyawan;
        if (!formData.username) delete formData.username;
        button.disabled = true;

        try {
            const result = await sendStaffRequest(`{{ url('/hrd/staff') }}/${encodeURIComponent(id)}`, 'PUT', formData, form);
            alert(result.message || 'Data staff berhasil diperbarui.');
            window.location.reload();
        } catch (error) {
            alert(error.message || 'Gagal mengubah data staff.');
        } finally {
            button.disabled = false;
        }
    }

    async function hapusAnggota(button) {
        const id = button.dataset.id;
        if (!confirm(`Hapus ${button.dataset.nama} beserta akun loginnya?`)) return;

        try {
            const result = await sendStaffRequest(
                `{{ url('/hrd/staff') }}/${encodeURIComponent(id)}`,
                'DELETE',
                null,
                document.getElementById('formTambahAnggota')
            );
            alert(result.message || 'Staff berhasil dihapus.');
            window.location.reload();
        } catch (error) {
            alert(error.message || 'Gagal menghapus staff.');
        }
    }
</script>
