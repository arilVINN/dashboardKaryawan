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

                    {{-- FILE DOCX --}}
                    <div id="task-support-card" class="mt-6 items-center justify-between w-[299px] h-[62px] bg-white rounded-xl border border-gray-300 shadow-md px-5" style="display: none;">
                        <div class="flex flex-col">
                            <a id="detail-task-file" href="#" target="_blank" rel="noopener" class="text-black text-[15px] font-bold underline cursor-pointer">-</a>
                            <span id="detail-task-file-type" class="text-gray-400 text-[13px] font-medium">-</span>
                        </div>

                        {{-- Ikon Microsoft Word --}}
                        <div class="w-9 h-9 flex items-center justify-center border-l border-gray-200 pl-4">
                            <svg class="w-8 h-8" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M18.5 3H7A2 2 0 005 5v22a2 2 0 002 2h18a2 2 0 002-2V11.5L18.5 3z" fill="#185ABD"/>
                                <path d="M18.5 3v8.5H27L18.5 3z" fill="#4786E7"/>
                                <path d="M7 13h10v12H7V13z" fill="#103F91"/>
                                <text x="9.5" y="22" font-family="Arial" font-weight="bold" font-size="10" fill="white">W</text>
                            </svg>
                        </div>
                    </div>
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

                        {{-- FILE PDF --}}
                        <div id="task-submission-card" class="items-center justify-between w-[299px] h-[62px] bg-white rounded-xl border border-gray-300 shadow-md px-5 shrink-0 ml-auto" style="display: none;">
                            <div class="flex flex-col">
                                <a id="detail-submission-file" href="#" target="_blank" rel="noopener" class="text-black text-[15px] font-bold underline cursor-pointer">-</a>
                                <span id="detail-submission-file-type" class="text-gray-400 text-[13px] font-medium">-</span>
                            </div>

                            {{-- Ikon PDF --}}
                            <div class="w-9 h-9 flex items-center justify-center border-l border-gray-200 pl-4">
                                <div class="w-7 h-7 bg-[#E53935] rounded flex flex-col items-center justify-center text-white font-bold text-[9px] leading-tight shadow-sm">
                                    <span>PDF</span>
                                </div>
                            </div>
                        </div>
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

                    const taskFile = document.getElementById('detail-task-file');
                    const taskFileName = typeof task.file_pendukung === 'string'
                        ? task.file_pendukung.trim()
                        : '';
                    const taskLink = typeof task.link_pendukung === 'string'
                        ? task.link_pendukung.trim()
                        : '';
                    const taskFilePath = taskFileName
                        && taskFileName !== '-'
                        ? '/storage/' + taskFileName.replace(/^\/+/, '')
                        : (taskLink && taskLink !== '-' ? taskLink : '');
                    if (taskFilePath) {
                        document.getElementById('task-support-card').style.display = 'inline-flex';
                        taskFile.href = taskFilePath;
                        taskFile.textContent = taskFileName && taskFileName !== '-'
                            ? taskFileName.split('/').pop()
                            : taskLink;
                        document.getElementById('detail-task-file-type').textContent =
                            taskFileName && taskFileName !== '-' ? taskFileName.split('.').pop() : 'Link';
                    }

                    const submissionFile = document.getElementById('detail-submission-file');
                    const submissionFileName = typeof submission?.file_hasil === 'string'
                        ? submission.file_hasil.trim()
                        : '';
                    const submissionLink = typeof submission?.link_submit === 'string'
                        ? submission.link_submit.trim()
                        : '';
                    const submissionPath = submissionFileName
                        && submissionFileName !== '-'
                        ? '/storage/' + submissionFileName.replace(/^\/+/, '')
                        : (submissionLink && submissionLink !== '-' ? submissionLink : '');
                    if (submissionPath) {
                        document.getElementById('task-submission-card').style.display = 'inline-flex';
                        submissionFile.href = submissionPath;
                        submissionFile.textContent = submissionFileName && submissionFileName !== '-'
                            ? submissionFileName.split('/').pop()
                            : submissionLink;
                        document.getElementById('detail-submission-file-type').textContent =
                            submissionFileName && submissionFileName !== '-' ? submissionFileName.split('.').pop() : 'Link';
                    }

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
                                const link = document.createElement('a');
                                link.href = '/storage/' + revisionFile.replace(/^\/+/, '');
                                link.target = '_blank';
                                link.rel = 'noopener';
                                link.className = 'mt-4 inline-flex text-sm font-semibold text-cyan-700 underline';
                                link.textContent = revisionFile.split('/').pop();
                                item.appendChild(link);
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