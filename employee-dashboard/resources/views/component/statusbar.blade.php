<div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-5">

    {{-- Tugas Berjalan --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 flex flex-col gap-2">
        <div class="flex items-start justify-between">
            <span class="text-xs font-bold uppercase tracking-widest text-slate-700">Tugas Berjalan</span>
            <span class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100">
                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
            </span>
        </div>
        <div>
            <p id="stat-berjalan" class="text-3md font-bold text-slate-900 leading-none">0/0 Tugas</p>
            <p class="text-xs text-slate-400 mt-1">Sedang dikerjakan</p>
        </div>
    </div>

    {{-- Tugas Pending --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 flex flex-col gap-2">
        <div class="flex items-start justify-between">
            <span class="text-xs font-bold uppercase tracking-widest text-slate-700">Tugas Pending</span>
            <span class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100">
                <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6"></path>
                </svg>
            </span>
        </div>
        <div>
            <p id="stat-pending" class="text-3md font-bold text-slate-900 leading-none">0/0 Tugas</p>
            <p class="text-xs text-slate-400 mt-1">Menunggu ACC</p>
        </div>
    </div>

    {{-- Tugas Telat --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 flex flex-col gap-2">
        <div class="flex items-start justify-between">
            <span class="text-xs font-bold uppercase tracking-widest text-slate-700">Tugas Telat</span>
            <span class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"></path>
                </svg>
            </span>
        </div>
        <div>
            <p id="stat-telat" class="text-3md font-bold text-slate-900 leading-none">0/0 Tugas</p>
            <p class="text-xs text-slate-400 mt-1">Melewati tenggat</p>
        </div>
    </div>

    {{-- Presentase --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 flex flex-col gap-2">
        <span class="text-xs font-bold uppercase tracking-widest text-slate-700">Presentase</span>
        <div class="flex items-center gap-3">
            <div class="relative w-12 h-12 shrink-0">
                <svg class="block h-12 w-12 -rotate-90" viewBox="0 0 56 56" role="img" aria-label="Grafik persentase tugas">
                    <circle cx="28" cy="28" r="22" fill="none" stroke="#e2e8f0" stroke-width="8"></circle>
                    <circle id="ring-berjalan" cx="28" cy="28" r="22" fill="none" stroke="#22c55e" stroke-width="8"></circle>
                    <circle id="ring-pending" cx="28" cy="28" r="22" fill="none" stroke="#eab308" stroke-width="8"></circle>
                    <circle id="ring-telat" cx="28" cy="28" r="22" fill="none" stroke="#ef4444" stroke-width="8"></circle>
                </svg>
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
<script>
    window.fetchStaffDashboardData = window.fetchStaffDashboardData || (async function fetchStaffDashboardData() {
        const token = localStorage.getItem('staff_token') || sessionStorage.getItem('staff_token');
        if (!token) return null;

        if (window.__staffDashboardRequest) {
            return window.__staffDashboardRequest;
        }

        window.__staffDashboardRequest = fetch('/api/staff/dashboard', {
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            }
        }).then(async response => {
            const json = await response.json();
            if (!response.ok) {
                throw new Error(json.message || 'Gagal memuat statistik dashboard staff.');
            }
            return json.data || {};
        }).finally(() => {
            window.__staffDashboardRequest = null;
        });

        return window.__staffDashboardRequest;
    });

    document.addEventListener('DOMContentLoaded', async function() {
        const token = localStorage.getItem('staff_token') || sessionStorage.getItem('staff_token');
        if (!token) {
            window.location.href = '/login';
            return;
        }

        try {
            const dashboardData = await window.fetchStaffDashboardData();
            if (!dashboardData) return;
            const s = dashboardData.statistik || {};

            // Total tugas tetap mencakup tugas yang sudah di-ACC.
            const totalTugas = Number(s.total_tugas) || 0;
            const tugasBerjalan = (Number(s.tugas_baru) || 0) + (Number(s.tugas_berjalan) || 0);
            const tugasPending = Number(s.tugas_menunggu_acc) || 0;
            const tugasTelat = Number(s.tugas_telat) || 0;
            
            document.getElementById('stat-berjalan').textContent = tugasBerjalan + '/' + totalTugas + ' Tugas';
            document.getElementById('stat-pending').textContent = tugasPending + '/' + totalTugas + ' Tugas';
            document.getElementById('stat-telat').textContent = tugasTelat + '/' + totalTugas + ' Tugas';

            // Persentase tiap status dihitung dari seluruh tugas, termasuk yang sudah di-ACC.
            const pctBerjalan = totalTugas > 0 ? Math.round((tugasBerjalan / totalTugas) * 100) : 0;
            const pctPending = totalTugas > 0 ? Math.round((tugasPending / totalTugas) * 100) : 0;
            const pctTelat = totalTugas > 0 ? Math.round((tugasTelat / totalTugas) * 100) : 0;

            document.getElementById('pct-berjalan').textContent = pctBerjalan + '%';
            document.getElementById('pct-pending').textContent = pctPending + '%';
            document.getElementById('pct-telat').textContent = pctTelat + '%';

            const circumference = 2 * Math.PI * 22;
            const totalStatusTampil = tugasBerjalan + tugasPending + tugasTelat;
            let offset = 0;
            [
                ['ring-berjalan', tugasBerjalan],
                ['ring-pending', tugasPending],
                ['ring-telat', tugasTelat]
            ].forEach(([id, count]) => {
                const ring = document.getElementById(id);
                const length = totalStatusTampil > 0 ? (count / totalStatusTampil) * circumference : 0;
                ring.setAttribute('stroke-dasharray', `${length} ${circumference - length}`);
                ring.setAttribute('stroke-dashoffset', `${-offset}`);
                offset += length;
            });

        } catch (error) {
            console.error('Gagal memuat statistik:', error);
        }
    });
</script>
