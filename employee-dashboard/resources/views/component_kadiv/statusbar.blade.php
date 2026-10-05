@php
    $total = ([
        'Tugas belum' => '30',
        'Acc' => '6',
        'Selesai' => '50',
        'Belum Selesai' => '50',
    ]);
@endphp


<div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-5">

    <a href="{{ url('#') }}" class="rounded-md drop-shadow-md bg-white p-4 flex items-center justify-between transition-transform duration-300 hover:scale-105">
        <div class="flex flex-col">
            <span class="text-sm font-bold text-slate-500">Total Tugas Selesai</span>
            <span class="text-xl font-bold text-slate-800 mt-1">{{ $total['Tugas belum'] }}</span>
        </div>
        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z">
            </path>
        </svg>
    </a>

    <a href="{{url('#')}}" class="rounded-md drop-shadow-md bg-white p-4 flex items-center justify-between duration-300 hover:scale-105">
        <div class="flex flex-col">
            <span class="text-sm font-bold text-slate-500">Total Tugas Acc</span>
            <span class="text-xl font-bold text-slate-800 mt-1">{{ $total['Acc'] }}</span>
        </div>
        <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"></circle>
            <path stroke-linecap="round" stroke-linejoin="round" 
            d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"></path>
        </svg>
    </a>

    <a href="{{url('#')}}" class="rounded-md drop-shadow-md bg-white p-4 flex items-center justify-between transition-transform duration-300 hover:scale-105">
        <div class="flex flex-col">
            <span class="text-sm font-bold text-slate-500">Total Tugas BelumSelesai</span>
            <span class="text-xl font-bold text-slate-800 mt-1">{{ $total['Belum Selesai'] }}</span>
        </div>
        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path>
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
                        <span class="w-2.5 h-2.5 rounded-sm bg-green-500"></span> Selesai
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
                        <span class="w-2.5 h-2.5 rounded-sm bg-red-500"></span> Belum Selesai
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
                    labels: ['Selesai', 'Pending', 'Belum Selesai'],
                    datasets: [{
                        data: [30, 30, 40,],
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
