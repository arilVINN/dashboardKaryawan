<div class="mt-6 grid grid-cols-1 items-start gap-6 min-[900px]:grid-cols-[1.15fr_1fr]">
    <section class="overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-[#EEF2F5] px-4 py-3">
            <h2 class="text-sm font-medium text-[#172B36]">Tugas</h2>
            <a href="/kadiv/tugas" class="text-xs text-[#146C94] hover:underline">Lihat semua &rsaquo;</a>
        </div>

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

    <section class="overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-[#EEF2F5] px-4 py-3">
            <h2 class="text-sm font-medium text-[#172B36]">Pesan</h2>
            <a href="/kadiv/pesan" class="text-xs text-[#146C94] hover:underline">Lihat semua &rsaquo;</a>
        </div>

        <div class="grid h-8 grid-cols-[minmax(0,1fr)_minmax(0,1.4fr)_minmax(0,0.7fr)_24px] items-center gap-3 bg-[#EAF4F8] px-4 text-xs font-medium text-[#334155]">
            <span>Dari</span>
            <span>Topik</span>
            <span>Tanggal</span>
            <span></span>
        </div>

        <div id="kadiv-message-rows" class="divide-y divide-[#EEF2F5]">
            <div class="flex min-h-11 items-center px-4 text-[13px] text-[#4B5563]">Memuat data pesan...</div>
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
            fallback.className = 'inline-flex w-fit items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] text-[#172B36]';
            fallback.textContent = key || '-';
            return fallback;
        }

        const badge = document.createElement('span');
        badge.className = 'inline-flex w-fit items-center gap-1.5 rounded-full border px-2 py-0.5 text-[11px] text-[#172B36]';
        badge.style.backgroundColor = palette.bg;
        badge.style.borderColor = palette.border;
        badge.title = palette.label;

        const dot = document.createElement('span');
        dot.className = 'h-1.5 w-1.5 shrink-0 rounded-full';
        dot.style.backgroundColor = palette.dot;

        badge.append(dot, document.createTextNode(shortLabel ? palette.short : palette.label));

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

    function makeKadivChevron() {
        const chevron = document.createElement('span');
        chevron.className = 'flex justify-end';
        chevron.innerHTML = '<svg class="h-4 w-4 text-[#94A3B8]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>';

        return chevron;
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

    function formatKadivMessageDate(value) {
        if (!value) return '-';

        const date = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) return value;

        const now = new Date();
        const yesterday = new Date(now);
        yesterday.setDate(now.getDate() - 1);

        const sameDay = (first, second) => first.getFullYear() === second.getFullYear()
            && first.getMonth() === second.getMonth()
            && first.getDate() === second.getDate();

        if (sameDay(date, now)) return 'Hari ini';
        if (sameDay(date, yesterday)) return 'Kemarin';

        const options = { day: 'numeric', month: 'short' };
        if (date.getFullYear() !== now.getFullYear()) {
            options.year = 'numeric';
        }

        return date.toLocaleDateString('id-ID', options);
    }

    async function loadKadivTaskAndMessageTables() {
        const taskRows = document.getElementById('kadiv-task-rows');
        const messageRows = document.getElementById('kadiv-message-rows');
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
                    row.classList.add('grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)_minmax(0,0.7fr)_24px]');

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

                    row.appendChild(makeKadivChevron());
                    taskRows.appendChild(row);
                });
            } catch (error) {
                console.error('Gagal memuat tugas Kadiv:', error);
                taskRows.innerHTML = '<div class="flex min-h-11 items-center px-4 text-[13px] text-red-600">Data tugas gagal dimuat. Silakan muat ulang halaman.</div>';
            }
        };

        const loadMessages = async () => {
            try {
                const response = await fetch('/api/kadiv/pesan?per_page=5', { headers });
                const result = await response.json();
                if (!response.ok) {
                    throw new Error(result.message || 'Gagal mengambil daftar pesan.');
                }

                const messages = Array.isArray(result.data) ? result.data : [];
                messageRows.replaceChildren();
                if (messages.length === 0) {
                    messageRows.innerHTML = '<div class="flex min-h-11 items-center px-4 text-[13px] text-[#4B5563]">Belum ada pesan.</div>';
                    return;
                }

                messages.forEach((message) => {
                    const row = makeKadivClickableRow('/kadiv/detailPesan/' + encodeURIComponent(message.id_pesan));
                    row.classList.add('grid-cols-[minmax(0,1fr)_minmax(0,1.4fr)_minmax(0,0.7fr)_24px]');

                    const sender = document.createElement('span');
                    sender.className = 'text-[13px] text-[#475569]';
                    sender.textContent = message.pengirim?.nama || message.pengirim?.username || '-';
                    row.appendChild(sender);

                    const subject = document.createElement('span');
                    subject.className = 'truncate text-[13px] font-medium text-[#172B36]';
                    subject.textContent = message.judul_pesan || message.deskripsi || '-';
                    subject.title = subject.textContent;
                    row.appendChild(subject);

                    const date = document.createElement('span');
                    date.className = 'text-[13px] text-[#4B5563]';
                    date.textContent = formatKadivMessageDate(message.tanggal_pesan || message.created_at);
                    row.appendChild(date);

                    row.appendChild(makeKadivChevron());
                    messageRows.appendChild(row);
                });
            } catch (error) {
                console.error('Gagal memuat pesan Kadiv:', error);
                messageRows.innerHTML = '<div class="flex min-h-11 items-center px-4 text-[13px] text-red-600">Data pesan gagal dimuat. Silakan muat ulang halaman.</div>';
            }
        };

        await Promise.all([loadTasks(), loadMessages()]);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadKadivTaskAndMessageTables);
    } else {
        loadKadivTaskAndMessageTables();
    }
</script>
