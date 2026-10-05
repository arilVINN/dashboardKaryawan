<div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-5">

    <div class="rounded-md drop-shadow-xs bg-white p-4 flex items-center justify-between">
        <div class="flex flex-col">
            <span class="text-sm font-bold text-slate-500">Tugas Berjalan</span>
            <span id="stat-berjalan" class="text-xl font-bold text-slate-800 mt-1">0/0 Tugas</span>
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
            <span id="stat-pending" class="text-xl font-bold text-slate-800 mt-1">0/0 Tugas</span>
        </div>
        <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"></circle>
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6"></path>
        </svg>
    </div>

    <div class="rounded-md drop-shadow-xs bg-white p-4 flex items-center justify-between">
        <div class="flex flex-col">
            <span class="text-sm font-bold text-slate-500">Tugas Telat</span>
            <span id="stat-telat" class="text-xl font-bold text-slate-800 mt-1">0/0 Tugas</span>
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
                    <span id="pct-berjalan" class="text-slate-800">0%</span>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-yellow-500"></span> Pending
                    </div>
                    <span id="pct-pending" class="text-slate-800">0%</span>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-red-500"></span> Telat
                    </div>
                    <span id="pct-telat" class="text-slate-800">0%</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script grafik & Ambil Data API -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', async function() {
        const token = localStorage.getItem('staff_token');
        if (!token) {
            // Jika token tidak ada, tendang kembali ke login
            window.location.href = '/login';
            return;
        }

        try {
            // Panggil API
            const response = await fetch('/api/staff/dashboard', {
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });
            const json = await response.json();
            const s = json.data.statistik;

            // 1. Tampilkan Angka Statistik Asli (Tanpa Tugas yang Selesai)
            const totalAktif = s.tugas_berjalan + s.tugas_baru + s.tugas_telat;
            
            document.getElementById('stat-berjalan').textContent = s.tugas_berjalan + '/' + totalAktif + ' Tugas';
            document.getElementById('stat-pending').textContent = s.tugas_baru + '/' + totalAktif + ' Tugas';
            document.getElementById('stat-telat').textContent = s.tugas_telat + '/' + totalAktif + ' Tugas';

            // 2. Hitung Persentase (Tanpa yang Selesai)
            const pctBerjalan = totalAktif > 0 ? Math.round((s.tugas_berjalan / totalAktif) * 100) : 0;
            const pctPending = totalAktif > 0 ? Math.round((s.tugas_baru / totalAktif) * 100) : 0;
            const pctTelat = totalAktif > 0 ? Math.round((s.tugas_telat / totalAktif) * 100) : 0;

            document.getElementById('pct-berjalan').textContent = pctBerjalan + '%';
            document.getElementById('pct-pending').textContent = pctPending + '%';
            document.getElementById('pct-telat').textContent = pctTelat + '%';

            // 3. Masukkan Data ke Grafik Lingkaran
            const ctx = document.getElementById('miniChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Ongoing', 'Pending', 'Telat'],
                        datasets: [{
                            data: [pctBerjalan, pctPending, pctTelat], // Data dimasukkan ke sini
                            backgroundColor: ['#22c55e', '#eab308', '#ef4444'],
                            borderWidth: 0,
                            cutout: '60%'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { enabled: false } },
                        layout: { padding: 0 }
                    }
                });
            }

        } catch (error) {
            console.error('Gagal memuat statistik:', error);
        }
    });
</script>