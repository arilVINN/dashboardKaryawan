<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil {{ ucfirst($profileRole) }} - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden text-slate-800">
    @if ($profileRole === 'kadiv')
        @include('component_kadiv.sidebar')
    @elseif ($profileRole === 'hrd')
        @include('component_hrd.sidebar')
    @else
        @include('component.sidebar')
    @endif

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')

        <main class="flex-1 overflow-y-auto px-6 pb-8 pt-6 sm:px-8">
            @include('component.breadcrumbs', [
                'parentText' => 'Dashboard',
                'parentUrl' => $profileRole === 'staff' ? url('/') : url('/'.$profileRole.'/dashboard'),
                'currentPage' => 'Profil Saya',
                'inline' => true,
            ])

            <div class="mb-6">
                <h1 class="text-xl font-semibold text-[#172B36]">Profil Saya</h1>
                <p class="mt-1 text-[12px] text-[#4B5563]">Perbarui data pribadi dan keamanan akun Anda.</p>
            </div>

            {{-- Informasi pribadi --}}
            <div id="view-info" class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-lg font-semibold text-[#172B36]">Informasi Pribadi</h2>
                    <button type="button" id="btn-edit-info" aria-label="Ubah informasi pribadi"
                            class="cursor-pointer rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-50 hover:text-slate-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </button>
                </div>

                <div class="flex flex-wrap items-center gap-6">
                    <div id="prof-inisial"
                         class="flex h-32 w-32 shrink-0 items-center justify-center rounded-full bg-slate-300 text-4xl font-bold text-slate-600">U</div>
                    <img id="prof-foto" alt="Foto profil" class="hidden h-32 w-32 shrink-0 rounded-full object-cover"
                         onerror="this.classList.add('hidden'); document.getElementById('prof-inisial').classList.remove('hidden');">
                    <div class="min-w-0 flex-1">
                        <h3 id="hdr-nama" class="text-2xl font-bold leading-tight text-slate-900">Memuat...</h3>
                        <p id="hdr-email" class="mt-1 text-sm text-slate-500">-</p>
                        <p id="hdr-telp" class="mt-0.5 text-sm text-slate-500">-</p>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <p class="mb-0.5 text-xs text-slate-400">Alamat</p>
                        <p id="prof-alamat" class="text-sm font-semibold text-slate-900">-</p>
                    </div>
                    <div>
                        <p class="mb-0.5 text-xs text-slate-400">No. Rekening</p>
                        <p id="prof-rekening" class="text-sm font-semibold text-slate-900">-</p>
                    </div>
                </div>
            </div>

            {{-- Informasi pribadi — mode edit --}}
            <div id="edit-info" class="mt-6 hidden rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <h2 class="mb-4 border-b border-slate-100 pb-3 text-lg font-semibold text-[#172B36]">Informasi Pribadi</h2>
                <form id="form-edit-profil" class="space-y-4">
                    <div>
                        <label for="input-nama" class="mb-1 block text-xs text-slate-500">Nama</label>
                        <input type="text" id="input-nama" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#146C94]">
                    </div>
                    <div>
                        <label for="input-email" class="mb-1 block text-xs text-slate-500">Email</label>
                        <input type="email" id="input-email" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#146C94]">
                    </div>
                    <div>
                        <label for="input-telepon" class="mb-1 block text-xs text-slate-500">Nomor Telepon</label>
                        <input type="text" id="input-telepon" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#146C94]">
                    </div>
                    <div>
                        <label for="input-rekening" class="mb-1 block text-xs text-slate-500">No. Rekening</label>
                        <input type="text" id="input-rekening" placeholder="Contoh: BCA 1234567890"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#146C94]">
                    </div>
                    <div>
                        <label for="input-alamat" class="mb-1 block text-xs text-slate-500">Alamat</label>
                        <textarea id="input-alamat" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#146C94]"></textarea>
                    </div>
                    <div>
                        <label for="input-foto" class="mb-1 block text-xs text-slate-500">Foto Profil (JPG/PNG/WebP, maks. 2 MB)</label>
                        <input type="file" id="input-foto" accept="image/jpeg,image/png,image/webp"
                               class="w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border file:border-slate-300 file:bg-slate-50 file:px-3 file:py-2 file:text-sm hover:file:bg-slate-100">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" id="btn-cancel-edit"
                                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                        <button type="submit"
                                class="rounded-lg bg-[#146C94] px-4 py-2 text-sm font-semibold text-white hover:bg-[#0F5678]">Simpan</button>
                    </div>
                </form>
            </div>

            {{-- Detail karyawan --}}
            <div class="mt-6 rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <h2 class="mb-5 border-b border-slate-100 pb-3 text-lg font-semibold text-[#172B36]">Detail Karyawan</h2>

                <h3 class="text-[12px] font-semibold uppercase tracking-wide text-[#475569]">Informasi Kepegawaian</h3>
                <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div><p class="mb-0.5 text-xs text-slate-400">ID Karyawan</p><p id="prof-id" class="text-sm font-semibold text-slate-900">-</p></div>
                    <div><p class="mb-0.5 text-xs text-slate-400">Role Akun</p><p id="prof-role" class="text-sm font-semibold text-slate-900">-</p></div>
                    <div><p class="mb-0.5 text-xs text-slate-400">Jabatan</p><p id="prof-jabatan" class="text-sm font-semibold text-slate-900">-</p></div>
                    <div><p class="mb-0.5 text-xs text-slate-400">Divisi</p><p id="prof-divisi" class="text-sm font-semibold text-slate-900">-</p></div>
                    <div><p class="mb-0.5 text-xs text-slate-400">Status Karyawan</p><p id="prof-status-karyawan" class="text-sm font-semibold text-slate-900">-</p></div>
                    <div><p class="mb-0.5 text-xs text-slate-400">Status Keaktifan</p><p id="prof-status-aktif" class="text-sm font-semibold text-slate-900">-</p></div>
                </div>

                <h3 class="mt-6 text-[12px] font-semibold uppercase tracking-wide text-[#475569]">Informasi Pekerjaan</h3>
                <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div><p class="mb-0.5 text-xs text-slate-400">Job Level</p><p id="prof-job-level" class="text-sm font-semibold text-slate-900">-</p></div>
                    <div><p class="mb-0.5 text-xs text-slate-400">Pendidikan Terakhir</p><p id="prof-pendidikan" class="text-sm font-semibold text-slate-900">-</p></div>
                    <div><p class="mb-0.5 text-xs text-slate-400">Tanggal Masuk</p><p id="prof-rekrut" class="text-sm font-semibold text-slate-900">-</p></div>
                    <div><p class="mb-0.5 text-xs text-slate-400">Tanggal Akhir Kontrak</p><p id="prof-akhir-kontrak" class="text-sm font-semibold text-slate-900">-</p></div>
                    <div><p class="mb-0.5 text-xs text-slate-400">Akun Dibuat</p><p id="prof-dibuat" class="text-sm font-semibold text-slate-900">-</p></div>
                </div>

                <h3 class="mt-6 text-[12px] font-semibold uppercase tracking-wide text-[#475569]">Informasi Personal</h3>
                <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div><p class="mb-0.5 text-xs text-slate-400">Jenis Kelamin</p><p id="prof-gender" class="text-sm font-semibold text-slate-900">-</p></div>
                    <div><p class="mb-0.5 text-xs text-slate-400">Tanggal Lahir</p><p id="prof-tgl-lahir" class="text-sm font-semibold text-slate-900">-</p></div>
                </div>
            </div>

            {{-- Keamanan akun --}}
            <div class="mt-6 rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <h2 class="mb-5 border-b border-slate-100 pb-3 text-lg font-semibold text-[#172B36]">Keamanan Akun</h2>
                <form id="form-password" class="space-y-4">
                    <div>
                        <label for="current_password" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-[#475569]">Password Saat Ini</label>
                        <div class="relative">
                            <input type="password" id="current_password" required placeholder="Masukkan password saat ini"
                                   data-toggle-password="current_password"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 pr-10 text-sm outline-none focus:ring-2 focus:ring-[#146C94]">
                            <button type="button" class="toggle-password absolute inset-y-0 right-0 flex w-10 items-center justify-center text-slate-400 hover:text-[#146C94]"
                                    aria-label="Tampilkan password saat ini">👁</button>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="new_password" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-[#475569]">Password Baru</label>
                            <div class="relative">
                                <input type="password" id="new_password" required placeholder="Minimal 6 karakter"
                                       data-toggle-password="new_password"
                                       class="w-full rounded-lg border border-slate-300 px-3 py-2 pr-10 text-sm outline-none focus:ring-2 focus:ring-[#146C94]">
                                <button type="button" class="toggle-password absolute inset-y-0 right-0 flex w-10 items-center justify-center text-slate-400 hover:text-[#146C94]"
                                        aria-label="Tampilkan password baru">👁</button>
                            </div>
                        </div>
                        <div>
                            <label for="confirm_password" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-[#475569]">Ulangi Password Baru</label>
                            <div class="relative">
                                <input type="password" id="confirm_password" required placeholder="Ulangi password baru"
                                       data-toggle-password="confirm_password"
                                       class="w-full rounded-lg border border-slate-300 px-3 py-2 pr-10 text-sm outline-none focus:ring-2 focus:ring-[#146C94]">
                                <button type="button" class="toggle-password absolute inset-y-0 right-0 flex w-10 items-center justify-center text-slate-400 hover:text-[#146C94]"
                                        aria-label="Tampilkan konfirmasi password">👁</button>
                            </div>
                        </div>
                    </div>
                    <p class="text-[12px] text-[#4B5563]">Minimal 6 karakter.</p>
                    <p id="password-error" class="hidden text-[12px] text-[#B91C1C]" role="alert"></p>
                    <div class="pt-2">
                        <button type="submit" class="rounded-lg bg-[#146C94] px-5 py-2 text-sm font-semibold text-white hover:bg-[#0F5678]">Perbarui Password</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async function () {
            const token = sessionStorage.getItem('staff_token');
            const endpoint = @json('/api/'.$profileRole.'/profile', JSON_UNESCAPED_SLASHES);

            function headers(json) {
                const h = { 'Accept': 'application/json' };
                if (token) h['Authorization'] = 'Bearer ' + token;
                if (json) h['Content-Type'] = 'application/json';
                return h;
            }

            function teks(id, nilai) {
                const el = document.getElementById(id);
                if (el) el.textContent = nilai && nilai !== '-' ? nilai : '-';
            }

            function isi(id, nilai) {
                const el = document.getElementById(id);
                if (el) el.value = nilai && nilai !== '-' ? nilai : '';
            }

            async function loadProfile() {
                try {
                    const response = await fetch(endpoint, { headers: headers(false) });
                    const json = await response.json();
                    if (!response.ok || !json.data) return;

                    const p = json.data;
                    teks('hdr-nama', p.nama);
                    teks('hdr-email', p.email);
                    teks('hdr-telp', p.no_telepon);
                    const inisial = document.getElementById('prof-inisial');
                    inisial.textContent = p.nama && p.nama !== '-' ? p.nama.charAt(0).toUpperCase() : 'U';

                    const foto = document.getElementById('prof-foto');
                    if (p.foto_url) {
                        foto.src = p.foto_url;
                        foto.classList.remove('hidden');
                        inisial.classList.add('hidden');
                    } else {
                        foto.classList.add('hidden');
                        inisial.classList.remove('hidden');
                    }

                    ['id', 'role', 'jabatan', 'divisi', 'status-karyawan', 'status-aktif',
                        'job-level', 'pendidikan', 'rekrut', 'akhir-kontrak', 'dibuat',
                        'gender', 'tgl-lahir', 'alamat', 'rekening'].forEach((k) => {
                        const field = {
                            id: 'id_karyawan', role: 'tingkatan', dibuat: 'tanggal_dibuat',
                            'status-karyawan': 'status_karyawan', 'status-aktif': 'status_aktif',
                            'job-level': 'job_level', 'tgl-lahir': 'tanggal_lahir',
                            rekrut: 'tanggal_masuk_label', 'akhir-kontrak': 'tanggal_akhir_kontrak',
                            gender: 'jenis_kelamin', rekening: 'no_rekening', alamat: 'alamat',
                            jabatan: 'jabatan', divisi: 'divisi', pendidikan: 'pendidikan',
                        }[k];
                        teks('prof-' + k, p[field]);
                    });

                    isi('input-nama', p.nama);
                    isi('input-email', p.email);
                    isi('input-telepon', p.no_telepon);
                    isi('input-rekening', p.no_rekening);
                    isi('input-alamat', p.alamat);
                } catch (e) {
                    console.error('Gagal memuat profil:', e);
                }
            }

            await loadProfile();

            const viewInfo = document.getElementById('view-info');
            const editInfo = document.getElementById('edit-info');
            document.getElementById('btn-edit-info').addEventListener('click', () => {
                viewInfo.classList.add('hidden');
                editInfo.classList.remove('hidden');
            });
            document.getElementById('btn-cancel-edit').addEventListener('click', () => {
                editInfo.classList.add('hidden');
                viewInfo.classList.remove('hidden');
            });

            document.getElementById('form-edit-profil').addEventListener('submit', async function (e) {
                e.preventDefault();
                let profilTersimpan = false;
                try {
                    const res = await fetch(endpoint, {
                        method: 'PUT',
                        headers: headers(true),
                        body: JSON.stringify({
                            nama: document.getElementById('input-nama').value.trim(),
                            email: document.getElementById('input-email').value.trim(),
                            no_telepon: document.getElementById('input-telepon').value.trim(),
                            no_rekening: document.getElementById('input-rekening').value.trim() || null,
                            alamat: document.getElementById('input-alamat').value.trim(),
                        }),
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        alert(data.message || 'Gagal memperbarui profil.');
                        return;
                    }
                    profilTersimpan = true;

                    const fotoInput = document.getElementById('input-foto');
                    if (fotoInput.files.length > 0) {
                        const fotoData = new FormData();
                        fotoData.append('foto', fotoInput.files[0]);
                        const fotoRes = await fetch(endpoint + '/foto', {
                            method: 'POST',
                            headers: headers(false),
                            body: fotoData,
                        });
                        if (!fotoRes.ok) {
                            alert('Data profil tersimpan, tetapi foto gagal diunggah.');
                        }
                        fotoInput.value = '';
                    }

                    alert('Profil berhasil diperbarui!');
                    editInfo.classList.add('hidden');
                    viewInfo.classList.remove('hidden');
                    await loadProfile();
                } catch (err) {
                    alert(profilTersimpan
                        ? 'Data profil tersimpan, tetapi unggah foto gagal.'
                        : 'Kesalahan jaringan.');
                }
            });

            // Show/hide password controls
            document.querySelectorAll('[data-toggle-password]').forEach((input) => {
                input.parentElement.querySelector('.toggle-password').addEventListener('click', function () {
                    input.type = input.type === 'password' ? 'text' : 'password';
                    this.setAttribute('aria-label', input.type === 'password' ? 'Tampilkan password' : 'Sembunyikan password');
                });
            });

            document.getElementById('form-password').addEventListener('submit', async function (e) {
                e.preventDefault();
                const error = document.getElementById('password-error');
                error.classList.add('hidden');
                error.textContent = '';

                const baru = document.getElementById('new_password').value;
                const konfirmasi = document.getElementById('confirm_password').value;
                if (baru !== konfirmasi) {
                    error.textContent = 'Konfirmasi password tidak cocok.';
                    error.classList.remove('hidden');
                    return;
                }
                if (baru.length < 6) {
                    error.textContent = 'Password minimal 6 karakter.';
                    error.classList.remove('hidden');
                    return;
                }

                try {
                    const res = await fetch(endpoint + '/password', {
                        method: 'PUT',
                        headers: headers(true),
                        body: JSON.stringify({
                            current_password: document.getElementById('current_password').value,
                            new_password: baru,
                            confirm_password: konfirmasi,
                        }),
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        error.textContent = data.message || 'Gagal memperbarui password.';
                        error.classList.remove('hidden');
                        return;
                    }
                    alert(data.message || 'Password berhasil diperbarui.');
                    this.reset();
                } catch (err) {
                    error.textContent = 'Kesalahan jaringan.';
                    error.classList.remove('hidden');
                }
            });
        });
    </script>
</body>

</html>
