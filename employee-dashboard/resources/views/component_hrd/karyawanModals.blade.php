@php
    $daftarDivisi = $daftarDivisi ?? collect();
    // Optional: preset + lock the division (used by the division detail page).
    $divisiTerkunci = $divisiTerkunci ?? null;
    $judulTambah = $judulTambah ?? 'Tambah Karyawan';
    $formControl = 'w-full px-3 py-2 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]';
@endphp

<!-- Modal Tambah Karyawan -->
<div id="modalTambahKaryawan" data-staff-modal="create"
    class="opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center transition-opacity duration-300">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupModalKaryawan()"></div>
    <div id="modalBoxTambahKaryawan"
        class="transform scale-95 translate-y-4 relative bg-white rounded-xl shadow-xl w-full max-w-xl max-h-[90vh] flex flex-col overflow-hidden transition-all duration-300">
        <div class="flex items-center justify-between p-4 border-b border-dashed border-gray-300">
            <div class="flex items-center gap-3">
                <div class="bg-blue-100 p-1.5 rounded text-blue-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0ZM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900">{{ $judulTambah }}</h3>
            </div>

            <button type="button" onclick="tutupModalKaryawan()" class="text-slate-400 hover:text-red-500 transition"
                aria-label="Tutup">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
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
                        <div class="flex flex-col gap-1.5 sm:col-span-2">
                            <label class="text-xs font-bold text-slate-500">ALAMAT</label>
                            <textarea name="alamat" rows="2" class="{{ $formControl }}" placeholder="Jalan, nomor, kota"></textarea>
                        </div>
                        <div class="flex flex-col gap-1.5 sm:col-span-2">
                            <label class="text-xs font-bold text-slate-500">NO. REKENING (OPSIONAL)</label>
                            <input type="text" name="no_rekening" maxlength="50" placeholder="Contoh: BCA 1234567890" class="{{ $formControl }}">
                        </div>
                        <div class="flex flex-col gap-1.5 sm:col-span-2">
                            <label class="text-xs font-bold text-slate-500">FOTO PROFIL (JPG/PNG/WEBP, MAKS. 2 MB,
                                OPSIONAL)</label>
                            <input type="file" id="input-foto-karyawan" accept="image/jpeg,image/png,image/webp"
                                class="{{ $formControl }}">
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
                            <select name="jabatan" required class="{{ $formControl }}">
                                <option value="">Pilih jabatan</option>
                                @foreach (\App\Models\Karyawan::JABATAN_OPTIONS as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-slate-500">DIVISI</label>
                            @if ($divisiTerkunci)
                                <select class="{{ $formControl }}" disabled>
                                    @foreach ($daftarDivisi as $d)
                                        <option value="{{ $d->id_divisi }}" @selected($d->id_divisi === $divisiTerkunci)>
                                            {{ $d->nama_divisi }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="divisi_id_divisi" value="{{ $divisiTerkunci }}">
                            @else
                                <select name="divisi_id_divisi" required class="{{ $formControl }}">
                                    <option value="">Pilih divisi</option>
                                    @foreach ($daftarDivisi as $d)
                                        <option value="{{ $d->id_divisi }}">{{ $d->nama_divisi }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-slate-500">TANGGAL MASUK</label>
                            <input type="date" name="tanggal_masuk" class="{{ $formControl }}">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-slate-500">TANGGAL AKHIR KONTRAK (KOSONGKAN JIKA TETAP)</label>
                            <input type="date" name="tanggal_akhir_kontrak" class="{{ $formControl }}">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-slate-500">STATUS</label>
                            <select name="status_aktif" required class="{{ $formControl }}">
                                @foreach (\App\Models\Karyawan::STATUS_OPTIONS as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </section>

                <section class="space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="h-4 w-1 rounded bg-[#0097B2]"></span>
                        <h4 class="text-xs font-bold uppercase tracking-wide text-slate-600">Upload Lampiran (Maks 5MB per file, PDF/JPG/PNG)</h4>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-slate-500">SURAT LAMARAN (OPSIONAL)</label>
                            <input type="file" name="lampiran[lamaran]" accept=".pdf,.jpg,.jpeg,.png" class="{{ $formControl }}">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-slate-500">IJAZAH (OPSIONAL)</label>
                            <input type="file" name="lampiran[ijazah]" accept=".pdf,.jpg,.jpeg,.png" class="{{ $formControl }}">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-slate-500">TRANSKRIP NILAI (OPSIONAL)</label>
                            <input type="file" name="lampiran[transkrip]" accept=".pdf,.jpg,.jpeg,.png" class="{{ $formControl }}">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-slate-500">KTP (OPSIONAL)</label>
                            <input type="file" name="lampiran[ktp]" accept=".pdf,.jpg,.jpeg,.png" class="{{ $formControl }}">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-slate-500">KARTU KELUARGA / KK (OPSIONAL)</label>
                            <input type="file" name="lampiran[kk]" accept=".pdf,.jpg,.jpeg,.png" class="{{ $formControl }}">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-slate-500">OFFERING LETTER (OPSIONAL)</label>
                            <input type="file" name="lampiran[offering_letter]" accept=".pdf,.jpg,.jpeg,.png" class="{{ $formControl }}">
                        </div>
                        <div class="flex flex-col gap-1.5 sm:col-span-2">
                            <label class="text-xs font-bold text-slate-500">LAINNYA / SK (OPSIONAL)</label>
                            <input type="file" name="lampiran[lainnya]" accept=".pdf,.jpg,.jpeg,.png" class="{{ $formControl }}">
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
                            <input type="text" name="username" maxlength="50" required
                                class="{{ $formControl }}">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-slate-500">PASSWORD (MIN. 6)</label>
                            <input type="password" name="password" minlength="6" required
                                class="{{ $formControl }}">
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
            <div class="flex items-center gap-3">
                <div class="bg-blue-100 p-1.5 rounded text-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900">Edit Karyawan</h3>
            </div>
            <button type="button" onclick="tutupEditKaryawan()" class="text-slate-400 hover:text-red-500"
                aria-label="Tutup">&times;
            </button>
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
                            <input type="text" name="nama" maxlength="100" required
                                class="{{ $formControl }}">
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
                        <div class="flex flex-col gap-1.5 sm:col-span-2">
                            <label class="text-xs font-bold text-slate-500">ALAMAT</label>
                            <textarea name="alamat" rows="2" class="{{ $formControl }}" placeholder="Jalan, nomor, kota"></textarea>
                        </div>
                        <div class="flex flex-col gap-1.5 sm:col-span-2">
                            <label class="text-xs font-bold text-slate-500">NO. REKENING (OPSIONAL)</label>
                            <input type="text" name="no_rekening" maxlength="50" placeholder="Contoh: BCA 1234567890" class="{{ $formControl }}">
                        </div>
                        <div class="flex flex-col gap-1.5 sm:col-span-2">
                            <label class="text-xs font-bold text-slate-500">FOTO PROFIL BARU (JPG/PNG/WEBP, MAKS. 2 MB,
                                OPSIONAL)</label>
                            <input type="file" id="input-foto-edit-karyawan"
                                accept="image/jpeg,image/png,image/webp" class="{{ $formControl }}">
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
                            <select name="jabatan" required class="{{ $formControl }}">
                                <option value="">Pilih jabatan</option>
                                @foreach (\App\Models\Karyawan::JABATAN_OPTIONS as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-slate-500">DIVISI</label>
                            <select name="divisi_id_divisi" required class="{{ $formControl }}">
                                @foreach ($daftarDivisi as $d)
                                    <option value="{{ $d->id_divisi }}">{{ $d->nama_divisi }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-slate-500">TANGGAL MASUK</label>
                            <input type="date" name="tanggal_masuk" class="{{ $formControl }}">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-slate-500">TANGGAL AKHIR KONTRAK (KOSONGKAN JIKA TETAP)</label>
                            <input type="date" name="tanggal_akhir_kontrak" class="{{ $formControl }}">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-slate-500">STATUS</label>
                            <select name="status_aktif" required class="{{ $formControl }}">
                                @foreach (\App\Models\Karyawan::STATUS_OPTIONS as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </section>

                <section class="space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="h-4 w-1 rounded bg-[#0097B2]"></span>
                        <h4 class="text-xs font-bold uppercase tracking-wide text-slate-600">Upload Lampiran (Maks 5MB per file, PDF/JPG/PNG)</h4>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-slate-500">SURAT LAMARAN (OPSIONAL)</label>
                            <input type="file" name="lampiran[lamaran]" accept=".pdf,.jpg,.jpeg,.png" class="{{ $formControl }}">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-slate-500">IJAZAH (OPSIONAL)</label>
                            <input type="file" name="lampiran[ijazah]" accept=".pdf,.jpg,.jpeg,.png" class="{{ $formControl }}">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-slate-500">TRANSKRIP NILAI (OPSIONAL)</label>
                            <input type="file" name="lampiran[transkrip]" accept=".pdf,.jpg,.jpeg,.png" class="{{ $formControl }}">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-slate-500">KTP (OPSIONAL)</label>
                            <input type="file" name="lampiran[ktp]" accept=".pdf,.jpg,.jpeg,.png" class="{{ $formControl }}">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-slate-500">KARTU KELUARGA / KK (OPSIONAL)</label>
                            <input type="file" name="lampiran[kk]" accept=".pdf,.jpg,.jpeg,.png" class="{{ $formControl }}">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-slate-500">OFFERING LETTER (OPSIONAL)</label>
                            <input type="file" name="lampiran[offering_letter]" accept=".pdf,.jpg,.jpeg,.png" class="{{ $formControl }}">
                        </div>
                        <div class="flex flex-col gap-1.5 sm:col-span-2">
                            <label class="text-xs font-bold text-slate-500">LAINNYA / SK (OPSIONAL)</label>
                            <input type="file" name="lampiran[lainnya]" accept=".pdf,.jpg,.jpeg,.png" class="{{ $formControl }}">
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
                    class="px-4 py-1.5 bg-[#004A65] text-white text-sm font-medium rounded-md hover:bg-[#003347] transition">Simpan
                    Perubahan</button>
            </div>
        </form>
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
        if (form.elements['tanggal_masuk']) {
            form.elements['tanggal_masuk'].value = button.dataset.masuk || button.dataset.rekrut || '';
        }
        if (form.elements['tanggal_akhir_kontrak']) {
            form.elements['tanggal_akhir_kontrak'].value = button.dataset.akhirKontrak || '';
        }
        if (form.elements['no_rekening']) {
            form.elements['no_rekening'].value = button.dataset.rekening || '';
        }
        form.elements['email'].value = button.dataset.email || '';
        form.elements['alamat'].value = button.dataset.alamat || '';
        form.elements['no_telepon'].value = button.dataset.telepon || '';
        form.elements['jabatan'].value = button.dataset.jabatan || '';
        form.elements['status_aktif'].value = button.dataset.status || 'Aktif';
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

    async function unggahFotoKaryawan(url, inputId, form) {
        const input = document.getElementById(inputId);
        if (!input || input.files.length === 0) return;
        const body = new FormData();
        body.append('foto', input.files[0]);
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': karyawanCsrf(form),
            },
            body,
        });
        const result = await response.json();
        if (!response.ok) {
            const errors = result.errors ? Object.values(result.errors).flat().join('\n') : result.message;
            throw new Error(errors || 'Gagal mengunggah foto.');
        }
        input.value = '';
    }

    async function submitFormKaryawan(event) {
        event.preventDefault();
        const form = event.currentTarget;
        const button = document.getElementById('btnSubmitKaryawan');
        const formData = new FormData(form);
        button.disabled = true;
        try {
            const response = await fetch('{{ url('/hrd/staff') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': karyawanCsrf(form),
                },
                body: formData,
            });
            const result = await response.json();
            if (!response.ok) {
                const errors = result.errors ? Object.values(result.errors).flat().join('\n') : result.message;
                throw new Error(errors || 'Permintaan gagal.');
            }
            const createdId = result.data && result.data.karyawan ? result.data.karyawan.id_karyawan : null;
            if (createdId) {
                await unggahFotoKaryawan('{{ url('/hrd/staff') }}/' + encodeURIComponent(createdId) + '/foto',
                    'input-foto-karyawan', form);
            }
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
        const formData = new FormData(form);
        const id = formData.get('id_karyawan');
        if (!formData.get('password')) {
            formData.delete('password');
        }
        formData.append('_method', 'PUT');
        button.disabled = true;
        try {
            const response = await fetch('{{ url('/hrd/staff') }}/' + encodeURIComponent(id), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': karyawanCsrf(form),
                },
                body: formData,
            });
            const result = await response.json();
            if (!response.ok) {
                const errors = result.errors ? Object.values(result.errors).flat().join('\n') : result.message;
                throw new Error(errors || 'Gagal mengubah data karyawan.');
            }
            await unggahFotoKaryawan('{{ url('/hrd/staff') }}/' + encodeURIComponent(id) + '/foto',
                'input-foto-edit-karyawan', form);
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
