<div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-5">

    <a href="{{ url('/hrd/daftarKaryawan') }}" class="rounded-md drop-shadow-md bg-white p-4 flex items-center justify-between transition-transform duration-300 hover:scale-105">
        <div class="flex flex-col">
            <span class="text-sm font-bold text-slate-500">Total Karyawan</span>
            <span class="text-xl font-bold text-slate-800 mt-1" data-dashboard-metric="total-staff">{{ $totalStaff }}</span>
        </div>
        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z">
            </path>
        </svg>
    </a>

    <a href="{{url('/hrd/daftarDivisi')}}" class="rounded-md drop-shadow-md bg-white p-4 flex items-center justify-between duration-300 hover:scale-105">
        <div class="flex flex-col">
            <span class="text-sm font-bold text-slate-500">Total Divisi</span>
            <span class="text-xl font-bold text-slate-800 mt-1" data-dashboard-metric="total-divisi">{{ $totalDivisi }}</span>
        </div>
        <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"></circle>
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6"></path>
        </svg>
    </a>

    <a href="{{url('/hrd/daftarPesan')}}" class="rounded-md drop-shadow-md bg-white p-4 flex items-center justify-between transition-transform duration-300 hover:scale-105">
        <div class="flex flex-col">
            <span class="text-sm font-bold text-slate-500">Total Pesan</span>
            <span class="text-xl font-bold text-slate-800 mt-1" data-dashboard-metric="total-pesan">{{ $totalPesanPerusahaan }}</span>
        </div>
        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"></path>
        </svg>
    </a>

    <div class="rounded-md drop-shadow-md bg-white p-4 flex flex-col justify-center">
        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2.5">Presentase</span>

        <div class="flex items-center gap-3">
            <div class="relative w-14 h-14 shrink-0">
                <canvas id="miniChart"></canvas>
            </div>

            <div class="flex-1 space-y-1 text-[10px] font-bold text-slate-600">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-green-500"></span> Ongoing
                    </div>
                    <span class="text-slate-800">60%</span>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-yellow-500"></span> Pending
                    </div>
                    <span class="text-slate-800">25%</span>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-red-500"></span> Revisi
                    </div>
                    <span class="text-slate-800">15%</span>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Script grafik  -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('miniChart');

        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Ongoing', 'Pending', 'Revisi'],
                    datasets: [{
                        data: [60, 25, 15],
                        backgroundColor: [
                            '#22c55e', // text-green-500
                            '#eab308', // text-yellow-500
                            '#ef4444' // text-red-500
                        ],
                        borderWidth: 0,
                        cutout: '60%' // Semakin besar, donat semakin tipis
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
