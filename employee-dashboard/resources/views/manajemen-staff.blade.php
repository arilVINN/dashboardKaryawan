<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Staff - PT SILINDO</title>

    @vite('resources/css/app.css')
</head>

<body class="m-0 min-h-screen bg-white antialiased">

    <div class="relative min-h-[1024px] min-w-[1440px] w-full overflow-hidden bg-white">

        {{-- =========================================================
             SIDEBAR
        ========================================================== --}}
        <aside class="absolute left-0 top-0 z-20 h-[1024px] w-[335px] bg-gradient-to-b from-[#146C94] to-[#19A7CE]">

            {{-- Logo / Brand --}}
            <div class="absolute left-0 top-0 flex h-[80px] w-[335px] items-center justify-between rounded-bl-[20px] bg-white px-5">

                <div class="flex items-center gap-3">
                    <img
                        src="{{ asset('gambar/silindo.png') }}"
                        alt="Logo PT SILINDO"
                        class="h-12 w-12 shrink-0 object-contain"
                    >

                    <div class="flex flex-col">
                        <h1 class="text-[16px] font-bold leading-6 text-[#2E4D70]">
                            PT SILINDO
                        </h1>

                        <p class="whitespace-nowrap text-[9.5px] font-medium leading-[11.4px] tracking-[0.19px] text-[#19A7CE]">
                            PT SINERGI ILMIAH INDONESIA
                        </p>
                    </div>
                </div>

                {{-- Hamburger --}}
                <button
                    type="button"
                    class="flex h-10 w-10 items-center justify-center rounded-lg text-[#2E4D70] transition hover:bg-gray-100"
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


            {{-- Menu --}}
            <nav class="absolute left-[22px] top-[105px] w-[298px]">

                {{-- Dashboard --}}
                <a
                    href="/"
                    class="flex h-[43px] items-center gap-3 rounded-[10px] px-4 text-[14px] font-normal leading-[22px] text-white transition hover:bg-white/40"
                >
                    <svg
                        class="h-[20px] w-[20px] shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 11.5L12 4l9 7.5M5 10v9h5v-6h4v6h5v-9"
                        />
                    </svg>

                    <span>Dashboard</span>
                </a>


                {{-- Manajemen Staff - ACTIVE --}}
                <a
                    href="/manajemen-staff"
                    class="mt-2 flex h-[43px] items-center gap-3 rounded-[10px] bg-white/40 px-4 text-[14px] font-medium leading-[22px] text-white"
                >
                    <svg
                        class="h-[20px] w-[20px] shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13 5h8m-8 7h8m-8 7h8M3 17l2 2 4-4M3 5h6M3 12h6"
                        />
                    </svg>

                    <span>Manajemen Staff</span>
                </a>


                {{-- Tugas --}}
                <a
                    href="#"
                    class="mt-2 flex h-[43px] items-center gap-3 rounded-[10px] px-4 text-[14px] font-normal leading-[22px] text-white transition hover:bg-white/40"
                >
                    <svg
                        class="h-[20px] w-[20px] shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13 5h8m-8 7h8m-8 7h8M3 17l2 2 4-4M3 5h6"
                        />
                    </svg>

                    <span>Tugas</span>
                </a>


                {{-- Pesan --}}
                <a
                    href="#"
                    class="mt-2 flex h-[43px] items-center gap-3 rounded-[10px] px-4 text-[14px] font-normal leading-[22px] text-white transition hover:bg-white/40"
                >
                    <svg
                        class="h-[20px] w-[20px] shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4l4 3 4-3h4a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Z"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M7 9h10M7 13h7"
                        />
                    </svg>

                    <span>Pesan</span>
                </a>

            </nav>


            {{-- Profile + Logout --}}
            <div class="absolute bottom-5 left-5 right-5">

                <div class="mb-4 flex items-center gap-3 px-2">

                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-500 font-bold text-white">
                        S
                    </div>

                    <div class="min-w-0 overflow-hidden">
                        <p class="truncate text-[14px] font-medium leading-[22px] text-white">
                            Nama Pengguna
                        </p>

                        <p class="truncate text-[12px] leading-5 text-white/80">
                            Role: Kadiv / HRD
                        </p>
                    </div>

                </div>


                <form action="#" method="POST">
                    @csrf

                    <button
                        type="submit"
                        class="flex h-10 w-full items-center justify-center gap-2 rounded-lg bg-white px-4 text-[14px] font-medium text-red-400 transition hover:bg-red-500 hover:text-white"
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
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1"
                            />
                        </svg>

                        Keluar
                    </button>
                </form>

            </div>

        </aside>


        {{-- =========================================================
             HEADER
        ========================================================== --}}
        <header class="absolute left-[335px] top-0 h-[79px] w-[1105px] bg-white shadow-[0px_4px_4px_rgba(0,0,0,0.2)]">

            <div class="absolute right-[137px] top-[20px] flex items-center gap-6">

                {{-- Notification --}}
                <button
                    type="button"
                    class="flex h-10 w-10 items-center justify-center"
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
                            d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"
                        />
                    </svg>
                </button>

                {{-- Profile --}}
                <button
                    type="button"
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-[#D9D9D9]"
                    aria-label="Profil"
                >
                    <svg
                        class="h-4 w-4 text-[#565E74]"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="m9 18 6-6-6-6"
                        />
                    </svg>
                </button>

            </div>

        </header>


        {{-- =========================================================
             CONTENT
        ========================================================== --}}

        {{-- Page Title --}}
        <h1 class="absolute left-[371px] top-[111px] m-0 text-[24px] font-bold leading-8 text-black">
            Manajemen Staff
        </h1>

        {{-- Role --}}
        <p class="absolute left-[384px] top-[161px] m-0 text-[24px] font-bold leading-8 text-black">
            Writer
        </p>


        {{-- =========================================================
             TABLE
        ========================================================== --}}
        <section class="absolute left-[384px] top-[201px] h-[746px] w-[1011px] shadow-[0px_4px_4px_rgba(0,0,0,0.25)]">

            {{-- Table Header --}}
            <div class="flex h-[30px] w-[1011px] items-center bg-[#D9D9D9]">

                {{-- Nama Divisi --}}
                <div class="flex h-full w-[404px] items-center gap-[10px] px-[10px]">
                    <span class="text-[14px] font-normal leading-[22px] text-[#565E74]">
                        Nama Divisi
                    </span>

                    <svg
                        class="h-3 w-3 text-[#565E74]"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="m8 9 4-4 4 4M8 15l4 4 4-4"
                        />
                    </svg>
                </div>


                {{-- Tugas --}}
                <div class="flex h-full w-[207px] items-center justify-center px-[10px]">
                    <span class="text-[14px] font-normal leading-[22px] text-[#565E74]">
                        Tugas
                    </span>
                </div>


                {{-- Keterangan --}}
                <div class="flex h-full w-[215px] items-center justify-end gap-[10px] px-[10px]">
                    <span class="text-[14px] font-normal leading-[22px] text-[#565E74]">
                        Keterangan
                    </span>

                    <svg
                        class="h-3 w-3 text-[#565E74]"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="m8 9 4-4 4 4M8 15l4 4 4-4"
                        />
                    </svg>
                </div>


                {{-- Aksi --}}
                <div class="flex h-full flex-1 items-center justify-center">
                    <span class="text-[14px] font-normal leading-[22px] text-[#565E74]">
                        Aksi
                    </span>
                </div>

            </div>


            {{-- Empty table body --}}
            <div class="h-[716px] w-[1010px] bg-white">
            </div>

        </section>


        {{-- =========================================================
             ACTION BUTTON
        ========================================================== --}}
        <button
            type="button"
            class="absolute left-[1247px] top-[143px] flex h-[36px] w-[148px] items-center justify-center gap-[7px] rounded-[9px] bg-[#0E9DC3] px-4 text-[16px] font-normal leading-6 text-white transition hover:bg-[#0c8eaf]"
        >
            Aksi
        </button>

    </div>

</body>
</html>