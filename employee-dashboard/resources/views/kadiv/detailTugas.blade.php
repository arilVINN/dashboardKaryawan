<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detail Tugas - PT SILINDO</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white flex h-screen overflow-hidden">

    {{-- SIDEBAR --}}
    @include('component_kadiv.sidebar')

    {{-- AREA KANAN --}}
    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        {{-- TOPBAR --}}
        @include('component_kadiv.topbar')
        @include('component.breadcrumbs')

        {{-- CONTENT --}}
        <main class="flex-1 overflow-y-auto">
            <div class="px-9 pt-7 pb-16 max-w-[1134px]">

                {{-- TUGAS 1 --}}
                <section>
                    <h1 id="detail-task-id" class="text-[28px] leading-[36px] font-bold text-black">Tugas</h1>

                    <h2 id="detail-task-title" class="mt-6 text-[28px] leading-[36px] font-bold text-black">Memuat tugas...</h2>

                    <p class="text-[16px] leading-[24px] font-bold text-black" id="detail-task-deadline">
                        tenggat : -
                    </p>
                    <p class="text-[16px] leading-[24px] font-bold text-black" id="detail-task-status">
                        status : -
                    </p>

                    <p id="detail-task-description" class="mt-10 text-[16px] leading-[24px] text-black">
                        Memuat deskripsi tugas...
                    </p>

                    <div id="task-support-card" class="mt-6 flex flex-wrap gap-4" style="display: none;"></div>
                </section>

                {{-- GARIS PEMISAH --}}
                <div class="mt-10 border-t border-gray-400"></div>

                {{-- TUGAS KEDUA --}}
                <section class="pt-6">
                    <h2 id="detail-submission-title" class="text-[24px] leading-[32px] font-bold text-black">Pengumpulan Tugas</h2>

                    <p id="detail-submission-deadline" class="text-[16px] leading-[24px] font-bold text-black">
                        tenggat : -
                    </p>
                    <p id="detail-submission-status" class="text-[16px] leading-[24px] font-bold text-black">
                        status : -
                    </p>

                    <div class="mt-4 flex flex-col md:flex-row md:items-end justify-between gap-4">
                        <div>
                            <p id="detail-submission-note" class="text-[16px] leading-[24px] text-black">
                                -
                            </p>
                        </div>

                        <div id="task-submission-card" class="flex flex-wrap gap-4 shrink-0 ml-auto" style="display: none;"></div>
                    </div>
                </section>

                <div id="backendRevision" class="hidden mt-8 border-t border-gray-400 pt-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-[18px] leading-[26px] font-bold text-black">Riwayat Revisi</h3>
                        <span id="revision-count" class="text-sm text-[#565E74]"></span>
                    </div>
                    <div id="revision-history-list" class="max-h-[500px] overflow-y-auto pr-2 space-y-6">
                    </div>
                </div>

                {{-- AKSI / FORM REVISI --}}
                <div class="mt-8">

                    {{-- STATE 1: TOMBOL --}}
                    <div id="aksiTombol" class="items-center gap-5" style="display: none;">

                        <button type="button"
                                data-toggle-form="formRevisi"
                                class="w-[148px] h-9 rounded-[9px] bg-[#D22B2B] flex items-center justify-center text-white text-[16px] leading-[24px] font-medium hover:bg-[#b92323] transition cursor-pointer">
                            Revisi
                        </button>

                        <button type="button"
                                data-modal-open="modalKonfirmasiAcc"
                                class="w-[148px] h-9 rounded-[9px] bg-[#0E9DC3] flex items-center justify-center text-white text-[16px] leading-[24px] font-medium hover:bg-[#0c89aa] transition cursor-pointer">
                            Acc
                        </button>

                    </div>

                    {{-- STATE 2: FORM REVISI (hidden by default) --}}
                    <form id="formRevisi"
                          action="#"
                          method="POST"
                          enctype="multipart/form-data"
                          class="hidden">

                        @csrf

                        <div class="max-w-[820px]">

                            <div class="border border-gray-300 rounded-lg p-5 bg-white space-y-5 shadow-sm">

                                {{-- Textarea catatan --}}
                                <div>
                                    <textarea name="isi_revisi"
                                              rows="4"
                                              required
                                              placeholder="Tulis catatan revisi..."
                                              class="w-full resize-none rounded-lg border border-[#CBD5E1] px-3 py-2.5 text-sm text-[#283044] placeholder:text-[#94A3B8] outline-none focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]"></textarea>
                                </div>

                                {{-- Kartu file yang direvisi --}}
                                {{-- Choose File + Tanggal (sejajar) --}}
                                <div class="flex flex-wrap gap-4">

                                    {{-- Choose File --}}
                                    <div class="w-[299px] h-[42px] border border-[#CBD5E1] rounded-lg bg-white flex items-center overflow-hidden shrink-0">
                                        <label class="h-full px-4 bg-[#E4E8ED] border-r border-[#CBD5E1] flex items-center text-sm font-medium text-[#283044] cursor-pointer hover:bg-[#d5dbe2] transition">
                                            <input type="file" name="file_revisi" id="fileRevisi" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip" class="hidden">
                                            Choose File
                                        </label>
                                        <span id="namaFileRevisi" class="px-4 text-sm text-[#94A3B8] truncate">No file chosen</span>
                                    </div>

                                    {{-- Tanggal Tenggat --}}
                                    <div class="w-[299px] h-[42px] border border-[#CBD5E1] rounded-lg bg-white flex items-center px-3 shrink-0">
                                        <input type="datetime-local"
                                               name="tenggat"
                                               required
                                               class="w-full h-full outline-none text-sm text-[#283044] bg-transparent cursor-pointer">
                                    </div>

                                </div>

                            </div>

                            {{-- Tombol Submit --}}
                            <div class="mt-4 flex justify-end">
                                <button type="submit"
                                        class="h-9 px-6 bg-[#0E9DC3] rounded-lg text-white font-bold hover:bg-[#0c89aa] transition cursor-pointer">
                                    Submit
                                </button>
                            </div>

                        </div>

                    </form>

                </div>

            </div>
        </main>

    </div>

    {{-- MODAL KONFIRMASI ACC --}}
    <div id="modalKonfirmasiAcc"
         class="hidden fixed inset-0 z-50 bg-[rgba(86,94,116,0.4)] flex items-center justify-center p-4">

        <div class="bg-white rounded-xl w-full max-w-[400px] p-6 shadow-[0_8px_16px_rgba(0,0,0,0.12)]">

            <h3 class="text-xl font-bold text-black mb-2">Konfirmasi Acc</h3>

            <p class="text-sm text-[#565E74] mb-6">
                Apakah Anda yakin ingin menyetujui tugas ini?
            </p>

            <div class="flex items-center justify-end gap-3">

                <button type="button"
                        data-modal-close="modalKonfirmasiAcc"
                        class="h-9 px-4 bg-[#E8E7E9] rounded-lg text-[#333335] hover:bg-[#D9D9D9] transition cursor-pointer">
                    Batal
                </button>

                <button type="button"
                        id="confirmTaskAcc"
                        data-modal-close="modalKonfirmasiAcc"
                        class="h-9 px-4 bg-[#0E9DC3] rounded-lg text-white font-bold hover:bg-[#0c89aa] transition cursor-pointer">
                    Ya, Acc
                </button>

            </div>

        </div>
    </div>

    {{-- SCRIPT --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const token = sessionStorage.getItem('staff_token');
            const taskId = @json($id);
            const formRevisi = document.getElementById('formRevisi');
            const setText = (id, value) => {
                document.getElementById(id).textContent = value || '-';
            };
            const formatDate = (value) => value
                ? new Date(value).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' })
                : '-';
            function appendAttachmentCard(container, filePath, label, href = null) {
                if (typeof filePath !== 'string' || !filePath.trim() || filePath.trim() === '-') return false;

                const value = filePath.trim();
                let targetUrl;
                try {
                    targetUrl = new URL(href || (/^https?:\/\//i.test(value) ? value : '/storage/' + value.replace(/^\/+/, '')), window.location.origin);
                } catch {
                    return false;
                }
                if (!['http:', 'https:'].includes(targetUrl.protocol)) return false;

                const isExternalLink = /^https?:\/\//i.test(value) && !href;
                const fileName = isExternalLink
                    ? (label || 'Link Referensi')
                    : (value.split(/[?#]/)[0].split('/').pop() || label || 'Lampiran');
                const extension = isExternalLink
                    ? 'URL'
                    : (fileName.includes('.') ? fileName.split('.').pop().toUpperCase() : 'FILE');
                const iconColor = extension === 'PDF'
                    ? 'text-red-500'
                    : ['DOC', 'DOCX'].includes(extension)
                        ? 'text-blue-600'
                        : 'text-slate-600';
                let iconSvg;

                if (isExternalLink) {
                    iconSvg = '<svg class="w-7 h-7 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>';
                } else if (extension === 'PDF') {
                    iconSvg = `<svg class="w-8 h-8 ${iconColor}" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM8.5 17.5c-.3 0-.5-.2-.5-.5v-5c0-.3.2-.5.5-.5s.5.2.5.5v5c0 .3-.2.5-.5.5zm3 0c-1.1 0-2-.9-2-2v-1c0-1.1.9-2 2-2s2 .9 2 2v1c0 1.1-.9 2-2 2zm0-4c-.6 0-1 .4-1 1v1c0 .6.4 1 1 1s1-.4 1-1v-1c0-.6-.4-1-1-1zm3.5 4c-.3 0-.5-.2-.5-.5v-2h1.5c.3 0 .5-.2.5-.5s-.2-.5-.5-.5H14.5v-1.5c0-.3.2-.5.5-.5s.5.2.5.5v5c0 .3-.2.5-.5.5z"/></svg>`;
                } else if (['DOC', 'DOCX'].includes(extension)) {
                    iconSvg = `<svg class="w-8 h-8 ${iconColor}" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM9 13h1.5l1 3.5 1-3.5H14l-1.8 5H11L9 13z"/></svg>`;
                } else {
                    iconSvg = `<svg class="w-8 h-8 ${iconColor}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>`;
                }

                const card = document.createElement('a');
                card.href = targetUrl.href;
                card.target = '_blank';
                card.rel = 'noopener noreferrer';
                card.className = 'flex items-center gap-3 bg-white border border-slate-300 rounded-xl px-4 py-3 shadow-sm hover:shadow-md transition w-56 cursor-pointer';

                const details = document.createElement('div');
                details.className = 'flex-1 min-w-0';
                const name = document.createElement('p');
                name.className = 'text-sm font-bold text-slate-900 underline truncate';
                name.textContent = fileName;
                const type = document.createElement('p');
                type.className = 'text-xs text-slate-400 mt-0.5';
                type.textContent = extension;
                details.append(name, type);
                card.append(details);

                const icon = document.createElement('span');
                icon.innerHTML = iconSvg;
                card.append(icon.firstElementChild);
                container.appendChild(card);
                return true;
            }

            async function requestTask(method, payload) {
                const headers = {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                };
                if (!(payload instanceof FormData)) {
                    headers['Content-Type'] = 'application/json';
                }
                const response = await fetch('/api/kadiv/tugas/' + encodeURIComponent(taskId) + (method === 'POST' ? '/review' : ''), {
                    method,
                    headers,
                    ...(payload ? { body: payload instanceof FormData ? payload : JSON.stringify(payload) } : {})
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Permintaan tugas gagal.');
                return result;
            }

            async function loadTaskDetail() {
                if (!token) {
                    window.location.href = '/login';
                    return;
                }
                try {
                    const response = await fetch('/api/kadiv/tugas/' + encodeURIComponent(taskId), {
                        headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                    });
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.message || 'Gagal memuat tugas.');
                    const task = result.data;
                    const submissions = Array.isArray(task.submit_tugas) ? task.submit_tugas : [];
                    const submission = submissions[0];
                    setText('detail-task-id', 'Tugas ' + task.id_tugas);
                    setText('detail-task-title', task.judul_tugas);
                    setText('detail-task-deadline', 'tenggat : ' + formatDate(task.deadline));
                    setText('detail-task-status', 'status : ' + (task.status_efektif || task.status));
                    setText('detail-task-description', task.deskripsi);
                    setText('detail-submission-title', submission ? 'Pengumpulan Tugas' : 'Belum ada pengumpulan');
                    setText('detail-submission-deadline', 'tenggat : ' + formatDate(task.deadline));
                    setText('detail-submission-status', 'status : ' + (submission?.status_review || 'belum dikumpulkan'));
                    setText('detail-submission-note', submission?.catatan_karyawan);

                    const taskSupportCard = document.getElementById('task-support-card');
                    taskSupportCard.replaceChildren();
                    const taskFileName = typeof task.file_pendukung === 'string'
                        ? task.file_pendukung.trim()
                        : '';
                    const taskLink = typeof task.link_pendukung === 'string'
                        ? task.link_pendukung.trim()
                        : '';
                    const hasTaskFile = appendAttachmentCard(
                        taskSupportCard,
                        taskFileName,
                        task.judul_tugas || 'File Pendukung',
                        taskFileName && taskFileName !== '-'
                            ? '/storage/' + taskFileName.replace(/^\/+/, '')
                            : null
                    );
                    const hasTaskLink = appendAttachmentCard(taskSupportCard, taskLink, 'Link Referensi');
                    taskSupportCard.style.display = hasTaskFile || hasTaskLink ? 'flex' : 'none';

                    const submissionCard = document.getElementById('task-submission-card');
                    submissionCard.replaceChildren();
                    const submissionFileName = typeof submission?.file_hasil === 'string'
                        ? submission.file_hasil.trim()
                        : '';
                    const submissionLink = typeof submission?.link_submit === 'string'
                        ? submission.link_submit.trim()
                        : '';
                    const hasSubmissionFile = appendAttachmentCard(
                        submissionCard,
                        submissionFileName,
                        'File Hasil',
                        submissionFileName && submissionFileName !== '-'
                            ? '/storage/' + submissionFileName.replace(/^\/+/, '')
                            : null
                    );
                    const hasSubmissionLink = appendAttachmentCard(submissionCard, submissionLink, 'Link Pengumpulan');
                    submissionCard.style.display = hasSubmissionFile || hasSubmissionLink ? 'flex' : 'none';

                    const revisions = submissions.filter((item) =>
                        item.status_review === 'revisi'
                        && typeof item.catatan_revisi === 'string'
                        && item.catatan_revisi.trim() !== ''
                        && item.catatan_revisi.trim() !== '-'
                    );
                    const history = document.getElementById('revision-history-list');
                    history.replaceChildren();
                    if (revisions.length) {
                        setText('revision-count', revisions.length + ' kali revisi');
                        revisions.forEach((revision) => {
                            const item = document.createElement('div');
                            const heading = document.createElement('h2');
                            heading.className = 'text-[24px] leading-[32px] font-bold text-black';
                            heading.textContent = 'Catatan Revisi';
                            item.appendChild(heading);

                            const date = document.createElement('p');
                            date.className = 'text-[14px] leading-[20px] text-[#565E74]';
                            date.textContent = formatDate(revision.created_at || revision.tanggal_submit);
                            item.appendChild(date);

                            const deadline = document.createElement('p');
                            deadline.className = 'text-[16px] leading-[24px] font-bold text-black mt-1';
                            deadline.textContent = 'tenggat : ' + formatDate(revision.deadline_revisi || task.deadline);
                            item.appendChild(deadline);

                            const note = document.createElement('p');
                            note.className = 'text-[16px] leading-[24px] text-black mt-4';
                            note.textContent = revision.catatan_revisi;
                            item.appendChild(note);

                            const revisionFile = typeof revision.file_revisi === 'string'
                                ? revision.file_revisi.trim()
                                : '';
                            if (revisionFile && revisionFile !== '-') {
                                const revisionAttachment = document.createElement('div');
                                revisionAttachment.className = 'mt-4 flex flex-wrap gap-4';
                                appendAttachmentCard(
                                    revisionAttachment,
                                    revisionFile,
                                    'File Revisi',
                                    '/storage/' + revisionFile.replace(/^\/+/, '')
                                );
                                item.appendChild(revisionAttachment);
                            }

                            history.appendChild(item);
                        });
                        document.getElementById('backendRevision').classList.remove('hidden');
                    } else {
                        document.getElementById('backendRevision').classList.add('hidden');
                    }

                    const canReview = submission?.status_review === 'submitted'
                        && task.status !== 'sudah di-acc';
                    document.getElementById('aksiTombol').style.display = canReview ? 'flex' : 'none';
                    formRevisi.classList.add('hidden');
                } catch (error) {
                    console.error('Gagal memuat detail tugas Kadiv:', error);
                    setText('detail-task-title', error.message);
                }
            }

            loadTaskDetail();

            // Toggle form revisi ↔ tombol
            document.querySelectorAll('[data-toggle-form]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const form   = document.getElementById(this.dataset.toggleForm);
                    const tombol = document.getElementById('aksiTombol');
                    if (form && tombol) {
                        form.classList.remove('hidden');
                        tombol.classList.add('hidden');
                    }
                });
            });

            // Modal open
            document.querySelectorAll('[data-modal-open]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const t = document.getElementById(this.dataset.modalOpen);
                    if (t) {
                        t.classList.remove('hidden');
                        document.body.classList.add('overflow-hidden');
                    }
                });
            });

            // Modal close
            document.querySelectorAll('[data-modal-close]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const t = document.getElementById(this.dataset.modalClose);
                    if (t) {
                        t.classList.add('hidden');
                        document.body.classList.remove('overflow-hidden');
                    }
                });
            });

            // Klik area overlay → tutup modal
            document.querySelectorAll('[id^="modalKonfirmasi"]').forEach(function (modal) {
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) {
                        modal.classList.add('hidden');
                        document.body.classList.remove('overflow-hidden');
                    }
                });
            });

            document.getElementById('fileRevisi').addEventListener('change', function () {
                const file = this.files[0];
                if (file && file.size > 20 * 1024 * 1024) {
                    alert('Ukuran file maksimal 20 MB.');
                    this.value = '';
                    document.getElementById('namaFileRevisi').textContent = 'No file chosen';
                    return;
                }
                document.getElementById('namaFileRevisi').textContent = file?.name || 'No file chosen';
            });

            formRevisi.addEventListener('submit', async function (event) {
                event.preventDefault();
                const note = this.elements.isi_revisi.value.trim();
                const deadline = this.elements.tenggat.value;
                if (!note) {
                    alert('Catatan revisi wajib diisi.');
                    this.elements.isi_revisi.focus();
                    return;
                }
                if (!deadline) {
                    alert('Tenggat revisi wajib ditentukan.');
                    this.elements.tenggat.focus();
                    return;
                }

                const payload = new FormData();
                payload.append('status_review', 'revisi');
                payload.append('catatan_revisi', note);
                payload.append('deadline', deadline);
                const file = this.elements.file_revisi.files[0];
                if (file) payload.append('file_revisi', file);

                try {
                    await requestTask('POST', payload);
                    this.reset();
                    document.getElementById('namaFileRevisi').textContent = 'No file chosen';
                    this.classList.add('hidden');
                    await loadTaskDetail();
                    alert('Revisi berhasil dikirim.');
                } catch (error) {
                    alert(error.message);
                }
            });

            document.getElementById('confirmTaskAcc').addEventListener('click', async function () {
                try {
                    await requestTask('POST', { status_review: 'acc' });
                    await loadTaskDetail();
                    alert('Tugas berhasil di-ACC.');
                } catch (error) {
                    alert(error.message);
                }
            });
        });
    </script>

</body>
</html>