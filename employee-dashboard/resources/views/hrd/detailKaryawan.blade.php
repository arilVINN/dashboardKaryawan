<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Karyawan - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">
    @include('component_hrd.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')
        @include('component.breadcrumbs', ['parentText' => 'Karyawan', 'parentUrl' => url('/hrd/daftarKaryawan'), 'currentPage' => 'Detail Karyawan'])

        <main class="flex-1 overflow-y-auto p-8 pt-6 bg-slate-50 space-y-6">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 flex items-center gap-5">
                <div class="w-20 h-20 rounded-full bg-[#0097B2] text-white text-3xl font-bold flex items-center justify-center shrink-0">
                    {{ strtoupper(substr($karyawan->nama, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <h1 class="text-2xl font-bold text-slate-900 truncate">{{ $karyawan->nama }}</h1>
                    <p class="text-sm text-slate-500">{{ $karyawan->jabatan }} · {{ $karyawan->id_karyawan }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <span class="px-2.5 py-1 bg-cyan-100 text-cyan-700 rounded-full text-xs font-bold">
                            {{ $karyawan->divisi->nama_divisi ?? 'Tanpa divisi' }}
                        </span>
                        <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-bold">
                            {{ $karyawan->user?->role?->nama_role ?? 'Belum ada akun' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-bold text-slate-800 mb-4">Informasi Pribadi</h2>
                    <dl class="space-y-3 text-sm">
                        @php
                            $info = [
                                'Jenis Kelamin' => $karyawan->jenis_kelamin,
                                'Tanggal Lahir' => $karyawan->tanggal_lahir ? \Carbon\Carbon::parse($karyawan->tanggal_lahir)->format('d M Y') : null,
                                'No. Telepon' => $karyawan->no_telepon,
                                'Email' => $karyawan->email,
                                'Alamat' => $karyawan->alamat,
                                'Tanggal Rekrut' => $karyawan->tanggal_rekrut ? \Carbon\Carbon::parse($karyawan->tanggal_rekrut)->format('d M Y') : null,
                            ];
                        @endphp
                        @foreach ($info as $label => $nilai)
                            <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                                <dt class="text-slate-500">{{ $label }}</dt>
                                <dd class="text-slate-900 font-medium text-right">{{ $nilai ?: '-' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <div class="space-y-6">
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-bold text-slate-800 mb-4">Akun</h2>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between border-b border-slate-100 pb-2">
                                <dt class="text-slate-500">Username</dt>
                                <dd class="text-slate-900 font-medium">{{ $karyawan->user?->username ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500">Login terakhir</dt>
                                <dd class="text-slate-900 font-medium">
                                    {{ $karyawan->user?->last_login_at?->format('d M Y, H.i') ?? 'Belum pernah' }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-5">
                            <p class="text-sm text-slate-500">Total Tugas</p>
                            <p class="text-3xl font-bold text-slate-800">{{ $totalTugas }}</p>
                        </div>
                        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-5">
                            <p class="text-sm text-slate-500">Tugas Selesai</p>
                            <p class="text-3xl font-bold text-green-600">{{ $tugasSelesai }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h2 class="text-lg font-bold text-slate-800">Tugas {{ $karyawan->nama }}</h2>
                </div>
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3 font-medium">Tugas</th>
                            <th class="px-6 py-3 font-medium text-center">Tanggal</th>
                            <th class="px-6 py-3 font-medium text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($karyawan->tugas->take(5) as $t)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-3 font-semibold text-slate-800">{{ $t->judul_tugas }}</td>
                                <td class="px-6 py-3 text-center">{{ $t->tanggal_tugas }}</td>
                                <td class="px-6 py-3 text-center">{{ $t->status }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-10 text-center text-slate-400">Belum ada tugas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
