<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pesan - PT SILINDO</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white flex h-screen overflow-hidden">

    {{-- SIDEBAR --}}
    @include('component_kadiv.sidebar')

    {{-- AREA KANAN --}}
    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        {{-- TOPBAR --}}
        @include('component_kadiv.topbar')

        {{-- CONTENT --}}
        <main class="flex-1 overflow-y-auto">
            <div class="px-9 pt-7 pb-10 max-w-[1134px]">

                {{-- JUDUL --}}
                <h1 class="text-[28px] leading-[36px] font-bold text-black">Pesan</h1>

                {{-- SUB-JUDUL --}}
                <h2 class="mt-3 text-[28px] leading-[36px] font-bold text-black">lorem ipsum</h2>

                {{-- TENGGAT --}}
                <p class="mt-1 text-[16px] leading-[24px] font-bold text-black">
                    tenggat : 21 sep 2026, 16.00
                </p>

                {{-- STATUS --}}
                <p class="text-[16px] leading-[24px] font-bold text-black">
                    status : on going
                </p>

                {{-- DESKRIPSI --}}
                <p class="mt-4 text-[16px] leading-[24px] text-black max-w-[1072px]">
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque pharetra ut lectus vel luctus.
                    Aenean pellentesque sapien placerat justo tincidunt, sit amet laoreet lectus dapibus.
                    Etiam fermentum erat faucibus, auctor nisi vitae, aliquet quam. Cras eget lacus et mauris
                    gravida aliquet. Proin auctor arcu nec dapibus accumsan. Quisque nec mauris leo.
                </p>

                {{-- LINK --}}
                <div class="mt-5 text-[14px] leading-[22px]">
                    <a href="https://www.figma.com/design/H6V50AsbSvJiabZP2x9onv/"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="text-[#19A7CE] hover:text-[#146C94]">
                        <span class="mr-1">•</span>
                        <span class="underline">Link</span>
                        <span class="ml-1">https://www.figma.com/design/H6V50AsbSvJiabZP2x9onv/</span>
                    </a>
                </div>

                {{-- DIVIDER --}}
                <div class="mt-7 border-t border-black/50"></div>
                {{-- DAFTAR BALASAN --}}
@php
    $semuaBalasan = collect(session('balasan_pesan', []))
        ->where('id_pesan', $id)
        ->values();
@endphp

@if ($semuaBalasan->count() > 0)
    <div class="mt-6 space-y-6">

        @foreach ($semuaBalasan as $balasan)
            <div class="max-w-[1000px]">

                <p class="text-base leading-6 text-[#283044]">
                    {{ $balasan['pengirim'] }}
                </p>

                <p class="text-xs text-[#565E74]">
                    {{ $balasan['waktu'] }}
                </p>

                <p class="mt-1 text-base leading-6 text-[#283044]">
                    {{ $balasan['isi'] }}
                </p>

            </div>
        @endforeach

    </div>
@endif

<div class="mt-6 border-t border-black/50"></div>

                {{-- FORM BALAS --}}
                <form action="{{ route('kadiv.balasPesan', $id) }}" method="POST" class="mt-5">
                    @csrf

                    <div class="w-full max-w-[420px]">
                        <label for="pesan"
                               class="block pb-[6px] text-[16px] leading-[24px] font-bold text-[#1E293B]">
                            Pesan
                        </label>

                        <input type="text"
                               id="pesan"
                               name="pesan"
                               placeholder="Ketik pesan anda"
                               class="w-full h-[37px] rounded-lg border border-[#CBD5E1] bg-white px-[11px] text-sm text-[#283044] placeholder:text-[#94A3B8] outline-none focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]">
                    </div>

                    {{-- TOMBOL --}}
                    <div class="flex items-center gap-7 mt-16">

                        <button type="button"
                                onclick="history.back()"
                                class="w-[148px] h-9 rounded-[9px] bg-[#D22B2B] flex items-center justify-center text-white text-[16px] leading-[24px] hover:bg-[#b92323] transition">
                            Batal
                        </button>

                        <button type="submit"
                                class="w-[148px] h-9 rounded-[9px] bg-[#0E9DC3] flex items-center justify-center text-white text-[16px] leading-[24px] hover:bg-[#0c89aa] transition">
                            Kirim
                        </button>

                    </div>

                </form>

            </div>
        </main>

    </div>

</body>
</html>