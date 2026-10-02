<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manajement Tugas - Kadiv</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white flex h-screen overflow-hidden">

    {{-- SIDEBAR --}}
    @include('component.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        {{-- TOPBAR --}}
        @include('component.topbar')

        <main class="flex-1 overflow-y-auto">

            <div class="px-9 pt-7 pb-10">

                {{-- HEADER HALAMAN --}}
                <div class="relative h-[98px]">

                    <h1 class="text-[24px] leading-[32px] font-bold text-black">
                        Manajement Tugas
                    </h1>

                    {{-- BUTTON AKSI --}}
                    <button
                        type="button"
                        class="absolute right-0 top-[32px]
                               w-[148px] h-[36px]
                               rounded-[9px]
                               bg-[#0E9DC3]
                               text-white
                               text-[16px]
                               leading-[24px]
                               font-normal
                               flex items-center justify-center
                               hover:bg-[#0B8EAF]
                               transition-colors duration-200">
                        Aksi
                    </button>

                </div>

                {{-- TABEL --}}
                <div class="w-full bg-white
                            shadow-[0_4px_4px_rgba(0,0,0,0.25)]
                            overflow-hidden">

                    {{-- HEADER TABEL --}}
                    <div class="grid grid-cols-[40%_20.5%_21.3%_18.2%]
                                h-[30px]
                                bg-[#D9D9D9]
                                text-[14px]
                                leading-[22px]
                                text-[#565E74]">

                        {{-- NAMA DIVISI --}}
                        <div class="flex items-center gap-[10px] px-[10px]">
                            <span>Nama Divisi</span>

                            <span class="text-[12px] leading-none">
                                ↕
                            </span>
                        </div>

                        {{-- TUGAS --}}
                        <div class="flex items-center justify-center px-[10px]">
                            <span>Tugas</span>
                        </div>

                        {{-- KETERANGAN --}}
                        <div class="flex items-center gap-[10px] px-[10px]">
                            <span>Keterangan</span>

                            <span class="text-[12px] leading-none">
                                ↕
                            </span>
                        </div>

                        {{-- KOLOM AKSI --}}
                        <div></div>

                    </div>

                    {{-- ISI TABEL --}}
                    <div class="bg-white min-h-[716px]">

                        {{-- BARIS 1 --}}
                        <div class="grid grid-cols-[40%_20.5%_21.3%_18.2%]
                                    min-h-[64px]
                                    border-b border-[#E5E7EB]
                                    text-[14px]
                                    leading-[22px]
                                    text-[#565E74]">

                            <div class="flex items-center px-[20px]">
                                Content Writer
                            </div>

                            <div class="flex items-center px-[20px]">
                                Membuat artikel website
                            </div>

                            <div class="flex items-center px-[20px]">
                                Sedang dikerjakan
                            </div>

                            <div></div>

                        </div>

                        {{-- BARIS 2 --}}
                        <div class="grid grid-cols-[40%_20.5%_21.3%_18.2%]
                                    min-h-[64px]
                                    border-b border-[#E5E7EB]
                                    text-[14px]
                                    leading-[22px]
                                    text-[#565E74]">

                            <div class="flex items-center px-[20px]">
                                Digital Marketing
                            </div>

                            <div class="flex items-center px-[20px]">
                                Membuat konten promosi
                            </div>

                            <div class="flex items-center px-[20px]">
                                Menunggu pemeriksaan
                            </div>

                            <div></div>

                        </div>

                        {{-- BARIS 3 --}}
                        <div class="grid grid-cols-[40%_20.5%_21.3%_18.2%]
                                    min-h-[64px]
                                    border-b border-[#E5E7EB]
                                    text-[14px]
                                    leading-[22px]
                                    text-[#565E74]">

                            <div class="flex items-center px-[20px]">
                                IT Support
                            </div>

                            <div class="flex items-center px-[20px]">
                                Pemeriksaan sistem
                            </div>

                            <div class="flex items-center px-[20px]">
                                Selesai
                            </div>

                            <div></div>

                        </div>

                        {{-- BARIS 4 --}}
                        <div class="grid grid-cols-[40%_20.5%_21.3%_18.2%]
                                    min-h-[64px]
                                    border-b border-[#E5E7EB]
                                    text-[14px]
                                    leading-[22px]
                                    text-[#565E74]">

                            <div class="flex items-center px-[20px]">
                                Human Resource
                            </div>

                            <div class="flex items-center px-[20px]">
                                Rekap data karyawan
                            </div>

                            <div class="flex items-center px-[20px]">
                                Sedang dikerjakan
                            </div>

                            <div></div>

                        </div>

                        {{-- BARIS 5 --}}
                        <div class="grid grid-cols-[40%_20.5%_21.3%_18.2%]
                                    min-h-[64px]
                                    border-b border-[#E5E7EB]
                                    text-[14px]
                                    leading-[22px]
                                    text-[#565E74]">

                            <div class="flex items-center px-[20px]">
                                Content Writer
                            </div>

                            <div class="flex items-center px-[20px]">
                                Review artikel
                            </div>

                            <div class="flex items-center px-[20px]">
                                Menunggu revisi
                            </div>

                            <div></div>

                        </div>

                    </div>

                </div>

            </div>

        </main>

    </div>

</body>
</html>