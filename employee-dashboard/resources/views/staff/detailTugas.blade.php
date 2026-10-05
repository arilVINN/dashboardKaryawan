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
        @include('component.breadcrumbs')

        <main class="flex-1 overflow-y-auto px-10 py-8">
            <h1 class="text-2xl font-bold text-slate-900 mb-6">Tugas</h1>

            <div class="max-w-4xl space-y-6">
                <div>
                    <h2 id="tugas-judul" class="text-2xl font-bold text-slate-900 leading-snug">Memuat...</h2>
                    <p id="tugas-tenggat" class="text-sm font-semibold text-slate-900 mt-1">tenggat : -</p>
                    <p id="teks-status" class="text-sm font-semibold text-slate-900">status : -</p>
                </div>

                <div class="text-sm text-slate-800 leading-relaxed text-justify">
                    <p id="tugas-deskripsi">Memuat deskripsi...</p>
                </div>

                <!-- Kontainer untuk melampirkan file dari backend -->
                <div id="lampiran-container" class="flex flex-wrap items-center gap-6 pt-2">
                    <!-- File pendukung akan dimuat di sini -->
                </div>

                <div>
                    <hr class="border-t border-slate-300 my-6">

                    <button type="button" id="btn-toggle-tugas"
                        class="mt-6 px-6 py-2.5 bg-[#0097B2] hover:bg-[#008199] text-white text-sm font-semibold rounded-lg shadow-sm transition">
                        Kerjakan Tugas
                    </button>

                    <div id="form-tugas" class="hidden mt-6 space-y-4 max-w-xl transition-all">
                        <h2 class="text-xl font-bold text-slate-900">Submit Tugas</h2>

                        <form id="form-submit-tugas" class="space-y-4">
                            <div>
                                <input type="file" id="file_tugas" name="file_tugas"
                                    class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-700 bg-white focus:outline-none focus:ring-1 focus:ring-cyan-500">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-900 mb-1.5">Deskripsi / Catatan</label>
                                <input type="text" id="deskripsi_tugas" name="deskripsi_tugas" placeholder="Tambahkan catatan"
                                    class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                            </div>

                            <div class="flex items-center gap-4 pt-1">
                                <button type="button" id="btn-batal-tugas"
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
                </div>

                <script>
                    const tugasId = window.location.pathname.split('/').filter(Boolean).pop();

                    async function loadDetailTugas() {
                        const token = localStorage.getItem('staff_token');
                        if (!token || !tugasId) return;

                        try {
                            const res = await fetch('/api/staff/tugas/' + tugasId, {
                                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                            });
                            const json = await res.json();
                            const t = json.data;

                            document.getElementById('tugas-judul').textContent = t.judul_tugas;
                            
                            let deadline = '-';
                            if (t.deadline) {
                                const d = new Date(t.deadline);
                                deadline = d.getDate() + ' ' + ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'][d.getMonth()] + ' ' + d.getFullYear();
                            }
                            
                            document.getElementById('tugas-tenggat').textContent = 'tenggat : ' + deadline;
                            document.getElementById('teks-status').textContent = 'status : ' + (t.status || 'Baru');
                            document.getElementById('tugas-deskripsi').textContent = t.deskripsi || '-';

                            // Tampilkan lampiran pendukung jika ada
                            const lampiranContainer = document.getElementById('lampiran-container');
                            lampiranContainer.innerHTML = '';
                            if (t.file_pendukung) {
                                // Ekstrak tipe file untuk UI
                                const fileParts = t.file_pendukung.split('.');
                                const ext = fileParts.length > 1 ? fileParts.pop().toUpperCase() : 'FILE';
                                
                                lampiranContainer.innerHTML = `
                                <a href="/storage/${t.file_pendukung}" target="_blank"
                                    class="flex items-center justify-between bg-white border border-slate-300 rounded-xl px-5 py-3 shadow-sm hover:shadow-md transition w-72">
                                    <div class="truncate pr-4">
                                        <h3 class="font-bold text-sm text-slate-900 underline truncate">File Pendukung</h3>
                                        <p class="text-xs text-slate-400 mt-0.5">${ext}</p>
                                    </div>
                                    <div class="pl-4 border-l border-slate-200">
                                        <div class="px-1.5 py-0.5 border-2 border-[#185abd] rounded text-[11px] font-extrabold text-[#185abd] tracking-tighter">
                                            ${ext}
                                        </div>
                                    </div>
                                </a>`;
                            }
                            if (t.link_pendukung) {
                                lampiranContainer.innerHTML += `
                                <a href="${t.link_pendukung}" target="_blank"
                                    class="flex items-center justify-between bg-white border border-slate-300 rounded-xl px-5 py-3 shadow-sm hover:shadow-md transition w-72">
                                    <div class="truncate pr-4">
                                        <h3 class="font-bold text-sm text-slate-900 underline truncate">Link Referensi</h3>
                                        <p class="text-xs text-slate-400 mt-0.5">Tautan Luar</p>
                                    </div>
                                </a>`;
                            }

                            // Nonaktifkan tombol "Kerjakan" jika statusnya sudah di-acc atau menunggu acc
                            const st = (t.status || '').toLowerCase();
                            if (st === 'sudah di-acc' || st === 'menunggu acc' || st === 'menunggu di-acc') {
                                const btnToggle = document.getElementById('btn-toggle-tugas');
                                btnToggle.disabled = true;
                                btnToggle.className = "mt-6 px-6 py-2.5 bg-slate-300 text-slate-500 text-sm font-semibold rounded-lg shadow-none cursor-not-allowed";
                                btnToggle.textContent = 'Menunggu Review / Selesai';
                            }

                        } catch(e) {
                            console.error('Gagal load detail tugas', e);
                            document.getElementById('tugas-judul').textContent = 'Gagal memuat detail tugas';
                        }
                    }
                    loadDetailTugas();


                    // FORM LOGIC
                    const btnToggleTugas = document.getElementById('btn-toggle-tugas');
                    const formTugas = document.getElementById('form-tugas');
                    const btnBatalTugas = document.getElementById('btn-batal-tugas');
                    const teksStatus = document.getElementById('teks-status'); 

                    const fileInput = document.getElementById('file_tugas');
                    const deskripsiInput = document.getElementById('deskripsi_tugas');
                    const formSubmit = document.getElementById('form-submit-tugas');

                    let isFormDirty = false;

                    const checkFormStatus = () => {
                        const hasFile = fileInput && fileInput.files.length > 0;
                        const hasDeskripsi = deskripsiInput && deskripsiInput.value.trim().length > 0;
                        isFormDirty = hasFile || hasDeskripsi;
                    };

                    if (fileInput) fileInput.addEventListener('change', checkFormStatus);
                    if (deskripsiInput) deskripsiInput.addEventListener('input', checkFormStatus);

                    btnToggleTugas.addEventListener('click', () => {
                        formTugas.classList.remove('hidden');
                        btnToggleTugas.classList.add('hidden');
                        if (teksStatus && !teksStatus.innerText.includes('sudah di-acc') && !teksStatus.innerText.includes('menunggu')) {
                            teksStatus.innerText = 'status : berjalan';
                            teksStatus.classList.add('text-[#0097B2]');
                        }
                    });

                    btnBatalTugas.addEventListener('click', () => {
                        if (isFormDirty) {
                            const konfirmasi = confirm("Perubahan belum disimpan. Yakin ingin membatalkan?");
                            if (!konfirmasi) return; 
                        }
                        formTugas.classList.add('hidden');
                        btnToggleTugas.classList.remove('hidden');
                        
                        if (teksStatus) teksStatus.classList.remove('text-[#0097B2]');

                        if (fileInput) fileInput.value = '';
                        if (deskripsiInput) deskripsiInput.value = '';
                        isFormDirty = false;
                        loadDetailTugas(); // reset status text
                    });

                    // KIRIM TUGAS (SUBMIT) API
                    formSubmit.addEventListener('submit', async function(e) {
                        e.preventDefault();
                        const token = localStorage.getItem('staff_token');
                        if (!token) return alert('Sesi habis, silakan login ulang');

                        const formData = new FormData();
                        if (fileInput.files[0]) formData.append('file_hasil', fileInput.files[0]);
                        if (deskripsiInput.value) formData.append('catatan_karyawan', deskripsiInput.value);
                        formData.append('progress', 100); 

                        try {
                            const res = await fetch('/api/staff/tugas/' + tugasId + '/submit', {
                                method: 'POST',
                                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
                                body: formData
                            });
                            const json = await res.json();
                            if (res.ok) {
                                isFormDirty = false;
                                alert('Hasil tugas berhasil dikirim!');
                                window.location.reload();
                            } else {
                                alert(json.message || 'Gagal mengirim hasil tugas');
                            }
                        } catch(error) {
                            alert('Terjadi kesalahan jaringan');
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
            </div>
        </main>

    </div>

</body>
</html>
