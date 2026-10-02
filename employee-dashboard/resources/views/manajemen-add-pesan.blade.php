<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Pesan - PT SILINDO</title>

    @vite('resources/css/app.css')
</head>

<body class="bg-white text-[#283044]">

    <div class="min-h-screen flex">

        {{-- =====================================================
            SIDEBAR
        ====================================================== --}}
        <aside class="w-64 bg-gradient-l text-slate-300 flex flex-col h-screen border-r border-whiite-30%">

            <div class="bg-gradient-to-b from-[#044564] from-50% to-[#19A7CE] min-h-screen">

                <div class="flex items-center justify-between p-4 bg-white rounded-bl-xl">

                    <div class="flex items-center gap-3">

                        <img src="{{ asset('gambar/silindo.png') }}"
                            alt="Logo"
                            class="w-12 h-12 object-contain shrink-0">

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

                        <svg class="w-8 h-8"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2.5"
                                d="M4 6h16M4 12h16M4 18h16">
                            </path>

                        </svg>

                    </button>

                </div>


                <nav class="flex-1 p-4 pt-8 space-y-3 text-sm">

                    <a href="/"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-white/40 hover:text-white transition">

                        <svg class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10 20v-6h4v6h5v-8h3L12 3L2 12h3v8">
                            </path>

                        </svg>

                        Dashboard

                    </a>


                    <a href="#"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-white/40 hover:text-white transition">

                        <svg class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 5h8m-8 7h8m-8 7h8M3 17l2 2l4-4" />

                            <rect width="6"
                                height="6"
                                x="3"
                                y="4"
                                rx="1">
                            </rect>

                        </svg>

                        Tugas

                    </a>


                    <a href="/manajemen-pesan"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg bg-white/40 text-white font-medium transition">

                        <svg class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1.9-2-2-2m-3 12H7c-.55 0-1-.45-1-1s.45-1 1-1h10c.55 0 1-.45 1-1s-.45-1-1-1m0-3H7c-.55 0-1-.45-1-1s.45-1-1-1h10c.55 0 1-.45 1-1">
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

                        <button type="submit"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2 rounded-lg outline-1 bg-white text-red-400 hover:bg-red-500 hover:text-white text-sm font-medium transition">

                            <svg class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">

                                <path stroke-linecap="round"
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


        {{-- =====================================================
            MAIN
        ====================================================== --}}
        <main class="flex-1 min-w-0 min-h-screen bg-white">


            {{-- =================================================
                HEADER
            ================================================== --}}
            <header
                class="h-[79px] bg-white shadow-[0_4px_4px_rgba(0,0,0,0.20)] flex items-center px-6 lg:px-8 xl:px-10">

                <div class="w-full flex items-center gap-6">


                    {{-- SEARCH --}}
                    <div class="flex-1 flex justify-center">

                        <div
                            class="w-full max-w-[693px] h-[51px] border border-[rgba(148,163,184,0.5)] rounded-lg px-2 flex items-center gap-[6px]">

                            <svg
                                class="w-[22px] h-[22px] shrink-0 text-[#64748B]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0a8 8 0 0 1 16 0">
                                </path>

                            </svg>

                            <input
                                type="text"
                                placeholder="Cari"
                                class="w-full h-full border-0 outline-none bg-transparent text-[14px] leading-[22px] text-[#283044] placeholder:text-[#64748B]"
                            >

                        </div>

                    </div>


                    {{-- NOTIFICATION --}}
                    <button
                        type="button"
                        class="w-10 h-10 flex items-center justify-center shrink-0">

                        <svg
                            class="w-[30px] h-[30px] text-[#283044]"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0">
                            </path>

                        </svg>

                    </button>


                    {{-- PROFILE --}}
                    <button
                        type="button"
                        class="w-10 h-10 rounded-full bg-[#D9D9D9] flex items-center justify-center shrink-0">

                        <svg
                            class="w-5 h-5 text-[#64748B]"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 9l6 6 6-6">
                            </path>

                        </svg>

                    </button>

                </div>

            </header>


            {{-- =================================================
                CONTENT
            ================================================== --}}
            <section class="px-7 lg:px-8 xl:px-9 py-10">

                <div class="max-w-[1072px]">


                    {{-- TITLE --}}
                    <h1
                        class="text-[28px] leading-[36px] font-bold text-black">
                        Pesan
                    </h1>


                    {{-- TASK TITLE --}}
                    <h2
                        class="mt-2 text-[28px] leading-[36px] font-bold text-black">
                        lorem ipsum
                    </h2>


                    {{-- DEADLINE --}}
                    <p
                        class="mt-0 text-[16px] leading-[24px] font-bold text-black">
                        tenggat : 21 sep 2026, 16.00
                    </p>


                    {{-- STATUS --}}
                    <p
                        class="text-[16px] leading-[24px] font-bold text-black">
                        status : on going
                    </p>


                    {{-- DESCRIPTION --}}
                    <p
                        class="mt-5 max-w-[1072px] text-[16px] leading-[24px] font-normal text-black">

                        Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                        Quisque pharetra ut lectus vel luctus. Aenean pellentesque
                        sapien placerat justo tincidunt, sit amet laoreet lectus
                        dapibus. Etiam fermentum erat faucibus, auctor nisi vitae,
                        aliquet quam. Cras eget lacus et mauris gravida aliquet.
                        Proin auctor arcu nec dapibus accumsan. Quisque nec mauris leo.

                    </p>


                    {{-- LINK --}}
                    <div class="mt-7 text-[14px] leading-[22px]">

                        <a
                            href="https://www.figma.com/design/H6V50AsbSvJiabZP2x9onv/"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-[#19A7CE] hover:text-[#146C94]">

                            <span class="mr-1">•</span>

                            <span class="underline">
                                Link
                            </span>

                            <span class="ml-1">
                                https://www.figma.com/design/H6V50AsbSvJiabZP2x9onv/
                            </span>

                        </a>

                    </div>


                    {{-- DIVIDER --}}
                    <div
                        class="w-full border-t border-black/50 mt-7">
                    </div>


                    {{-- =================================================
                        FORM PESAN
                    ================================================== --}}
                    <form
                        action="#"
                        method="POST"
                        class="mt-5">

                        @csrf

                        <div class="w-full max-w-[420px]">

                            {{-- LABEL --}}
                            <label
                                for="pesan"
                                class="block pb-[6px] text-[16px] leading-[24px] font-bold text-[#1E293B]">

                                Pesan

                            </label>


                            {{-- INPUT --}}
                            <input
                                type="text"
                                id="pesan"
                                name="pesan"
                                placeholder="Ketik pesan anda"
                                class="w-full h-[37px] px-[11px] py-2 bg-white border border-[#CBD5E1] rounded-lg outline-none text-[14px] leading-[22px] text-[#283044] placeholder:text-[#94A3B8] focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]"
                            >

                        </div>


                        {{-- BUTTON --}}
                        <div class="flex items-center gap-7 mt-16">


                            {{-- BATAL --}}
                            <button
                                type="button"
                                onclick="history.back()"
                                class="w-[148px] h-[36px] rounded-[9px] bg-[#D22B2B] px-4 flex items-center justify-center text-[16px] leading-[24px] text-white hover:bg-[#b92323] transition">

                                Batal

                            </button>


                            {{-- KIRIM --}}
                            <button
                                type="submit"
                                class="w-[148px] h-[36px] rounded-[9px] bg-[#0E9DC3] px-4 flex items-center justify-center text-[16px] leading-[24px] text-white hover:bg-[#0c89aa] transition">

                                Kirim

                            </button>

                        </div>

                    </form>

                </div>

            </section>

        </main>

    </div>

</body>

</html>