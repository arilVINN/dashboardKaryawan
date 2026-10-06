<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start mt-8">
    <section>
        <h2 class="text-2xl font-bold text-gray-800">Tugas</h2>
        <div class="mt-4 rounded-md bg-white shadow-md overflow-hidden">
            <div class="grid grid-cols-[1.2fr_1fr_1fr_1fr] items-center bg-slate-100 px-4 py-3">
                <span class="text-xs text-slate-600">Nama Tugas</span>
                <span class="text-xs text-slate-600">Status</span>
                <span class="text-xs text-slate-600">Tenggat</span>
                <span class="text-xs text-slate-600 text-center">Aksi</span>
            </div>
            <div id="kadiv-task-rows">
                <div class="h-52 flex items-center justify-center text-sm text-slate-500">Memuat data tugas...</div>
            </div>
        </div>
    </section>

    <section>
        <h2 class="text-2xl font-bold text-gray-800">Pesan</h2>
        <div class="mt-4 rounded-md bg-white shadow-md overflow-hidden">
            <div class="grid grid-cols-[1.2fr_1fr_1fr_1fr] items-center bg-slate-100 px-4 py-3">
                <span class="text-xs text-slate-600">Pengirim</span>
                <span class="text-xs text-slate-600">Topik/judul</span>
                <span class="text-xs text-slate-600">Tanggal</span>
                <span class="text-xs text-slate-600 text-center">Aksi</span>
            </div>
            <div id="kadiv-message-rows">
                <div class="h-52 flex items-center justify-center text-sm text-slate-500">Memuat data pesan...</div>
            </div>
        </div>
    </section>
</div>

<script>
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
                    taskRows.innerHTML = '<div class="h-52 flex items-center justify-center text-sm text-slate-500">Belum ada tugas.</div>';
                    return;
                }

                tasks.forEach((task) => {
                    const row = document.createElement('div');
                    row.className = 'grid grid-cols-[1.2fr_1fr_1fr_1fr] items-center gap-2 border-t border-slate-100 px-4 py-3 text-xs text-slate-700';

                    const title = document.createElement('span');
                    title.className = 'truncate font-medium';
                    title.textContent = task.judul_tugas || '-';
                    row.appendChild(title);

                    const status = document.createElement('span');
                    status.className = 'capitalize';
                    status.textContent = (task.status_efektif || task.status || '-').replaceAll('-', ' ');
                    row.appendChild(status);

                    const deadline = document.createElement('span');
                    deadline.textContent = task.deadline
                        ? new Date(task.deadline + 'T00:00:00').toLocaleDateString('id-ID')
                        : '-';
                    row.appendChild(deadline);

                    const actionCell = document.createElement('span');
                    actionCell.className = 'text-center';
                    const action = document.createElement('a');
                    action.href = '/kadiv/detailTugas/' + encodeURIComponent(task.id_tugas);
                    action.className = 'font-semibold text-cyan-700 hover:underline';
                    action.textContent = 'Lihat';
                    actionCell.appendChild(action);
                    row.appendChild(actionCell);
                    taskRows.appendChild(row);
                });
            } catch (error) {
                console.error('Gagal memuat tugas Kadiv:', error);
                taskRows.innerHTML = '<div class="h-52 flex items-center justify-center px-4 text-center text-sm text-red-600">Data tugas gagal dimuat. Silakan muat ulang halaman.</div>';
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
                    messageRows.innerHTML = '<div class="h-52 flex items-center justify-center text-sm text-slate-500">Belum ada pesan.</div>';
                    return;
                }

                messages.forEach((message) => {
                    const row = document.createElement('div');
                    row.className = 'grid grid-cols-[1.2fr_1fr_1fr_1fr] items-center gap-2 border-t border-slate-100 px-4 py-3 text-xs text-slate-700';

                    const sender = document.createElement('span');
                    sender.className = 'truncate';
                    sender.textContent = message.pengirim?.nama || message.pengirim?.username || '-';
                    row.appendChild(sender);

                    const subject = document.createElement('span');
                    subject.className = 'truncate font-medium';
                    subject.textContent = message.judul_pesan || message.deskripsi || '-';
                    row.appendChild(subject);

                    const date = document.createElement('span');
                    const sentAt = message.tanggal_pesan || message.created_at;
                    date.textContent = sentAt ? new Date(sentAt).toLocaleDateString('id-ID') : '-';
                    row.appendChild(date);

                    const actionCell = document.createElement('span');
                    actionCell.className = 'text-center';
                    const action = document.createElement('a');
                    action.href = '/kadiv/detailPesan/' + encodeURIComponent(message.id_pesan);
                    action.className = 'font-semibold text-cyan-700 hover:underline';
                    action.textContent = 'Lihat';
                    actionCell.appendChild(action);
                    row.appendChild(actionCell);
                    messageRows.appendChild(row);
                });
            } catch (error) {
                console.error('Gagal memuat pesan Kadiv:', error);
                messageRows.innerHTML = '<div class="h-52 flex items-center justify-center px-4 text-center text-sm text-red-600">Data pesan gagal dimuat. Silakan muat ulang halaman.</div>';
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
