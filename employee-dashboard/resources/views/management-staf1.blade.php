s<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manajemen Staff - PT SILINDO</title>

    @vite('resources/css/app.css')
</head>

<body class="m-0 min-h-screen bg-white font-sans antialiased overflow-x-hidden">

    <div class="flex min-h-screen w-full bg-white">

        {{-- =========================================================
            SIDEBAR KADIV
        ========================================================== --}}
        <div class="w-64 shrink-0 h-screen sticky top-0
                    bg-gradient-to-b from-[#146C94] to-[#19A7CE]">

            <aside class="w-64 bg-gradient-l text-slate-300 flex flex-col h-screen border-r border-whiite-30%">

                <div class="bg-gradient-to-b from-[#044564] from-50% to-[#19A7CE] min-h-screen">

                    {{-- Logo --}}
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

                        <button type="button"
                                class="p-2 text-[#2A4B6A] hover:bg-gray-100 rounded-md">

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


                    {{-- Garis pemisah pertama --}}
                    <div class="mx-4 h-px bg-white"></div>


                    {{-- Navigation --}}
                    <nav class="flex-1 p-4 pt-8 space-y-3 text-sm">

                        {{-- Dashboard --}}
                        <a href="/"
                           class="flex items-center gap-3 px-4 py-2.5 rounded-lg
                                  hover:bg-white/40 hover:text-white transition">

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


                        {{-- Tugas --}}
                        <a href="#"
                           class="flex items-center gap-3 px-4 py-2.5 rounded-lg
                                  hover:bg-white/40 hover:text-white transition">

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


                        {{-- Pesan --}}
                        <a href="/manajemen-pesan"
                           class="flex items-center gap-3 px-4 py-2.5 rounded-lg
                                  hover:bg-white/40 hover:text-white transition">

                            <svg class="w-5 h-5"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2m-3 12H7c-.55 0-1-.45-1-1s.45-1 1-1h10c.55 0 1 .45 1 1s-.45 1-1 1m0-3H7c-.55 0-1-.45-1-1s.45-1 1-1h10c.55 0 1 .45 1 1s-.45 1-1 1m0-3H7c-.55 0-1-.45-1-1s.45-1 1-1h10c.55 0 1 .45 1 1s-.45 1-1 1">
                                </path>

                            </svg>

                            Pesan
                        </a>

                    </nav>


                    {{-- Garis pemisah kedua + profile --}}
                    <div class="p-4 border-t border-white mt-auto">

                        <div class="flex items-center gap-3 mb-3 px-2 bottom-0 mt-80">

                            <div class="w-9 h-9 rounded-full bg-blue-500
                                        flex items-center justify-center
                                        font-bold text-white">
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
                                    class="w-full flex items-center justify-center gap-2
                                           px-4 py-2 rounded-lg outline-1
                                           bg-white text-red-400
                                           hover:bg-red-500 hover:text-white
                                           text-sm font-medium transition">

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


        {{-- =========================================================
            MAIN CONTENT
        ========================================================== --}}
        <main class="flex-1 min-w-0 min-h-screen bg-white">


            {{-- =====================================================
                HEADER
            ====================================================== --}}
            <header class="h-[79px] w-full bg-white
                           shadow-[0_4px_4px_rgba(0,0,0,0.20)]
                           flex items-center">

                <div class="w-full flex items-center justify-between
                            gap-8 px-8 lg:px-10">


                    {{-- Search --}}
                    <div class="h-[51px] w-full max-w-[693px]
                                ml-[10%]
                                flex items-center gap-[6px]
                                px-[8px] py-[4px]
                                rounded-[8px]
                                border border-[#94A3B880]">

                        <svg class="w-[22px] h-[22px] shrink-0"
                             fill="none"
                             stroke="#64748B"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0a7 7 0 0 1 14 0z">
                            </path>

                        </svg>

                        <span class="text-[14px] leading-[22px] text-[#64748B]">
                            Cari
                        </span>

                    </div>


                    {{-- Notification + profile --}}
                    <div class="shrink-0 flex items-center gap-[24px]">

                        {{-- Notification --}}
                        <button type="button"
                                class="w-[40px] h-[40px]
                                       flex items-center justify-center">

                            <svg class="w-[30px] h-[30px]"
                                 fill="none"
                                 stroke="#283044"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.7"
                                      d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4">
                                </path>

                            </svg>

                        </button>


                        {{-- Profile --}}
                        <div class="flex items-center">

                            <div class="w-[40px] h-[40px]
                                        rounded-full bg-[#D9D9D9]">
                            </div>

                            <button type="button"
                                    class="w-[40px] h-[40px]
                                           flex items-center justify-center">

                                <svg class="w-[16px] h-[16px]"
                                     fill="none"
                                     stroke="#283044"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="m6 9 6 6 6-6">
                                    </path>

                                </svg>

                            </button>

                        </div>

                    </div>

                </div>

            </header>


            {{-- =====================================================
                CONTENT
            ====================================================== --}}
            <div class="px-8 lg:px-12 pt-8 pb-16">

                {{-- Title + Action --}}
                <div class="flex items-center justify-between gap-6">

                    <h1 class="m-0 font-['PT_Sans']
                               font-bold text-[24px]
                               leading-[32px] text-black
                               whitespace-nowrap">

                        Manajement Staff

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
                                   leading-[24px]
                                   hover:bg-[#146C94]
                                   transition">

                        Aksi

                    </button>

                </div>


                {{-- =================================================
                    STAFF TABLE
                ================================================== --}}
                <section class="mt-[30px] w-full
                                max-w-[1011px]
                                overflow-hidden
                                shadow-[0_4px_4px_rgba(0,0,0,0.25)]">


                    {{-- Table Header --}}
                    <div class="grid
                                grid-cols-[40%_20.5%_21.3%_18.2%]
                                w-full
                                min-h-[30px]
                                bg-[#D9D9D9]">


                        {{-- Nama Staff --}}
                        <div class="flex items-center
                                    justify-start
                                    px-[10px]
                                    gap-[10px]">

                            <span class="text-[14px]
                                         leading-[22px]
                                         text-[#565E74]">

                                Nama staff

                            </span>

                            <svg class="w-[12px] h-[12px]"
                                 fill="none"
                                 stroke="#565E74"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="m8 9 4-4 4 4M8 15l4 4 4-4">
                                </path>

                            </svg>

                        </div>


                        {{-- Tugas --}}
                        <div class="flex items-center
                                    justify-center
                                    px-[10px]">

                            <span class="text-[14px]
                                         leading-[22px]
                                         text-[#565E74]">

                                Tugas

                            </span>

                        </div>


                        {{-- Approval --}}
                        <div class="flex items-center
                                    justify-end
                                    px-[10px]
                                    gap-[10px]">

                            <span class="text-[14px]
                                         leading-[22px]
                                         text-[#565E74]">

                                Approval

                            </span>

                            <svg class="w-[12px] h-[12px]"
                                 fill="none"
                                 stroke="#565E74"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="m8 9 4-4 4 4M8 15l4 4 4-4">
                                </path>

                            </svg>

                        </div>


                        {{-- Aksi --}}
                        <div class="flex items-center
                                    justify-center
                                    px-[10px]">

                            <span class="text-[14px]
                                         leading-[22px]
                                         text-[#565E74]">

                                Aksi

                            </span>

                        </div>

                    </div>


                    {{-- =================================================
                        TABLE ROW
                    ================================================== --}}
                    <div class="grid
                                grid-cols-[40%_20.5%_21.3%_18.2%]
                                w-full
                                min-h-[50px]
                                bg-[#F6F1F1]
                                border border-black/50">


                        {{-- Staff --}}
                        <div class="flex items-center
                                    justify-start
                                    px-[10px]">

                            <span class="text-[14px]
                                         leading-[22px]
                                         text-[#565E74]">

                                Agung

                            </span>

                        </div>


                        {{-- Tugas --}}
                        <div class="flex items-center
                                    justify-center
                                    px-[10px]">

                            <span class="text-[14px]
                                         leading-[22px]
                                         text-[#565E74]">

                                Menulis poster

                            </span>

                        </div>


                        {{-- Approval --}}
                        <div class="flex items-center
                                    justify-end
                                    px-[10px]">

                            <span class="text-[14px]
                                         leading-[22px]
                                         text-[#565E74]">

                                Pending

                            </span>

                        </div>


                        {{-- Aksi --}}
                        <div class="flex items-center
                                    justify-center
                                    px-[10px]">

                            <button type="button"
                                    class="text-[14px]
                                           leading-[22px]
                                           text-[#146C94]
                                           hover:underline">

                                Lihat

                            </button>

                        </div>

                    </div>


                    {{-- Empty table area --}}
                    <div class="w-full min-h-[637px] bg-white">
                    </div>

                </section>

            </div>

        </main>

    </div>

</body>
</html>
```
