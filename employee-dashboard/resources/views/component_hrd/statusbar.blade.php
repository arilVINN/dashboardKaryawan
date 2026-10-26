@php
    $total = $total ?? ['karyawan' => 0, 'divisi' => 0, 'pesan' => 0];
    $tugasBuckets = $tugasBuckets ?? ['ongoing' => 0, 'pending' => 0, 'revisi' => 0];
    $bucketTotal = array_sum($tugasBuckets);
    $persen = [
        'ongoing' => $bucketTotal > 0 ? (int) round(($tugasBuckets['ongoing'] / $bucketTotal) * 100) : 0,
        'pending' => $bucketTotal > 0 ? (int) round(($tugasBuckets['pending'] / $bucketTotal) * 100) : 0,
        'revisi' => $bucketTotal > 0 ? (int) round(($tugasBuckets['revisi'] / $bucketTotal) * 100) : 0,
    ];
@endphp
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-5">

    <a href="{{ route('hrd.daftarKaryawan') }}"
        class="rounded-md drop-shadow-md bg-white p-4 flex items-center justify-between hover:shadow-lg transition block">
        <div class="flex flex-col">
            <span class="text-sm font-bold">Total Karyawan</span>
            <span class="text-xl font-bold mt-1" data-dashboard-metric="total-staff">{{ $totalStaff }}</span>
        </div>
        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z">
            </path>
        </svg>
    </a>

    <a href="{{ route('hrd.analitikKaryawan') }}"
        class="rounded-md drop-shadow-md bg-white p-4 flex items-center justify-between  hover:shadow-lg transition block">
        <div class="flex flex-col">
            <span class="text-sm font-bold">Total Divisi</span>
            <span class="text-xl font-bold mt-1" data-dashboard-metric="total-divisi">{{ $totalDivisi }}</span>
        </div>
        <svg class="w-8 h-8 text-violet-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21">
            </path>
        </svg>
    </a>

    <a href="{{ route('hrd.pesan') }}"
        class="rounded-md drop-shadow-md bg-white p-4 flex items-center justify-between hover:shadow-lg transition block">
        <div class="flex flex-col">
            <span class="text-sm font-bold">Total Pesan</span>
            <span class="text-xl font-bold mt-1" data-dashboard-metric="total-pesan">{{ $totalPesanPerusahaan }}</span>
        </div>
        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75">
            </path>
        </svg>
    </a>

    <a href="{{ route('hrd.turnoverKaryawan') }}"
        class="rounded-md drop-shadow-md bg-white p-4 flex flex-col justify-center hover:shadow-lg transition block">
        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2.5">Turnover Karyawan</span>

        <div class="flex items-center gap-3">
            <div class="relative w-14 h-14 shrink-0">
                <canvas id="miniChartTurnover"></canvas>
            </div>

            <div class="flex-1 space-y-1 text-[10px] font-bold text-slate-600">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-red-500"></span> Keluar
                    </div>
                    <span class="text-slate-800">{{ $totalKeluarTahunIni }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-green-500"></span> Aktif
                    </div>
                    <span class="text-slate-800">{{ $totalKaryawanAktif }}</span>
                </div>
                <div class="flex justify-between items-center pt-0.5">
                    <span class="text-[9px] text-slate-400 normal-case font-medium">Lihat detail →</span>
                </div>
            </div>
        </div>
    </a>

    <!-- Script grafik -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('miniChartTurnover');

            if (ctx) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Keluar', 'Aktif'],
                        datasets: [{
                            data: [{{ $totalKeluarTahunIni }}, {{ $totalKaryawanAktif }}],
                            backgroundColor: [
                                '#ef4444', // text-red-500 — keluar
                                '#22c55e', // text-green-500 — aktif
                            ],
                            borderWidth: 0,
                            cutout: '60%'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                enabled: false
                            }
                        },
                        layout: {
                            padding: 0
                        }
                    }
                });
            }
        });
    </script>
