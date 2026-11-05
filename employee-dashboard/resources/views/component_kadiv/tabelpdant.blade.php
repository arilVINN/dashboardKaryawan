<div class="mt-6">
    <div class="mb-2 flex items-center justify-between px-1">
        <h2 class="text-lg font-semibold text-[#172B36]">Tugas</h2>
        <a href="/kadiv/tugas" class="text-xs text-[#146C94] hover:underline">Lihat semua &rsaquo;</a>
    </div>

    <section class="overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">
        <div class="grid h-8 grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)_minmax(0,0.7fr)_24px] items-center gap-3 bg-[#EAF4F8] px-4 text-xs font-medium text-[#334155]">
            <span>Nama tugas</span>
            <span>Status</span>
            <span>Tenggat</span>
            <span></span>
        </div>

        <div id="kadiv-task-rows" class="divide-y divide-[#EEF2F5]">
            <div class="flex min-h-11 items-center px-4 text-[13px] text-[#4B5563]">Memuat data tugas...</div>
        </div>
    </section>
</div>

<script>
    const KADIV_STATUS_PALETTE = @json(\App\Support\StatusPalette::all());

    function kadivStatusKey(status) {
        const value = String(status || '').toLowerCase().trim();

        if (value === 'baru') return 'baru';
        if (value === 'berjalan') return 'berjalan';
        if (value.includes('menunggu')) return 'menunggu';
        if (value.includes('sudah') || value.includes('disetujui') || value.includes('selesai')) return 'disetujui';
        if (value.includes('terlambat') || value.includes('telat')) return 'terlambat';

        return null;
    }

    function kadivStatusBadge(key, shortLabel = false) {
        const palette = KADIV_STATUS_PALETTE[key];

        if (!palette) {
            const fallback = document.createElement('span');
            fallback.className = 'inline-flex w-fit items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-xs font-semibold text-slate-700';
            fallback.textContent = key || '-';
            return fallback;
        }

        const badge = document.createElement('span');
        badge.className = 'inline-flex w-fit items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold';
        badge.style.backgroundColor = palette.bg;
        badge.style.borderColor = palette.border;
        badge.style.color = palette.text;
        badge.title = palette.label;
        badge.textContent = shortLabel ? palette.short : palette.label;

        return badge;
    }

    function makeKadivClickableRow(url) {
        const row = document.createElement('div');
        row.className = 'grid min-h-11 cursor-pointer items-center gap-3 px-4 py-2 text-[13px] text-[#475569] hover:bg-[#F5FAFC] focus:bg-[#F5FAFC] focus:outline-none';
        row.tabIndex = 0;
        row.setAttribute('role', 'link');
        row.addEventListener('click', () => {
            window.location.href = url;
        });
        row.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                window.location.href = url;
            }
        });

        return row;
    }

    function makeKadivDetail(url) {
        const detail = document.createElement('a');
        detail.href = url;
        detail.className = 'inline-flex justify-self-end rounded px-1 py-1 text-sm font-semibold text-[#146C94] transition hover:text-[#0B4A66] hover:underline focus:outline-none focus:ring-2 focus:ring-[#146C94]/30';
        detail.textContent = 'Detail';
        detail.addEventListener('click', (event) => event.stopPropagation());

        return detail;
    }

    function formatKadivDeadline(value) {
        if (!value) return '-';

        const date = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) return '-';

        const options = { day: 'numeric', month: 'short' };
        if (date.getFullYear() !== new Date().getFullYear()) {
            options.year = 'numeric';
        }

        return date.toLocaleDateString('id-ID', options);
    }

    async function loadKadivTaskTable() {
        const taskRows = document.getElementById('kadiv-task-rows');
        const token = sessionStorage.getItem('staff_token');
        if (!token) {
            window.location.href = '/login';
            return;
        }

        const headers = {
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/json'
        };

        const loadTasks = async () => {
            try {
                const response = await fetch('/api/kadiv/tugas', { headers });
                const result = await response.json();
                if (!response.ok) {
                    throw new Error(result.message || 'Gagal mengambil daftar tugas.');
                }

                const tasks = Array.isArray(result.data) ? result.data.slice(0, 5) : [];
                taskRows.replaceChildren();
                if (tasks.length === 0) {
                    taskRows.innerHTML = '<div class="flex min-h-11 items-center px-4 text-[13px] text-[#4B5563]">Belum ada tugas.</div>';
                    return;
                }

                tasks.forEach((task) => {
                    const row = makeKadivClickableRow('/kadiv/detailTugas/' + encodeURIComponent(task.id_tugas));
                    row.classList.add('grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)_minmax(0,0.7fr)_64px]');

                    const title = document.createElement('span');
                    title.className = 'truncate text-[13px] font-medium text-[#172B36]';
                    title.textContent = task.judul_tugas || '-';
                    title.title = task.judul_tugas || '-';
                    row.appendChild(title);

                    const statusCell = document.createElement('span');
                    statusCell.className = 'flex flex-wrap items-center gap-1';
                    const statusKey = kadivStatusKey(task.status_efektif || task.status);
                    if (statusKey) {
                        statusCell.appendChild(kadivStatusBadge(statusKey, true));
                    }
                    if (task.is_terlambat && statusKey !== 'terlambat') {
                        statusCell.appendChild(kadivStatusBadge('terlambat', true));
                    }
                    row.appendChild(statusCell);

                    const deadline = document.createElement('span');
                    deadline.className = 'text-[13px] text-[#4B5563]';
                    deadline.textContent = formatKadivDeadline(task.deadline);
                    row.appendChild(deadline);

                    row.appendChild(makeKadivDetail('/kadiv/detailTugas/' + encodeURIComponent(task.id_tugas)));
                    taskRows.appendChild(row);
                });
            } catch (error) {
                console.error('Gagal memuat tugas Kadiv:', error);
                taskRows.innerHTML = '<div class="flex min-h-11 items-center px-4 text-[13px] text-red-600">Data tugas gagal dimuat. Silakan muat ulang halaman.</div>';
            }
        };

        await loadTasks();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadKadivTaskTable);
    } else {
        loadKadivTaskTable();
    }
</script>
