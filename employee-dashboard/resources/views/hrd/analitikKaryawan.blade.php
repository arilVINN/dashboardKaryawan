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
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
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
                        <div class="w-12 h-12 shrink-0 rounded-lg bg-blue-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-blue-700">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>


            {{-- LIST KARYAWAN PER DIVISI --}}
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h3 class="font-semibold text-slate-800">Karyawan per Divisi</h3>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($divisiList as $d)
                        <a href="{{ route('hrd.daftarKaryawan', ['divisi' => $d->id_divisi]) }}"
                            class="flex items-center justify-between px-5 py-3 hover:bg-blue-100 transition">
                            <span class="text-sm text-slate-700">{{ $d->nama_divisi }}</span>
                            <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">
                                {{ $d->karyawans_count }} orang
                            </span>
                        </a>
                    @empty
                        <p class="px-5 py-6 text-center text-slate-400 text-sm">Belum ada divisi.</p>
                    @endforelse
                </div>
            </div>

            <div>
                <h1 class="text-2xl font-bold text-slate-800">Persentasi Data Karyawan</h1>
            </div>

            {{-- BREAKDOWN --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @php
                    $breakdowns = [
                        'Gender' => $gender,
                        'Jenjang Pendidikan' => $pendidikan,
                        'Job Level' => $jobLevel,
                        'Status Karyawan' => $statusKaryawan,
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
                                            'Jenjang Pendidikan' => 'bg-blue-500',
                                            'Job Level' => match (strtolower((string) $label)) {
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
