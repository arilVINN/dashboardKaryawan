<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Kadiv - PT Silindo</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">

    {{-- Sidebar --}}
    @include('component_kadiv.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        {{-- Topbar --}}
        @include('component.topbar')

        <main class="flex-1 overflow-y-auto px-10 py-8">

            <div class="max-w-5xl space-y-8">

                {{-- Header Profil --}}
                <div class="flex items-center gap-6">

                    <div
                        class="w-28 h-28 rounded-full bg-slate-300 shrink-0 flex items-center justify-center text-4xl font-bold text-slate-500 uppercase">
                        <span id="profile-initial">-</span>
                    </div>

                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                            <span id="profile-name">-</span>
                        </h1>

                        <p class="text-sm text-slate-600 mt-1">
                            <span id="profile-header-email">-</span>
                        </p>

                        <p class="text-sm text-slate-600">
                            <span id="profile-header-phone">-</span>
                        </p>
                    </div>

                </div>

                {{-- Content --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">

                    {{-- KOLOM KIRI --}}
                    <div class="space-y-6">

                        {{-- Detail Karyawan --}}
                        <div class="bg-white border border-slate-100 rounded-xl p-6 shadow-sm">

                            <h2
                                class="text-lg font-bold text-slate-900 border-b border-slate-200 pb-3 mb-4">
                                Detail Karyawan
                            </h2>

                            <div class="space-y-4 text-sm">

                                <div>
                                    <p class="text-xs text-slate-500">
                                        Tingkatan
                                    </p>

                                    <p class="font-bold text-slate-900 mt-0.5">
                                        <span id="profile-role">-</span>
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-500">
                                        Divisi
                                    </p>

                                    <p class="font-bold text-slate-900 mt-0.5">
                                        <span id="profile-division">-</span>
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-500">
                                        Tanggal Masuk
                                    </p>

                                    <p class="font-bold text-slate-900 mt-0.5">
                                        <span id="profile-hire-date">-</span>
                                    </p>
                                </div>

                            </div>

                        </div>

                        {{-- Informasi Akun --}}
                        <div class="bg-white border border-slate-100 rounded-xl p-6 shadow-sm">

                            <h2
                                class="text-lg font-bold text-slate-900 border-b border-slate-200 pb-3 mb-4">
                                Informasi Akun
                            </h2>

                            <div class="text-sm">

                                <p class="text-xs text-slate-500">
                                    Tanggal Dibuat
                                </p>

                                <p class="font-bold text-slate-900 mt-0.5">
                                    <span id="profile-created-date">-</span>
                                </p>

                            </div>

                        </div>

                    </div>

                    {{-- KOLOM KANAN --}}
                    <div>

                        {{-- Informasi Pribadi --}}
                        <div
                            class="bg-white border border-slate-100 rounded-xl p-6 shadow-sm mb-6">

                            <h2
                                class="text-lg font-bold text-slate-900 border-b border-slate-200 pb-3 mb-4">
                                Informasi Pribadi
                            </h2>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">

                                <div>
                                    <p class="text-xs text-slate-500">
                                        Email
                                    </p>

                                    <p class="font-bold text-slate-900 mt-0.5">
                                        <span id="profile-email">-</span>
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-500">
                                        Alamat
                                    </p>

                                    <p class="font-bold text-slate-900 mt-0.5">
                                        <span id="profile-address">-</span>
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-500">
                                        Nomor Telepon
                                    </p>

                                    <p class="font-bold text-slate-900 mt-0.5">
                                        <span id="profile-phone">-</span>
                                    </p>
                                </div>

                            </div>

                        </div>

                        {{-- Keamanan Akun --}}
                        <div
                            class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">

                            <h3
                                class="text-lg font-bold text-slate-900 border-b border-slate-200 pb-3 mb-5">
                                Keamanan Akun
                            </h3>

                            <form id="kadiv-password-form" action="#" method="POST" class="space-y-4 max-w-xl">

                                @csrf

                                <div>
                                    <label
                                        class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">
                                        Password Saat Ini
                                    </label>

                                    <input
                                        type="password"
                                        name="current_password"
                                        required
                                        placeholder="********"
                                        class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-800 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                                    <div>
                                        <label
                                            class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">
                                            Password Baru
                                        </label>

                                        <input
                                            type="password"
                                            name="new_password"
                                            required
                                            placeholder="********"
                                            class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-800 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                                    </div>

                                    <div>
                                        <label
                                            class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">
                                            Ulangi Password Baru
                                        </label>

                                        <input
                                            type="password"
                                            name="confirm_password"
                                            required
                                            placeholder="********"
                                            class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-800 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                                    </div>

                                </div>

                                <div class="pt-2">

                                    <button
                                        type="submit"
                                        class="px-6 py-2 bg-[#044564] hover:bg-[#03344b] text-white text-sm font-medium rounded-lg shadow-sm transition">
                                        Perbarui Password
                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        </main>

    </div>

<script>
    async function loadKadivProfile() {
        const token = sessionStorage.getItem('staff_token');
        if (!token) {
            window.location.href = '/login';
            return;
        }

        try {
            const response = await fetch('/api/kadiv/profile', {
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Gagal memuat profil.');
            const profile = result.data;
            const values = {
                'profile-initial': (profile.nama || '-').charAt(0).toUpperCase(),
                'profile-name': profile.nama,
                'profile-header-email': profile.email,
                'profile-header-phone': profile.no_telepon,
                'profile-role': profile.jabatan,
                'profile-division': profile.divisi,
                'profile-hire-date': profile.tanggal_rekrut,
                'profile-created-date': profile.tanggal_dibuat,
                'profile-email': profile.email,
                'profile-address': profile.alamat,
                'profile-phone': profile.no_telepon
            };
            Object.entries(values).forEach(([id, value]) => {
                document.getElementById(id).textContent = value || '-';
            });
        } catch (error) {
            console.error('Gagal memuat profil Kadiv:', error);
            alert(error.message);
        }
    }

    document.getElementById('kadiv-password-form').addEventListener('submit', async function (event) {
        event.preventDefault();
        const token = sessionStorage.getItem('staff_token');
        const data = new FormData(this);
        if (data.get('new_password') !== data.get('confirm_password')) {
            alert('Konfirmasi password baru tidak sama.');
            return;
        }

        try {
            const response = await fetch('/api/kadiv/profile/password', {
                method: 'PUT',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    current_password: data.get('current_password'),
                    new_password: data.get('new_password')
                })
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Password gagal diperbarui.');
            this.reset();
            alert(result.message || 'Password berhasil diperbarui.');
        } catch (error) {
            alert(error.message);
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadKadivProfile);
    } else {
        loadKadivProfile();
    }
</script>
</body>
</html>