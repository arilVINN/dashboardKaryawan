@php
    $pegawai = session('user_session', [
        'nama' => 'Samuel Sigalingging',
        'email' => 'staff@silindo.co.id',
        'telepon' => '081234567890',
        'tingkatan' => 'Staff',
        'divisi' => 'Content Writer',
        'tanggal_masuk' => '12 Januari 2025',
        'alamat' => 'Salatiga, Jawa Tengah',
        'tanggal_dibuat' => '10 Januari 2025',
    ]);
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pegawai - PT Silindo</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">

    @include('component.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        @include('component.topbar')


        <main class="flex-1 overflow-y-auto px-10 py-8">
            <div class="max-w-5xl space-y-8">

                <div class="flex items-center gap-6">
                    <div
                        class="w-28 h-28 rounded-full bg-slate-300 shrink-0 flex items-center justify-center text-4xl font-bold text-slate-500 uppercase">
                        <!-- Menampilkan inisial nama di tempat foto -->
                        {{ substr($pegawai['nama'], 0, 1) }}
                    </div>

                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                            {{ $pegawai['nama'] }}
                        </h1>
                        <p class="text-sm text-slate-600 mt-1">
                            {{ $pegawai['email'] }}
                        </p>
                        <p class="text-sm text-slate-600">
                            {{ $pegawai['telepon'] }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-2 gap-6 items-start">

                    <div class="space-y-6 grid-cols 2">
                        <div class="bg-white border border-slate-100 rounded-xl p-6 shadow-sm">
                            <h2
                                class="flex justify-between items-center text-lg font-bold text-slate-900 border-b border-slate-200 pb-3 mb-4">
                                <span>Detail Karyawan</span>
                            </h2>


                            <div class="space-y-4 text-sm">
                                <div>
                                    <p class="text-xs text-slate-500">Tingkatan</p>
                                    <p class="font-bold text-slate-900 mt-0.5">{{ $pegawai['tingkatan'] }}</p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-500">Divisi</p>
                                    <p class="font-bold text-slate-900 mt-0.5">{{ $pegawai['divisi'] }}</p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-500">Tanggal Masuk</p>
                                    <p class="font-bold text-slate-900 mt-0.5">{{ $pegawai['tanggal_masuk'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border border-slate-100 rounded-xl p-6 shadow-sm">
                            <h2 class="text-lg font-bold text-slate-900 border-b border-slate-200 pb-3 mb-4">
                                Informasi Akun
                            </h2>

                            <div class="text-sm">
                                <p class="text-xs text-slate-500">Tanggal Dibuat</p>
                                <p class="font-bold text-slate-900 mt-0.5">{{ $pegawai['tanggal_dibuat'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="bg-white border border-slate-100 rounded-xl p-6 shadow-sm mb-6">
                            <h2 class="flex items-center justify-between text-lg font-bold text-slate-900 border-b border-slate-200 pb-3 mb-4">
                                <span>Informasi Pribadi</span>
                                <button type="button" id="btn-edit-profile" aria-label="Edit profil" title="Edit profil"
                                    class="text-bold hover:text-cyan-600 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                    </svg>
                                </button>
                            </h2>

                            @if (session('success'))
                                <p class="mb-4 text-sm text-emerald-700">{{ session('success') }}</p>
                            @endif

                            <div id="profile-details" class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-xs text-slate-500">Nama</p>
                                    <p class="font-bold text-slate-900 mt-0.5">{{ $pegawai['nama'] }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500">Email</p>
                                    <p class="font-bold text-slate-900 mt-0.5">{{ $pegawai['email'] }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500">Alamat</p>
                                    <p class="font-bold text-slate-900 mt-0.5">{{ $pegawai['alamat'] }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500">Nomor Telepon</p>
                                    <p class="font-bold text-slate-900 mt-0.5">{{ $pegawai['telepon'] }}</p>
                                </div>
                            </div>

                            <form id="form-edit-profile" action="{{ route('profile.update') }}" method="POST"
                                class="{{ $errors->any() ? '' : 'hidden' }} space-y-4">
                                @csrf
                                <div>
                                    <label for="nama" class="block text-xs font-semibold text-slate-700 mb-1">Nama</label>
                                    <input id="nama" name="nama" type="text" value="{{ old('nama', $pegawai['nama']) }}" required
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500">
                                    @error('nama') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">Email</label>
                                    <input id="email" name="email" type="email" value="{{ old('email', $pegawai['email']) }}" required
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500">
                                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="telepon" class="block text-xs font-semibold text-slate-700 mb-1">Nomor Telepon</label>
                                    <input id="telepon" name="telepon" type="tel" value="{{ old('telepon', $pegawai['telepon']) }}" required
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500">
                                    @error('telepon') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="alamat" class="block text-xs font-semibold text-slate-700 mb-1">Alamat</label>
                                    <textarea id="alamat" name="alamat" rows="2" required
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-cyan-500">{{ old('alamat', $pegawai['alamat']) }}</textarea>
                                    @error('alamat') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div class="flex justify-end gap-3 pt-1">
                                    <button type="button" id="btn-cancel-edit-profile"
                                        class="px-4 py-2 border border-slate-300 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 transition">Batal</button>
                                    <button type="submit"
                                        class="px-4 py-2 bg-[#044564] hover:bg-[#03344b] text-white text-sm font-medium rounded-lg transition">Simpan</button>
                                </div>
                            </form>
                        </div>

                        <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                            <h3 class="text-lg font-bold text-slate-900 border-b border-slate-200 pb-3 mb-5">Keamanan
                                Akun</h3>

                            <form action="#" method="POST" class="space-y-4 max-w-xl">
                                @csrf
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">Password
                                        Saat Ini</label>
                                    <input type="password" placeholder="********"
                                        class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-800 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">Password
                                            Baru</label>
                                        <input type="password" placeholder="********"
                                            class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-800 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">Ulangi
                                            Password Baru</label>
                                        <input type="password" placeholder="********"
                                            class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-800 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                                    </div>
                                </div>

                                <div class="pt-2">
                                    <button type="submit"
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
        const editProfileButton = document.getElementById('btn-edit-profile');
        const cancelEditProfileButton = document.getElementById('btn-cancel-edit-profile');
        const profileDetails = document.getElementById('profile-details');
        const editProfileForm = document.getElementById('form-edit-profile');

        editProfileButton.addEventListener('click', () => {
            profileDetails.classList.add('hidden');
            editProfileForm.classList.remove('hidden');
            editProfileButton.classList.add('hidden');
        });

        cancelEditProfileButton.addEventListener('click', () => {
            editProfileForm.classList.add('hidden');
            profileDetails.classList.remove('hidden');
            editProfileButton.classList.remove('hidden');
        });
    </script>

</body>

</html>
