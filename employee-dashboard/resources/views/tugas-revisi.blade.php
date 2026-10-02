<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tugas Revisi - PT SILINDO</title>

    @vite('resources/css/app.css')
</head>

<body class="bg-white text-[#283044]">

    <div class="min-h-screen flex">

        {{-- =========================================================
            SIDEBAR
            Struktur sengaja dipertahankan sesuai snippet user
        ========================================================== --}}
        <aside class="w-64 bg-gradient-l text-slate-300 flex flex-col h-screen border-r border-whiite-30%">

            <div class="bg-gradient-to-b from-[#044564] from-50% to-[#19A7CE] min-h-screen">

                <div class="flex items-center justify-between p-4 bg-white rounded-bl-xl">

                    <div class="flex items-center gap-3">

                        <img
                            src="{{ asset('gambar/silindo.png') }}"
                            alt="Logo"
                            class="w-12 h-12 object-contain shrink-0"
                        >

                        <div class="flex flex-col">

                            <h1 class="text-xl font-bold text-[#2A4B6A] leading-tight">
                                PT SILINDO
                            </h1>

                            <p class="text-[11px] font-small text-[#1CA4BA] tracking-tight leading-tight">
                                PT SINERGI ILMIAH INDONESIA
                            </p>

                        </div>

                    </div>

                    <button
                        type="button"
                        class="p-2 text-[#2A4B6A] hover:bg-gray-100 rounded-md"
                    >

                        <svg
                            class="w-8 h-8"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2.5"
                                d="M4 6h16M4 12h16M4 18h16"
                            >
                            </path>
                        </svg>

                    </button>

                </div>


                <nav class="flex-1 p-4 pt-8 space-y-3 text-sm">

                    {{-- Dashboard --}}
                    <a
                        href="/"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-white/40 hover:text-white transition"
                    >

                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10 20v-6h4v6h5v-8h3L12 3L2 12h3v8"
                            >
                            </path>
                        </svg>

                        Dashboard

                    </a>


                    {{-- Tugas --}}
                    <a
                        href="#"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg bg-white/40 text-white font-medium transition"
                    >

                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 5h8m-8 7h8m-8 7h8M3 17l2 2l4-4"
                            />

                            <rect
                                width="6"
                                height="6"
                                x="3"
                                y="4"
                                rx="1"
                            >
                            </rect>

                        </svg>

                        Tugas

                    </a>


                    {{-- Pesan --}}
                    <a
                        href="/manajemen-pesan"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-white/40 hover:text-white transition"
                    >

                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1.9-2-2-2m-3 12H7c-.55 0-1-.45-1-1s.45-1 1-1h10c.55 0 1-.45 1-1s-.45-1-1-1m0-3H7c-.55 0-1-.45-1-1s-.45-1-1-1h10c.55 0 1-.45 1-1"
                            >
                            </path>
                        </svg>

                        Pesan

                    </a>

                </nav>


                {{-- PROFILE --}}
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
                            class="w-full flex items-center justify-center gap-2 px-4 py-2 rounded-lg outline-1 bg-white text-red-400 hover:bg-red-500 hover:text-white text-sm font-medium transition"
                        >

                            <svg
                                class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                >
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
        <div class="flex-1 min-w-0 bg-white">


            {{-- =====================================================
                HEADER
            ====================================================== --}}
            <header
                class="h-[79px] bg-white shadow-[0_4px_4px_rgba(0,0,0,0.2)]
                       flex items-center px-6 lg:px-8 xl:px-10"
            >

                <div class="w-full flex items-center gap-6">


                    {{-- SEARCH --}}
                    <div class="flex-1 flex justify-center">

                        <div
                            class="w-full max-w-[693px] h-[51px]
                                   rounded-lg
                                   border border-[rgba(148,163,184,0.5)]
                                   px-2
                                   flex items-center gap-[6px]"
                        >

                            <svg
                                class="w-[22px] h-[22px] shrink-0 text-[#64748B]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0"
                                >
                                </path>

                            </svg>


                            <input
                                type="text"
                                placeholder="Cari"
                                class="w-full h-full bg-transparent border-0 outline-none
                                       text-[#283044] text-[14px] leading-[22px]
                                       placeholder:text-[#64748B]"
                            >

                        </div>

                    </div>


                    {{-- NOTIFICATION --}}
                    <button
                        type="button"
                        class="w-10 h-10 flex items-center justify-center shrink-0"
                    >

                        <svg
                            class="w-[30px] h-[30px] text-[#283044]"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"
                            >
                            </path>

                        </svg>

                    </button>


                    {{-- PROFILE --}}
                    <button
                        type="button"
                        class="w-10 h-10 rounded-full bg-[#D9D9D9]
                               flex items-center justify-center shrink-0"
                    >

                        <svg
                            class="w-5 h-5 text-[#64748B]"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="m6 9 6 6 6-6"
                            >
                            </path>

                        </svg>

                    </button>

                </div>

            </header>


            {{-- =====================================================
                PAGE CONTENT
            ====================================================== --}}
            <main class="px-6 lg:px-8 xl:px-9 py-5">

                <div class="w-full max-w-[1072px]">


                    {{-- =================================================
                        TUGAS 1
                    ================================================== --}}
                    <section>

                        <h1
                            class="text-[28px] leading-[36px] font-bold text-black"
                        >
                            Tugas 1
                        </h1>


                        <h2
                            class="mt-5 text-[28px] leading-[36px]
                                   font-bold text-black"
                        >
                            lorem ipsum
                        </h2>


                        <p
                            class="text-[16px] leading-[24px]
                                   font-bold text-black"
                        >
                            tenggat : 21 sep 2026, 16.00
                        </p>


                        <p
                            class="text-[16px] leading-[24px]
                                   font-bold text-black"
                        >
                            status : on going
                        </p>


                        {{-- DESCRIPTION --}}
                        <p
                            class="mt-10 text-[16px] leading-[24px]
                                   text-black"
                        >
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                            Quisque pharetra ut lectus vel luctus. Aenean pellentesque
                            sapien placerat justo tincidunt, sit amet laoreet lectus
                            dapibus. Etiam fermentum erat faucibus, auctor nisi vitae,
                            aliquet quam. Cras eget lacus et mauris gravida aliquet.
                            Proin auctor arcu nec dapibus accumsan. Quisque nec mauris leo.
                            Pellentesque eu pellentesque arcu, ac varius diam. Phasellus
                            a libero sem. Pellentesque placerat at odio eu tempor.
                            Ut non eros tortor. Aenean tincidunt sit amet risus vel
                            imperdiet. Vestibulum posuere facilisis urna, quis pulvinar
                            nisl porttitor ut. Ut sollicitudin ullamcorper eros.
                        </p>


                        {{-- ORIGINAL FILE --}}
                        <div
                            class="mt-8 w-[299px] h-[62px]
                                   bg-white rounded-[10px]
                                   border border-black/30
                                   shadow-[0_4px_4px_rgba(0,0,0,0.3)]
                                   overflow-hidden flex items-center"
                        >

                            <div class="flex-1 ml-7 min-w-0">

                                <p
                                    class="text-[16px] leading-[24px]
                                           font-bold underline text-black truncate"
                                >
                                    Tugas 1 Divisi Writer
                                </p>

                                <p
                                    class="text-[16px] leading-[24px]
                                           font-bold text-black/40"
                                >
                                    docx
                                </p>

                            </div>


                            <div
                                class="w-[60px] h-full shrink-0
                                       border-l border-black/50
                                       flex items-center justify-center"
                            >

                                <svg
                                    class="w-7 h-7 text-[#146C94]"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.7"
                                        d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.7"
                                        d="M14 2v6h6M8 13h8M8 17h6"
                                    />

                                </svg>

                            </div>

                        </div>

                    </section>


                    {{-- LINE --}}
                    <div class="border-t border-black mt-8"></div>


                    {{-- =================================================
                        REVISI
                    ================================================== --}}
                    <section class="pt-3">

                        <h2
                            class="text-[28px] leading-[36px]
                                   font-bold text-black"
                        >
                            Revisi
                        </h2>


                        <h3
                            class="mt-4 text-[24px] leading-[32px]
                                   font-bold text-black"
                        >
                            tugas1
                        </h3>


                        <p
                            class="text-[16px] leading-[24px]
                                   font-bold text-black"
                        >
                            tenggat : 21 sep 2026, 16.00
                        </p>


                        <p
                            class="text-[16px] leading-[24px]
                                   font-bold text-black"
                        >
                            status : on going
                        </p>


                        {{-- CATATAN --}}
                        <p
                            class="mt-2 text-[16px] leading-[24px] text-black"
                        >
                            buk ini untuk tugas kemarin
                        </p>


                        {{-- PDF FILE --}}
                        <div
                            class="mt-[-55px] ml-auto
                                   w-[299px] h-[62px]
                                   bg-white rounded-[10px]
                                   border border-black/30
                                   shadow-[0_4px_4px_rgba(0,0,0,0.3)]
                                   overflow-hidden flex items-center"
                        >

                            <div class="flex-1 ml-7 min-w-0">

                                <p
                                    class="text-[16px] leading-[24px]
                                           font-bold underline text-black truncate"
                                >
                                    Tugas 1 Divisi Writer
                                </p>

                                <p
                                    class="text-[16px] leading-[24px]
                                           font-bold text-black/40"
                                >
                                    Pdf
                                </p>

                            </div>


                            <div
                                class="w-[60px] h-full shrink-0
                                       border-l border-black/50
                                       flex items-center justify-center"
                            >

                                <svg
                                    class="w-7 h-7 text-[#D93838]"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.7"
                                        d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.7"
                                        d="M14 2v6h6M8 13h8M8 17h5"
                                    />

                                </svg>

                            </div>

                        </div>


                        {{-- CATATAN REVISI --}}
                        <p
                            class="mt-7 max-w-[422px]
                                   text-[16px] leading-[24px]
                                   text-black"
                        >
                            revisian yg ini jngn lupa, lusa harus dikirim ke client soalnya
                        </p>


                        {{-- FILE REVISI --}}
                        <div
                            class="mt-5 w-[299px] h-[62px]
                                   bg-white rounded-[10px]
                                   border border-black/30
                                   shadow-[0_4px_4px_rgba(0,0,0,0.3)]
                                   overflow-hidden flex items-center"
                        >

                            <div class="flex-1 ml-7 min-w-0">

                                <p
                                    class="text-[16px] leading-[24px]
                                           font-bold underline text-black truncate"
                                >
                                    Tugas 1 Divisi Writer
                                </p>

                                <p
                                    class="text-[16px] leading-[24px]
                                           font-bold text-black/40"
                                >
                                    docx
                                </p>

                            </div>


                            <div
                                class="w-[60px] h-full shrink-0
                                       border-l border-black/50
                                       flex items-center justify-center"
                            >

                                <svg
                                    class="w-7 h-7 text-[#146C94]"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.7"
                                        d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.7"
                                        d="M14 2v6h6M8 13h8M8 17h6"
                                    />

                                </svg>

                            </div>

                        </div>


                        {{-- =================================================
                            FORM REVISI
                        ================================================== --}}
                        <div
                            class="mt-5 w-full
                                   min-h-[132px]
                                   bg-white
                                   rounded-[10px]
                                   border border-black/50
                                   p-4"
                        >

                            <form
                                action="#"
                                method="POST"
                                enctype="multipart/form-data"
                            >

                                @csrf

                                <div
                                    class="flex flex-col lg:flex-row
                                           lg:items-end gap-5"
                                >


                                    {{-- TANGGAL TENGGAT --}}
                                    <div class="w-full lg:w-[250px]">

                                        <label
                                            for="deadline"
                                            class="block text-[14px]
                                                   leading-[22px]
                                                   text-[#283044] mb-1"
                                        >
                                            Tenggat
                                        </label>

                                        <input
                                            type="date"
                                            id="deadline"
                                            name="deadline"
                                            class="w-full h-[42px]
                                                   bg-[#F1F1F1]
                                                   rounded-[8px]
                                                   border border-black/30
                                                   px-3
                                                   text-[14px]
                                                   text-black
                                                   outline-none
                                                   focus:border-[#19A7CE]"
                                        >

                                    </div>


                                    {{-- UPLOAD FILE --}}
                                    <div class="w-full lg:flex-1">

                                        <label
                                            for="file_revisi"
                                            class="block text-[14px]
                                                   leading-[22px]
                                                   text-[#283044] mb-1"
                                        >
                                            File Revisi
                                        </label>


                                        <div
                                            class="w-full h-[42px]
                                                   bg-[#F1F1F1]
                                                   rounded-[8px]
                                                   border border-black/30
                                                   flex items-center
                                                   overflow-hidden"
                                        >

                                            <label
                                                for="file_revisi"
                                                class="ml-2 h-[28px]
                                                       px-3
                                                       rounded-[4px]
                                                       bg-[#CCCCCC]
                                                       shadow-[0_4px_4px_rgba(0,0,0,0.25)]
                                                       flex items-center
                                                       cursor-pointer
                                                       text-[14px]
                                                       text-black
                                                       shrink-0"
                                            >
                                                Choose File
                                            </label>


                                            <span
                                                id="file-name"
                                                class="ml-3
                                                       text-[12px]
                                                       leading-[20px]
                                                       text-black/50
                                                       truncate"
                                            >
                                                No file chosen
                                            </span>


                                            <input
                                                type="file"
                                                id="file_revisi"
                                                name="file_revisi"
                                                class="hidden"
                                                onchange="
                                                    document.getElementById('file-name').textContent =
                                                    this.files.length
                                                        ? this.files[0].name
                                                        : 'No file chosen'
                                                "
                                            >

                                        </div>

                                    </div>


                                    {{-- SUBMIT --}}
                                    <button
                                        type="submit"
                                        class="w-[148px] h-[36px]
                                               shrink-0
                                               bg-[#0E9DC3]
                                               hover:bg-[#0C89AA]
                                               rounded-[9px]
                                               px-4
                                               flex items-center
                                               justify-center
                                               text-white
                                               text-[16px]
                                               leading-[24px]
                                               transition"
                                    >
                                        Submit
                                    </button>

                                </div>

                            </form>

                        </div>

                    </section>

                </div>

            </main>

        </div>

    </div>

</body>

</html>