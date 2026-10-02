@php
    $pegawai = session('user_session', [
        'nama' => 'Nama Kadiv',
        'email' => 'kadiv@silindo.co.id',
        'telepon' => '081234567890',
        'tingkatan' => 'Kadiv',
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
    <title>Profil Kadiv - PT Silindo</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">

    {{-- Sidebar --}}
    @include('component.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        {{-- Topbar --}}
        @include('component.topbar')

        <main class="flex-1 overflow-y-auto px-10 py-8">

            <div class="max-w-5xl space-y-8">

                {{-- Header Profil --}}
                <div class="flex items-center gap-6">

                    <div
                        class="w-28 h-28 rounded-full bg-slate-300 shrink-0 flex items-center justify-center text-4xl font-bold text-slate-500 uppercase">
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
                                        {{ $pegawai['tingkatan'] }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-500">
                                        Divisi
                                    </p>

                                    <p class="font-bold text-slate-900 mt-0.5">
                                        {{ $pegawai['divisi'] }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-500">
                                        Tanggal Masuk
                                    </p>

                                    <p class="font-bold text-slate-900 mt-0.5">
                                        {{ $pegawai['tanggal_masuk'] }}
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
                                    {{ $pegawai['tanggal_dibuat'] }}
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
                                        {{ $pegawai['email'] }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-500">
                                        Alamat
                                    </p>

                                    <p class="font-bold text-slate-900 mt-0.5">
                                        {{ $pegawai['alamat'] }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-500">
                                        Nomor Telepon
                                    </p>

                                    <p class="font-bold text-slate-900 mt-0.5">
                                        {{ $pegawai['telepon'] }}
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

                            <form action="#" method="POST" class="space-y-4 max-w-xl">

                                @csrf

                                <div>
                                    <label
                                        class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">
                                        Password Saat Ini
                                    </label>

                                    <input
                                        type="password"
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

</body>

</html>