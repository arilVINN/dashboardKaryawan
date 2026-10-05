<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Kepala Divisi - PT SILINDO</title>

    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">

    {{-- =========================================================
         SIDEBAR
         Menggunakan sidebar utama project
    ========================================================== --}}
    @include('component_kadiv.sidebar')


    {{-- =========================================================
         CONTENT AREA
    ========================================================== --}}
    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        {{-- Topbar utama project --}}
        @include('component_kadiv.topbar')


        {{-- =====================================================
             MAIN CONTENT
        ====================================================== --}}
        <main class="flex-1 overflow-y-auto p-8">

            {{-- =================================================
                 SUMMARY CARDS
            ================================================== --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 pt-5">

                {{-- Card 1 --}}
                <div class="rounded-md drop-shadow-md bg-white p-4 flex items-center justify-between">

                    <div class="flex flex-col min-w-0">
                        <span class="text-sm font-bold text-slate-500">
                            Tugas yang belum
                        </span>

                        <span class="text-xl font-bold text-slate-800 mt-1">
                            0/10 Tugas
                        </span>
                    </div>

                    <svg
                        class="w-8 h-8 text-yellow-500 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24">

                        <circle cx="12" cy="12" r="10"></circle>

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 8v4m0 4h.01">
                        </path>

                    </svg>

                </div>


                {{-- Card 2 --}}
                <div class="rounded-md drop-shadow-md bg-white p-4 flex items-center justify-between">

                    <div class="flex flex-col min-w-0">
                        <span class="text-sm font-bold text-slate-500">
                            Tugas belum di-ACC
                        </span>

                        <span class="text-xl font-bold text-slate-800 mt-1">
                            0/10 Tugas
                        </span>
                    </div>

                    <svg
                        class="w-8 h-8 text-yellow-500 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24">

                        <circle cx="12" cy="12" r="10"></circle>

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M10 9v6m4-6v6">
                        </path>

                    </svg>

                </div>


                {{-- Card 3 --}}
                <div class="rounded-md drop-shadow-md bg-white p-4 flex items-center justify-between">

                    <div class="flex flex-col min-w-0">
                        <span class="text-sm font-bold text-slate-500">
                            Tugas selesai
                        </span>

                        <span class="text-xl font-bold text-slate-800 mt-1">
                            0/10 Tugas
                        </span>
                    </div>

                    <svg
                        class="w-8 h-8 text-green-500 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.5"
                        viewBox="0 0 24 24">

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M5 13l4 4L19 7">
                        </path>

                    </svg>

                </div>


                {{-- Card 4 --}}
                <div class="rounded-md drop-shadow-md bg-white p-4 flex items-center justify-between">

                    <div class="flex flex-col min-w-0">
                        <span class="text-sm font-bold text-slate-500">
                            Persentase tugas
                        </span>

                        <span class="text-xl font-bold text-slate-800 mt-1">
                            17%
                        </span>
                    </div>

                    <svg
                        class="w-8 h-8 shrink-0"
                        viewBox="0 0 24 24">

                        <circle
                            cx="12"
                            cy="12"
                            r="10"
                            fill="#60A5FA">
                        </circle>

                        <path
                            d="M12 12 L12 2 A10 10 0 0 1 22 12 Z"
                            fill="#FCD34D">
                        </path>

                        <path
                            d="M12 12 L22 12 A10 10 0 0 1 18 19.5 Z"
                            fill="#F87171">
                        </path>

                        <circle
                            cx="12"
                            cy="12"
                            r="10"
                            fill="none"
                            stroke="white"
                            stroke-width="1">
                        </circle>

                        <path
                            d="M12 12 L12 2"
                            stroke="white"
                            stroke-width="1">
                        </path>

                        <path
                            d="M12 12 L22 12"
                            stroke="white"
                            stroke-width="1">
                        </path>

                    </svg>

                </div>

            </div>


            {{-- =================================================
                 MANAJEMEN STAFF
            ================================================== --}}
            <h1 class="text-2xl font-bold text-gray-800 pt-8">
                Manajemen Staff
            </h1>


            <div class="mt-5 overflow-x-auto">

                <div class="min-w-[700px] rounded-md bg-white shadow-md overflow-hidden">

                    {{-- Header --}}
                    <div class="grid grid-cols-[2fr_1fr_1.5fr_1fr] items-center bg-slate-100 px-4 py-3">

                        <span class="text-sm font-medium text-slate-600">
                            Nama Divisi
                        </span>

                        <span class="text-sm font-medium text-slate-600 text-center">
                            Tugas
                        </span>

                        <span class="text-sm font-medium text-slate-600 text-center">
                            Keterangan
                        </span>

                        <span class="text-sm font-medium text-slate-600 text-center">
                            Aksi
                        </span>

                    </div>


                    {{-- Empty --}}
                    <div class="h-44 flex items-center justify-center text-sm text-slate-500">

                        Belum ada data staff.

                    </div>

                </div>

            </div>


            {{-- =================================================
                 FILTER + PAGINATION
            ================================================== --}}
            <div class="flex flex-wrap items-center justify-between gap-4 mt-4">

                <button
                    type="button"
                    class="flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-600 hover:bg-slate-50">

                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24">

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M7 12h10M10 18h4">
                        </path>

                    </svg>

                    Filter

                </button>


                <div class="flex items-center gap-2">

                    <button
                        type="button"
                        class="h-7 w-7 rounded border border-slate-300 text-sm">
                        «
                    </button>

                    <button
                        type="button"
                        class="h-7 w-7 rounded bg-[#19A7CE] text-sm text-white">
                        1
                    </button>

                    <button
                        type="button"
                        class="h-7 w-7 rounded border border-slate-300 text-sm">
                        2
                    </button>

                    <button
                        type="button"
                        class="h-7 w-7 rounded border border-slate-300 text-sm">
                        3
                    </button>

                    <button
                        type="button"
                        class="h-7 w-7 rounded border border-slate-300 text-sm">
                        4
                    </button>

                    <button
                        type="button"
                        class="h-7 w-7 rounded border border-slate-300 text-sm">
                        » 
                    </button>

                </div>

            </div>


            {{-- =================================================
                 TUGAS + PESAN
            ================================================== --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start mt-8">


                {{-- =================================================
                     TUGAS
                ================================================== --}}
                <section>

                    <h2 class="text-2xl font-bold text-gray-800">
                        Tugas
                    </h2>


                    <div class="mt-4 rounded-md bg-white shadow-md overflow-hidden">

                        {{-- Header --}}
                        <div class="grid grid-cols-[1.2fr_1fr_1fr_1fr] items-center bg-slate-100 px-4 py-3">

                            <span class="text-xs text-slate-600">
                                Nama
                            </span>

                            <span class="text-xs text-slate-600">
                                Keterangan
                            </span>

                            <span class="text-xs text-slate-600">
                                Tanggal
                            </span>

                            <span class="text-xs text-slate-600 text-center">
                                Aksi
                            </span>

                        </div>


                        {{-- Empty --}}
                        <div class="h-52 flex items-center justify-center text-sm text-slate-500">

                            Belum ada tugas.

                        </div>

                    </div>

                </section>


                {{-- =================================================
                     PESAN
                ================================================== --}}
                <section>

                    <h2 class="text-2xl font-bold text-gray-800">
                        Pesan
                    </h2>


                    <div class="mt-4 rounded-md bg-white shadow-md overflow-hidden">

                        {{-- Header --}}
                        <div class="grid grid-cols-[1.2fr_1fr_1fr_1fr] items-center bg-slate-100 px-4 py-3">

                            <span class="text-xs text-slate-600">
                                Isi
                            </span>

                            <span class="text-xs text-slate-600">
                                Pengirim
                            </span>

                            <span class="text-xs text-slate-600">
                                Tanggal
                            </span>

                            <div class="flex items-center justify-center gap-2">

                                <button
                                    type="button"
                                    class="rounded bg-[#0E9DC3] px-3 py-1 text-xs text-white">
                                    Lihat
                                </button>

                            </div>

                        </div>


                        {{-- Empty --}}
                        <div class="h-52 flex items-center justify-center text-sm text-slate-500">

                            Belum ada pesan.

                        </div>

                    </div>

                </section>

            </div>

        </main>

    </div>

</body>

</html>