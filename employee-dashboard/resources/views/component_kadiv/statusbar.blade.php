<div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-5">

    {{-- Tugas Yang Belum --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 flex flex-col gap-2">
        <div class="flex items-start justify-between">
            <span class="text-xs font-bold uppercase tracking-widest text-slate-700">Tugas Yang Belum</span>
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
            <p class="text-xs text-slate-400 mt-1">Belum dikerjakan</p>
        </div>
    </div>

    {{-- Tugas Belum Di-acc --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 flex flex-col gap-2">
        <div class="flex items-start justify-between">
            <span class="text-xs font-bold uppercase tracking-widest text-slate-700">Tugas Belum Di-acc</span>
            <span class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100">
                <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6"></path>
                </svg>
            </span>
        </div>
        <div>
            <p id="stat-pending" class="text-3md font-bold text-slate-900 leading-none">0/0 Tugas</p>
            <p class="text-xs text-slate-400 mt-1">Menunggu persetujuan</p>
        </div>
    </div>

    {{-- Tugas Selesai --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 flex flex-col gap-2">
        <div class="flex items-start justify-between">
            <span class="text-xs font-bold uppercase tracking-widest text-slate-700">Tugas Selesai</span>
            <span class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </span>
        </div>
        <div>
            <p id="stat-selesai" class="text-3md font-bold text-slate-900 leading-none">0/0 Tugas</p>
            <p class="text-xs text-slate-400 mt-1">Sudah disetujui</p>
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
                    <circle id="ring-selesai" cx="28" cy="28" r="22" fill="none" stroke="#3b82f6" stroke-width="8"></circle>
                </svg>
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

<script>
    async function loadKadivStatusbar() {
        const token = sessionStorage.getItem('staff_token');
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
                + (Number(metrics.tugas_telat) || 0)
                - (Number(metrics.tugas_selesai_terlambat) || 0);
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

            const circumference = 2 * Math.PI * 22;
            let offset = 0;
            [
                ['ring-berjalan', belum],
                ['ring-pending', pending],
                ['ring-selesai', selesai]
            ].forEach(([id, count]) => {
                const ring = document.getElementById(id);
                const length = total > 0 ? (count / total) * circumference : 0;
                ring.setAttribute('stroke-dasharray', `${length} ${circumference - length}`);
                ring.setAttribute('stroke-dashoffset', `${-offset}`);
                offset += length;
            });
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
