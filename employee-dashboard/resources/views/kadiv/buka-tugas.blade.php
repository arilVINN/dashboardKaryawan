<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buka Tugas - PT SILINDO</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-white">

<div class="min-h-screen flex overflow-hidden">

    {{-- SIDEBAR KADIV --}}
    @include('component_kadiv.sidebar')

    {{-- MAIN AREA --}}
    <div class="flex-1 min-w-0 min-h-screen flex flex-col bg-white">

        {{-- TOPBAR KADIV --}}
        @include('component_kadiv.topbar')

        {{-- CONTENT --}}
        <main class="flex-1 min-h-0 overflow-y-auto">
            <div class="w-full px-6 sm:px-8 lg:px-8 xl:px-10 py-6">

                {{-- TUGAS 1 --}}
                <section>
                    <h1 class="text-black font-bold text-[28px] leading-[36px]">Tugas 1</h1>

                    <h2 class="mt-6 text-black font-bold text-[28px] leading-[36px]">lorem ipsum</h2>

                    <p class="text-black font-bold text-[16px] leading-[24px]">tenggat : 21 sep 2026, 16.00</p>
                    <p class="text-black font-bold text-[16px] leading-[24px]">status : on going</p>

                    <p class="mt-10 text-black text-[16px] leading-[24px]">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                        Quisque pharetra ut lectus vel luctus. Aenean pellentesque
                        sapien placerat justo tincidunt, sit amet laoreet lectus
                        dapibus. Etiam fermentum erat faucibus, auctor nisi vitae,
                        aliquet quam. Cras eget lacus et mauris gravida aliquet.
                        Proin auctor arcu nec dapibus accumsan. Quisque nec mauris
                        leo. Pellentesque eu pellentesque arcu, ac varius diam.
                        Phasellus a libero sem. Pellentesque placerat at odio eu
                        tempor. Ut non eros tortor. Aenean tincidunt sit amet risus
                        vel imperdiet. Vestibulum posuere facilisis urna, quis
                        pulvinar nisl porttitor ut. Ut sollicitudin ullamcorper eros.
                    </p>

                    {{-- FILE DOCX --}}
                    <div class="mt-6 w-[299px] h-[62px] max-w-full bg-white rounded-[10px] border-[0.3px] border-black shadow-[0px_4px_4px_0px_rgba(0,0,0,0.3)] flex items-center relative overflow-hidden">
                        <div class="pl-7 flex flex-col">
                            <span class="text-black text-[16px] leading-[24px] font-bold underline">Tugas 1 Divisi Writer</span>
                            <span class="text-black/40 text-[16px] leading-[24px] font-bold">docx</span>
                        </div>

                        <div class="absolute right-5 top-1/2 -translate-y-1/2 w-9 h-9 rounded bg-[#146C94] flex items-center justify-center text-white font-bold text-xs">
                            W
                        </div>
                    </div>
                </section>

                {{-- GARIS PEMISAH --}}
                <div class="mt-10 border-t border-black"></div>

                {{-- TUGAS KEDUA --}}
                <section class="pt-4">
                    <h2 class="text-black font-bold text-[24px] leading-[32px]">tugas1</h2>

                    <p class="text-black font-bold text-[16px] leading-[24px]">tenggat : 21 sep 2026, 16.00</p>
                    <p class="text-black font-bold text-[16px] leading-[24px]">status : on going</p>

                    <div class="mt-5 grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_299px] gap-6 items-start">

                        {{-- KIRI --}}
                        <div>
                            <p class="text-black text-[16px] leading-[24px]">buk ini untuk tugas kemarin</p>

                            {{-- Tombol --}}
                            <div class="mt-16 flex items-center gap-5">
                                <button type="button"
                                        class="w-[148px] h-[36px] rounded-[9px] bg-[#D22B2B] flex items-center justify-center text-white text-[16px] leading-[24px] hover:bg-[#b92323] transition">
                                    Revisi
                                </button>

                                <button type="button"
                                        class="w-[148.4px] h-[36px] rounded-[9px] bg-[#0E9DC3] flex items-center justify-center text-white text-[16px] leading-[24px] hover:bg-[#0c8eaf] transition">
                                    Acc
                                </button>
                            </div>
                        </div>

                        {{-- FILE PDF --}}
                        <div class="w-[299px] h-[62px] max-w-full bg-white rounded-[10px] border-[0.3px] border-black shadow-[0px_4px_4px_0px_rgba(0,0,0,0.3)] flex items-center relative overflow-hidden lg:justify-self-end">
                            <div class="pl-7 flex flex-col">
                                <span class="text-black text-[16px] leading-[24px] font-bold underline">Tugas 1 Divisi Writer</span>
                                <span class="text-black/40 text-[16px] leading-[24px] font-bold">Pdf</span>
                            </div>

                            <div class="absolute right-5 top-1/2 -translate-y-1/2 w-9 h-9 rounded bg-[#D22B2B] flex items-center justify-center text-white font-bold text-[9px]">
                                PDF
                            </div>
                        </div>

                    </div>
                </section>

            </div>
        </main>

    </div>

</div>

</body>
</html>