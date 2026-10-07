<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pesan->judul_pesan }} - Pesan HRD</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">
    @include('component_hrd.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')
        @include('component.breadcrumbs')

        <main class="flex-1 overflow-y-auto px-6 py-8 lg:px-10">
            <a href="{{ route('hrd.pesan') }}" class="text-sm font-medium text-cyan-700 hover:underline">
                &larr; Kembali ke daftar pesan
            </a>

            <h1 class="text-2xl font-bold text-slate-900 mt-5 mb-6">Detail Pesan</h1>

            <section class="space-y-5 bg-white p-5 sm:p-7 rounded-2xl shadow-lg">
                <header class="border-b border-slate-200 pb-4">
                    <div class="flex flex-wrap gap-2 items-center mb-2">
                        <span class="px-2.5 py-1 bg-cyan-50 text-cyan-700 rounded-full text-xs font-bold">
                            {{ ucfirst($pesan->tipe ?? 'pesan') }}
                        </span>
                        <span id="pesan-tanggal" class="text-xs text-slate-500">{{ $pesan->tanggal_pesan }}</span>
                    </div>
                    <h2 id="pesan-judul" class="text-2xl font-bold text-slate-900">{{ $pesan->judul_pesan }}</h2>
                    <p id="pesan-pengirim" class="text-sm text-slate-600 mt-2">
                        Dari {{ $pesan->pengirim?->karyawan?->nama ?? $pesan->pengirim?->username ?? 'Pengguna tidak tersedia' }}
                        kepada {{ $pesan->penerima?->karyawan?->nama ?? $pesan->penerima?->username ?? 'Pengguna tidak tersedia' }}
                    </p>
                </header>

                @if ($pesan->tugas)
                    <p class="text-sm text-slate-600">
                        Terkait tugas:
                        <a href="{{ url('/kadiv/tugas/' . $pesan->tugas->id_tugas) }}"
                            class="text-cyan-700 hover:underline">
                            {{ $pesan->tugas->judul_tugas }}
                        </a>
                    </p>
                @endif

                <div id="pesan-lampiran" class="hidden"></div>

                @foreach ($thread as $item)
                    <article class="rounded-xl border border-slate-200 p-4 {{ $item->id_pesan === $pesan->id_pesan ? 'bg-cyan-50/50' : 'bg-slate-50' }}">
                        <div class="flex flex-wrap justify-between gap-2 text-xs text-slate-500 mb-2">
                            <span>{{ $item->pengirim?->karyawan?->nama ?? $item->pengirim?->username ?? 'Pengguna tidak tersedia' }}</span>
                            <time>{{ $item->tanggal_pesan }}</time>
                        </div>
                        <p class="text-sm text-slate-800 whitespace-pre-wrap break-words">{{ $item->deskripsi }}</p>

                        @if ($item->link_lampiran)
                            <p class="mt-3 text-sm">
                                <a href="{{ $item->link_lampiran }}" target="_blank" rel="noopener noreferrer"
                                    class="text-cyan-700 hover:underline">Buka tautan lampiran</a>
                            </p>
                        @endif
                        @if ($item->file_lampiran)
                            <p class="mt-3 text-sm">
                                <a href="{{ Storage::disk('public')->url($item->file_lampiran) }}" target="_blank"
                                    rel="noopener noreferrer" class="text-cyan-700 hover:underline">Unduh lampiran</a>
                            </p>
                        @endif
                    </article>
                @endforeach
            </section>

            @if ($canReply)
                <section class="mt-6 max-w-3xl">
                    <h2 class="text-xl font-bold text-slate-900 mb-3">Balas Pesan</h2>
                    <form id="formBalasPesan" action="{{ route('hrd.detailPesan.balas', $pesan->id_pesan) }}"
                        method="POST" class="space-y-3">
                        @csrf
                        <label for="deskripsiBalasan" class="sr-only">Isi balasan</label>
                        <textarea id="deskripsiBalasan" name="deskripsi" rows="4" maxlength="10000" required
                            placeholder="Tulis balasan..."
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"></textarea>
                        <button type="submit" id="btnBalasPesan"
                            class="px-6 py-2.5 bg-[#0097B2] hover:bg-[#008199] text-white text-sm font-semibold rounded-lg shadow-sm transition">
                            Kirim Balasan
                        </button>
                        <p id="errorBalasPesan" class="hidden text-sm text-red-600" role="alert"></p>
                    </form>
                </section>
            @else
                <p class="mt-5 text-sm text-slate-500">Surat ini tidak dapat dibalas melalui sistem.</p>
            @endif
        </main>
    </div>

    @if ($canReply)
        <script>
            const form = document.getElementById('formBalasPesan');
            const button = document.getElementById('btnBalasPesan');
            const error = document.getElementById('errorBalasPesan');

            if (form && button && error) {
                form.addEventListener('submit', async function(event) {
                    event.preventDefault();
                    const formData = new FormData(form);
                    button.disabled = true;
                    error.classList.add('hidden');

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                            },
                            body: formData
                        });
                        const result = await response.json();

                        if (!response.ok) {
                            const validationErrors = result.errors ? Object.values(result.errors).flat().join('\n') : '';
                            throw new Error(validationErrors || result.message || 'Balasan gagal dikirim.');
                        }

                        window.location.reload();
                    } catch (exception) {
                        error.textContent = exception.message || 'Terjadi kesalahan saat mengirim balasan.';
                        error.classList.remove('hidden');
                    } finally {
                        button.disabled = false;
                    }
                });
            }

            const messageId = window.location.pathname.split('/').filter(Boolean).pop();
            const token = sessionStorage.getItem('staff_token');
            const btnToggle = document.getElementById('btn-toggle-balas');
            const formBalasan = document.getElementById('form-balasan');
            const formSubmit = document.getElementById('form-submit-balasan');
            const pesanInput = document.getElementById('pesanInput');
            const lampiranInput = document.getElementById('lampiranBalas');

            if (btnToggle && formBalasan && formSubmit) {
                btnToggle.addEventListener('click', () => {
                    formBalasan.classList.toggle('hidden');
                });

                let isFormDirty = false;
                const updateDirtyState = () => {
                    isFormDirty = (pesanInput?.value.trim().length || 0) > 0 || (lampiranInput?.files.length || 0) > 0;
                };

                pesanInput?.addEventListener('input', updateDirtyState);
                lampiranInput?.addEventListener('change', () => {
                    const file = lampiranInput.files[0];
                    if (file && file.size > 20 * 1024 * 1024) {
                        alert('Ukuran file maksimal 20 MB');
                        lampiranInput.value = '';
                    }
                    updateDirtyState();
                });
                formSubmit.addEventListener('reset', () => {
                    isFormDirty = false;
                });

                formSubmit.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const file = lampiranInput?.files[0];
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
                        window.location.reload();
                    } catch (submitError) {
                        alert(submitError.message || 'Terjadi kesalahan saat mengirim balasan.');
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
            }
        </script>
    @endif

</body>

</html>
