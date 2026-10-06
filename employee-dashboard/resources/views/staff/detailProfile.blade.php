<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - PT Silindo</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-slate-50 flex h-screen overflow-hidden text-slate-800">
    @include('component.sidebar')
    
    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')
        
        <main class="flex-1 overflow-y-auto p-8 lg:p-12">
            <!-- Header Profil (Mirip Screenshot) -->
            <div class="flex items-center gap-6 mb-10">
                <div id="prof-inisial" class="w-24 h-24 rounded-full bg-slate-300 text-slate-600 flex items-center justify-center text-4xl font-bold shrink-0">
U
                </div>
                <div>
                    <h1 id="hdr-nama" class="text-2xl font-bold text-slate-900 leading-tight">Memuat...</h1>
                    <p id="hdr-email" class="text-sm text-slate-500 mt-1">-</p>
                    <p id="hdr-telp" class="text-sm text-slate-500 mt-0.5">-</p>
                </div>
            </div>

            <!-- Grid Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                
                <!-- KIRI -->
                <div class="space-y-6">
                    <!-- Detail Karyawan -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                        <h2 class="text-lg font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Detail Karyawan</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs text-slate-400 mb-0.5">Tingkatan</p>
                                <p class="text-sm font-semibold text-slate-900">Staff</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 mb-0.5">Divisi</p>
                                <p id="prof-divisi" class="text-sm font-semibold text-slate-900">-</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 mb-0.5">Tanggal Masuk</p>
                                <p id="prof-rekrut" class="text-sm font-semibold text-slate-900">-</p>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Akun -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                        <h2 class="text-lg font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Informasi Akun</h2>
                        <div>
                            <p class="text-xs text-slate-400 mb-0.5">Tanggal Dibuat</p>
                            <p id="prof-dibuat" class="text-sm font-semibold text-slate-900">-</p>
                        </div>
                    </div>
                </div>

                <!-- KANAN -->
                <div class="space-y-6">
                    <!-- Informasi Pribadi (Mode Baca) -->
                    <div id="view-info" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative">
                        <div class="flex justify-between items-center border-b border-slate-100 pb-3 mb-4">
                            <h2 class="text-lg font-bold text-slate-900">Informasi Pribadi</h2>
                            <button id="btn-edit-info" class="text-slate-400 hover:text-slate-700 transition cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-slate-400 mb-0.5">Nama</p>
                                <p id="prof-nama" class="text-sm font-semibold text-slate-900">-</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 mb-0.5">Email</p>
                                <p id="prof-email" class="text-sm font-semibold text-slate-900">-</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 mb-0.5">Alamat</p>
                                <p id="prof-alamat" class="text-sm font-semibold text-slate-900">-</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 mb-0.5">Nomor Telepon</p>
                                <p id="prof-telepon" class="text-sm font-semibold text-slate-900">-</p>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Pribadi (Mode Edit) -->
                    <div id="edit-info" class="hidden bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative">
                        <h2 class="text-lg font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Informasi Pribadi</h2>
                        <form id="form-edit-profil" class="space-y-4">
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Nama</label>
                                <input type="text" id="input-nama" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Email</label>
                                <input type="email" id="input-email" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Nomor Telepon</label>
                                <input type="text" id="input-telepon" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Alamat</label>
                                <textarea id="input-alamat" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500 outline-none"></textarea>
                            </div>
                            <div class="flex justify-end gap-2 pt-2">
                                <button type="button" id="btn-cancel-edit" class="px-4 py-2 border border-slate-300 text-slate-600 text-sm font-semibold rounded-lg hover:bg-slate-50">Batal</button>
                                <button type="submit" class="px-4 py-2 bg-[#044564] text-white text-sm font-semibold rounded-lg hover:bg-[#03344b]">Simpan</button>
                            </div>
                        </form>
                    </div>

                    <!-- Keamanan Akun (Ganti Password) -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                        <h2 class="text-lg font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Keamanan Akun</h2>
                        <form id="form-password" class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1 uppercase tracking-wide">Password Saat Ini</label>
                                <input type="password" id="current_password" required placeholder="********" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500 outline-none">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1 uppercase tracking-wide">Password Baru</label>
                                    <input type="password" id="new_password" required placeholder="********" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1 uppercase tracking-wide">Ulangi Password Baru</label>
                                    <input type="password" id="confirm_password" required placeholder="********" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500 outline-none">
                                </div>
                            </div>
                            <div class="pt-2">
                                <button type="submit" class="px-5 py-2 bg-[#044564] text-white text-sm font-semibold rounded-lg hover:bg-[#03344b]">Perbarui Password</button>
                            </div>
                        </form>
                    </div>

                    <!-- Tombol Keluar (Opsional, tambahan jika mau di paling bawah) -->
                    <div class="pt-2 text-right">
                        <button id="btn-logout" class="px-5 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700">Logout dari Perangkat</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async function() {
            const token = localStorage.getItem('staff_token');
            if (!token) { window.location.href = '/login'; return; }

            // 1. Fetch Profile Data
            async function loadProfile() {
                try {
                    const response = await fetch('/api/staff/profile', {
                        headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                    });
                    const json = await response.json();
                    if (response.ok && json.data) {
                        const p = json.data;
                        
                        // Header
                        document.getElementById('hdr-nama').textContent = p.nama;
                        document.getElementById('hdr-email').textContent = p.email;
                        document.getElementById('hdr-telp').textContent = p.no_telepon;
                        document.getElementById('prof-inisial').textContent = p.nama && p.nama !== '-' ? p.nama.charAt(0).toUpperCase() : 'U';

                        // Kiri
                        document.getElementById('prof-divisi').textContent = p.divisi;
                        document.getElementById('prof-dibuat').textContent = p.tanggal_dibuat;
                        
                        let rekrut = p.tanggal_rekrut;
                        if (rekrut && rekrut !== '-') {
                            const d = new Date(rekrut);
                            rekrut = d.getDate() + ' ' + ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'][d.getMonth()] + ' ' + d.getFullYear();
                        }
                        document.getElementById('prof-rekrut').textContent = rekrut;

                        // Kanan View
                        document.getElementById('prof-nama').textContent = p.nama;
                        document.getElementById('prof-email').textContent = p.email;
                        document.getElementById('prof-alamat').textContent = p.alamat;
                        document.getElementById('prof-telepon').textContent = p.no_telepon;

                        // Form Edit Values
                        document.getElementById('input-nama').value = p.nama !== '-' ? p.nama : '';
                        document.getElementById('input-email').value = p.email !== '-' ? p.email : '';
                        document.getElementById('input-telepon').value = p.no_telepon !== '-' ? p.no_telepon : '';
                        document.getElementById('input-alamat').value = p.alamat !== '-' ? p.alamat : '';
                    }
                } catch (e) {
                    console.error(e);
                }
            }
            
            await loadProfile();

            // 2. Edit Profile Toggle
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

            // 3. Submit Update Profile
            document.getElementById('form-edit-profil').addEventListener('submit', async function(e) {
                e.preventDefault();
                try {
                    const res = await fetch('/api/staff/profile', {
                        method: 'PUT',
                        headers: {
                            'Authorization': 'Bearer ' + token,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            nama: document.getElementById('input-nama').value || '-',
                            email: document.getElementById('input-email').value || '-',
                            no_telepon: document.getElementById('input-telepon').value || '-',
                            alamat: document.getElementById('input-alamat').value || '-',
                        })
                    });
                    const data = await res.json();
                    if (res.ok) {
                        alert('Profil berhasil diperbarui!');
                        editInfo.classList.add('hidden');
                        viewInfo.classList.remove('hidden');
                        loadProfile();
                    } else {
                        alert(data.message || 'Gagal memperbarui profil.');
                    }
                } catch (err) {
                    alert('Kesalahan jaringan.');
                }
            });

            // 4. Update Password
            document.getElementById('form-password').addEventListener('submit', async function(e) {
                e.preventDefault();
                const current = document.getElementById('current_password').value;
                const newPass = document.getElementById('new_password').value;
                const confirm = document.getElementById('confirm_password').value;

                if (newPass !== confirm) {
                    return alert('Password baru tidak cocok dengan ulangi password.');
                }

                try {
                    const res = await fetch('/api/staff/profile/password', {
                        method: 'PUT',
                        headers: {
                            'Authorization': 'Bearer ' + token,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            current_password: current,
                            new_password: newPass
                        })
                    });
                    const data = await res.json();
                    if (res.ok) {
                        alert('Password berhasil diperbarui!');
                        document.getElementById('form-password').reset();
                    } else {
                        alert(data.message || 'Gagal memperbarui password.');
                    }
                } catch (err) {
                    alert('Kesalahan jaringan.');
                }
            });

            // 5. Logout
            const btnLogout = document.getElementById('btn-logout');
            if (btnLogout) {
                btnLogout.addEventListener('click', async function() {
                    if(!confirm("Apakah Anda yakin ingin logout?")) return;
                    try {
                        await fetch('/api/logout', { method: 'POST', headers: { 'Authorization': 'Bearer ' + token } });
                    } catch(e) {}
                    localStorage.removeItem('staff_token');
                    window.location.href = '/login';
                });
            }

        });
    </script>
</body>
</html>
