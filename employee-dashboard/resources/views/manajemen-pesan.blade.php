<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manajemen Pesan - PT SILINDO</title>

    @vite('resources/css/app.css')
</head>

<body class="m-0 min-h-screen bg-white font-sans antialiased overflow-x-hidden">

    <div class="flex min-h-screen w-full bg-white">


        {{-- =====================================================
             SIDEBAR
             SNIPPET TETAP
        ====================================================== --}}
        <div class="w-64 shrink-0 h-screen sticky top-0
                    bg-gradient-to-b from-[#146C94] to-[#19A7CE]">

            <aside class="w-64 bg-gradient-l text-slate-300 flex flex-col h-screen border-r border-whiite-30%">

                <div class="bg-gradient-to-b from-[#044564] from-50% to-[#19A7CE] min-h-screen">

                    <div class="flex items-center justify-between p-4 bg-white rounded-bl-xl border-b border-[#D9D9D9]">
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
                                      d="M10 20v-6h4v6h5v-8h3L12 3L2 12h3v8z">
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
                                      d="M13 5h8m-8 7h8m-8 7h8M3 17l2 2l4-4"/>

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
                                      d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1.9-2-2-2m-3 12H7c-.55 0-1-.45-1-1s.45-1 1-1h10c.55 0 1 .45 1 1s-.45 1-1 1m0-3H7c-.55 0-1-.45-1-1s.45-1 1-1h10c.55 0 1 .45 1 1s-.45 1-1 1m0-3H7c-.55 0-1-.45-1-1s.45-1 1-1h10c.55 0 1 .45 1 1s-.45 1-1 1">
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

        </div>



        {{-- =====================================================
             AREA KONTEN KANAN
        ====================================================== --}}
        <main class="flex-1 min-w-0 min-h-screen bg-white">


            {{-- =================================================
                 HEADER
            ================================================== --}}
            <header class="h-[79px] w-full
                           bg-white
                           shadow-[0_4px_4px_rgba(0,0,0,0.20)]
                           flex items-center">

                <div class="w-full flex items-center
                            justify-between
                            gap-8
                            px-8 lg:px-10">


                    {{-- SEARCH --}}
                    <div class="h-[51px]
                                w-full max-w-[693px]
                                ml-[10%]
                                flex items-center gap-[6px]
                                px-[8px] py-[4px]
                                rounded-[8px]
                                border border-[#94A3B880]">

                        <svg class="w-[22px] h-[22px] shrink-0 text-[#64748B]"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z">
                            </path>

                        </svg>

                        <span class="text-[14px] leading-[22px] text-[#64748B]">
                            Cari
                        </span>

                    </div>


                    {{-- NOTIFICATION + PROFILE --}}
                    <div class="shrink-0 flex items-center gap-[24px]">

                        {{-- Notification --}}
                        <div class="flex w-[40px] h-[40px]
                                    justify-center items-center">

                            <svg class="w-[30px] h-[30px]"
                                 fill="none"
                                 stroke="#283044"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4">
                                </path>

                            </svg>

                        </div>


                        {{-- Profile circle --}}
                        <div class="w-[40px] h-[40px]
                                    shrink-0
                                    rounded-full
                                    bg-[#D9D9D9]">
                        </div>


                        {{-- Forward --}}
                        <div class="flex w-[40px] h-[40px]
                                    justify-center items-center
                                    rounded-[8px]">

                            <svg class="w-[16px] h-[16px]"
                                 fill="none"
                                 stroke="#283044"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="m9 18 6-6-6-6">
                                </path>

                            </svg>

                        </div>

                    </div>

                </div>

            </header>



            {{-- =================================================
                 KONTEN UTAMA
            ================================================== --}}
            <div class="px-8 lg:px-12 pt-8 pb-16">


                {{-- TITLE + ACTION --}}
                <div class="flex items-center justify-between gap-6">

                    <h1 class="m-0
                               font-['PT_Sans']
                               font-bold
                               text-[24px]
                               leading-[32px]
                               text-black
                               whitespace-nowrap">

                        Manajement Pesan

                    </h1>


                    <button type="button"
                            class="shrink-0
                                   w-[148px] h-[36px]
                                   flex items-center justify-center
                                   gap-[7px]
                                   px-[16px]
                                   rounded-[9px]
                                   bg-[#0E9DC3]
                                   text-white
                                   font-['PT_Sans']
                                   font-normal
                                   text-[16px]
                                   leading-[24px]">

                        Aksi

                    </button>

                </div>



                {{-- =================================================
                     TABLE
                ================================================== --}}
                <section class="mt-[30px]
                                w-full max-w-[1011px]
                                flex flex-col
                                shadow-[0_4px_4px_rgba(0,0,0,0.25)]">


                    {{-- TABLE HEADER --}}
                    <div class="grid
                                grid-cols-[40%_20.5%_21.3%_18.2%]
                                w-full h-[30px]
                                shrink-0
                                bg-[#D9D9D9]">


                        {{-- NAMA DIVISI --}}
                        <div class="flex h-[30px]
                                    items-center
                                    gap-[10px]
                                    px-[10px]">

                            <span class="font-['PT_Sans']
                                         font-normal
                                         text-[14px]
                                         leading-[22px]
                                         text-[#565E74]
                                         whitespace-nowrap">

                                Nama Divisi

                            </span>


                            <svg class="w-[12px] h-[12px] shrink-0"
                                 viewBox="0 0 12 12"
                                 fill="none">

                                <path d="M6 1L9 4H3L6 1Z"
                                      fill="#565E74"/>

                                <path d="M6 11L3 8H9L6 11Z"
                                      fill="#565E74"/>

                            </svg>

                        </div>



                        {{-- TUGAS --}}
                        <div class="flex h-[30px]
                                    items-center
                                    justify-center
                                    px-[10px]">

                            <span class="font-['PT_Sans']
                                         font-normal
                                         text-[14px]
                                         leading-[22px]
                                         text-[#565E74]
                                         whitespace-nowrap">

                                Tugas

                            </span>

                        </div>



                        {{-- KETERANGAN --}}
                        <div class="flex h-[30px]
                                    items-center
                                    justify-end
                                    gap-[10px]
                                    px-[10px]">

                            <span class="font-['PT_Sans']
                                         font-normal
                                         text-[14px]
                                         leading-[22px]
                                         text-[#565E74]
                                         whitespace-nowrap">

                                Keterangan

                            </span>


                            <svg class="w-[12px] h-[12px] shrink-0"
                                 viewBox="0 0 12 12"
                                 fill="none">

                                <path d="M6 1L9 4H3L6 1Z"
                                      fill="#565E74"/>

                                <path d="M6 11L3 8H9L6 11Z"
                                      fill="#565E74"/>

                            </svg>

                        </div>



                        {{-- AKSI --}}
                        <div class="flex h-[30px]
                                    items-center
                                    justify-center
                                    px-[10px]">

                            <span class="font-['PT_Sans']
                                         font-normal
                                         text-[14px]
                                         leading-[22px]
                                         text-[#565E74]
                                         whitespace-nowrap">

                                Aksi

                            </span>

                        </div>

                    </div>



                    {{-- TABLE BODY --}}
                    <div class="w-full
                                min-h-[716px]
                                bg-white">
                    </div>

                </section>

            </div>

        </main>

    </div>

</body>
</html>