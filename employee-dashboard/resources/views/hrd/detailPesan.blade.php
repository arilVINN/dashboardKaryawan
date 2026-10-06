<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesan HRD - PT Silindo</title>

    @vite('resources/css/app.css')
</head>

<body class="bg-white flex h-screen overflow-hidden">

    @include('component_hrd.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        @include('component.topbar')
        @include('component.breadcrumbs')

        <main class="flex-1 overflow-y-auto px-10 py-8">
            <h1 class="text-2xl font-bold text-slate-900 mb-6">Pesan</h1>

            <div class="space-y-4 bg-white p-5 rounded-3xl w-full drop-shadow-2xl">
                <div>
                    <h2 id="pesan-judul" class="text-2xl font-bold text-slate-900 leading-snug">Memuat judul pesan...</h2>
                    <p id="pesan-pengirim" class="text-sm font-semibold text-slate-900 mt-1">Dari: -</p>
                    <p id="pesan-tanggal" class="text-sm font-semibold text-slate-900">Tanggal: -</p>
                </div>

                <div class="text-sm text-slate-800 leading-relaxed text-justify pt-2">
                    <p id="pesan-isi">Memuat isi pesan...</p>
                </div>

                <div id="pesan-lampiran" class="hidden"></div>
                <div id="thread-balasan" class="mt-4 space-y-3"></div>

                <hr class="border-t border-slate-300 my-6">

                <div>
                    <button type="button" id="btn-toggle-balas"
                        class="px-6 py-2.5 bg-[#0097B2] hover:bg-[#008199] text-white text-sm font-semibold rounded-lg shadow-sm transition">
                        Balas Pesan
                    </button>
                </div>
            </div>

            <div id="form-balasan" class="hidden pt-6 space-y-4 max-w-xl transition-all">
                <h2 class="text-xl font-bold text-slate-900">Balasan</h2>

                <form id="form-submit-balasan" enctype="multipart/form-data" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-1.5">Pesan</label>
                        <textarea name="deskripsi" id="pesanInput" rows="4" required placeholder="Tulis balasan pesan..."
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-cyan-500 resize-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-1.5">Lampiran (opsional)</label>
                        <input type="file" name="file_lampiran" id="lampiranBalas" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                            class="block w-full text-sm text-slate-600 border border-slate-300 rounded-lg cursor-pointer file:mr-4 file:py-2 file:px-4 file:border-0 file:bg-slate-200 file:text-slate-700 file:font-medium hover:file:bg-slate-300">
                        <p class="mt-1 text-xs text-slate-400">Maks 20 MB</p>
                    </div>

                    <div class="flex items-center gap-4 pt-1">
                        <button type="reset" id="btn-reset-balas"
                            class="px-7 py-2 bg-[#d32f2f] hover:bg-[#b71c1c] text-white text-sm font-medium rounded-lg shadow-sm transition">
                            reset
                        </button>

                        <button type="submit"
                            class="px-7 py-2 bg-[#0097B2] hover:bg-[#008199] text-white text-sm font-medium rounded-lg shadow-sm transition">
                            submit
                        </button>
                    </div>
                </form>
            </div>
        </main>

    </div>

    <script>
        const messageId = window.location.pathname.split('/').filter(Boolean).pop();
        const token = sessionStorage.getItem('staff_token');
        const btnToggle = document.getElementById('btn-toggle-balas');
        const formBalasan = document.getElementById('form-balasan');
        const formSubmit = document.getElementById('form-submit-balasan');
        const pesanInput = document.getElementById('pesanInput');
        const lampiranInput = document.getElementById('lampiranBalas');
        let isFormDirty = false;

        function renderAttachments(container, attachment) {
            container.replaceChildren();
            const entries = [
                { url: attachment?.file, label: 'Lihat lampiran' },
                { url: attachment?.link, label: attachment?.link }
            ];

            entries.forEach(({ url, label }) => {
                if (typeof url !== 'string' || !url.trim() || url.trim() === '-') return;

                let safeUrl;
                try {
                    safeUrl = new URL(url.trim(), window.location.origin);
                } catch {
                    return;
                }
                if (!['http:', 'https:'].includes(safeUrl.protocol)) return;

                const link = document.createElement('a');
                link.href = safeUrl.href;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                link.className = 'inline-block text-sm font-semibold text-cyan-700 underline';
                link.textContent = label;
                container.appendChild(link);
            });

            container.classList.toggle('hidden', container.childElementCount === 0);
        }

        function formatDate(value, includeTime = false) {
            if (!value) return '-';
            const date = new Date(value.length === 10 ? value + 'T00:00:00' : value);
            return Number.isNaN(date.getTime())
                ? value
                : date.toLocaleString('id-ID', includeTime ? undefined : { dateStyle: 'short' });
        }

        function renderReplies(replies) {
            const container = document.getElementById('thread-balasan');
            container.replaceChildren();

            [...replies]
                .sort((a, b) => new Date(a.created_at || a.tanggal_pesan || 0) - new Date(b.created_at || b.tanggal_pesan || 0))
                .forEach((reply) => {
                    const card = document.createElement('div');
                    card.className = 'p-4 rounded-xl border border-slate-100 bg-slate-50';

                    const metadata = document.createElement('div');
                    metadata.className = 'flex justify-between items-center mb-2';
                    const sender = document.createElement('span');
                    sender.className = 'text-xs font-bold text-slate-800';
                    sender.textContent = reply.pengirim?.nama || reply.pengirim?.username || 'Sistem';
                    const date = document.createElement('span');
                    date.className = 'text-[10px] text-slate-500';
                    date.textContent = formatDate(reply.tanggal_pesan || reply.created_at);
                    metadata.append(sender, date);
                    card.appendChild(metadata);

                    const body = document.createElement('p');
                    body.className = 'text-sm text-slate-700';
                    body.textContent = reply.deskripsi || '-';
                    card.appendChild(body);

                    const attachments = document.createElement('div');
                    attachments.className = 'mt-3 space-x-3';
                    renderAttachments(attachments, reply.lampiran);
                    if (attachments.childElementCount) card.appendChild(attachments);

                    container.appendChild(card);
                });
        }

        async function loadMessage() {
            if (!token || !messageId) {
                window.location.href = '/login';
                return;
            }

            try {
                const response = await fetch('/api/hrd/pesan/' + encodeURIComponent(messageId), {
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Gagal memuat pesan.');

                const message = result.data;
                document.getElementById('pesan-judul').textContent = message.judul_pesan || '-';
                document.getElementById('pesan-pengirim').textContent =
                    'Dari: ' + (message.pengirim?.nama || message.pengirim?.username || 'Sistem');
                document.getElementById('pesan-tanggal').textContent =
                    'Tanggal: ' + formatDate(message.tanggal_pesan || message.created_at);
                document.getElementById('pesan-isi').textContent = message.deskripsi || '-';
                renderAttachments(document.getElementById('pesan-lampiran'), message.lampiran);
                renderReplies(message.balasan || []);
                btnToggle.classList.toggle('hidden', !message.can_reply);
                btnToggle.disabled = !message.can_reply;
            } catch (error) {
                console.error('Gagal memuat pesan HRD:', error);
                document.getElementById('pesan-judul').textContent = 'Pesan Tidak Ditemukan';
                document.getElementById('pesan-isi').textContent = error.message || 'Gagal memuat pesan.';
                btnToggle.classList.add('hidden');
            }
        }

        btnToggle.addEventListener('click', () => {
            formBalasan.classList.remove('hidden');
            btnToggle.classList.add('hidden');
        });

        pesanInput.addEventListener('input', () => {
            isFormDirty = pesanInput.value.trim().length > 0 || lampiranInput.files.length > 0;
        });
        lampiranInput.addEventListener('change', () => {
            const file = lampiranInput.files[0];
            if (file && file.size > 20 * 1024 * 1024) {
                alert('Ukuran file maksimal 20 MB');
                lampiranInput.value = '';
            }
            isFormDirty = pesanInput.value.trim().length > 0 || lampiranInput.files.length > 0;
        });
        formSubmit.addEventListener('reset', () => {
            isFormDirty = false;
        });

        formSubmit.addEventListener('submit', async (event) => {
            event.preventDefault();
            const file = lampiranInput.files[0];
            if (file && file.size > 20 * 1024 * 1024) {
                alert('Ukuran file maksimal 20 MB');
                return;
            }

            try {
                const response = await fetch('/api/hrd/pesan/' + encodeURIComponent(messageId) + '/balas', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
                    body: new FormData(formSubmit)
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Balasan gagal dikirim.');

                formSubmit.reset();
                isFormDirty = false;
                formBalasan.classList.add('hidden');
                await loadMessage();
            } catch (error) {
                alert(error.message || 'Terjadi kesalahan saat mengirim balasan.');
            }
        });

        document.addEventListener('click', (event) => {
            const link = event.target.closest('a');
            if (!link || !isFormDirty || link.target === '_blank' || link.getAttribute('href')?.startsWith('#')) return;
            if (!confirm('Perubahan belum disimpan. Yakin ingin meninggalkan halaman ini?')) {
                event.preventDefault();
            } else {
                isFormDirty = false;
            }
        });

        window.addEventListener('beforeunload', (event) => {
            if (!isFormDirty) return;
            event.preventDefault();
            event.returnValue = '';
        });

        loadMessage();
    </script>

</body>

</html>
