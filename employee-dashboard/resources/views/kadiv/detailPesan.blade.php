<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesan KADIV - PT Silindo</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white flex h-screen overflow-hidden">

    @include('component_kadiv.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        @include('component_kadiv.topbar')
        @include('component.breadcrumbs', ['parentText' => 'Pesan', 'parentUrl' => url('/kadiv/pesan'), 'currentPage' => 'Detail Pesan'])

        <main class="flex-1 overflow-y-auto px-10 py-8">
            <h1 class="text-2xl font-bold text-slate-900 mb-6">Pesan</h1>

            @if (session('success'))
                <div class="mb-4 px-4 py-2 rounded-lg bg-green-100 text-green-700 text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            <div class="space-y-4 bg-white p-5 rounded-3xl w-full drop-shadow-2xl">

                {{-- PESAN ASLI --}}
                <div>
                    <h2 id="detail-message-subject" class="text-2xl font-bold text-slate-900 leading-snug">Memuat pesan...</h2>
                    <p id="detail-message-sender" class="text-sm font-semibold text-slate-900 mt-1">Dari: -</p>
                    <p id="detail-message-date" class="text-sm font-semibold text-slate-900">Tanggal: -</p>
                </div>

                <div class="text-sm text-slate-800 leading-relaxed text-justify pt-2">
                    <p id="detail-message-body">-</p>
                </div>

                <div id="detail-message-attachment" class="hidden flex flex-wrap gap-3"></div>
                <div id="reply-history-list" class="mt-4 space-y-3"></div>

                <hr class="border-t border-slate-300 my-6">

                <div>
                    <button type="button" id="btn-toggle-balas"
                        class="px-6 py-2.5 bg-[#0097B2] hover:bg-[#008199] text-white text-sm font-semibold rounded-lg shadow-sm transition cursor-pointer">
                        Balas Pesan
                    </button>
                </div>

            </div>

            <!-- Form Balasan -->
            <div id="form-balasan" class="hidden pt-6 space-y-4 max-w-xl transition-all">
                <h2 class="text-xl font-bold text-slate-900">Balasan</h2>

                <form id="formPesan" action="#" method="POST"
                    enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-1.5">Pesan</label>
                        <textarea name="deskripsi" id="pesanInput" rows="4" required placeholder="Tulis balasan pesan..."
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-cyan-500 resize-none">{{ old('isi_balasan') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-1.5">Lampiran (opsional)</label>
                        <input type="file" name="file_lampiran" id="lampiranBalas"
                            class="block w-full text-sm text-slate-600 border border-slate-300 rounded-lg cursor-pointer file:mr-4 file:py-2 file:px-4 file:border-0 file:bg-slate-200 file:text-slate-700 file:font-medium hover:file:bg-slate-300">
                        <p class="mt-1 text-xs text-slate-400">Maks 20 MB</p>
                    </div>

                    <div class="flex items-center gap-4 pt-1">
                        <button type="reset" id="btnReset"
                            class="px-7 py-2 bg-[#d32f2f] hover:bg-[#b71c1c] text-white text-sm font-medium rounded-lg shadow-sm transition cursor-pointer">
                            reset
                        </button>

                        <button type="submit"
                            class="px-7 py-2 bg-[#0097B2] hover:bg-[#008199] text-white text-sm font-medium rounded-lg shadow-sm transition cursor-pointer">
                            submit
                        </button>
                    </div>
                </form>
            </div>
        </main>

    </div>

    <script>
        const btnToggle = document.getElementById('btn-toggle-balas');
        const formBalasan = document.getElementById('form-balasan');
        const pesanInput = document.getElementById('pesanInput');
        const formPesan = document.getElementById('formPesan');
        const messageId = @json($id);
        const token = sessionStorage.getItem('staff_token');
        let isFormDirty = false;

        function bukaForm() {
            formBalasan.classList.remove('hidden');
            btnToggle.classList.add('hidden');
        }

        btnToggle.addEventListener('click', bukaForm);

        function renderAttachments(container, attachment) {
            container.replaceChildren();

            [
                { url: attachment?.file, label: 'File lampiran' },
                { url: attachment?.link, label: 'Tautan lampiran' }
            ].forEach(({ url, label }) => {
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
                link.className = 'inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-cyan-700 underline shadow-sm hover:bg-slate-50';
                link.textContent = label;
                container.appendChild(link);
            });

            container.classList.toggle('hidden', container.childElementCount === 0);
        }

        async function loadMessage() {
            if (!token) {
                window.location.href = '/login';
                return;
            }

            try {
                const response = await fetch('/api/kadiv/pesan/' + encodeURIComponent(messageId), {
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Gagal memuat pesan.');
                const message = result.data;
                document.getElementById('detail-message-subject').textContent = message.judul_pesan || '-';
                document.getElementById('detail-message-sender').textContent =
                    'Dari: ' + (message.pengirim?.nama || message.pengirim?.username || 'Sistem');
                document.getElementById('detail-message-date').textContent =
                    'Tanggal: ' + (message.tanggal_pesan || message.created_at || '-');
                document.getElementById('detail-message-body').textContent = message.deskripsi || '-';

                renderAttachments(document.getElementById('detail-message-attachment'), message.lampiran);

                const replies = message.balasan || [];
                const list = document.getElementById('reply-history-list');
                list.replaceChildren();
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
                        date.textContent = reply.tanggal_pesan || reply.created_at || '-';
                        metadata.append(sender, date);
                        card.appendChild(metadata);

                        const body = document.createElement('p');
                        body.className = 'text-sm text-slate-700';
                        body.textContent = reply.deskripsi || '-';
                        card.appendChild(body);

                        const attachments = document.createElement('div');
                        attachments.className = 'mt-3 flex flex-wrap gap-3';
                        renderAttachments(attachments, reply.lampiran);
                        if (attachments.childElementCount) card.appendChild(attachments);

                        list.appendChild(card);
                    });

                if (!message.can_reply) {
                    btnToggle.disabled = true;
                    btnToggle.title = 'Jenis pesan ini tidak dapat dibalas.';
                }
            } catch (error) {
                console.error('Gagal memuat pesan Kadiv:', error);
                document.getElementById('detail-message-sender').textContent = error.message;
            }
        }

        loadMessage();
        pesanInput.addEventListener('input', function() {
            isFormDirty = this.value.trim().length > 0;
        });

        document.getElementById('btnReset').addEventListener('click', () => {
            isFormDirty = false;
        });

        document.getElementById('lampiranBalas').addEventListener('change', function() {
            const file = this.files[0];
            if (file && file.size > 20 * 1024 * 1024) {
                alert('Ukuran file maksimal 20 MB');
                this.value = '';
            }
        });

        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (link && isFormDirty) {
                if (link.getAttribute('target') === '_blank') return;
                if (!confirm('Perubahan belum disimpan. Yakin ingin meninggalkan halaman ini?')) {
                    e.preventDefault();
                }
            }
        });

        window.addEventListener('beforeunload', function(e) {
            if (isFormDirty) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        formPesan.addEventListener('submit', async function(event) {
            event.preventDefault();
            const file = document.getElementById('lampiranBalas').files[0];
            if (file && file.size > 20 * 1024 * 1024) {
                alert('Ukuran file maksimal 20 MB');
                return;
            }
            const payload = new FormData(this);
            try {
                const response = await fetch('/api/kadiv/pesan/' + encodeURIComponent(messageId) + '/balas', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
                    body: payload
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Balasan gagal dikirim.');
                this.reset();
                isFormDirty = false;
                tutupForm();
                await loadMessage();
            } catch (error) {
                alert(error.message);
            }
        });

        function tutupForm() {
            formBalasan.classList.add('hidden');
            btnToggle.classList.remove('hidden');
        }

        formPesan.addEventListener('reset', function() {
            isFormDirty = false;
        });
    </script>

</body>

</html>
