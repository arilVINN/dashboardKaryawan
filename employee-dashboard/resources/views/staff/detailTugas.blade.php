<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Tugas - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">

    @include('component.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        @include('component.topbar')

        <main class="flex-1 overflow-y-auto px-6 sm:px-8 pt-6 pb-8">
            <div class="w-full max-w-[640px]">

                {{-- Breadcrumb --}}
                <nav aria-label="Breadcrumb" class="mb-4">
                    <div class="flex flex-wrap items-center gap-1 text-[13px]">
                        <a href="{{ url('/tugas') }}"
                            class="inline-flex items-center gap-1 font-medium text-[#146C94] hover:underline">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18">
                                </path>
                            </svg>
                            Tugas
                        </a>
                        <span class="px-0.5 text-slate-400" aria-hidden="true">&rsaquo;</span>
                        <span id="breadcrumb-tugas" class="font-medium text-[#172B36]">Detail Tugas</span>
                    </div>
                </nav>

                {{-- Task header --}}
                <div class="flex flex-wrap items-center gap-3">
                    <h1 id="tugas-judul" class="text-2xl font-semibold leading-snug text-[#172B36]">Memuat...</h1>
                    <span id="tugas-status" class="hidden"></span>
                </div>

                <p id="tugas-meta" class="mt-1 hidden text-sm text-[#4B5563]">
                    <span id="tugas-tenggat-text"></span><span id="tugas-relative"></span>
                </p>

                <p id="tugas-deskripsi" class="mt-4 hidden text-sm font-normal leading-[1.6] text-[#172B36]"></p>

                {{-- File pendukung dari Kadiv --}}
                <div id="lampiran-container" class="mt-4 flex flex-wrap gap-3 empty:hidden"></div>

                {{-- Revisi (submit sebelumnya + catatan revisi) --}}
                <div id="section-revisi" class="mt-6 hidden">
                    <h2 id="revisi-judul" class="text-2xl font-bold text-slate-900 leading-snug mb-1">Revisi</h2>
                    <p id="revisi-tenggat" class="text-sm font-semibold text-slate-800">tenggat : -</p>
                    <p id="revisi-status" class="text-sm font-semibold text-slate-800 mb-4">status : -</p>
                    <p id="revisi-catatan" class="text-sm text-slate-700 leading-relaxed text-justify mb-6">-</p>

                    {{-- File Pendukung Revisi dari Kadiv --}}
                    <div id="revisi-attachment-container" class="flex flex-wrap gap-4 mb-4"></div>

                    {{-- File Hasil Submit Sebelumnya --}}
                    <div id="revisi-file-container" class="flex flex-wrap gap-4"></div>
                </div>

                {{-- Divider --}}
                <div class="mt-6">
                    <hr class="border-0 border-t border-[#E2E8EC]">
                </div>

                {{-- Submission form --}}
                <section id="section-submit" class="mt-4">
                    <h2 class="mb-4 text-base font-semibold text-[#172B36]">Kirim tugas</h2>

                    <form id="form-submit-tugas" class="hidden space-y-4">
                        <div>
                            <label for="file_tugas" class="mb-1.5 block text-[13px] font-medium text-[#172B36]">File hasil</label>
                            <div class="flex h-10 items-center overflow-hidden rounded-lg border border-[#D5DEE3] bg-white">
                                <label for="file_tugas"
                                    class="flex h-full shrink-0 cursor-pointer items-center border-r border-[#D5DEE3] bg-slate-50 px-3 text-[13px] font-medium text-[#172B36] transition hover:bg-slate-100">
                                    Pilih file
                                </label>
                                <span id="file-status" class="ml-3 min-w-0 flex-1 truncate text-[13px] text-[#4B5563]">Belum ada file dipilih</span>
                                <button type="button" id="btn-hapus-file"
                                    class="mr-3 hidden shrink-0 text-xs text-[#4B5563] hover:underline">Hapus</button>
                            </div>
                            <input type="file" id="file_tugas" name="file_tugas" class="sr-only">
                        </div>

                        <div>
                            <label for="deskripsi_tugas" class="mb-1.5 block text-[13px] font-medium text-[#172B36]">Catatan</label>
                            <textarea id="deskripsi_tugas" name="deskripsi_tugas" rows="3" placeholder="Tambahkan catatan"
                                class="w-full resize-none rounded-lg border border-[#D5DEE3] px-3 py-2.5 text-sm text-[#172B36] placeholder:text-[#4B5563] focus:border-[#146C94] focus:outline-none focus:ring-1 focus:ring-[#146C94]"></textarea>
                        </div>

                        <p id="form-helper" class="text-[13px] text-[#4B5563]">Isi catatan atau lampirkan file hasil.</p>

                        <div class="flex items-center gap-4">
                            <button type="submit" id="btn-submit-tugas"
                                class="inline-flex h-10 items-center justify-center rounded-lg bg-[#146C94] px-5 text-sm font-medium text-white transition hover:bg-[#0f5878] disabled:cursor-not-allowed disabled:bg-[#A9C7D6]">
                                Kirim tugas
                            </button>
                            <button type="button" id="btn-reset-tugas"
                                class="text-sm font-medium text-[#4B5563] hover:underline">
                                Reset
                            </button>
                        </div>
                    </form>

                    {{-- State jika tugas sudah dikirim / disetujui --}}
                    <div id="status-disabled" class="hidden">
                        <p class="text-sm text-[#4B5563]">
                            <span id="status-disabled-text" class="font-medium text-[#172B36]">-</span><span
                                id="status-disabled-file-wrap" class="hidden"> &middot; <a id="status-disabled-file"
                                    href="#" download class="text-[#146C94] hover:underline"></a></span>
                        </p>
                    </div>
                </section>

            </div>
        </main>

    </div>

    <script>
        const tugasId = window.location.pathname.split('/').filter(Boolean).pop();
        const STATUS_PALETTE = @json(\App\Support\StatusPalette::all());

        const fileInput = document.getElementById('file_tugas');
        const deskripsiInput = document.getElementById('deskripsi_tugas');
        const submitButton = document.getElementById('btn-submit-tugas');
        const helper = document.getElementById('form-helper');
        const fileStatus = document.getElementById('file-status');
        const btnHapusFile = document.getElementById('btn-hapus-file');
        let submissionCompleted = false;

        // Helper: render kartu file
        function renderFileCard(filePath, label) {
            if (!filePath || filePath === '-') return '';
            const ext = filePath.split('.').pop().toUpperCase();
            const iconColor = ext === 'PDF' ? 'text-red-500' : ext === 'DOCX' || ext === 'DOC' ? 'text-blue-600' : 'text-slate-600';

            // Pilih ikon berdasarkan ekstensi
            let iconSvg = '';
            if (ext === 'PDF') {
                iconSvg = `<svg class="w-8 h-8 ${iconColor}" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM8.5 17.5c-.3 0-.5-.2-.5-.5v-5c0-.3.2-.5.5-.5s.5.2.5.5v5c0 .3-.2.5-.5.5zm3 0c-1.1 0-2-.9-2-2v-1c0-1.1.9-2 2-2s2 .9 2 2v1c0 1.1-.9 2-2 2zm0-4c-.6 0-1 .4-1 1v1c0 .6.4 1 1 1s1-.4 1-1v-1c0-.6-.4-1-1-1zm3.5 4c-.3 0-.5-.2-.5-.5v-2h1.5c.3 0 .5-.2.5-.5s-.2-.5-.5-.5H14.5v-1.5c0-.3.2-.5.5-.5s.5.2.5.5v5c0 .3-.2.5-.5.5z"/></svg>`;
            } else if (ext === 'DOCX' || ext === 'DOC') {
                iconSvg = `<svg class="w-8 h-8 ${iconColor}" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM9 13h1.5l1 3.5 1-3.5H14l-1.8 5H11L9 13z"/></svg>`;
            } else {
                iconSvg = `<svg class="w-8 h-8 ${iconColor}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>`;
            }

            return `
            <a href="/storage/${filePath}" download target="_blank"
                class="flex items-center gap-3 bg-white border border-slate-300 rounded-xl px-4 py-3 shadow-sm hover:shadow-md transition w-56 cursor-pointer">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-slate-900 underline truncate">${label}</p>
                    <p class="text-xs text-slate-400 mt-0.5">${ext}</p>
                </div>
                ${iconSvg}
            </a>`;
        }

        function pad2(value) {
            return String(value).padStart(2, '0');
        }

        function parseDate(raw) {
            if (!raw) return null;
            const date = new Date(String(raw).replace(' ', 'T'));
            return Number.isNaN(date.getTime()) ? null : date;
        }

        function fmtDate(raw) {
            const date = parseDate(raw);
            if (!date) return '-';
            return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        }

        function fmtDateTime(raw) {
            const date = parseDate(raw);
            if (!date) return '-';
            return fmtDate(raw) + ', ' + pad2(date.getHours()) + ':' + pad2(date.getMinutes());
        }

        function normalizeStatus(status) {
            const value = String(status || '').toLowerCase().trim();
            if (!value) return null;

            if (value.includes('tidak') && value.includes('aktif')) return 'tidak_aktif';
            if (value === 'aktif') return 'aktif';
            if (value === 'baru') return 'baru';
            if (value === 'berjalan') return 'berjalan';
            if (value.includes('menunggu')) return 'menunggu';
            if (value.includes('sudah') || value.includes('disetujui') || value.includes('selesai')) return 'disetujui';
            if (value.includes('terlambat') || value.includes('telat')) return 'terlambat';

            return null;
        }

        function renderStatusBadge(status) {
            const badge = document.getElementById('tugas-status');
            const palette = STATUS_PALETTE[normalizeStatus(status)] || null;

            if (!palette) {
                badge.textContent = '';
                badge.className = 'hidden';
                return;
            }

            badge.textContent = palette.label;
            badge.className = 'inline-flex w-fit items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold';
            badge.style.backgroundColor = palette.bg;
            badge.style.borderColor = palette.border;
            badge.style.color = palette.text;
        }

        function renderMeta(tugas, lastSubmit) {
            const meta = document.getElementById('tugas-meta');
            const deadline = parseDate(tugas.deadline);

            if (!deadline) {
                meta.classList.add('hidden');
                return;
            }

            const hours = pad2(deadline.getHours());
            const minutes = pad2(deadline.getMinutes());
            const showTime = !(hours === '23' && minutes === '59');
            const status = String(tugas.status || '').toLowerCase();
            const isSubmitted = ['menunggu di-acc', 'sudah di-acc', 'menunggu acc', 'sudah acc'].includes(status);
            const relative = document.getElementById('tugas-relative');

            let relativeText = '';
            let overdue = Boolean(tugas.is_terlambat);

            if (overdue) {
                const submittedAt = isSubmitted && lastSubmit
                    ? parseDate(lastSubmit.tanggal_submit || lastSubmit.created_at)
                    : null;
                const reference = submittedAt || new Date();
                const days = Math.max(1, Math.ceil((reference.getTime() - deadline.getTime()) / 86400000));
                relativeText = 'Terlambat ' + days + ' hari';
            } else if (deadline.getTime() > Date.now()) {
                const days = Math.max(1, Math.ceil((deadline.getTime() - Date.now()) / 86400000));
                relativeText = days + ' hari lagi';
            }

            document.getElementById('tugas-tenggat-text').textContent =
                'Tenggat ' + fmtDate(tugas.deadline) +
                (showTime ? ', ' + hours + ':' + minutes : '') +
                (relativeText ? ' · ' : '');
            relative.textContent = relativeText;
            relative.className = overdue ? 'font-medium text-[#B91C1C]' : '';

            meta.classList.remove('hidden');
        }

        function renderDescription(tugas) {
            const description = document.getElementById('tugas-deskripsi');
            const text = String(tugas.deskripsi || '').trim();

            if (!text || text === '-') {
                description.textContent = '';
                description.classList.add('hidden');
                return;
            }

            description.textContent = text;
            description.classList.remove('hidden');
        }

        async function loadDetailTugas() {
            const token = sessionStorage.getItem('staff_token');
            if (!token || !tugasId) { window.location.href = '/login'; return; }

            try {
                const res = await fetch('/api/staff/tugas/' + tugasId, {
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                });
                const json = await res.json();
                const t = json.data;

                const submissions = Array.isArray(t.submit_tugas) ? t.submit_tugas : [];
                const lastSubmit = submissions[submissions.length - 1];

                // === HEADER ===
                document.getElementById('tugas-judul').textContent = t.judul_tugas || '-';
                document.getElementById('breadcrumb-tugas').textContent = t.judul_tugas || 'Detail Tugas';
                renderStatusBadge(t.status_efektif || t.status);
                renderMeta(t, lastSubmit);
                renderDescription(t);

                // File pendukung dari Kadiv
                const lampiranContainer = document.getElementById('lampiran-container');
                lampiranContainer.innerHTML = '';
                if (t.file_pendukung && t.file_pendukung !== '-') {
                    lampiranContainer.innerHTML += renderFileCard(t.file_pendukung, t.judul_tugas || 'File Pendukung');
                }
                if (t.link_pendukung && t.link_pendukung !== '-') {
                    lampiranContainer.innerHTML += `
                    <a href="${t.link_pendukung}" target="_blank"
                        class="flex items-center gap-3 bg-white border border-slate-300 rounded-xl px-4 py-3 shadow-sm hover:shadow-md transition w-56">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-slate-900 underline truncate">Link Referensi</p>
                            <p class="text-xs text-slate-400 mt-0.5">URL</p>
                        </div>
                        <svg class="w-7 h-7 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>`;
                }

                // === REVISI ===
                const sectionRevisi = document.getElementById('section-revisi');

                const revisionSubmit = [...submissions].reverse().find((submission) =>
                    submission.status_review === 'revisi'
                    && typeof submission.catatan_revisi === 'string'
                    && submission.catatan_revisi.trim() !== ''
                    && submission.catatan_revisi.trim() !== '-'
                );

                if (revisionSubmit) {
                    const catatanRevisi = revisionSubmit.catatan_revisi;

                    document.getElementById('revisi-judul').textContent = 'Revisi ' + (t.judul_tugas || '');
                    document.getElementById('revisi-tenggat').textContent =
                        'tenggat : ' + fmtDateTime(revisionSubmit.deadline_revisi || t.deadline);
                    document.getElementById('revisi-status').textContent =
                        'status : ' + (revisionSubmit.status_review || '-');
                    document.getElementById('revisi-catatan').textContent = catatanRevisi;

                    const revisionAttachmentContainer = document.getElementById('revisi-attachment-container');
                    revisionAttachmentContainer.replaceChildren();
                    if (revisionSubmit.file_revisi && revisionSubmit.file_revisi !== '-') {
                        revisionAttachmentContainer.innerHTML = renderFileCard(
                            revisionSubmit.file_revisi,
                            'File Pendukung Revisi'
                        );
                    }

                    const revisiFileContainer = document.getElementById('revisi-file-container');
                    revisiFileContainer.innerHTML = '';
                    if (lastSubmit?.file_hasil && lastSubmit.file_hasil !== '-') {
                        revisiFileContainer.innerHTML = renderFileCard(lastSubmit.file_hasil, t.judul_tugas || 'File Hasil');
                    }

                    sectionRevisi.classList.remove('hidden');
                }

                // Juga tampilkan pesan dari Kadiv di section revisi jika ada
                if (t.pesans && t.pesans.length > 0) {
                    const pesanHtml = t.pesans.map(p => {
                        const tgl = p.created_at ? new Date(p.created_at).toLocaleDateString('id-ID', {day:'numeric', month:'short', year:'numeric'}) : '-';
                        const pengirim = p.pengirim ? p.pengirim.username : 'Kadiv';
                        return `<div class="mt-3 bg-slate-50 border border-slate-200 rounded-lg px-4 py-3">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-bold text-[#044564]">${pengirim}</span>
                                <span class="text-xs text-slate-400">${tgl}</span>
                            </div>
                            <p class="text-sm text-slate-700">${p.deskripsi || '-'}</p>
                        </div>`;
                    }).join('');

                    // Tambahkan ke section revisi
                    document.getElementById('revisi-file-container').insertAdjacentHTML('afterend', pesanHtml);
                    sectionRevisi.classList.remove('hidden');
                }

                // === SUBMIT STATE ===
                const status = String(t.status || '').toLowerCase();
                const approved = ['sudah di-acc', 'sudah acc', 'disetujui'].includes(status);
                const pending = ['menunggu di-acc', 'menunggu acc'].includes(status);
                const formSubmit = document.getElementById('form-submit-tugas');
                const statusDisabled = document.getElementById('status-disabled');

                if (approved || pending) {
                    formSubmit.classList.add('hidden');
                    statusDisabled.classList.remove('hidden');

                    document.getElementById('status-disabled-text').textContent = approved
                        ? 'Disetujui'
                        : 'Dikirim ' + fmtDate(lastSubmit?.tanggal_submit || lastSubmit?.created_at);

                    const file = lastSubmit?.file_hasil;
                    const fileWrap = document.getElementById('status-disabled-file-wrap');
                    const fileLink = document.getElementById('status-disabled-file');

                    if (file && file !== '-') {
                        fileLink.textContent = String(file).split('/').pop();
                        fileLink.href = '/storage/' + file;
                        fileWrap.classList.remove('hidden');
                    } else {
                        fileWrap.classList.add('hidden');
                    }
                } else {
                    formSubmit.classList.remove('hidden');
                    statusDisabled.classList.add('hidden');
                }

            } catch(e) {
                console.error('Gagal load detail tugas', e);
                document.getElementById('tugas-judul').textContent = 'Gagal memuat detail tugas';
            }
        }

        function formatSize(bytes) {
            if (!bytes) return '0 KB';
            const kb = bytes / 1024;
            if (kb < 1024) return kb.toFixed(1).replace('.', ',') + ' KB';
            return (kb / 1024).toFixed(1).replace('.', ',') + ' MB';
        }

        function updateFileDisplay() {
            const file = fileInput.files[0];
            if (file) {
                fileStatus.textContent = file.name + ' · ' + formatSize(file.size);
                btnHapusFile.classList.remove('hidden');
            } else {
                fileStatus.textContent = 'Belum ada file dipilih';
                btnHapusFile.classList.add('hidden');
            }
        }

        function setHelperError(isError) {
            helper.textContent = 'Isi catatan atau lampirkan file hasil.';
            helper.classList.toggle('text-[#B91C1C]', isError);
            helper.classList.toggle('text-[#4B5563]', !isError);
        }

        function hasSubmissionInput() {
            return Boolean(fileInput.files.length || deskripsiInput.value.trim());
        }

        function hasUnsavedSubmission() {
            return !submissionCompleted && hasSubmissionInput();
        }

        loadDetailTugas();

        fileInput.addEventListener('change', () => {
            updateFileDisplay();
            setHelperError(false);
        });

        deskripsiInput.addEventListener('input', () => setHelperError(false));

        btnHapusFile.addEventListener('click', () => {
            fileInput.value = '';
            updateFileDisplay();
            setHelperError(false);
        });

        document.addEventListener('click', function(event) {
            const link = event.target.closest('a');
            if (!link || !hasUnsavedSubmission()) return;
            if (link.target === '_blank' || link.hasAttribute('download') || link.hash) return;

            const confirmed = confirm('Pengumpulan tugas belum dikirim. Yakin ingin meninggalkan halaman ini?');
            if (!confirmed) {
                event.preventDefault();
            } else {
                submissionCompleted = true;
            }
        });

        window.addEventListener('beforeunload', function(event) {
            if (!hasUnsavedSubmission()) return;
            event.preventDefault();
            event.returnValue = '';
        });

        // Reset form
        document.getElementById('btn-reset-tugas').addEventListener('click', () => {
            fileInput.value = '';
            deskripsiInput.value = '';
            updateFileDisplay();
            setHelperError(false);
            submitButton.disabled = false;
            submitButton.textContent = 'Kirim tugas';
        });

        // Submit tugas
        document.getElementById('form-submit-tugas').addEventListener('submit', async function(e) {
            e.preventDefault();

            if (!hasSubmissionInput()) {
                setHelperError(true);
                return;
            }

            const token = sessionStorage.getItem('staff_token');
            if (!token) return alert('Sesi habis, silakan login ulang.');

            const formData = new FormData();

            if (fileInput.files[0]) formData.append('file_hasil', fileInput.files[0]);
            if (deskripsiInput.value) formData.append('catatan_karyawan', deskripsiInput.value);
            formData.append('progress', 100);

            setHelperError(false);
            submitButton.disabled = true;
            submitButton.textContent = 'Mengirim...';

            try {
                const res = await fetch('/api/staff/tugas/' + tugasId + '/submit', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
                    body: formData
                });
                const json = await res.json();
                if (res.ok) {
                    submissionCompleted = true;
                    alert('Hasil tugas berhasil dikirim!');
                    window.location.reload();
                } else {
                    alert(json.message || 'Gagal mengirim hasil tugas.');
                }
            } catch(error) {
                alert('Terjadi kesalahan jaringan.');
            }

            submitButton.disabled = false;
            submitButton.textContent = 'Kirim tugas';
        });
    </script>

</body>
</html>
