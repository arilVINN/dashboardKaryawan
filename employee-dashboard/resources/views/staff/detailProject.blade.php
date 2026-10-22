<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Project - PT Silindo</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">
    @include('component.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')
        @include('component.breadcrumbs', [
            'parentText' => 'Daftar Tugas',
            'parentUrl' => url('/tugas'),
            'currentPage' => 'Detail Project',
        ])

        <main class="flex-1 overflow-y-auto p-8 pt-6">
            <div id="projectDetail" data-project-id="{{ $id }}" class="space-y-5">
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 text-center text-slate-400">
                    Memuat detail project...
                </div>
            </div>
        </main>
    </div>

    <script>
        const projectDetailContainer = document.getElementById('projectDetail');
        const projectDetailId = projectDetailContainer.dataset.projectId;

        function escapeProjectDetail(value) {
            return String(value ?? '').replace(/[&<>"']/g, character => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            })[character]);
        }

        async function loadProjectDetail() {
            const token = localStorage.getItem('staff_token') || sessionStorage.getItem('staff_token');
            if (!token) {
                projectDetailContainer.innerHTML = '<div class="bg-white border border-slate-200 rounded-xl p-6 text-center text-red-500">Sesi login tidak ditemukan. Silakan login kembali.</div>';
                return;
            }

            try {
                const response = await fetch(`/api/staff/projects/${encodeURIComponent(projectDetailId)}`, {
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                const result = await response.json();
                if (!response.ok) {
                    throw new Error(result.message || 'Gagal memuat detail project.');
                }
                renderProjectDetail(result.data);
            } catch (error) {
                console.error(error);
                projectDetailContainer.innerHTML = `<div class="bg-white border border-slate-200 rounded-xl p-6 text-center text-red-500">${escapeProjectDetail(error.message || 'Gagal memuat detail project.')}</div>`;
            }
        }

        function renderProjectDetail(project) {
            if (!project || !Array.isArray(project.tasks)) {
                throw new Error('Data detail project tidak valid.');
            }

            const total = project.tasks.length;
            const done = project.tasks.filter(task => task.selesai).length;
            const percent = total ? Math.round((done / total) * 100) : 0;
            const file = project.file_url
                ? `<a href="${escapeProjectDetail(project.file_url)}" target="_blank" rel="noopener noreferrer" class="inline-block mt-3 text-sm font-medium text-[#146C94] hover:underline">${escapeProjectDetail(project.file_name || 'Lihat file project')}</a>`
                : '';
            const taskRows = project.tasks.length
                ? project.tasks.map(task => {
                    const taskId = escapeProjectDetail(task.id_task);
                    const submittedAt = task.tanggal_submit
                        ? new Date(task.tanggal_submit).toLocaleString('id-ID')
                        : '';
                    const submittedFile = task.file_hasil_url
                        ? `<div class="flex min-w-0 flex-col gap-1">
                               <span class="inline-flex w-fit items-center rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">Sudah dikumpulkan</span>
                               <a href="${escapeProjectDetail(task.file_hasil_url)}" target="_blank" rel="noopener noreferrer" class="max-w-56 truncate text-xs text-[#146C94] hover:underline" title="${escapeProjectDetail(task.file_hasil_name)}">${escapeProjectDetail(task.file_hasil_name || 'Lihat file')}</a>
                               ${submittedAt ? `<span class="text-xs text-slate-400">${escapeProjectDetail(submittedAt)}</span>` : ''}
                           </div>`
                        : '<span class="inline-flex w-fit items-center rounded-full bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700">Belum dikumpulkan</span>';
                    const submitControl = task.milik_saya
                        ? `<input type="file" class="hidden projectSubmissionInput" data-id="${taskId}" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip">
                           <button type="button" class="projectSubmissionButton inline-flex shrink-0 items-center justify-center whitespace-nowrap rounded-lg bg-[#146C94] px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-[#0f5878] focus:outline-none focus:ring-2 focus:ring-[#146C94] focus:ring-offset-2 disabled:cursor-wait disabled:opacity-60 transition" data-id="${taskId}">
                               ${task.file_hasil_url ? 'Kumpulkan ulang' : 'Kumpulkan'}
                           </button>
                           <span class="block text-xs text-red-600 mt-1" data-submit-error="${taskId}"></span>`
                        : '<span class="text-xs text-slate-400">-</span>';

                    return `
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-4 align-middle">
                                <label class="flex items-start gap-3 ${task.milik_saya ? 'cursor-pointer' : 'cursor-not-allowed opacity-60'}">
                                    <input type="checkbox" class="projectTaskCheck mt-0.5 w-4 h-4 rounded border-slate-300 text-[#0E9DC3] focus:ring-[#0E9DC3]"
                                        data-id="${taskId}" ${task.selesai ? 'checked' : ''} ${task.milik_saya ? '' : 'disabled'}>
                                    <span class="break-words text-sm leading-5 ${task.selesai ? 'text-slate-400 line-through' : 'text-slate-700'}">${escapeProjectDetail(task.label)}</span>
                                </label>
                            </td>
                            <td class="px-5 py-4 text-center align-middle">
                                <span class="inline-flex max-w-full items-center justify-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-medium leading-4 text-slate-600">${escapeProjectDetail(task.karyawan)}</span>
                            </td>
                            <td class="px-5 py-4 align-middle">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="min-w-0">${submittedFile}</div>
                                    <div class="flex shrink-0 flex-col items-end">
                                        ${submitControl}
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `;
                }).join('')
                : '<tr><td colspan="3" class="px-5 py-8 text-center text-slate-400">Belum ada task pada project ini.</td></tr>';

            projectDetailContainer.innerHTML = `
                <section class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-5">
                        <div class="min-w-0">
                            <h1 class="text-2xl font-bold text-slate-800">${escapeProjectDetail(project.nama_project)}</h1>
                            <p class="text-sm text-slate-500 mt-2 whitespace-pre-line">${escapeProjectDetail(project.deskripsi || 'Tidak ada deskripsi project.')}</p>
                            ${file}
                        </div>
                        <div class="shrink-0 sm:w-48">
                            <div class="flex justify-between text-xs text-slate-500 mb-2">
                                <span>Progress</span><span>${done} / ${total} task (${percent}%)</span>
                            </div>
                            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-[#0E9DC3]" style="width:${percent}%"></div>
                            </div>
                        </div>
                    </div>
                </section>
                <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100">
                        <h2 class="font-semibold text-slate-800">Daftar Task</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[700px] table-fixed text-left text-sm">
                            <colgroup>
                                <col class="w-[30%]">
                                <col class="w-[30%]">
                                <col class="w-[40%]">
                            </colgroup>
                            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-5 py-3 font-semibold">Tugas</th>
                                    <th class="px-5 py-3 font-semibold text-center">Dikerjakan oleh</th>
                                    <th class="px-5 py-3 font-semibold">Pengumpulan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">${taskRows}</tbody>
                        </table>
                    </div>
                </section>
            `;
        }

        document.addEventListener('click', event => {
            const button = event.target.closest('.projectSubmissionButton');
            if (!button) return;

            const input = projectDetailContainer.querySelector(`.projectSubmissionInput[data-id="${CSS.escape(button.dataset.id)}"]`);
            if (input) input.click();
        });

        document.addEventListener('change', async event => {
            const input = event.target;
            if (!input.classList.contains('projectSubmissionInput') || !input.files.length) return;

            const token = localStorage.getItem('staff_token') || sessionStorage.getItem('staff_token');
            const taskId = input.dataset.id;
            const button = projectDetailContainer.querySelector(`.projectSubmissionButton[data-id="${CSS.escape(taskId)}"]`);
            const error = projectDetailContainer.querySelector(`[data-submit-error="${CSS.escape(taskId)}"]`);
            const formData = new FormData();
            formData.append('file_hasil', input.files[0]);
            input.disabled = true;
            if (button) button.disabled = true;
            if (error) error.textContent = '';

            try {
                const response = await fetch(`/api/staff/projects/task/${encodeURIComponent(taskId)}/submit`, {
                    method: 'POST',
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
                    body: formData
                });
                const result = await response.json();
                if (!response.ok) {
                    const validationMessage = result.data?.file_hasil?.[0];
                    throw new Error(validationMessage || result.message || 'Gagal mengumpulkan file.');
                }
                await loadProjectDetail();
            } catch (submissionError) {
                console.error(submissionError);
                if (error) error.textContent = submissionError.message || 'Gagal mengumpulkan file.';
                input.disabled = false;
                input.value = '';
                if (button) button.disabled = false;
            }
        });

        document.addEventListener('change', async event => {
            const checkbox = event.target;
            if (!checkbox.classList.contains('projectTaskCheck')) return;

            const token = localStorage.getItem('staff_token') || sessionStorage.getItem('staff_token');
            checkbox.disabled = true;
            try {
                const response = await fetch(`/api/staff/projects/task/${encodeURIComponent(checkbox.dataset.id)}/toggle`, {
                    method: 'PATCH',
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Gagal menyimpan status task.');
                await loadProjectDetail();
            } catch (error) {
                console.error(error);
                checkbox.checked = !checkbox.checked;
                checkbox.disabled = false;
                alert(error.message || 'Gagal menyimpan status task.');
            }
        });

        loadProjectDetail();
    </script>
</body>

</html>