<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil HRD - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden text-slate-800">
    @include('component_hrd.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')
        @include('component.breadcrumbs', ['parentText' => 'Dashboard', 'parentUrl' => url('/hrd/dashboard'), 'currentPage' => 'Profil Saya'])

        <main class="flex-1 overflow-y-auto p-8 pt-6">
            <div class="max-w-5xl space-y-8">
                <div class="flex items-center gap-5">
                    <div class="relative w-20 h-20 shrink-0">
                    <div id="hrd-profile-initial"
                        class="w-20 h-20 rounded-full bg-[#044564] text-white flex items-center justify-center text-3xl font-bold">
                        H
                    </div>
                    <img id="hrd-profile-photo" alt="Foto profil HRD"
                        class="hidden absolute inset-0 w-20 h-20 rounded-full object-cover">
                    <label for="input-foto-hrd" title="Ubah foto profil"
                        class="absolute bottom-0 right-0 w-7 h-7 rounded-full bg-[#044564] text-white flex items-center justify-center cursor-pointer shadow border-2 border-slate-50">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </label>
                    <input type="file" id="input-foto-hrd" accept="image/jpeg,image/png,image/webp" class="hidden">
                </div>
                    <div>
                        <h1 id="hrd-profile-name" class="text-2xl font-bold text-slate-900">Profil HRD</h1>
                        <p id="hrd-profile-email" class="mt-1 text-sm text-slate-600">-</p>
                        <p id="hrd-profile-phone" class="mt-1 text-sm text-slate-600">-</p>
                    </div>
                </div>

                <p id="hrd-profile-error" class="hidden rounded-lg bg-red-50 p-4 text-sm text-red-700"></p>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <section class="rounded-xl border border-slate-100 bg-white p-6 shadow-sm">
                        <h2 class="mb-5 border-b border-slate-200 pb-3 text-lg font-bold">Detail Karyawan</h2>
                        <dl class="space-y-4 text-sm">
                            <div>
                                <dt class="text-xs text-slate-500">Jabatan</dt>
                                <dd id="hrd-profile-job" class="mt-1 font-semibold">-</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-500">Divisi</dt>
                                <dd id="hrd-profile-division" class="mt-1 font-semibold">-</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-500">Tanggal Masuk</dt>
                                <dd id="hrd-profile-hire-date" class="mt-1 font-semibold">-</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="rounded-xl border border-slate-100 bg-white p-6 shadow-sm">
                        <h2 class="mb-5 border-b border-slate-200 pb-3 text-lg font-bold">Informasi Akun</h2>
                        <dl class="space-y-4 text-sm">
                            <div>
                                <dt class="text-xs text-slate-500">Role</dt>
                                <dd class="mt-1 font-semibold">HRD</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-500">Tanggal Dibuat</dt>
                                <dd id="hrd-profile-created-date" class="mt-1 font-semibold">-</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="rounded-xl border border-slate-100 bg-white p-6 shadow-sm md:col-span-2">
                        <h2 class="mb-5 border-b border-slate-200 pb-3 text-lg font-bold">Informasi Pribadi</h2>
                        <dl class="grid grid-cols-1 gap-5 text-sm sm:grid-cols-2">
                            <div>
                                <dt class="text-xs text-slate-500">Nama</dt>
                                <dd id="hrd-profile-personal-name" class="mt-1 font-semibold">-</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-500">Email</dt>
                                <dd id="hrd-profile-personal-email" class="mt-1 font-semibold">-</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-500">Nomor Telepon</dt>
                                <dd id="hrd-profile-personal-phone" class="mt-1 font-semibold">-</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-500">Jenis Kelamin</dt>
                                <dd id="hrd-profile-gender" class="mt-1 font-semibold">-</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-xs text-slate-500">Alamat</dt>
                                <dd id="hrd-profile-address" class="mt-1 font-semibold">-</dd>
                            </div>
                        </dl>
                    </section>
                </div>
            </div>
        </main>
    </div>

    <script>
        (async function loadHrdProfile() {
            const token = sessionStorage.getItem('staff_token') || sessionStorage.getItem('staff_token');
            const errorElement = document.getElementById('hrd-profile-error');
            if (!token) {
                errorElement.textContent = 'Sesi login tidak ditemukan. Silakan login kembali.';
                errorElement.classList.remove('hidden');
                return;
            }

            const response = await fetch('/api/hrd/profile', {
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            const result = await response.json();
            if (!response.ok || !result.data) {
                errorElement.textContent = result.message || 'Gagal memuat profil HRD.';
                errorElement.classList.remove('hidden');
                return;
            }

            const profile = result.data;
            const values = {
                'hrd-profile-initial': profile.nama?.charAt(0).toUpperCase() || 'H',
                'hrd-profile-name': profile.nama,
                'hrd-profile-email': profile.email,
                'hrd-profile-phone': profile.no_telepon,
                'hrd-profile-job': profile.jabatan,
                'hrd-profile-division': profile.divisi,
                'hrd-profile-hire-date': profile.tanggal_rekrut,
                'hrd-profile-created-date': profile.tanggal_dibuat,
                'hrd-profile-personal-name': profile.nama,
                'hrd-profile-personal-email': profile.email,
                'hrd-profile-personal-phone': profile.no_telepon,
                'hrd-profile-gender': profile.jenis_kelamin,
                'hrd-profile-address': profile.alamat
            };

            Object.entries(values).forEach(([id, value]) => {
                document.getElementById(id).textContent = value || '-';
            });

            const hrdPhoto = document.getElementById('hrd-profile-photo');
            if (profile.foto_url) {
                hrdPhoto.src = profile.foto_url;
                hrdPhoto.classList.remove('hidden');
            }

            document.getElementById('input-foto-hrd').addEventListener('change', async function () {
                if (this.files.length === 0) return;
                const fotoData = new FormData();
                fotoData.append('foto', this.files[0]);
                try {
                    const fotoRes = await fetch('/api/hrd/profile/foto', {
                        method: 'POST',
                        headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
                        body: fotoData
                    });
                    const fotoResult = await fotoRes.json();
                    if (!fotoRes.ok) throw new Error((fotoResult.data && fotoResult.data.foto ? fotoResult.data.foto.join('\n') : fotoResult.message) || 'Gagal mengunggah foto.');
                    hrdPhoto.src = fotoResult.foto_url;
                    hrdPhoto.classList.remove('hidden');
                    alert('Foto profil berhasil diperbarui!');
                } catch (error) {
                    errorElement.textContent = error.message || 'Gagal mengunggah foto.';
                    errorElement.classList.remove('hidden');
                } finally {
                    this.value = '';
                }
            });
        })().catch(error => {
            const errorElement = document.getElementById('hrd-profile-error');
            errorElement.textContent = error.message || 'Gagal memuat profil HRD.';
            errorElement.classList.remove('hidden');
            console.error('Gagal memuat profil HRD:', error);
        });
    </script>
</body>

</html>

