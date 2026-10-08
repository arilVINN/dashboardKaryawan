<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Tugas - PT Silindo</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-white flex h-screen overflow-hidden">

    @include('component.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        @include('component.topbar')
        @include('component.breadcrumbs', ['parentText' => 'Tugas', 'parentUrl' => url('/tugas'), 'currentPage' => 'Detail Tugas'])

        <main class="flex-1 overflow-y-auto px-10 py-8">
            <h1 class="text-xl font-bold text-slate-900 mb-8">Tugas</h1>

            <div class="max-w-3xl space-y-0">

                <!-- ===== SECTION 1: INFO TUGAS ===== -->
                <div class="pb-8">
                    <h2 id="tugas-judul" class="text-2xl font-bold text-slate-900 leading-snug mb-1">Memuat...</h2>
                    <p id="tugas-tenggat" class="text-sm font-semibold text-slate-800">tenggat : -</p>
                    <p id="teks-status" class="text-sm font-semibold text-slate-800 mb-4">status : -</p>
                    <p id="tugas-deskripsi" class="text-sm text-slate-700 leading-relaxed text-justify mb-6">-</p>

                    <!-- File Pendukung dari Kadiv -->
                    <div id="lampiran-container" class="flex flex-wrap gap-4"></div>
                </div>

                <hr class="border-slate-300">

                <!-- ===== SECTION 2: REVISI (submit sebelumnya + catatan revisi) ===== -->
                <div id="section-revisi" class="hidden py-8">
                    <h2 id="revisi-judul" class="text-2xl font-bold text-slate-900 leading-snug mb-1">Revisi</h2>
                    <p id="revisi-tenggat" class="text-sm font-semibold text-slate-800">tenggat : -</p>
                    <p id="revisi-status" class="text-sm font-semibold text-slate-800 mb-4">status : -</p>
                    <p id="revisi-catatan" class="text-sm text-slate-700 leading-relaxed text-justify mb-6">-</p>

                    <!-- File Pendukung Revisi dari Kadiv -->
                    <div id="revisi-attachment-container" class="flex flex-wrap gap-4 mb-4"></div>

                    <!-- File Hasil Submit Sebelumnya -->
                    <div id="revisi-file-container" class="flex flex-wrap gap-4"></div>
                </div>

                <div id="hr-revisi" class="hidden"><hr class="border-slate-300"></div>

                <!-- ===== SECTION 3: SUBMIT TUGAS ===== -->
                <div id="section-submit" class="py-8">
                    <h2 class="text-xl font-bold text-slate-900 mb-4">Submit Tugas</h2>

                    <form id="form-submit-tugas" class="space-y-4 max-w-xl">
                        <div>
                            <label class="block text-sm font-semibold text-slate-900 mb-1.5">File Hasil</label>
                            <input type="file" id="file_tugas" name="file_tugas"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-700 bg-white focus:outline-none focus:ring-1 focus:ring-cyan-500 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-900 mb-1.5">Deskripsi / Catatan</label>
                            <input type="text" id="deskripsi_tugas" name="deskripsi_tugas" placeholder="Tambahkan catatan"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-cyan-500 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed">
                        </div>

                        <div class="flex items-center gap-3 pt-1">
                            <button type="button" id="btn-reset-tugas"
                                class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                                Reset
                            </button>
                            <button type="submit" id="btn-submit-tugas" disabled
                                class="px-6 py-2 bg-slate-400 cursor-not-allowed opacity-70 text-white text-sm font-medium rounded-lg transition">
                                Submit
                            </button>
                        </div>
                    </form>

                    <!-- Pesan status jika tugas sudah selesai / menunggu -->
                    <div id="status-disabled" class="hidden">
                        <p id="status-disabled-text" class="text-sm font-semibold text-slate-500 italic">-</p>
                    </div>
                </div>

            </div>
        </main>

    </div>

    <script>
        const tugasId = window.location.pathname.split('/').filter(Boolean).pop();

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

        async function loadDetailTugas() {
            const token = localStorage.getItem('staff_token');
            if (!token || !tugasId) { window.location.href = '/login'; return; }

            try {
                const res = await fetch('/api/staff/tugas/' + tugasId, {
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                });
                const json = await res.json();
                const t = json.data;

                // Format tanggal
                function fmtDate(raw) {
                    if (!raw) return '-';
                    const d = new Date(raw);
                    return d.getDate() + ' ' + ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'][d.getMonth()] + ' ' + d.getFullYear() + ', ' + String(d.getHours()).padStart(2,'0') + '.00';
                }

                // === SECTION 1: TUGAS ===
                document.getElementById('tugas-judul').textContent = t.judul_tugas || '-';
                document.getElementById('tugas-tenggat').textContent = 'tenggat : ' + fmtDate(t.deadline);
                document.getElementById('teks-status').textContent = 'status : ' + (t.status || 'baru');
                document.getElementById('tugas-deskripsi').textContent = t.deskripsi || '-';

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

                // === SECTION 2: REVISI ===
                const sectionRevisi = document.getElementById('section-revisi');
                const hrRevisi = document.getElementById('hr-revisi');

                const submissions = Array.isArray(t.submit_tugas) ? t.submit_tugas : [];
                const lastSubmit = submissions[ submissions.length - 1 ];
                const revisionSubmit = [...submissions].reverse().find((submission) =>
                    submission.status_review === 'revisi'
                    && typeof submission.catatan_revisi === 'string'
                    && submission.catatan_revisi.trim() !== ''
                    && submission.catatan_revisi.trim() !== '-'
                );

                if (revisionSubmit) {
                    const catatanRevisi = revisionSubmit.catatan_revisi;
                    const fileHasil = lastSubmit.file_hasil;

                    document.getElementById('revisi-judul').textContent = 'Revisi ' + (t.judul_tugas || '');
                    document.getElementById('revisi-tenggat').textContent =
                        'tenggat : ' + fmtDate(revisionSubmit.deadline_revisi || t.deadline);
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
                    hrRevisi.classList.remove('hidden');
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
                    hrRevisi.classList.remove('hidden');
                }

                // === SECTION 3: SUBMIT ===
                const st = (t.status || '').toLowerCase();
                const formSubmit = document.getElementById('form-submit-tugas');
                const statusDisabled = document.getElementById('status-disabled');
                const statusDisabledText = document.getElementById('status-disabled-text');

                if (st === 'sudah di-acc' || st === 'sudah acc') {
                    formSubmit.classList.add('hidden');
                    statusDisabled.classList.remove('hidden');
                    statusDisabledText.textContent = '✅ Tugas ini sudah selesai dan telah disetujui oleh Kadiv.';
                } else if (st === 'menunggu acc' || st === 'menunggu di-acc') {
                    formSubmit.querySelectorAll('input, button').forEach(control => {
                        control.disabled = true;
                    });
                    const btnReset = document.getElementById('btn-reset-tugas');
                    btnReset.classList.remove('bg-red-600', 'hover:bg-red-700');
                    btnReset.classList.add('bg-slate-400', 'cursor-not-allowed', 'opacity-70');

                    const btnSubmit = document.getElementById('btn-submit-tugas');
                    btnSubmit.classList.remove('bg-[#0097B2]', 'hover:bg-[#008199]');
                    btnSubmit.classList.add('bg-slate-400', 'cursor-not-allowed', 'opacity-70');
                } else {
                    const btnSubmit = document.getElementById('btn-submit-tugas');
                    btnSubmit.disabled = false;
                    btnSubmit.classList.remove('bg-slate-400', 'cursor-not-allowed', 'opacity-70');
                    btnSubmit.classList.add('bg-[#0097B2]', 'hover:bg-[#008199]');
                }

            } catch(e) {
                console.error('Gagal load detail tugas', e);
                document.getElementById('tugas-judul').textContent = 'Gagal memuat detail tugas';
            }
        }

        loadDetailTugas();

        // Reset form
        document.getElementById('btn-reset-tugas').addEventListener('click', () => {
            document.getElementById('file_tugas').value = '';
            document.getElementById('deskripsi_tugas').value = '';
        });

        // Submit tugas
        document.getElementById('form-submit-tugas').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btnSubmit = document.getElementById('btn-submit-tugas');
            if (btnSubmit.disabled) return;

            const token = localStorage.getItem('staff_token');
            if (!token) return alert('Sesi habis, silakan login ulang.');

            const fileInput = document.getElementById('file_tugas');
            const deskripsiInput = document.getElementById('deskripsi_tugas');
            const formData = new FormData();

            if (fileInput.files[0]) formData.append('file_hasil', fileInput.files[0]);
            if (deskripsiInput.value) formData.append('catatan_karyawan', deskripsiInput.value);
            formData.append('progress', 100);

            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Mengirim...';

            try {
                const res = await fetch('/api/staff/tugas/' + tugasId + '/submit', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
                    body: formData
                });
                const json = await res.json();
                if (res.ok) {
                    alert('Hasil tugas berhasil dikirim!');
                    window.location.reload();
                } else {
                    alert(json.message || 'Gagal mengirim hasil tugas.');
                }
            } catch(error) {
                alert('Terjadi kesalahan jaringan.');
            }

            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Submit';
        });
    </script>

</body>
</html>

