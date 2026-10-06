<div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-5">

    <div class="rounded-md drop-shadow-md bg-white p-4 flex items-center justify-between transition-transform duration-300 hover:scale-105">
        <div class="flex flex-col">
            <span class="text-sm font-bold text-slate-500">Tugas Yang Belum</span>
            <span id="stat-berjalan" class="text-xl font-bold text-slate-800 mt-1">0/0 Tugas</span>
        </div>

        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
            </path>
        </svg>
    </div>

    <div class="rounded-md drop-shadow-md bg-white p-4 flex items-center justify-between transition-transform duration-300 hover:scale-105">
        <div class="flex flex-col">
            <span class="text-sm font-bold text-slate-500">Tugas Belum Di-acc</span>
            <span id="stat-pending" class="text-xl font-bold text-slate-800 mt-1">0/0 Tugas</span>
        </div>
        <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"></circle>
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6"></path>
        </svg>
    </div>

    <div class="rounded-md drop-shadow-md bg-white p-4 flex items-center justify-between transition-transform duration-300 hover:scale-105">
        <div class="flex flex-col">
            <span class="text-sm font-bold text-slate-500">Tugas Selesai</span>
            <span id="stat-selesai" class="text-xl font-bold text-slate-800 mt-1">0/0 Tugas</span>
        </div>
        <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
    </div>

    <div class="rounded-md drop-shadow-md bg-white p-4 flex flex-col justify-center">
        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2.5">Presentase</span>
        <div class="flex items-center gap-3">
            <div class="relative w-14 h-14 shrink-0">
                <canvas id="miniChart" class="block h-14 w-14"></canvas>
            </div>
            <div class="flex-1 space-y-1 text-[10px] font-bold text-slate-600">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-green-500"></span> Yang Belum
                    </div>
                    <span id="pct-berjalan" class="text-slate-800">0%</span>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-yellow-500"></span> Belum Di-acc
                    </div>
                    <span id="pct-pending" class="text-slate-800">0%</span>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-blue-500"></span> Selesai
                    </div>
                    <span id="pct-selesai" class="text-slate-800">0%</span>
                </div>
            </div>
        </div>
    </div>

</div>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    async function loadKadivStatusbar() {
        const token = localStorage.getItem('staff_token');
        if (!token) {
            window.location.href = '/login';
            return;
        }

        try {
            const response = await fetch('/api/kadiv/dashboard', {
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Gagal mengambil statistik tugas Kadiv: ' + response.status);
            }

            const json = await response.json();
            const metrics = json.data.metrics;
            const belum = (Number(metrics.tugas_baru) || 0)
                + (Number(metrics.tugas_berjalan) || 0)
                + (Number(metrics.tugas_telat) || 0);
            const pending = Number(metrics.tugas_menunggu_di_acc) || 0;
            const selesai = Number(metrics.tugas_sudah_di_acc) || 0;
            const total = belum + pending + selesai;
            const percentage = (count) => total > 0
                ? Math.round((count / total) * 100)
                : 0;

            document.getElementById('stat-berjalan').textContent = belum + '/' + total + ' Tugas';
            document.getElementById('stat-pending').textContent = pending + '/' + total + ' Tugas';
            document.getElementById('stat-selesai').textContent = selesai + '/' + total + ' Tugas';
            document.getElementById('pct-berjalan').textContent = percentage(belum) + '%';
            document.getElementById('pct-pending').textContent = percentage(pending) + '%';
            document.getElementById('pct-selesai').textContent = percentage(selesai) + '%';

            const chartCanvas = document.getElementById('miniChart');
            if (chartCanvas) {
                if (window.kadivMiniChart instanceof Chart) {
                    window.kadivMiniChart.destroy();
                }

                window.kadivMiniChart = new Chart(chartCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Yang Belum', 'Belum Di-acc', 'Selesai'],
                        datasets: [{
                            data: [belum, pending, selesai],
                            backgroundColor: [
                                '#22c55e',
                                '#eab308',
                                '#3b82f6'
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
        } catch (error) {
            console.error('Gagal memuat statistik Kadiv:', error);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadKadivStatusbar);
    } else {
        loadKadivStatusbar();
    }
</script>
