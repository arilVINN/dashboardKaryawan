<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Buka Tugas</title>

    @vite('resources/css/app.css')
</head>

<body class="bg-white">

<div class="min-h-screen flex overflow-hidden">

    {{-- =========================================================
        SIDEBAR
    ========================================================== --}}
    <aside class="w-64 bg-gradient-l text-slate-300 flex flex-col h-screen border-r border-whiite-30% ">

        <div class="bg-gradient-to-b from-[#044564] from-50% to-[#19A7CE] min-h-screen">

            <div class="flex items-center justify-between p-4 bg-white rounded-bl-xl">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('gambar/silindo.png') }}" alt="Logo" class="w-12 h-12 object-contain shrink-0">

                    <div class="flex flex-col">
                        <h1 class="text-xl font-bold text-[#2A4B6A] leading-tight">
                            PT SILINDO
                        </h1>

                        <p class="text-[11px] font-small text-[#1CA4BA] tracking-tight leading-tight">
                            PT SINERGI ILMIAH INDONESIA
                        </p>
                    </div>
                </div>

                <button class="p-2 text-[#2A4B6A] hover:bg-gray-100 rounded-md">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2.5"
                            d="M4 6h16M4 12h16M4 18h16">
                        </path>
                    </svg>
                </button>
            </div>

            <nav class="flex-1 p-4 pt-8 space-y-3 text-sm">

                <a href="#"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg bg-white/40 text-white font-medium transition">

                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 20v-6h4v6h5v-8h3L12 3L2 12h3v8z">
                        </path>
                    </svg>

                    Dashboard
                </a>

                <a href="#"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-white/40 hover:text-white transition">

                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13 5h8m-8 7h8m-8 7h8M3 17l2 2l4-4"/>
                        <rect width="6" height="6" x="3" y="4" rx="1">
                        </rect>
                    </svg>

                    Tugas
                </a>

                <a href="#"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-white/40 hover:text-white transition">

                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2m-3 12H7c-.55 0-1-.45-1-1s.45-1 1-1h10c.55 0 1-.45 1-1s-.45-1-1-1m0-3H7c-.55 0-1-.45-1-1s.45-1-1-1h10c.55 0 1-.45 1-1">
                        </path>
                    </svg>

                    Pesan
                </a>

            </nav>

            <div class="p-4 border-t border-white mt-auto">

                <div class="flex items-center gap-3 mb-3 px-2 bottom-0 mt-80">

                    <div class="w-9 h-9 rounded-full bg-blue-500 flex items-center justify-center font-bold text-white">
                        S
                    </div>

                    <div class="overflow-hidden">
                        <p class="text-sm font-medium text-white">
                            Nama Pengguna
                        </p>

                        <p class="text-xs text-white-100 capitalize">
                            Role: Kadiv / HRD
                        </p>
                    </div>

                </div>

                <form action="#" method="POST">
                    @csrf

                    <button
                        type="submit"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2 rounded-lg outline-1 bg-white text-red-400 hover:bg-red-500 hover:text-white text-sm font-medium transition">

                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>

                        Keluar
                    </button>
                </form>

            </div>

        </div>
    </aside>


    {{-- =========================================================
        MAIN AREA
    ========================================================== --}}
    <div class="flex-1 min-w-0 min-h-screen flex flex-col bg-white">


        {{-- =====================================================
            HEADER
        ====================================================== --}}
        <header
            class="h-[79px] shrink-0
                   bg-white
                   shadow-[0px_4px_4px_0px_rgba(0,0,0,0.2)]
                   flex items-center
                   px-6 lg:px-8 xl:px-10
                   gap-6">

            {{-- Search --}}
            <div
                class="w-full max-w-[693px]
                       h-[51px]
                       rounded-lg
                       border border-[rgba(148,163,184,0.5)]
                       px-2
                       flex items-center gap-[6px]">

                <svg
                    class="w-[22px] h-[22px] shrink-0 text-[#64748B]"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24">

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0a7 7 0 0 1 14 0z">
                    </path>

                </svg>

                <span class="text-[#64748B] text-[14px] leading-[22px]">
                    Cari
                </span>

            </div>


            {{-- Notification + Profile --}}
            <div class="ml-auto flex items-center gap-6 shrink-0">

                <button
                    type="button"
                    class="w-10 h-10 flex items-center justify-center">

                    <svg
                        class="w-[30px] h-[30px]"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24">

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4">
                        </path>

                    </svg>

                </button>


                <button
                    type="button"
                    class="w-10 h-10 rounded-full bg-[#D9D9D9]
                           flex items-center justify-center">

                    <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24">

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="m9 18 6-6-6-6">
                        </path>

                    </svg>

                </button>

            </div>

        </header>


        {{-- =====================================================
            CONTENT
        ====================================================== --}}
        <main class="flex-1 min-h-0 overflow-y-auto">

            <div
                class="w-full
                       px-6 sm:px-8 lg:px-8 xl:px-10
                       py-6">


                {{-- =================================================
                    TUGAS 1
                ================================================== --}}
                <section>

                    {{-- Judul --}}
                    <h1
                        class="text-black
                               font-bold
                               text-[28px]
                               leading-[36px]">

                        Tugas 1

                    </h1>


                    {{-- Nama tugas --}}
                    <h2
                        class="mt-6
                               text-black
                               font-bold
                               text-[28px]
                               leading-[36px]">

                        lorem ipsum

                    </h2>


                    {{-- Tenggat --}}
                    <p
                        class="text-black
                               font-bold
                               text-[16px]
                               leading-[24px]">

                        tenggat : 21 sep 2026, 16.00

                    </p>


                    {{-- Status --}}
                    <p
                        class="text-black
                               font-bold
                               text-[16px]
                               leading-[24px]">

                        status : on going

                    </p>


                    {{-- Deskripsi --}}
                    <p
                        class="mt-10
                               text-black
                               text-[16px]
                               leading-[24px]">

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


                    {{-- =================================================
                        FILE DOCX
                    ================================================== --}}
                    <div
                        class="mt-6
                               w-[299px]
                               h-[62px]
                               max-w-full
                               bg-white
                               rounded-[10px]
                               border-[0.3px]
                               border-black
                               shadow-[0px_4px_4px_0px_rgba(0,0,0,0.3)]
                               flex items-center
                               relative
                               overflow-hidden">

                        <div class="pl-7 flex flex-col">

                            <span
                                class="text-black
                                       text-[16px]
                                       leading-[24px]
                                       font-bold
                                       underline">

                                Tugas 1 Divisi Writer

                            </span>

                            <span
                                class="text-black/40
                                       text-[16px]
                                       leading-[24px]
                                       font-bold">

                                docx

                            </span>

                        </div>


                        {{-- Microsoft Word --}}
                        <div
                            class="absolute
                                   right-5
                                   top-1/2
                                   -translate-y-1/2
                                   w-9
                                   h-9
                                   rounded
                                   bg-[#146C94]
                                   flex
                                   items-center
                                   justify-center
                                   text-white
                                   font-bold
                                   text-xs">

                            W

                        </div>

                    </div>

                </section>


                {{-- =================================================
                    GARIS PEMISAH
                ================================================== --}}
                <div class="mt-10 border-t border-black"></div>


                {{-- =================================================
                    TUGAS KEDUA
                ================================================== --}}
                <section class="pt-4">

                    {{-- Judul --}}
                    <h2
                        class="text-black
                               font-bold
                               text-[24px]
                               leading-[32px]">

                        tugas1

                    </h2>


                    {{-- Tenggat --}}
                    <p
                        class="text-black
                               font-bold
                               text-[16px]
                               leading-[24px]">

                        tenggat : 21 sep 2026, 16.00

                    </p>


                    {{-- Status --}}
                    <p
                        class="text-black
                               font-bold
                               text-[16px]
                               leading-[24px]">

                        status : on going

                    </p>


                    {{-- =================================================
                        KONTEN + PDF
                    ================================================== --}}
                    <div
                        class="mt-5
                               grid
                               grid-cols-1
                               lg:grid-cols-[minmax(0,1fr)_299px]
                               gap-6
                               items-start">


                        {{-- KIRI --}}
                        <div>

                            <p
                                class="text-black
                                       text-[16px]
                                       leading-[24px]">

                                buk ini untuk tugas kemarin

                            </p>


                            {{-- Tombol --}}
                            <div
                                class="mt-16
                                       flex
                                       items-center
                                       gap-5">

                                {{-- Revisi --}}
                                <button
                                    type="button"
                                    class="w-[148px]
                                           h-[36px]
                                           rounded-[9px]
                                           bg-[#D22B2B]
                                           flex
                                           items-center
                                           justify-center
                                           text-white
                                           text-[16px]
                                           leading-[24px]
                                           hover:bg-[#b92323]
                                           transition">

                                    Revisi

                                </button>


                                {{-- Acc --}}
                                <button
                                    type="button"
                                    class="w-[148.4px]
                                           h-[36px]
                                           rounded-[9px]
                                           bg-[#0E9DC3]
                                           flex
                                           items-center
                                           justify-center
                                           text-white
                                           text-[16px]
                                           leading-[24px]
                                           hover:bg-[#0c8eaf]
                                           transition">

                                    Acc

                                </button>

                            </div>

                        </div>


                        {{-- =================================================
                            FILE PDF
                        ================================================== --}}
                        <div
                            class="w-[299px]
                                   h-[62px]
                                   max-w-full
                                   bg-white
                                   rounded-[10px]
                                   border-[0.3px]
                                   border-black
                                   shadow-[0px_4px_4px_0px_rgba(0,0,0,0.3)]
                                   flex
                                   items-center
                                   relative
                                   overflow-hidden
                                   lg:justify-self-end">

                            <div class="pl-7 flex flex-col">

                                <span
                                    class="text-black
                                           text-[16px]
                                           leading-[24px]
                                           font-bold
                                           underline">

                                    Tugas 1 Divisi Writer

                                </span>

                                <span
                                    class="text-black/40
                                           text-[16px]
                                           leading-[24px]
                                           font-bold">

                                    Pdf

                                </span>

                            </div>


                            {{-- PDF --}}
                            <div
                                class="absolute
                                       right-5
                                       top-1/2
                                       -translate-y-1/2
                                       w-9
                                       h-9
                                       rounded
                                       bg-[#D22B2B]
                                       flex
                                       items-center
                                       justify-center
                                       text-white
                                       font-bold
                                       text-[9px]">

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