<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Karyawan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">
    @include('component_hrd.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')
        @include('component.breadcrumbs', ['parentText' => 'Karyawan', 'parentUrl' => url('/hrd/daftarKaryawan'), 'currentPage' => 'Detail Karyawan'])

        <main class="flex-1 overflow-y-auto p-8 pt-4 space-y-6">
            <h1 class="text-2xl font-bold text-slate-900">Profil Karyawan</h1>

            <section class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-5">
                    @php
                        $fields = [
                            'ID Karyawan' => $karyawan->id_karyawan,
                            'Nama' => $karyawan->nama,
                            'Divisi' => $karyawan->divisi->nama_divisi ?? '-',
                            'Jabatan' => $karyawan->jabatan,
                            'Jenis Kelamin' => $karyawan->jenis_kelamin,
                            'Tanggal Lahir' => $karyawan->tanggal_lahir ?? '-',
                            'No. Telepon' => $karyawan->no_telepon ?? '-',
                            'Email' => $karyawan->email ?? '-',
                            'Username' => $karyawan->user->username ?? '-',
                            'Role' => $karyawan->user->role->nama_role ?? '-',
                        ];
                    @endphp

                    @foreach ($fields as $label => $value)
                        <div>
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wide">{{ $label }}</p>
                            <p class="text-sm text-slate-800 mt-1">{{ $value }}</p>
                        </div>
                    @endforeach

                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wide">Status Akun</p>
                        <p class="mt-1">
                            @if ($karyawan->user)
                                <span class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">Aktif</span>
                            @else
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-500 rounded-full text-xs font-bold">Belum ada akun</span>
                            @endif
                        </p>
                    </div>
                </div>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-bold text-slate-800">Daftar Tugas</h2>

                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-600">
                            <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                                <tr>
                                    <th class="px-6 py-4 font-medium whitespace-nowrap">Judul Tugas</th>
                                    <th class="px-6 py-4 font-medium whitespace-nowrap">Status</th>
                                    <th class="px-6 py-4 font-medium whitespace-nowrap">Progress</th>
                                    <th class="px-6 py-4 font-medium whitespace-nowrap">Deadline</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse ($karyawan->tugas as $tugas)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-6 py-4 font-medium text-slate-900">{{ $tugas->judul_tugas }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $tugas->status_efektif }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $tugas->progress }}%</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $tugas->deadline ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-10 text-center text-slate-400">
                                            Belum ada tugas untuk karyawan ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>

</html>
