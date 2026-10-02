@php
    $detailTugas = [
        'judul' => 'lorem ipsum',
        'tenggat' => '21 sep 2026, 16.00',
        'status' => 'on going',
        'deskripsi' =>
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque pharetra ut lectus vel luctus. Aenean pellentesque sapien placerat justo tincidunt, sit amet laoreet lectus dapibus. Etiam fermentum erat faucibus, auctor nisi vitae, aliquet quam. Cras eget lacus et mauris gravida aliquet. Proin auctor arcu nec dapibus accumsan. Quisque nec mauris leo. Pellentesque eu pellentesque arcu, ac varius diam. Phasellus a libero sem. Pellentesque placerat at odio eu tempor. Ut non eros tortor. Aenean tincidunt sit amet risus vel imperdiet. Vestibulum posuere facilisis urna, quis pulvinar nisl porttitor ut. Ut sollicitudin ullamcorper eros.',
        'lampiran' => [
            [
                'nama' => 'Tugas 1 Divisi Writer',
                'tipe' => 'Pdf',
                'format' => 'pdf',
                'url' => '#',
            ],
        ],
    ];
@endphp

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
                    <h2 class="text-2xl font-bold text-slate-900 leading-snug">{{ $detailTugas['judul'] }}</h2>
                    <p class="text-sm font-semibold text-slate-900 mt-1">tenggat : {{ $detailTugas['tenggat'] }}</p>
                    <p class="text-sm font-semibold text-slate-900">status : {{ $detailTugas['status'] }}</p>
                </div>

                <div class="text-sm text-slate-800 leading-relaxed text-justify">
                    <p>{{ $detailTugas['deskripsi'] }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-6 pt-2">
                    @foreach ($detailTugas['lampiran'] as $file)
                        <a href="{{ $file['url'] }}"
                            class="flex items-center justify-between bg-white border border-slate-300 rounded-xl px-5 py-3 shadow-sm hover:shadow-md transition w-72">
                            <div>
                                <h3 class="font-bold text-sm text-slate-900 underline">{{ $file['nama'] }}</h3>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $file['tipe'] }}</p>
                            </div>

                            <div class="pl-4 border-l border-slate-200">
                                @if ($file['format'] === 'pdf')
                                    <div
                                        class="px-1.5 py-0.5 border-2 border-red-600 rounded text-[11px] font-extrabold text-red-600 tracking-tighter">
                                        PDF
                                    </div>
                                @else
                                    <div
                                        class="w-6 h-6 bg-[#185abd] text-white flex items-center justify-center rounded font-bold text-xs">
                                        Docx
                                    </div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>

                <div>


                    <hr class="border-t border-slate-300 my-6">

                    <button type="button" id="btn-toggle-tugas"
                        class="mt-6 px-6 py-2.5 bg-[#0097B2] hover:bg-[#008199] text-white text-sm font-semibold rounded-lg shadow-sm transition">
                        Kerjakan Tugas
                    </button>

                    <div id="form-tugas" class="hidden mt-6 space-y-4 max-w-xl transition-all">
                        <h2 class="text-xl font-bold text-slate-900">Submit Tugas</h2>

                        <form action="#" method="GET" class="space-y-4">
                            @csrf

                            <div>
                                <input type="file" id="file_tugas" name="file_tugas"
                                    class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-700 bg-white focus:outline-none focus:ring-1 focus:ring-cyan-500">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-900 mb-1.5">Deskripsi</label>
                                <input type="text" id="deskripsi_tugas" name="deskripsi_tugas" placeholder="deskripsi"
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
                    const btnToggleTugas = document.getElementById('btn-toggle-tugas');
                    const formTugas = document.getElementById('form-tugas');
                    const btnBatalTugas = document.getElementById('btn-batal-tugas');
                    const teksStatus = document.getElementById('teks-status'); 

                    // Ambil elemen input untuk fitur keamanan (safeguard)
                    const fileInput = document.getElementById('file_tugas');
                    const deskripsiInput = document.getElementById('deskripsi_tugas');
                    const formSubmit = formTugas.querySelector('form');

                    let isFormDirty = false;

                    const checkFormStatus = () => {
                        const hasFile = fileInput && fileInput.files.length > 0;
                        const hasDeskripsi = deskripsiInput && deskripsiInput.value.trim().length > 0;
                        isFormDirty = hasFile || hasDeskripsi;
                    };

                    if (fileInput) fileInput.addEventListener('change', checkFormStatus);
                    if (deskripsiInput) deskripsiInput.addEventListener('input', checkFormStatus);

                    // 1. Buka form dan ubah warna status
                    btnToggleTugas.addEventListener('click', () => {
                        formTugas.classList.remove('hidden');
                        btnToggleTugas.classList.add('hidden');

                        if (teksStatus) {
                            teksStatus.innerText = 'status : on going';
                            teksStatus.classList.add('text-[#0097B2]');
                        }
                    });

                    btnBatalTugas.addEventListener('click', () => {
                        if (isFormDirty) {
                            const konfirmasi = confirm("Perubahan belum disimpan. Yakin ingin membatalkan?");
                            if (!konfirmasi) return; // Jika user memilih 'Cancel', batal menutup form
                        }

                        formTugas.classList.add('hidden');
                        btnToggleTugas.classList.remove('hidden');

                        if (teksStatus) {
                            teksStatus.innerText = 'status : belum mulai'; // Kembalikan teks awal
                            teksStatus.classList.remove('text-[#0097B2]');
                        }

                        if (fileInput) fileInput.value = '';
                        if (deskripsiInput) deskripsiInput.value = '';
                        isFormDirty = false;
                    });

                    document.addEventListener('click', function(e) {
                        const link = e.target.closest('a');
                        if (link && isFormDirty) {
                            if (link.getAttribute('target') === '_blank' || link.getAttribute('href')?.startsWith('#')) return;

                            const konfirmasi = confirm("Perubahan belum disimpan. Yakin ingin meninggalkan halaman ini?");
                            if (!konfirmasi) {
                                e.preventDefault(); // Batalkan perpindahan halaman
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

                    if (formSubmit) {
                        formSubmit.addEventListener('submit', function() {
                            isFormDirty = false;
                        });
                    }
                    
                </script>
            </div>
        </main>

    </div>

</body>

</html>
