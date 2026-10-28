<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analitik Karyawan - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">

    @include('component_hrd.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')
        @include('component.breadcrumbs', [
            'parentText' => 'Dashboard',
            'parentUrl' => route('hrd.dashboard'),
            'currentPage' => 'Analitik Karyawan',
        ])

        <main class="flex-1 overflow-y-auto p-8 pt-6 space-y-6">

            <div>
                <h1 class="text-2xl font-bold text-slate-800">Analitik Karyawan</h1>
                <p class="text-sm text-gray-500 mt-1">Ringkasan data karyawan di seluruh divisi</p>
            </div>

            {{-- KARTU RINGKASAN --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Total Divisi</p>
                            <p class="text-3xl font-bold text-slate-800 mt-1">{{ $totalDivisi }}</p>
                        </div>
                        <div class="w-12 h-12 shrink-0 rounded-lg bg-violet-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-violet-700">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Total Karyawan Aktif</p>
                            <p class="text-3xl font-bold text-slate-800 mt-1">{{ $totalKaryawanAktif }}</p>
                        </div>
                        <div class="w-12 h-12 shrink-0 rounded-lg bg-emerald-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-emerald-700">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Total Karyawan Nonaktif</p>
                            <p class="text-3xl font-bold text-slate-800 mt-1">{{ $totalKaryawanTidakAktif }}</p>
                        </div>
                        <div class="w-12 h-12 shrink-0 rounded-lg bg-red-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-red-600">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>


            {{-- CONTAINER UTAMA UNTUK DAFTAR TABEL PER DIVISI --}}
            <div class="space-y-6 mt-6">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 text-lg">Detail Analitik Karyawan per Divisi</h3>
                </div>

                @forelse ($divisiList as $d)
                    @php
                        $karyawanAktifDivisi = $d->karyawans; // Karyawan aktif di divisi ini
                        $totalAktif = $karyawanAktifDivisi->count();

                        // Hitung gender
                        $jmlLaki = $karyawanAktifDivisi->where('jenis_kelamin', 'Laki-laki')->count();
                        $jmlPerempuan = $karyawanAktifDivisi->where('jenis_kelamin', 'Perempuan')->count();

                        // Hitung status (Tetap / Kontrak)
                        $jmlTetap = $karyawanAktifDivisi->whereNull('tanggal_akhir_kontrak')->count();
                        $jmlKontrak = $karyawanAktifDivisi->whereNotNull('tanggal_akhir_kontrak')->count();
                    @endphp

                    {{-- KARTU & TABEL UNTUK MASING-MASING DIVISI --}}
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                        {{-- Header Divisi --}}
                        <div
                            class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                            <a href="{{ route('hrd.daftarKaryawan', ['divisi' => $d->id_divisi]) }}"
                                class="font-bold text-black hover:underline flex items-center gap-2">
                                {{ $d->nama_divisi }}
                            </a>
                            <span class="text-xs font-bold px-3 py-1 bg-blue-100 text-blue-800 rounded-full">
                                Total Karyawan Aktif: {{ $totalAktif }} orang
                            </span>
                        </div>

                        {{-- Tabel Rincian Anggota Divisi --}}
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr
                                        class="text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100 bg-slate-50/50">
                                        <th class="px-5 py-2.5">Nama Karyawan</th>
                                        <th class="px-5 py-2.5">Jabatan</th>
                                        <th class="px-5 py-2.5">Gender</th>
                                        <th class="px-5 py-2.5">Pendidikan</th>
                                        <th class="px-5 py-2.5">Job Level</th>
                                        <th class="px-5 py-2.5">Status Karyawan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                                    @forelse ($karyawanAktifDivisi as $k)
                                        <tr class="hover:bg-slate-50/75 transition">
                                            <td class="px-5 py-3 font-medium text-slate-900">{{ $k->nama }}</td>
                                            <td class="px-5 py-3 text-slate-600">{{ $k->jabatan ?? '-' }}</td>
                                            <td class="px-5 py-3 text-slate-600">{{ $k->jenis_kelamin ?? '-' }}</td>
                                            <td class="px-5 py-3 text-slate-600">{{ $k->pendidikan ?? '-' }}</td>
                                            <td class="px-5 py-3 text-slate-600">{{ $k->job_level ?? '-' }}</td>
                                            <td class="px-5 py-3">
                                                @if (empty($k->tanggal_akhir_kontrak))
                                                    <span
                                                        class="px-2 py-0.5 text-xs font-semibold bg-emerald-50 text-emerald-700 rounded">Tetap</span>
                                                @else
                                                    <span
                                                        class="px-2 py-0.5 text-xs font-semibold bg-amber-50 text-amber-700 rounded">Kontrak</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6"
                                                class="px-5 py-4 text-center text-slate-400 text-xs italic">
                                                Tidak ada karyawan aktif di divisi ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="bg-white border border-slate-200 rounded-xl p-6 text-center text-slate-400 text-sm">
                        Belum ada data divisi yang tersedia.
                    </div>
                @endforelse
            </div>

            <div>
                <h1 class="text-2xl font-bold text-slate-800">Persentasi Data Karyawan</h1>
            </div>

            {{-- BREAKDOWN --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @php
                    $breakdowns = [
                        'Gender' => $gender,
                        'Status Karyawan' => $statusKaryawan,
                        'Pendidikan' => $pendidikan,
                        'JobLevel' => $jobLevel,
                    ];
                @endphp

                @foreach ($breakdowns as $judul => $data)
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-5">
                        <h3 class="font-semibold text-slate-800 mb-3">{{ $judul }}</h3>
                        @if ($data->isEmpty())
                            <p class="text-sm text-slate-400">Belum ada data.</p>
                        @else
                            <div class="space-y-2">
                                @foreach ($data as $label => $jumlah)
                                    @php
                                        $pct =
                                            $totalKaryawanAktif > 0 ? round(($jumlah / $totalKaryawanAktif) * 100) : 0;
                                        $barColor = match ($judul) {
                                            'Gender' => match (strtolower((string) $label)) {
                                                'perempuan' => 'bg-pink-500',
                                                'laki-laki' => 'bg-blue-500',
                                                default => 'bg-[#0097B2]',
                                            },
                                            'Pendidikan' => 'bg-blue-500',
                                            'JobLevel' => match (strtolower((string) $label)) {
                                                'a' => 'bg-blue-500',
                                                'b' => 'bg-blue-500',
                                                default => 'bg-[#0097B2]',
                                            },
                                            'Status Karyawan' => match (strtolower((string) $label)) {
                                                'tetap' => 'bg-blue-500',
                                                'tidak tetap' => 'bg-red-500',
                                                default => 'bg-[#0097B2]',
                                            },
                                            default => 'bg-[#0097B2]',
                                        };
                                    @endphp
                                    <div>
                                        <div class="flex justify-between text-xs text-slate-600 mb-1">
                                            <span>{{ $label ?: 'Tidak diketahui' }}</span>
                                            <span class="font-semibold">{{ $jumlah }}
                                                ({{ $pct }}%)
                                            </span>
                                        </div>
                                        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full {{ $barColor }}"
                                                style="width: {{ $pct }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

        </main>
    </div>
</body>

</html>
