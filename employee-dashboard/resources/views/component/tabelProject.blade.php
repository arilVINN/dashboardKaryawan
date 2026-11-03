<div class="flex flex-col gap-3 w-full mt-6">
    <h2 class="text-xl font-bold text-slate-800">Project Saya</h2>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-4 font-medium whitespace-nowrap">Nama Project</th>
                        <th class="px-6 py-4 font-medium">Deskripsi</th>
                        <th class="px-6 py-4 font-medium text-center whitespace-nowrap">Status</th>
                        <th class="px-6 py-4 font-medium text-center whitespace-nowrap">Tenggat</th>
                        <th class="px-6 py-4 font-medium text-center whitespace-nowrap">Progress</th>
                        <th class="px-6 py-4 font-medium text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="projectList" class="divide-y divide-slate-200">
                    <tr>
                        <td colspan="6" class="text-center py-6 text-slate-400">Memuat data project...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    let staffProjectRequestId = 0;
    let staffProjectAbortController = null;

    async function loadStaffProjects() {
        const token = localStorage.getItem('staff_token') || sessionStorage.getItem('staff_token');
        const container = document.getElementById('projectList');
        if (!container) return;
        if (!token) {
            container.innerHTML = '<tr><td colspan="6" class="text-center py-6 text-red-500">Sesi login tidak ditemukan. Silakan login kembali.</td></tr>';
            return;
        }

        if (staffProjectAbortController) {
            staffProjectAbortController.abort();
        }

        staffProjectAbortController = new AbortController();
        const requestId = ++staffProjectRequestId;

        try {
            const overview = await (window.fetchStaffOverview ? window.fetchStaffOverview() : fetch('/api/staff/overview', {
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
                signal: staffProjectAbortController.signal,
            }).then(async res => {
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Gagal memuat project.');
                return json.data || { tasks: [], projects: [] };
            }));

            if (requestId !== staffProjectRequestId) {
                return;
            }

            if (!overview || !Array.isArray(overview.projects)) {
                throw new Error('Format data project tidak valid.');
            }

            renderStaffProjects(overview.projects);
        } catch (e) {
            if (e.name === 'AbortError') {
                return;
            }
            console.error(e);
            if (requestId === staffProjectRequestId) {
                container.innerHTML = `<tr><td colspan="6" class="text-center py-6 text-red-500">${escapeProjectHtml(e.message || 'Gagal memuat project.')}</td></tr>`;
            }
        }
    }

    function renderStaffProjects(projects) {
        const container = document.getElementById('projectList');
        if (projects.length === 0) {
            container.innerHTML = '<tr><td colspan="6" class="text-center py-10 text-slate-400">Belum ada project.</td></tr>';
            return;
        }

        container.innerHTML = projects.map(project => {
            const tasks = project.tasks || [];
            const total = tasks.length;
            const selesai = tasks.filter(task => task.selesai).length;
            const pct = total > 0 ? Math.round((selesai / total) * 100) : 0;
            const description = project.deskripsi
                ? escapeProjectHtml(project.deskripsi)
                : '<span class="text-slate-400">-</span>';
            const statusClasses = {
                'berjalan': 'bg-emerald-100 text-emerald-700',
                'menunggu di-acc': 'bg-amber-100 text-amber-700',
                'sudah di-acc': 'bg-emerald-700 text-white',
                'terlambat': 'bg-red-100 text-red-700',
                'baru': 'bg-[#C0E7FF] text-blue-700'
            };
            const status = escapeProjectHtml(project.status || 'baru');
            const statusBadge = `<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold ${statusClasses[project.status] || statusClasses.baru}">${status}</span>`;
            const deadline = project.tenggat ? new Date(project.tenggat).toLocaleString('id-ID') : '-';
            const deadlineClass = project.tenggat && new Date(project.tenggat) < new Date()
                ? 'font-bold text-red-600'
                : 'text-slate-500';

            return `<tr class="hover:bg-slate-50 transition">
                <td class="px-6 py-4 font-semibold text-slate-800">${escapeProjectHtml(project.nama_project)}</td>
                <td class="px-6 py-4 text-slate-500 max-w-md truncate" title="${description}">${description}</td>
                <td class="px-6 py-4 text-center whitespace-nowrap">${statusBadge}</td>
                <td class="px-6 py-4 text-center whitespace-nowrap text-xs ${deadlineClass}">${escapeProjectHtml(deadline)}</td>
                <td class="px-6 py-4 text-center whitespace-nowrap">${selesai} / ${total} task <span class="text-slate-400">(${pct}%)</span></td>
                <td class="px-6 py-4 text-center whitespace-nowrap">
                    <a href="/project/detail/${encodeURIComponent(project.id_project)}"
                        class="text-[#0097B2] hover:text-[#008199] font-medium hover:underline">Detail</a>
                </td>
            </tr>`;
        }).join('');
    }

    function escapeProjectHtml(value) {
        return String(value).replace(/[&<>"']/g, character => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        })[character]);
    }

    document.addEventListener('DOMContentLoaded', loadStaffProjects);
</script>