<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Tugas - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Detail Tugas: revision thread + submission form layout */
        .tugas-body { display: block; }
        .tugas-form-col { max-width: 640px; }

        .tugas-body.has-revisi .tugas-form-col {
            max-width: none;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #E2E8EC;
        }

        @media (min-width: 1100px) {
            .tugas-body.has-revisi {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 360px;
                align-items: start;
            }

            .tugas-body.has-revisi .tugas-thread {
                padding-right: 24px;
            }

            .tugas-body.has-revisi .tugas-form-col {
                margin-top: 0;
                padding-top: 0;
                padding-left: 24px;
                border-top: 0;
                border-left: 1px solid #E2E8EC;
            }

            .tugas-body.has-revisi #section-submit-inner {
                position: sticky;
                top: 24px;
            }
        }
    </style>
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">

    @include('component.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        @include('component.topbar')

        <main class="flex-1 overflow-y-auto px-6 sm:px-8 pt-6 pb-8">
            <div class="w-full">

                {{-- Breadcrumb --}}
                @include('component.breadcrumbs', [
                    'parentText' => 'Daftar Tugas',
                    'parentUrl' => url('/tugas'),
                    'currentPage' => 'Detail Tugas',
                    'inline' => true,
                ])

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

                {{-- Divider --}}
                <hr class="mt-6 border-0 border-t border-[#E2E8EC]">

                {{-- Body: revision thread + submission form --}}
                <div id="tugas-body" class="tugas-body mt-6">

                    <div class="tugas-thread min-w-0">
                        {{-- Riwayat revisi (catatan + lampiran per revisi) --}}
                        <section id="section-revisi" class="hidden">
                            <div class="flex items-center justify-between gap-4">
                                <h2 class="text-lg font-semibold text-[#172B36]">Riwayat Revisi</h2>
                                <span id="revisi-count" class="text-[13px] text-[#4B5563]"></span>
                            </div>

                            <div id="revisi-history-list" class="mt-4 space-y-5"></div>
                        </section>

                        {{-- Pesan Kadiv, terpisah dari riwayat revisi --}}
                        <section id="section-pesan" class="mb-6 mt-6 hidden max-w-[640px]">
                            <h2 class="text-lg font-semibold text-[#172B36]">Pesan dari Kadiv</h2>
                            <div id="revisi-pesan-container" class="mt-3"></div>
                        </section>
                    </div>

                    {{-- Submission form --}}
                    <div id="section-submit-wrap" class="tugas-form-col min-w-0">
                        <div id="section-submit-inner">
                            <section id="section-submit">
                                <h2 id="submit-title" class="mb-4 text-lg font-semibold text-[#172B36]">Kirim Tugas</h2>

                                <form id="form-submit-tugas" class="hidden space-y-4">
                                    {{-- Hasil sebelumnya tetap di sisi form sebagai acuan kirim ulang --}}
                                    <section id="referensi-revisi" class="hidden border-b border-[#E2E8EC] pb-4">
                                        <h3 class="text-[13px] font-semibold text-[#172B36]">Hasil sebelumnya</h3>
                                        <div id="revisi-file-container" class="mt-2 flex flex-wrap gap-3"></div>
                                    </section>

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
                                        <textarea id="deskripsi_tugas" name="deskripsi_tugas" rows="4" placeholder="Tambahkan catatan"
                                            class="w-full resize-none rounded-lg border border-[#D5DEE3] px-3 py-2.5 text-sm text-[#172B36] placeholder:text-[#4B5563] focus:border-[#146C94] focus:outline-none focus:ring-1 focus:ring-[#146C94]"></textarea>
                                    </div>

                                    <div id="form-errors" class="hidden space-y-1 rounded-lg border border-[#F5C2C7] bg-[#FEF2F2] px-3 py-2 text-[13px] leading-5 text-[#B91C1C]" role="alert" aria-live="polite"></div>

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
                                    <p id="status-disabled-text" class="text-sm font-medium text-[#172B36]">-</p>
                                    <div id="status-disabled-card" class="mt-3 flex flex-wrap gap-3"></div>
                                </div>
                            </section>
                        </div>
                    </div>

                </div>
            </div>
        </main>

    </div>

    {{-- Modal konfirmasi kirim tugas --}}
    <div id="modalKonfirmasiSubmit"
        class="opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center p-4
               bg-[rgba(86,94,116,0.4)] backdrop-blur-sm transition-opacity duration-150 ease-in-out"
        role="dialog" aria-modal="true" aria-labelledby="modalKonfirmasiSubmitTitle">
        <div data-modal-panel
            class="flex w-full max-w-[420px] flex-col overflow-hidden rounded-xl bg-white shadow-[0_8px_16px_rgba(0,0,0,0.12)]
                   scale-95 translate-y-2 transition-all duration-150 ease-in-out">
            <div class="flex items-center justify-between border-b border-[#E2E8F0] bg-[#F8FAFC] px-6 py-4">
                <div class="flex min-w-0 items-center gap-3">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#D9EEF5] text-[#146C94]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v4m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z"></path>
                        </svg>
                    </div>
                    <h3 id="modalKonfirmasiSubmitTitle" class="truncate text-base font-bold text-[#172B36]">
                        Kirim tugas ini?
                    </h3>
                </div>
                <button type="button" data-modal-close="modalKonfirmasiSubmit"
                    class="shrink-0 text-2xl leading-none text-[#565E74] transition hover:text-black"
                    aria-label="Tutup">&times;</button>
            </div>

            <div class="px-6 py-5">
                <p class="text-sm leading-relaxed text-[#4B5563]">
                    Pastikan file hasil dan catatan sudah benar. Setelah dikirim, tugas akan masuk ke proses
                    persetujuan Kadiv.
                </p>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-[#E2E8F0] px-6 py-4">
                <button type="button" data-modal-close="modalKonfirmasiSubmit"
                    class="h-9 rounded-lg bg-[#E8E7E9] px-4 text-sm text-[#333335] transition hover:bg-[#D9D9D9]">
                    Batal
                </button>
                <button type="button" id="btn-confirm-submit-tugas"
                    class="inline-flex h-9 items-center justify-center rounded-lg bg-[#146C94] px-4 text-sm font-bold text-white transition hover:bg-[#0f5878] disabled:cursor-not-allowed disabled:bg-[#A9C6D3]">
                    Ya, kirim tugas
                </button>
            </div>
        </div>
    </div>

    <script>
        const tugasId = window.location.pathname.split('/').filter(Boolean).pop();
        const STATUS_PALETTE = @json(\App\Support\StatusPalette::all());

        const fileInput = document.getElementById('file_tugas');
        const deskripsiInput = document.getElementById('deskripsi_tugas');
        const submitButton = document.getElementById('btn-submit-tugas');
        const submitTitle = document.getElementById('submit-title');
        const tugasBody = document.getElementById('tugas-body');
        const fileStatus = document.getElementById('file-status');
        const btnHapusFile = document.getElementById('btn-hapus-file');
        const modalSubmitTitle = document.getElementById('modalKonfirmasiSubmitTitle');
        const confirmSubmitButton = document.getElementById('btn-confirm-submit-tugas');
        let submissionCompleted = false;
        let submitLabel = 'Kirim tugas';

        function setSubmitLabel(label) {
            submitLabel = label;
            submitTitle.textContent = label;
            submitButton.textContent = label;
            if (modalSubmitTitle) modalSubmitTitle.textContent = label + '?';
            if (confirmSubmitButton) confirmSubmitButton.textContent = 'Ya, ' + label.toLowerCase();
        }

        function showRevisionSection() {
            document.getElementById('section-revisi').classList.remove('hidden');
            tugasBody.classList.add('has-revisi');
            setSubmitLabel('Kirim Ulang Tugas');
        }

        // Escape values that may come from uploaded filenames before they are
        // injected into innerHTML.
        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
            }[char]));
        }

        function fileDisplayName(filePath, originalName) {
            const storedName = String(filePath || '').split(/[?#]/)[0].split('/').pop() || '';
            const original = typeof originalName === 'string' ? originalName.trim() : '';
            if (original) return original;
            if (!storedName) return '';

            // Laravel's generated names are long random strings. Showing them
            // is what made attachments ambiguous, so let the role label win.
            if (/^[A-Za-z0-9]{24,}(?:\.[A-Za-z0-9]+)?$/.test(storedName)) return '';

            return storedName;
        }

        const downloadIconSvg = '<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>';

        const attachmentIconPaths = {
            document: 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
            text: 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
            table: 'M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125V6.375c0-.621.504-1.125 1.125-1.125h17.25c.621 0 1.125.504 1.125 1.125v12c0 .621-.504 1.125-1.125 1.125ZM3.375 9.75h17.25M9.75 9.75v9.75M15 9.75v9.75',
            image: 'm2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z',
            archive: 'm20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0-3-3m3 3 3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z',
            link: 'M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244',
        };

        function attachmentIcon(ext, isLink = false) {
            const icon = (path) => `<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="${path}" /></svg>`;

            if (isLink) return { color: 'text-[#146C94]', svg: icon(attachmentIconPaths.link) };
            if (ext === 'PDF') return { color: 'text-red-500', svg: icon(attachmentIconPaths.text) };
            if (['DOC', 'DOCX'].includes(ext)) return { color: 'text-blue-500', svg: icon(attachmentIconPaths.text) };
            if (['XLS', 'XLSX', 'CSV'].includes(ext)) return { color: 'text-emerald-600', svg: icon(attachmentIconPaths.table) };
            if (['JPG', 'JPEG', 'PNG', 'GIF', 'WEBP'].includes(ext)) return { color: 'text-violet-500', svg: icon(attachmentIconPaths.image) };
            if (['ZIP', 'RAR', '7Z'].includes(ext)) return { color: 'text-amber-600', svg: icon(attachmentIconPaths.archive) };

            return { color: 'text-[#64748B]', svg: icon(attachmentIconPaths.document) };
        }

        function renderAttachmentRow({ href, title, meta, icon, downloadName = null, download = true }) {
            const downloadAttribute = download
                ? (downloadName ? ` download="${escapeHtml(downloadName)}"` : ' download')
                : '';

            return `
            <a href="${escapeHtml(href)}"${downloadAttribute} target="_blank" rel="noopener noreferrer"
                class="group flex w-[320px] max-w-full items-center gap-3 rounded-lg bg-white px-3 py-2.5 shadow-sm transition hover:shadow-md">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#F1F5F9] ${icon.color}">
                    ${icon.svg}
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-medium text-[#172B36]">${escapeHtml(title)}</span>
                    <span class="block truncate text-[12px] leading-5 text-[#4B5563]">${escapeHtml(meta)}</span>
                </span>
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-[#4B5563] transition group-hover:text-[#146C94]" aria-hidden="true">
                    ${downloadIconSvg}
                </span>
            </a>`;
        }

        // Helper: render kartu file
        function renderFileCard(filePath, role, options = {}) {
            if (!filePath || filePath === '-') return '';

            const storedName = String(filePath).split(/[?#]/)[0].split('/').pop() || '';
            const ext = (storedName.includes('.') ? storedName.split('.').pop() : 'FILE').toUpperCase();
            const displayName = fileDisplayName(filePath, options.name);
            const title = displayName || role || 'Lampiran';
            const sizeLabel = options.size ? formatSize(options.size) : '';
            const meta = [ext, sizeLabel].filter(Boolean).join(' · ');
            const url = options.url || ('/storage/' + String(filePath).replace(/^\/+/, ''));

            return renderAttachmentRow({
                href: url,
                title,
                meta,
                icon: attachmentIcon(ext),
                downloadName: displayName || null,
            });
        }

        function renderLinkCard(url, label = 'Link Referensi') {
            if (!url || url === '-') return '';

            return renderAttachmentRow({
                href: url,
                title: label,
                meta: 'Tautan',
                icon: attachmentIcon('', true),
                download: false,
            });
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

        function renderMeta(tugas) {
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

            if (!isSubmitted && !tugas.is_terlambat && deadline.getTime() > Date.now()) {
                const days = Math.max(1, Math.ceil((deadline.getTime() - Date.now()) / 86400000));
                relativeText = days + ' hari lagi';
            }

            document.getElementById('tugas-tenggat-text').textContent =
                'Tenggat ' + fmtDate(tugas.deadline) +
                (showTime ? ', ' + hours + ':' + minutes : '') +
                (relativeText ? ' · ' : '');
            relative.textContent = relativeText;
            relative.className = '';

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
                const crumb = document.querySelector('[data-breadcrumb-current]');
                if (crumb) crumb.textContent = t.judul_tugas || 'Detail Tugas';
                renderStatusBadge(t.status_efektif || t.status);
                renderMeta(t);
                renderDescription(t);

                // File pendukung dari Kadiv
                const lampiranContainer = document.getElementById('lampiran-container');
                lampiranContainer.innerHTML = '';
                if (t.file_pendukung && t.file_pendukung !== '-') {
                    lampiranContainer.innerHTML += renderFileCard(t.file_pendukung, 'Lampiran dari Kadiv', {
                        name: t.file_pendukung_name,
                        size: t.file_pendukung_size,
                    });
                }
                if (t.link_pendukung && t.link_pendukung !== '-') {
                    lampiranContainer.innerHTML += renderLinkCard(t.link_pendukung);
                }

                // === RIWAYAT REVISI ===
                const revisions = submissions
                    .filter((submission) =>
                        submission.status_review === 'revisi'
                        && typeof submission.catatan_revisi === 'string'
                        && submission.catatan_revisi.trim() !== ''
                        && submission.catatan_revisi.trim() !== '-'
                    )
                    .sort((a, b) => {
                        const first = parseDate(a.created_at || a.tanggal_submit);
                        const second = parseDate(b.created_at || b.tanggal_submit);
                        return (second?.getTime() || 0) - (first?.getTime() || 0);
                    });

                const revisionHistory = document.getElementById('revisi-history-list');
                const revisionCount = document.getElementById('revisi-count');
                revisionHistory.replaceChildren();
                revisionCount.textContent = '';

                if (revisions.length) {
                    revisionCount.textContent = revisions.length + ' kali revisi';

                    revisions.forEach((revision, index) => {
                        // Revisi terbaru tampil paling atas dengan nomor terbesar.
                        const revisionNumber = revisions.length - index;

                        const item = document.createElement('div');

                        const headingRow = document.createElement('div');
                        headingRow.className = 'flex items-center gap-2';

                        const badge = document.createElement('span');
                        badge.className = 'flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#E6F2F7] text-[12px] font-semibold text-[#146C94]';
                        badge.textContent = String(revisionNumber);

                        const heading = document.createElement('p');
                        heading.className = 'text-[14px] font-semibold text-[#172B36]';
                        heading.textContent = 'Catatan revisi';

                        headingRow.append(badge, heading);
                        item.appendChild(headingRow);

                        const date = document.createElement('p');
                        date.className = 'text-[13px] leading-5 text-[#4B5563]';
                        date.textContent = fmtDateTime(revision.created_at || revision.tanggal_submit);
                        item.appendChild(date);

                        const note = document.createElement('p');
                        note.className = 'mt-2 text-sm leading-[1.6] text-[#172B36]';
                        note.textContent = revision.catatan_revisi;
                        item.appendChild(note);

                        if (revision.file_revisi && revision.file_revisi !== '-') {
                            const attachment = document.createElement('div');
                            attachment.className = 'mt-3 flex flex-col gap-3';
                            attachment.innerHTML = renderFileCard(revision.file_revisi, 'Lampiran revisi', {
                                name: revision.file_revisi_name,
                                size: revision.file_revisi_size,
                            });
                            item.appendChild(attachment);
                        }

                        revisionHistory.appendChild(item);
                    });

                    const revisiFileContainer = document.getElementById('revisi-file-container');
                    revisiFileContainer.innerHTML = '';
                    const hasPreviousFile = Boolean(lastSubmit?.file_hasil && lastSubmit.file_hasil !== '-');
                    if (hasPreviousFile) {
                        revisiFileContainer.innerHTML = renderFileCard(lastSubmit.file_hasil, 'Hasil sebelumnya', {
                            name: lastSubmit.file_hasil_name,
                            size: lastSubmit.file_hasil_size,
                        });
                    }

                    document.getElementById('referensi-revisi').classList.toggle('hidden', !hasPreviousFile);
                    showRevisionSection();
                } else {
                    document.getElementById('referensi-revisi').classList.add('hidden');
                }

                // Pesan dari Kadiv ditampilkan terpisah dari riwayat revisi
                const pesanSection = document.getElementById('section-pesan');
                const pesanContainer = document.getElementById('revisi-pesan-container');
                pesanContainer.replaceChildren();

                const hasMessages = Array.isArray(t.pesans) && t.pesans.length > 0;
                pesanSection.classList.toggle('hidden', !hasMessages);
                // Tanpa riwayat revisi, pesan tidak perlu jarak tambahan dari atas.
                pesanSection.classList.toggle('mt-6', revisions.length > 0);

                if (hasMessages) {
                    const pesanHtml = t.pesans.map(p => {
                        const tgl = p.created_at ? new Date(p.created_at).toLocaleDateString('id-ID', {day:'numeric', month:'short', year:'numeric'}) : '-';
                        const pengirim = p.pengirim ? p.pengirim.username : 'Kadiv';
                        return `<div class="mt-3 bg-slate-50 border border-slate-200 rounded-lg px-4 py-3">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-bold text-[#044564]">${escapeHtml(pengirim)}</span>
                                <span class="text-xs text-slate-400">${escapeHtml(tgl)}</span>
                            </div>
                            <p class="text-sm text-slate-700">${escapeHtml(p.deskripsi || '-')}</p>
                        </div>`;
                    }).join('');

                    pesanContainer.innerHTML = pesanHtml;
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
                    const statusCard = document.getElementById('status-disabled-card');
                    statusCard.replaceChildren();

                    if (file && file !== '-') {
                        statusCard.innerHTML = renderFileCard(file, 'Hasil terkirim', {
                            name: lastSubmit?.file_hasil_name,
                            size: lastSubmit?.file_hasil_size,
                        });
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

        const formErrors = document.getElementById('form-errors');

        function clearFormErrors() {
            formErrors.replaceChildren();
            formErrors.classList.add('hidden');
        }

        function showFormErrors(payload, fallbackMessage) {
            const messages = [];
            const fieldErrors = (payload?.errors && typeof payload.errors === 'object')
                ? payload.errors
                : ((payload?.data && typeof payload.data === 'object') ? payload.data : null);

            if (fieldErrors) {
                Object.values(fieldErrors).forEach((value) => {
                    (Array.isArray(value) ? value : [value]).forEach((message) => {
                        if (message) messages.push(String(message));
                    });
                });
            }

            if (!messages.length) {
                messages.push(payload?.message || fallbackMessage || 'Gagal mengirim hasil tugas.');
            }

            formErrors.replaceChildren();
            messages.forEach((message) => {
                const line = document.createElement('p');
                line.textContent = message;
                formErrors.appendChild(line);
            });
            formErrors.classList.remove('hidden');
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
            clearFormErrors();
        });

        deskripsiInput.addEventListener('input', () => {
            clearFormErrors();
        });

        btnHapusFile.addEventListener('click', () => {
            fileInput.value = '';
            updateFileDisplay();
            clearFormErrors();
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
            clearFormErrors();
            submitButton.disabled = false;
            submitButton.textContent = submitLabel;
        });

        // Kirim tugas ke server setelah dikonfirmasi
        async function sendSubmission() {
            const token = sessionStorage.getItem('staff_token');
            if (!token) {
                showFormErrors(null, 'Sesi habis, silakan login ulang.');
                return;
            }

            const formData = new FormData();

            if (fileInput.files[0]) formData.append('file_hasil', fileInput.files[0]);
            if (deskripsiInput.value) formData.append('catatan_karyawan', deskripsiInput.value);
            formData.append('progress', 100);

            submitButton.disabled = true;
            submitButton.textContent = 'Mengirim...';
            if (confirmSubmitButton) confirmSubmitButton.disabled = true;

            try {
                const res = await fetch('/api/staff/tugas/' + tugasId + '/submit', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
                    body: formData
                });

                let json = {};
                try {
                    json = await res.json();
                } catch {
                    json = {};
                }

                if (res.ok) {
                    submissionCompleted = true;
                    alert('Hasil tugas berhasil dikirim!');
                    window.location.reload();
                } else {
                    showFormErrors(json, res.status >= 500
                        ? 'Server gagal memproses pengumpulan. Silakan coba lagi.'
                        : 'Gagal mengirim hasil tugas.');
                }
            } catch(error) {
                showFormErrors(null, 'Tidak dapat terhubung ke server. Periksa koneksi Anda lalu coba lagi.');
            }

            submitButton.disabled = false;
            submitButton.textContent = submitLabel;
            if (confirmSubmitButton) confirmSubmitButton.disabled = false;
        }

        // Tampilkan konfirmasi sebelum mengirim tugas
        document.getElementById('form-submit-tugas').addEventListener('submit', function(e) {
            e.preventDefault();
            clearFormErrors();

            if (!hasSubmissionInput()) {
                showFormErrors(null, 'Lampirkan file hasil atau isi catatan pengumpulan.');
                return;
            }

            if (!sessionStorage.getItem('staff_token')) {
                showFormErrors(null, 'Sesi habis, silakan login ulang.');
                return;
            }

            if (typeof window.setModal === 'function') {
                window.setModal('modalKonfirmasiSubmit', true);
                confirmSubmitButton?.focus();
            } else {
                sendSubmission();
            }
        });

        confirmSubmitButton?.addEventListener('click', function() {
            if (typeof window.setModal === 'function') {
                window.setModal('modalKonfirmasiSubmit', false);
            }
            sendSubmission();
        });
    </script>

</body>
</html>
