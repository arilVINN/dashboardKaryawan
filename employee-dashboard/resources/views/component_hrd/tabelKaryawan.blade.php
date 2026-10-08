@php
    $isCompact = $compact ?? false;
    $cellPadding = $isCompact ? 'px-4 py-3' : 'px-6 py-4';
    $daftarDivisi = $daftarDivisi ?? collect();
    $formControl = 'w-full px-3 py-2 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]';
@endphp

<div class="flex flex-col gap-3 w-full mt-2">

    <div class="flex justify-between items-center mb-2">
        <h2 class="text-xl font-bold text-slate-800">Daftar Karyawan</h2>
        <button type="button" data-staff-create onclick="bukaModalKaryawan()"
            class="text-sm px-4 py-2 bg-[#0097B2] text-white rounded-lg font-medium hover:bg-[#008199] transition">
            Tambah
        </button>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
        <div class="{{ $isCompact ? 'overflow-hidden' : 'overflow-x-auto' }}">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                    <tr>
                        <x-sort-th column="id" label="ID Karyawan" :padding="$cellPadding" />
                        <x-sort-th column="nama" label="Nama Karyawan" :padding="$cellPadding" default="nama" />
                        <x-sort-th column="divisi" label="Divisi" :padding="$cellPadding" />
                        <x-sort-th column="jabatan" label="Jabatan" :padding="$cellPadding" />
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
                                <div class="flex items-center justify-center gap-3">
                                    <a href="{{ url('/hrd/detailKaryawan/' . $k->id_karyawan) }}"
                                        class="text-[#0097B2] hover:text-[#008199] font-medium hover:underline">
                                        Lihat Detail
                                    </a>
                                    <button type="button" data-staff-edit
                                        data-id="{{ $k->id_karyawan }}"
                                        data-nama="{{ $k->nama }}"
                                        data-gender="{{ $k->jenis_kelamin }}"
                                        data-lahir="{{ $k->tanggal_lahir }}"
                                        data-email="{{ $k->email }}"
                                        data-telepon="{{ $k->no_telepon }}"
                                        data-jabatan="{{ $k->jabatan }}"
                                        data-divisi="{{ $k->divisi_id_divisi }}"
                                        data-username="{{ $k->user->username ?? '' }}"
                                        onclick="bukaEditKaryawan(this)"
                                        class="text-amber-600 hover:text-amber-800 font-medium hover:underline">
                                        Edit
                                    </button>
                                    <button type="button" data-staff-delete
                                        data-id="{{ $k->id_karyawan }}"
                                        data-nama="{{ $k->nama }}"
                                        onclick="hapusKaryawan(this)"
                                        class="text-red-600 hover:text-red-800 font-medium hover:underline">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                                @if (collect(request()->query())->except(['page', 'sort', 'dir'])->filter()->isNotEmpty())
                                    <span>Tidak ada data yang cocok dengan filter.</span>
                                    <a href="{{ url()->current() }}"
                                        class="mt-1 inline-block text-sm text-[#0097B2] hover:underline">Reset filter</a>
                                @else
                                    Belum ada karyawan.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Karyawan -->
    <div id="modalTambahKaryawan" data-staff-modal="create"
        class="opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center transition-opacity duration-300">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupModalKaryawan()"></div>
        <div id="modalBoxTambahKaryawan"
            class="transform scale-95 translate-y-4 relative bg-white rounded-xl shadow-xl w-full max-w-xl max-h-[90vh] flex flex-col overflow-hidden transition-all duration-300">
            <div class="flex items-center justify-between p-4 border-b border-dashed border-gray-300">
                <h3 class="text-base font-bold text-slate-900">Tambah Karyawan</h3>
                <button type="button" onclick="tutupModalKaryawan()" class="text-slate-400 hover:text-red-500"
                    aria-label="Tutup">&times;</button>
            </div>
            <form id="formTambahKaryawan" onsubmit="submitFormKaryawan(event)" class="flex flex-col overflow-hidden">
                @csrf
                <div class="p-5 overflow-y-auto space-y-7">
                    <section class="space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="h-4 w-1 rounded bg-[#0097B2]"></span>
                            <h4 class="text-xs font-bold uppercase tracking-wide text-slate-600">Data Karyawan</h4>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1.5 sm:col-span-2">
                                <label class="text-xs font-bold text-slate-500">NAMA KARYAWAN</label>
                                <input type="text" name="nama" maxlength="100" required class="{{ $formControl }}">
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-bold text-slate-500">JENIS KELAMIN</label>
                                <select name="jenis_kelamin" required class="{{ $formControl }}">
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-bold text-slate-500">TANGGAL LAHIR</label>
                                <input type="date" name="tanggal_lahir" class="{{ $formControl }}">
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-bold text-slate-500">NO. TELEPON</label>
                                <input type="tel" name="no_telepon" maxlength="20" class="{{ $formControl }}">
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-bold text-slate-500">EMAIL</label>
                                <input type="email" name="email" maxlength="100" class="{{ $formControl }}">
                            </div>
                        </div>
                    </section>

                    <section class="space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="h-4 w-1 rounded bg-[#0097B2]"></span>
                            <h4 class="text-xs font-bold uppercase tracking-wide text-slate-600">Kepegawaian</h4>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-bold text-slate-500">JABATAN</label>
                                <input type="text" name="jabatan" maxlength="100" required class="{{ $formControl }}">
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-bold text-slate-500">DIVISI</label>
                                <select name="divisi_id_divisi" required class="{{ $formControl }}">
                                    <option value="">Pilih divisi</option>
                                    @foreach ($daftarDivisi as $d)
                                        <option value="{{ $d->id_divisi }}">{{ $d->nama_divisi }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </section>

                    <section class="space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="h-4 w-1 rounded bg-[#0097B2]"></span>
                            <h4 class="text-xs font-bold uppercase tracking-wide text-slate-600">Akun Login</h4>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-bold text-slate-500">USERNAME</label>
                                <input type="text" name="username" maxlength="50" required class="{{ $formControl }}">
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-bold text-slate-500">PASSWORD (MIN. 6)</label>
                                <input type="password" name="password" minlength="6" required class="{{ $formControl }}">
                            </div>
                        </div>
                    </section>
                </div>
                <div class="flex justify-end gap-2 p-4 border-t border-slate-100">
                    <button type="button" onclick="tutupModalKaryawan()"
                        class="px-5 py-1.5 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">Batal</button>
                    <button type="submit" id="btnSubmitKaryawan"
                        class="px-4 py-1.5 bg-[#004A65] text-white text-sm font-medium rounded-md hover:bg-[#003347] transition">Tambah</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Karyawan -->
    <div id="modalEditKaryawan" data-staff-modal="edit"
        class="opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center transition-opacity duration-300">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupEditKaryawan()"></div>
        <div id="modalBoxEditKaryawan"
            class="transform scale-95 translate-y-4 relative bg-white rounded-xl shadow-xl w-full max-w-xl max-h-[90vh] flex flex-col overflow-hidden transition-all duration-300">
            <div class="flex items-center justify-between p-4 border-b border-dashed border-gray-300">
                <h3 class="text-base font-bold text-slate-900">Edit Karyawan</h3>
                <button type="button" onclick="tutupEditKaryawan()" class="text-slate-400 hover:text-red-500"
                    aria-label="Tutup">&times;</button>
            </div>
            <form id="formEditKaryawan" onsubmit="submitEditKaryawan(event)" class="flex flex-col overflow-hidden">
                @csrf
                <input type="hidden" name="id_karyawan">
                <div class="p-5 overflow-y-auto space-y-7">
                    <section class="space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="h-4 w-1 rounded bg-[#0097B2]"></span>
                            <h4 class="text-xs font-bold uppercase tracking-wide text-slate-600">Data Karyawan</h4>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1.5 sm:col-span-2">
                                <label class="text-xs font-bold text-slate-500">NAMA KARYAWAN</label>
                                <input type="text" name="nama" maxlength="100" required class="{{ $formControl }}">
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-bold text-slate-500">JENIS KELAMIN</label>
                                <select name="jenis_kelamin" required class="{{ $formControl }}">
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-bold text-slate-500">TANGGAL LAHIR</label>
                                <input type="date" name="tanggal_lahir" class="{{ $formControl }}">
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-bold text-slate-500">NO. TELEPON</label>
                                <input type="tel" name="no_telepon" maxlength="20" class="{{ $formControl }}">
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-bold text-slate-500">EMAIL</label>
                                <input type="email" name="email" maxlength="100" class="{{ $formControl }}">
                            </div>
                        </div>
                    </section>

                    <section class="space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="h-4 w-1 rounded bg-[#0097B2]"></span>
                            <h4 class="text-xs font-bold uppercase tracking-wide text-slate-600">Kepegawaian</h4>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-bold text-slate-500">JABATAN</label>
                                <input type="text" name="jabatan" maxlength="100" required class="{{ $formControl }}">
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-bold text-slate-500">DIVISI</label>
                                <select name="divisi_id_divisi" required class="{{ $formControl }}">
                                    @foreach ($daftarDivisi as $d)
                                        <option value="{{ $d->id_divisi }}">{{ $d->nama_divisi }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </section>

                    <section class="space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="h-4 w-1 rounded bg-[#0097B2]"></span>
                            <h4 class="text-xs font-bold uppercase tracking-wide text-slate-600">Akun Login</h4>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-bold text-slate-500">USERNAME</label>
                                <input type="text" name="username" maxlength="50" class="{{ $formControl }}">
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-bold text-slate-500">PASSWORD BARU (OPSIONAL)</label>
                                <input type="password" name="password" minlength="6" class="{{ $formControl }}">
                            </div>
                        </div>
                    </section>
                </div>
                <div class="flex justify-end gap-2 p-4 border-t border-slate-100">
                    <button type="button" onclick="tutupEditKaryawan()"
                        class="px-5 py-1.5 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">Batal</button>
                    <button type="submit" id="btnSimpanEditKaryawan"
                        class="px-4 py-1.5 bg-[#004A65] text-white text-sm font-medium rounded-md hover:bg-[#003347] transition">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const modalTambahKaryawan = document.getElementById('modalTambahKaryawan');
    const modalBoxTambahKaryawan = document.getElementById('modalBoxTambahKaryawan');
    const modalEditKaryawan = document.getElementById('modalEditKaryawan');
    const modalBoxEditKaryawan = document.getElementById('modalBoxEditKaryawan');

    function showKaryawanModal(modal, box) {
        modal.classList.remove('opacity-0', 'pointer-events-none');
        box.classList.remove('scale-95', 'translate-y-4');
        box.classList.add('scale-100', 'translate-y-0');
    }

    function hideKaryawanModal(modal, box) {
        modal.classList.add('opacity-0', 'pointer-events-none');
        box.classList.remove('scale-100', 'translate-y-0');
        box.classList.add('scale-95', 'translate-y-4');
    }

    function bukaModalKaryawan() {
        showKaryawanModal(modalTambahKaryawan, modalBoxTambahKaryawan);
    }

    function tutupModalKaryawan() {
        hideKaryawanModal(modalTambahKaryawan, modalBoxTambahKaryawan);
        document.getElementById('formTambahKaryawan').reset();
    }

    function bukaEditKaryawan(button) {
        const form = document.getElementById('formEditKaryawan');
        form.elements['id_karyawan'].value = button.dataset.id || '';
        form.elements['nama'].value = button.dataset.nama || '';
        form.elements['jenis_kelamin'].value = button.dataset.gender || 'Laki-laki';
        form.elements['tanggal_lahir'].value = button.dataset.lahir || '';
        form.elements['email'].value = button.dataset.email || '';
        form.elements['no_telepon'].value = button.dataset.telepon || '';
        form.elements['jabatan'].value = button.dataset.jabatan || '';
        form.elements['divisi_id_divisi'].value = button.dataset.divisi || '';
        form.elements['username'].value = button.dataset.username || '';
        form.elements['password'].value = '';
        showKaryawanModal(modalEditKaryawan, modalBoxEditKaryawan);
    }

    function tutupEditKaryawan() {
        hideKaryawanModal(modalEditKaryawan, modalBoxEditKaryawan);
    }

    function karyawanCsrf(form) {
        return form.querySelector('input[name="_token"]').value;
    }

    async function kirimStaff(url, method, body, form) {
        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': karyawanCsrf(form),
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

    async function submitFormKaryawan(event) {
        event.preventDefault();
        const form = event.currentTarget;
        const button = document.getElementById('btnSubmitKaryawan');
        const data = Object.fromEntries(new FormData(form).entries());
        delete data._token;
        button.disabled = true;
        try {
            const result = await kirimStaff('{{ url('/hrd/staff') }}', 'POST', data, form);
            alert(result.message || 'Karyawan berhasil ditambahkan.');
            window.location.reload();
        } catch (error) {
            alert(error.message || 'Gagal terhubung ke server.');
        } finally {
            button.disabled = false;
        }
    }

    async function submitEditKaryawan(event) {
        event.preventDefault();
        const form = event.currentTarget;
        const button = document.getElementById('btnSimpanEditKaryawan');
        const data = Object.fromEntries(new FormData(form).entries());
        const id = data.id_karyawan;
        delete data._token;
        delete data.id_karyawan;
        if (!data.password) delete data.password;
        button.disabled = true;
        try {
            const result = await kirimStaff('{{ url('/hrd/staff') }}/' + encodeURIComponent(id), 'PUT', data, form);
            alert(result.message || 'Data karyawan berhasil diperbarui.');
            window.location.reload();
        } catch (error) {
            alert(error.message || 'Gagal mengubah data karyawan.');
        } finally {
            button.disabled = false;
        }
    }

    async function hapusKaryawan(button) {
        const id = button.dataset.id;
        if (!confirm(`Hapus ${button.dataset.nama} beserta akun loginnya?`)) return;

        try {
            const result = await kirimStaff(
                '{{ url('/hrd/staff') }}/' + encodeURIComponent(id),
                'DELETE',
                null,
                document.getElementById('formTambahKaryawan')
            );
            alert(result.message || 'Karyawan berhasil dihapus.');
            window.location.reload();
        } catch (error) {
            alert(error.message || 'Gagal menghapus karyawan.');
        }
    }
</script>
