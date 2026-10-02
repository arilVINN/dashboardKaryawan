@php
    $detailTugas = [
        'judul' => 'lorem ipsum',
        'tenggat' => '21 sep 2026, 16.00',
        'status' => 'on going',
        'deskripsi' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque pharetra ut lectus vel luctus. Aenean pellentesque sapien placerat justo tincidunt, sit amet laoreet lectus dapibus. Etiam fermentum erat faucibus, auctor nisi vitae, aliquet quam. Cras eget lacus et mauris gravida aliquet. Proin auctor arcu nec dapibus accumsan. Quisque nec mauris leo. Pellentesque eu pellentesque arcu, ac varius diam. Phasellus a libero sem. Pellentesque placerat at odio eu tempor. Ut non eros tortor. Aenean tincidunt sit amet risus vel imperdiet. Vestibulum posuere facilisis urna, quis pulvinar nisl porttitor ut. Ut sollicitudin ullamcorper eros.',
        'lampiran' => [
            [
                'nama' => 'Tugas 1 Divisi Writer',
                'tipe' => 'Pdf',
                'format' => 'pdf',
                'url' => '#',
            ],
            [
                'nama' => 'Tugas 1 Divisi Writer',
                'tipe' => 'docx',
                'format' => 'docx',
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
                                    <div class="px-1.5 py-0.5 border-2 border-red-600 rounded text-[11px] font-extrabold text-red-600 tracking-tighter">
                                        PDF
                                    </div>
                                @else
                                    <div class="w-6 h-6 bg-[#185abd] text-white flex items-center justify-center rounded font-bold text-xs">
                                        Docx
                                    </div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
                
                <hr class="border-t border-slate-300 my-8">
                <div>

                    <h2 class="text-xl font-bold text-slate-900 mb-4">Submit Tugas</h2>

                    <form action="#" method="POST" enctype="multipart/form-data" class="space-y-4 max-w-xl">
                        @csrf

                        <div>
                            <input type="file"
                                class="w-full text-sm text-slate-500 border border-slate-300 rounded-lg cursor-pointer bg-white file:mr-4 file:py-2.5 file:px-4 file:rounded-l-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-900 mb-1.5">Deskripsi</label>
                            <input type="text" placeholder="deskripsi"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                        </div>

                        <div class="flex items-center gap-4 pt-2">
                            <button type="reset"
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
        </main>

    </div>

</body>

</html>