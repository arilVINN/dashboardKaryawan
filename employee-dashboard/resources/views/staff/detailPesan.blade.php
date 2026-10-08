<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesan Staff - PT Silindo</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-white flex h-screen overflow-hidden">

    @include('component.sidebar')
    
    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        @include('component.topbar')
        @include('component.breadcrumbs', ['parentText' => 'Pesan', 'parentUrl' => url('/pesan'), 'currentPage' => 'Detail Pesan'])

        <main class="flex-1 overflow-y-auto px-10 py-8">
            <h1 class="text-2xl font-bold text-slate-900 mb-6">Pesan</h1>

            <!-- Detail Pesan -->
            <div class="space-y-4 bg-white p-5 rounded-3xl w-full drop-shadow-2xl">
                <div>
                    <h2 id="pesan-judul" class="text-2xl font-bold text-slate-900 leading-snug">Memuat judul pesan...</h2>
                    <p id="pesan-pengirim" class="text-sm font-semibold text-slate-900 mt-1">Dari : -</p>
                    <p id="pesan-tanggal" class="text-sm font-semibold text-slate-900">Tanggal : -</p>
                </div>

                <div class="text-sm text-slate-800 leading-relaxed text-justify pt-2">
                    <p id="pesan-isi">Memuat isi pesan...</p>
                </div>

                <div id="pesan-lampiran" class="flex flex-wrap gap-3"></div>
                
                <!-- Balasan thread -->
                <div id="thread-balasan" class="mt-4 space-y-3">
                    <!-- Balasan akan diload di sini -->
                </div>

                <hr class="border-t border-slate-300 my-6">

                <div>
                    <button type="button" id="btn-toggle-balas"
                        class="px-6 py-2.5 bg-[#0097B2] hover:bg-[#008199] text-white text-sm font-semibold rounded-lg shadow-sm transition">
                        Balas Pesan
                    </button>
                </div>
            </div>

            <!-- Form Balasan -->
            <div id="form-balasan" class="hidden pt-6 space-y-4 max-w-xl transition-all">
                <h2 class="text-xl font-bold text-slate-900">Kirim Balasan</h2>

                <form id="form-submit-balasan" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-1.5">Tulis Pesan</label>
                        <textarea name="pesan_balasan" id="pesanInput" placeholder="Ketik pesan Anda di sini..." rows="3"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-cyan-500"></textarea>
                    </div>

                    <div class="flex items-center gap-4 pt-1">
                        <button type="button" id="btn-batal-balas"
                            class="px-7 py-2 bg-[#d32f2f] hover:bg-[#b71c1c] text-white text-sm font-medium rounded-lg shadow-sm transition">
                            Batal
                        </button>

                        <button type="submit"
                            class="px-7 py-2 bg-[#0097B2] hover:bg-[#008199] text-white text-sm font-medium rounded-lg shadow-sm transition">
                            Kirim
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        const pesanId = window.location.pathname.split('/').filter(Boolean).pop();

        function renderAttachments(container, attachment) {
            container.replaceChildren();
            if (!attachment) return;

            [
                { url: attachment.file, label: 'File lampiran' },
                { url: attachment.link, label: 'Tautan lampiran' }
            ].forEach(({ url, label }) => {
                if (typeof url !== 'string' || !url.trim() || url.trim() === '-') return;

                let safeUrl;
                try {
                    safeUrl = new URL(url.trim(), window.location.origin);
                } catch {
                    return;
                }
                if (safeUrl.protocol !== 'http:' && safeUrl.protocol !== 'https:') return;

                const link = document.createElement('a');
                link.href = safeUrl.href;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                link.className = 'inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-cyan-700 underline shadow-sm hover:bg-slate-50';
                link.textContent = label;
                container.appendChild(link);
            });
        }

        async function loadDetailPesan() {
            const token = sessionStorage.getItem('staff_token');
            if (!token || !pesanId) return;

            try {
                const res = await fetch('/api/staff/pesan/' + pesanId, {
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                });
                const json = await res.json();
                
                // Jika API melempar error
                if (!res.ok) {
                    document.getElementById('pesan-judul').textContent = 'Pesan Tidak Ditemukan';
                    document.getElementById('pesan-isi').textContent = json.message || 'Gagal memuat pesan.';
                    document.getElementById('btn-toggle-balas').classList.add('hidden');
                    return;
                }

                // API Pesan Controller bisa jadi return thread tugas atau pesan langsung
                let p = json.data; 
                let balasan = [];
                let pesanUtama = p;

                // Jika data berbentuk thread Tugas (ada array 'pesan' di dalamnya)
                if (p && p.tugas && p.pesan) {
                    // Ambil pesan paling atas (terlama) sebagai parent
                    const rootPesan = p.pesan[0];
                    pesanUtama = rootPesan;
                    balasan = p.pesan.slice(1);
                    
                    document.getElementById('pesan-judul').textContent = 'Tugas: ' + p.tugas.judul_tugas;
                    document.getElementById('pesan-pengirim').textContent = 'Dari: ' + (rootPesan.pengirim ? rootPesan.pengirim.username : 'Sistem');
                    document.getElementById('pesan-tanggal').textContent = 'Tanggal: ' + rootPesan.tanggal_pesan;
                    document.getElementById('pesan-isi').textContent = rootPesan.deskripsi || '-';
                    
                } else if (p && p.id_pesan) {
                    // Jika data berbentuk pesan langsung
                    balasan = p.balasan || [];
                    document.getElementById('pesan-judul').textContent = p.judul_pesan || '-';
                    document.getElementById('pesan-pengirim').textContent = 'Dari: ' + (p.pengirim ? (p.pengirim.nama || p.pengirim.username) : 'Sistem');
                    document.getElementById('pesan-tanggal').textContent = 'Tanggal: ' + (p.tanggal_pesan || p.created_at || '-');
                    document.getElementById('pesan-isi').textContent = p.deskripsi || '-';

                    if (!p.can_reply) {
                        document.getElementById('btn-toggle-balas').classList.add('hidden');
                    }
                }

                renderAttachments(document.getElementById('pesan-lampiran'), pesanUtama?.lampiran);

                // Render semua balasan
                const threadContainer = document.getElementById('thread-balasan');
                threadContainer.replaceChildren();

                balasan.forEach((b) => {
                    const pengirim = b.pengirim ? (b.pengirim.nama || b.pengirim.username) : 'Sistem';
                    const reply = document.createElement('div');
                    reply.className = 'mt-4 p-4 rounded-xl border border-slate-100 bg-slate-50';

                    const metadata = document.createElement('div');
                    metadata.className = 'flex justify-between items-center mb-2';
                    const sender = document.createElement('span');
                    sender.className = 'text-xs font-bold text-slate-800';
                    sender.textContent = pengirim;
                    const date = document.createElement('span');
                    date.className = 'text-[10px] text-slate-500';
                    date.textContent = b.tanggal_pesan || b.created_at || '-';
                    metadata.append(sender, date);
                    reply.appendChild(metadata);

                    const description = document.createElement('p');
                    description.className = 'text-sm text-slate-700';
                    description.textContent = b.deskripsi || '-';
                    reply.appendChild(description);

                    const attachmentContainer = document.createElement('div');
                    attachmentContainer.className = 'mt-3 flex flex-wrap gap-3';
                    renderAttachments(attachmentContainer, b.lampiran);
                    if (attachmentContainer.childElementCount) {
                        reply.appendChild(attachmentContainer);
                    }

                    threadContainer.appendChild(reply);
                });

            } catch(e) {
                console.error('Gagal load pesan', e);
                document.getElementById('pesan-judul').textContent = 'Terjadi Kesalahan';
            }
        }
        
        loadDetailPesan();

        // FORM LOGIC
        const btnToggle = document.getElementById('btn-toggle-balas');
        const formBalasan = document.getElementById('form-balasan');
        const btnBatal = document.getElementById('btn-batal-balas');
        const pesanInput = document.getElementById('pesanInput');
        const formSubmit = document.getElementById('form-submit-balasan');

        let isFormDirty = false;

        btnToggle.addEventListener('click', () => {
            formBalasan.classList.remove('hidden');
            btnToggle.classList.add('hidden');
        });

        btnBatal.addEventListener('click', () => {
            if (pesanInput && pesanInput.value.trim() !== '') {
                const konfirmasi = confirm("Perubahan belum disimpan. Yakin ingin membatalkan?");
                if (!konfirmasi) return;
            }
            formBalasan.classList.add('hidden');
            btnToggle.classList.remove('hidden');
            if (pesanInput) pesanInput.value = '';
            isFormDirty = false;
        });

        if (pesanInput) {
            pesanInput.addEventListener('input', function() {
                isFormDirty = this.value.trim().length > 0;
            });
        }

        formSubmit.addEventListener('submit', async function(e) {
            e.preventDefault();
            const token = sessionStorage.getItem('staff_token');
            if (!token) return alert('Silakan login terlebih dahulu');
            
            const isiBalasan = pesanInput.value.trim();
            if (!isiBalasan) return;

            try {
                const response = await fetch('/api/staff/pesan/' + pesanId + '/balas', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ deskripsi: isiBalasan })
                });

                const data = await response.json();

                if (response.ok) {
                    isFormDirty = false;
                    alert('Balasan berhasil dikirim!');
                    window.location.reload();
                } else {
                    alert(data.message || 'Gagal membalas pesan.');
                }
            } catch (err) {
                alert('Terjadi kesalahan jaringan.');
            }
        });

        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (link && isFormDirty) {
                if (link.getAttribute('target') === '_blank' || link.getAttribute('href')?.startsWith('#')) return;
                const konfirmasi = confirm("Perubahan belum disimpan. Yakin ingin meninggalkan halaman ini?");
                if (!konfirmasi) {
                    e.preventDefault(); 
                } else {
                    isFormDirty = false;
                }
            }
        });

        window.addEventListener('beforeunload', function(e) {
            if (isFormDirty) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>
</body>
</html>
