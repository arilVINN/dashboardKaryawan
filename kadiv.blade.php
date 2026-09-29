```blade
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Kepala Divisi - PT SILINDO</title>

    @vite('resources/css/app.css')
</head>

<body class="m-0 min-h-screen min-w-[1440px] bg-white font-sans">

    <div class="relative min-h-[1024px] w-full overflow-hidden bg-white">


        {{-- =========================================================
             SIDEBAR
        ========================================================== --}}
        <aside
            class="absolute left-0 top-0 h-[1024px] w-[335px] overflow-hidden bg-gradient-to-b from-[#044564] from-50% to-[#19A7CE]"
        >

            {{-- =====================================================
                 HEADER SIDEBAR
            ====================================================== --}}
            <div
                class="absolute left-0 top-0 h-[80px] w-[335px] rounded-bl-[20px] bg-white"
            >

                <div class="absolute left-[25px] top-[14px] flex items-center gap-3">

                    <img
                        src="{{ asset('gambar/silindo.png') }}"
                        alt="Logo PT SILINDO"
                        class="h-[50px] w-[60px] shrink-0 object-contain"
                    >

                    <div class="flex flex-col">

                        <div class="text-[20px] font-bold leading-6 text-[#2A4B6A]">
                            PT SILINDO
                        </div>

                        <div
                            class="text-[9.5px] font-normal leading-[11.4px] tracking-[0.19px] text-[#1CA4BA]"
                        >
                            PT SINERGI ILMIAH INDONESIA
                        </div>

                    </div>

                </div>


                {{-- Hamburger --}}
                <button
                    type="button"
                    class="absolute right-5 top-[24px] flex h-8 w-8 items-center justify-center rounded-md text-[#2A4B6A] transition hover:bg-gray-100"
                    aria-label="Menu"
                >

                    <svg
                        class="h-7 w-7"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2.5"
                            d="M4 6h16M4 12h16M4 18h16"
                        />

                    </svg>

                </button>

            </div>


            {{-- =====================================================
                 MENU ACTIVE
            ====================================================== --}}
            <div
                class="absolute left-[22px] top-[110px] h-[43px] w-[298px] rounded-[10px] bg-[#F6F1F166]"
            ></div>


            {{-- =====================================================
                 NAVIGATION
            ====================================================== --}}
            <nav
                class="absolute left-[42px] right-[20px] top-[110px] text-white"
            >

                {{-- Dashboard --}}
                <a
                    href="#"
                    class="flex h-[43px] items-center gap-4 rounded-lg text-sm font-medium"
                >

                    <svg
                        class="h-[17px] w-[17px] shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 12l9-9 9 9M5 10v10h14V10M9 20v-6h6v6"
                        />

                    </svg>

                    <span>Dashboard</span>

                </a>


                {{-- Tugas --}}
                <a
                    href="#"
                    class="mt-3 flex h-[43px] items-center gap-4 rounded-lg text-sm transition hover:bg-white/20"
                >

                    <svg
                        class="h-[17px] w-[17px] shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 3h12v18H6zM9 7h6M9 11h6M9 15h4"
                        />

                    </svg>

                    <span>Tugas</span>

                </a>


                {{-- Pesan --}}
                <a
                    href="#"
                    class="mt-3 flex h-[43px] items-center gap-4 rounded-lg text-sm transition hover:bg-white/20"
                >

                    <svg
                        class="h-[17px] w-[17px] shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 5h16v11H8l-4 4V5z"
                        />

                    </svg>

                    <span>Pesan</span>

                </a>

            </nav>


            {{-- =====================================================
                 USER + LOGOUT
            ====================================================== --}}
            <div class="absolute bottom-[24px] left-[42px] right-[42px]">

                {{-- User --}}
                <div class="mb-4 flex items-center gap-3">

                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-500 font-bold text-white"
                    >
                        S
                    </div>

                    <div class="min-w-0 overflow-hidden">

                        <p class="truncate text-sm font-medium text-white">
                            Nama Pengguna
                        </p>

                        <p class="text-xs capitalize text-white/80">
                            Role: Kadiv / HRD
                        </p>

                    </div>

                </div>


                {{-- Logout --}}
                <form action="#" method="POST">

                    @csrf

                    <button
                        type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-medium text-red-400 outline-1 transition hover:bg-red-500 hover:text-white"
                    >

                        <svg
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                            />

                        </svg>

                        Keluar

                    </button>

                </form>

            </div>

        </aside>



        {{-- =========================================================
             TOP HEADER
        ========================================================== --}}
        <header
            class="absolute left-[335px] top-0 h-[79px] w-[1105px] bg-white shadow-[0_4px_4px_#00000033]"
        >

            <div class="absolute right-5 top-5 flex items-center gap-6">

                {{-- Notification --}}
                <button
                    type="button"
                    class="flex h-10 w-10 items-center justify-center rounded-md"
                    aria-label="Notifikasi"
                >

                    <svg
                        class="h-[30px] w-[30px] text-[#283044]"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M15 17H9m9-5a6 6 0 10-12 0c0 7-3 7-3 7h18s-3 0-3-7M13.73 21a2 2 0 01-3.46 0"
                        />

                    </svg>

                </button>


                {{-- Profile --}}
                <button
                    type="button"
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-[#D9D9D9]"
                    aria-label="Profile"
                >

                    <svg
                        class="h-4 w-4 text-[#283044]"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 9l6 6 6-6"
                        />

                    </svg>

                </button>

            </div>

        </header>



        {{-- =========================================================
             SUMMARY CARDS
        ========================================================== --}}
        <section
            class="absolute left-[373px] top-[100px] flex h-[112px] gap-[23px]"
        >

            {{-- Card 1 --}}
            <div
                class="h-[112px] w-[216px] bg-white p-4 shadow-[0_4px_8px_0_rgba(0,0,0,0.06),0_12px_24px_0_rgba(0,0,0,0.10)]"
            >

                <div class="flex h-[44px] items-center gap-[10px]">

                    <div class="flex-1 text-[16px] font-bold leading-6 text-[#565E74]">
                        TUGAS YANG BELUM
                    </div>

                    <div class="h-[44px] w-[44px] shrink-0 rounded-full bg-[#D9D9D9]"></div>

                </div>

                <div class="mt-1 flex h-9 items-end gap-[10px]">

                    <span class="text-[28px] font-bold leading-9 text-[#283044]">
                        0/10
                    </span>

                    <span class="text-sm leading-[22px] text-[#565E74]">
                        Tugas
                    </span>

                </div>

            </div>


            {{-- Card 2 --}}
            <div
                class="h-[112px] w-[260px] bg-white p-4 shadow-[0_4px_8px_0_rgba(0,0,0,0.06),0_12px_24px_0_rgba(0,0,0,0.10)]"
            >

                <div class="flex h-[44px] items-center gap-[10px]">

                    <div class="flex-1 text-[16px] font-bold leading-6 text-[#565E74]">
                        TUGAS YANG BELUM DI ACC
                    </div>

                    <div class="h-[44px] w-[44px] shrink-0 rounded-full bg-[#D9D9D9]"></div>

                </div>

                <div class="mt-1 flex h-9 items-end gap-[10px]">

                    <span class="text-[28px] font-bold leading-9 text-[#283044]">
                        0/10
                    </span>

                    <span class="text-sm leading-[22px] text-[#565E74]">
                        Tugas
                    </span>

                </div>

            </div>


            {{-- Card 3 --}}
            <div
                class="h-[112px] w-[260px] bg-white p-4 shadow-[0_4px_8px_0_rgba(0,0,0,0.06),0_12px_24px_0_rgba(0,0,0,0.10)]"
            >

                <div class="flex h-[44px] items-center gap-[10px]">

                    <div class="flex-1 text-[16px] font-bold leading-6 text-[#565E74]">
                        TUGAS SELESAI
                    </div>

                    <div class="h-[44px] w-[44px] shrink-0 rounded-full bg-[#D9D9D9]"></div>

                </div>

                <div class="mt-1 flex h-9 items-end gap-[10px]">

                    <span class="text-[28px] font-bold leading-9 text-[#283044]">
                        0/10
                    </span>

                    <span class="text-sm leading-[22px] text-[#565E74]">
                        Tugas
                    </span>

                </div>

            </div>


            {{-- Card 4 --}}
            <div
                class="h-[110px] w-[200px] bg-white p-4 shadow-[0_4px_8px_0_rgba(0,0,0,0.06),0_12px_24px_0_rgba(0,0,0,0.10)]"
            >

                <div class="flex h-[44px] items-center gap-[10px]">

                    <div class="flex-1 text-[16px] font-bold leading-6 text-[#565E74]">
                        PRESENTASE
                    </div>

                    <div class="h-[44px] w-[44px] shrink-0 rounded-full bg-[#D9D9D9]"></div>

                </div>

                <div class="mt-1 flex h-9 items-end gap-[10px]">

                    <span class="text-[28px] font-bold leading-9 text-[#283044]">
                        17%
                    </span>

                    <span class="text-sm leading-[22px] text-[#565E74]">
                        Tugas
                    </span>

                </div>

            </div>

        </section>



        {{-- =========================================================
             MANAJEMEN STAFF
        ========================================================== --}}
        <section
            class="absolute left-[371px] top-[240px]"
        >

            <h1 class="text-[24px] font-bold leading-8 text-[#283044]">
                Manajement Staff
            </h1>


            {{-- Table Card --}}
            <div
                class="relative mt-[25px] h-[236px] w-[1010px] bg-white shadow-[0_4px_4px_#00000040]"
            >

                {{-- Table Header --}}
                <div
                    class="absolute left-0 top-0 flex h-[30px] w-full items-center bg-white"
                >

                    <div class="flex h-full w-[404px] items-center gap-2.5 px-2.5">

                        <span class="text-sm text-[#565E74]">
                            Nama Divisi
                        </span>

                        <span class="text-xs text-[#565E74]">
                            ↕
                        </span>

                    </div>


                    <div
                        class="flex h-full w-[207px] items-center justify-center px-2.5"
                    >

                        <span class="text-sm text-[#565E74]">
                            Tugas
                        </span>

                    </div>


                    <div
                        class="flex h-full w-[215px] items-center justify-end gap-2.5 px-2.5"
                    >

                        <span class="text-sm text-[#565E74]">
                            Keterangan
                        </span>

                        <span class="text-xs text-[#565E74]">
                            ↕
                        </span>

                    </div>


                    <div
                        class="flex h-full flex-1 items-center justify-center text-sm text-[#565E74]"
                    >
                        Aksi
                    </div>

                </div>


                {{-- Empty state --}}
                <div
                    class="flex h-full items-center justify-center pt-[30px] text-sm text-[#64748B]"
                >
                    Belum ada data staff.
                </div>

            </div>


            {{-- Filter --}}
            <button
                type="button"
                class="absolute -left-[6px] top-[316px] flex h-[29px] w-[80px] items-center gap-2 rounded-lg border border-[#94A3B880] bg-white px-2 py-1 text-sm text-[#565E74]"
            >

                <svg
                    class="h-3.5 w-3.5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 6h16M7 12h10M10 18h4"
                    />

                </svg>

                <span>Filter</span>

            </button>


            {{-- Pagination --}}
            <div
                class="absolute left-[334px] top-[320px] flex items-center gap-6"
            >

                <div class="flex items-center gap-3">

                    <button
                        type="button"
                        class="flex h-6 w-6 items-center justify-center rounded border border-[#94A3B880] text-xs"
                    >
                        «
                    </button>

                    <button
                        type="button"
                        class="flex h-6 w-6 items-center justify-center rounded border border-[#94A3B880] text-xs"
                    >
                        ‹
                    </button>

                </div>


                <div class="flex items-center gap-4">

                    <button
                        type="button"
                        class="flex h-6 w-6 items-center justify-center rounded bg-[#19A7CE] text-sm text-white"
                    >
                        1
                    </button>

                    <button
                        type="button"
                        class="flex h-6 w-6 items-center justify-center rounded border border-[#94A3B880] text-sm text-[#131B2E]"
                    >
                        2
                    </button>

                    <button
                        type="button"
                        class="flex h-6 w-6 items-center justify-center rounded border border-[#94A3B880] text-sm text-[#131B2E]"
                    >
                        3
                    </button>

                    <button
                        type="button"
                        class="flex h-6 w-6 items-center justify-center rounded border border-[#94A3B880] text-sm text-[#131B2E]"
                    >
                        4
                    </button>

                    <button
                        type="button"
                        class="flex h-6 w-6 items-center justify-center rounded border border-[#94A3B880] text-sm text-[#131B2E]"
                    >
                        ...
                    </button>

                </div>


                <div class="flex items-center gap-3">

                    <button
                        type="button"
                        class="flex h-6 w-6 items-center justify-center rounded border border-[#94A3B880] text-xs"
                    >
                        ›
                    </button>

                    <button
                        type="button"
                        class="flex h-6 w-6 items-center justify-center rounded border border-[#94A3B880] text-xs"
                    >
                        »
                    </button>

                </div>

            </div>

        </section>



        {{-- =========================================================
             TUGAS
        ========================================================== --}}
        <section
            class="absolute left-[371px] top-[623px]"
        >

            <h2 class="text-[24px] font-bold leading-8 text-[#283044]">
                Tugas
            </h2>


            <div
                class="mt-[7px] h-[294px] w-[486px] bg-white shadow-[0_4px_4px_#00000040]"
            >

                <div
                    class="flex h-[30px] items-center rounded-t-lg bg-[rgba(148,163,184,0.25)]"
                >

                    <div class="w-[29.41%] px-2.5 text-[10px] text-[#565E74]">
                        Nama
                    </div>

                    <div class="w-[23.53%] px-2.5 text-[10px] text-[#565E74]">
                        Keterangan
                    </div>

                    <div class="w-[23.53%] px-2.5 text-[10px] text-[#565E74]">
                        Tanggal
                    </div>

                    <div class="flex w-[23.53%] justify-center text-[10px] text-[#283044]">
                        Aksi
                    </div>

                </div>

            </div>

        </section>



        {{-- =========================================================
             PESAN
        ========================================================== --}}
        <section
            class="absolute left-[884px] top-[623px]"
        >

            <h2 class="text-[24px] font-bold leading-8 text-[#283044]">
                Pesan
            </h2>


            <div
                class="mt-[7px] h-[294px] w-[483px] bg-white shadow-[0_4px_4px_#00000040]"
            >

                <div
                    class="flex h-[30px] items-center rounded-t-lg bg-white"
                >

                    <div class="w-[29.41%] px-2.5 text-xs text-[#565E74]">
                        Isi
                    </div>

                    <div class="w-[23.53%] px-2.5 text-xs text-[#565E74]">
                        Isi
                    </div>

                    <div class="w-[23.53%] px-2.5 text-xs text-[#565E74]">
                        Isi
                    </div>

                    <div class="flex w-[23.53%] items-center justify-center gap-1.5">

                        <button
                            type="button"
                            class="w-[50px] rounded-sm bg-[#0E9DC3] py-0.5 text-xs text-white"
                        >
                            Aksi
                        </button>

                        <button
                            type="button"
                            class="w-[50px] rounded-sm bg-[#F5A623] py-0.5 text-xs text-[#283044]"
                        >
                            Aksi
                        </button>

                    </div>

                </div>


                <div class="mt-[1px] h-[10px] w-full bg-[#D9D9D9]"></div>

            </div>

        </section>


    </div>

</body>

</html>
```

