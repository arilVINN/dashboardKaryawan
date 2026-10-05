<div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-5">

    <div class="rounded-md drop-shadow-xs bg-white p-4 flex items-center justify-between">
        <div class="flex flex-col">
            <span class="text-sm font-bold text-slate-500">Tugas Berjalan</span>
            <span class="text-xl font-bold text-slate-800 mt-1">2/10 Tugas</span>
        </div>
        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
            </path>
        </svg>
    </div>

    <div class="rounded-md drop-shadow-xs bg-white p-4 flex items-center justify-between">
        <div class="flex flex-col">
            <span class="text-sm font-bold text-slate-500">Tugas Pending</span>
            <span class="text-xl font-bold text-slate-800 mt-1">1/1 Tugas</span>
        </div>
        <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"></circle>
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6"></path>
        </svg>
    </div>

    <div class="rounded-md drop-shadow-xs bg-white p-4 flex items-center justify-between">
        <div class="flex flex-col">
            <span class="text-sm font-bold text-slate-500">Tugas Revisi</span>
            <span class="text-xl font-bold text-slate-800 mt-1">0/2 Tugas</span>
        </div>
        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"></circle>
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"></path>
        </svg>
    </div>

    <div class="rounded-md drop-shadow-xs bg-white p-4 flex flex-col justify-center">
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
