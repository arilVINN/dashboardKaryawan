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
        @include('component.breadcrumbs')

        <main class="flex-1 overflow-y-auto px-10 py-8">
            @php
                $semuaBalasan = collect(session('balasan_pesan', []))->where('id_pesan', (int) $id)->values();
            @endphp

            <h1 class="text-2xl font-bold text-slate-900 mb-6">Pesan</h1>

            @if (session('success'))
                <div class="mb-4 px-4 py-2 rounded-lg bg-green-100 text-green-700 text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            <div class="space-y-4 bg-white p-5 rounded-3xl w-full drop-shadow-2xl">

                {{-- PESAN ASLI --}}
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 leading-snug">{{ $pesan['pengirim'] }}</h2>
                    <p class="text-sm font-semibold text-slate-900 mt-1">tanggal : {{ $pesan['tanggal'] }}</p>
                </div>

                <div class="text-sm text-slate-800 leading-relaxed text-justify pt-2">
                    <p>{{ $pesan['isi'] }}</p>
                </div>

                {{-- RIWAYAT BALASAN --}}
                @if ($semuaBalasan->count() > 0)
                    <hr class="border-t border-slate-300 my-6">

                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900">Riwayat Balasan</h3>
                        <span class="text-sm text-slate-500">{{ $semuaBalasan->count() }} balasan</span>
                    </div>

                    <div class="max-h-[420px] overflow-y-auto pr-2 space-y-5">
                        @foreach ($semuaBalasan as $b)
                            <div>
                                <h4 class="text-base font-bold text-slate-900">Balasan ke-{{ $loop->iteration }}</h4>
                                <p class="text-xs text-slate-500">{{ $b['waktu'] }}</p>

                                <div class="mt-2 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
                                    <p class="text-sm text-slate-800 leading-relaxed">{{ $b['isi'] }}</p>

                                    @if ($b['file'])
                                        <div
                                            class="w-[260px] shrink-0 bg-white rounded-[10px] border border-black shadow-[0px_4px_4px_0px_rgba(0,0,0,0.3)] px-5 py-2">
                                            <p class="text-sm font-bold text-black underline truncate">
                                                {{ $b['file'] }}</p>
                                            <p class="text-xs font-bold text-black/40">Lampiran</p>
                                        </div>
                                    @endif
                                </div>

                                @if (!$loop->last)
                                    <div class="mt-5 border-t border-dashed border-slate-300"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

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

                <form id="formPesan" action="{{ route('kadiv.balasPesan', $id) }}" method="POST"
                    enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-1.5">Pesan</label>
                        <textarea name="isi_balasan" id="pesanInput" rows="4" required placeholder="Tulis balasan pesan..."
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-cyan-500 resize-none">{{ old('isi_balasan') }}</textarea>
                        @error('isi_balasan')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-1.5">Lampiran (opsional)</label>
                        <input type="file" name="lampiran" id="lampiranBalas"
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
        let isFormDirty = false;

        function bukaForm() {
            formBalasan.classList.remove('hidden');
            btnToggle.classList.add('hidden');
        }

        btnToggle.addEventListener('click', bukaForm);

        // kalau validasi gagal, form tetap terbuka
        @if ($errors->any())
            bukaForm();
            isFormDirty = true;
        @endif

        pesanInput.addEventListener('input', function() {
            isFormDirty = this.value.trim().length > 0;
        });

        document.getElementById('btnReset').addEventListener('click', () => {
            isFormDirty = false;
        });

        // validasi ukuran file 20 MB
        document.getElementById('lampiranBalas').addEventListener('change', function() {
            const f = this.files[0];
            if (f && f.size > 20 * 1024 * 1024) {
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

        formPesan.addEventListener('submit', function() {
            isFormDirty = false;
        });
    </script>

</body>

</html>
