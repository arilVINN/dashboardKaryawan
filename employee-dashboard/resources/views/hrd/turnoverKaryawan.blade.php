{{-- resources/views/hrd/turnoverKaryawan.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turnover Karyawan - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">

    @include('component_hrd.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')
        @include('component.breadcrumbs', [
            'parentText' => 'Dashboard',
            'parentUrl' => route('hrd.dashboard'),
            'currentPage' => 'Turnover Karyawan',
        ])

        <main class="flex-1 overflow-y-auto p-8 pt-6 space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Turnover Karyawan</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $totalKeluarTahunIni }} karyawan keluar tahun ini</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-slate-800 mb-4">Karyawan Keluar per Bulan</h3>
                <canvas id="chartTurnoverDetail" height="90"></canvas>
            </div>

            <h1 class="text-2xl font-bold text-slate-800">Riwaya Karyawan Keluar</h1>

            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">

                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="px-5 py-3 font-medium">Nama</th>
                            <th class="px-5 py-3 font-medium">Jabatan</th>
                            <th class="px-5 py-3 font-medium">Divisi</th>
                            <th class="px-5 py-3 font-medium text-center">Tanggal Keluar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($daftarKeluar as $k)
                            <tr>
                                <td class="px-5 py-3">{{ $k->nama }}</td>
                                <td class="px-5 py-3">{{ $k->jabatan }}</td>
                                <td class="px-5 py-3">{{ $k->divisi?->nama_divisi ?? '-' }}</td>
                                <td class="px-5 py-3 text-center">{{ $k->tanggal_keluar->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-slate-400">Belum ada karyawan
                                    keluar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        new Chart(document.getElementById('chartTurnoverDetail'), {
            type: 'line',
            data: {
                labels: @json($turnoverLabel),
                datasets: [{
                    label: 'Karyawan keluar',
                    data: @json($turnoverData),
                    backgroundColor: '#ef3333',
                    borderRadius: 6,
                    maxBarThickness: 35,
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>
