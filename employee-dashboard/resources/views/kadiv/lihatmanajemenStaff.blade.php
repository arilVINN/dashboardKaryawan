<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manajemen Staff - PT SILINDO</title>

    @vite('resources/css/app.css')
</head>

<body class="m-0 min-h-screen bg-white font-sans antialiased overflow-x-hidden">

<div class="flex min-h-screen w-full bg-white">

    {{-- SIDEBAR KADIV --}}
    @include('component_kadiv.sidebar')

    {{-- MAIN CONTENT --}}
    <div class="flex-1 min-w-0 min-h-screen bg-white">

        {{-- TOPBAR KADIV --}}
        <header class="h-[79px] w-full bg-white shadow-[0_4px_4px_rgba(0,0,0,0.20)] flex items-center">
            <div class="w-full flex items-center justify-between gap-8 px-8 lg:px-10">

                {{-- Search --}}
                <div class="h-[51px] w-full max-w-[693px] ml-[10%] flex items-center gap-[6px] px-[8px] py-[4px] rounded-[8px] border border-[#94A3B880]">
                    <svg class="w-[22px] h-[22px] shrink-0" fill="none" stroke="#64748B" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0a7 7 0 0 1 14 0z"></path>
                    </svg>

                    <span class="text-[14px] leading-[22px] text-[#64748B]">Cari</span>
                </div>

                {{-- Notification + Profile --}}
                <div class="shrink-0 flex items-center gap-[24px]">

                    {{-- Notification --}}
                    <button type="button" class="w-[40px] h-[40px] flex items-center justify-center">
                        <svg class="w-[30px] h-[30px]" fill="none" stroke="#283044" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                  d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"></path>
                        </svg>
                    </button>

                    {{-- Profile --}}
                    <div class="flex items-center">
                        <div class="w-[40px] h-[40px] rounded-full bg-[#D9D9D9]"></div>

                        <button type="button" class="w-[40px] h-[40px] flex items-center justify-center">
                            <svg class="w-[16px] h-[16px]" fill="none" stroke="#283044" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="m6 9 6 6 6-6"></path>
                            </svg>
                        </button>
                    </div>

                </div>

            </div>
        </header>

        {{-- CONTENT --}}
        <main class="px-8 lg:px-12 pt-8 pb-16">

            {{-- Title + Action --}}
            <div class="flex items-center justify-between gap-6">
                <h1 class="m-0 font-['PT_Sans'] font-bold text-[24px] leading-[32px] text-black whitespace-nowrap">
                    Manajement Staff
                </h1>

                <button type="button"
                        class="shrink-0 w-[148px] h-[36px] flex items-center justify-center gap-[7px] px-[16px] rounded-[9px] bg-[#0E9DC3] text-white font-['PT_Sans'] font-normal text-[16px] leading-[24px] hover:bg-[#146C94] transition">
                    Aksi
                </button>
            </div>

            {{-- STAFF TABLE --}}
            <section class="mt-[30px] w-full max-w-[1011px] overflow-hidden shadow-[0_4px_4px_rgba(0,0,0,0.25)]">

                {{-- Table Header --}}
                <div class="grid grid-cols-[40%_20.5%_21.3%_18.2%] w-full min-h-[30px] bg-[#D9D9D9]">

                    {{-- Nama Staff --}}
                    <div class="flex items-center justify-start px-[10px] gap-[10px]">
                        <span class="text-[14px] leading-[22px] text-[#565E74]">Nama staff</span>

                        <svg class="w-[12px] h-[12px]" fill="none" stroke="#565E74" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="m8 9 4-4 4 4M8 15l4 4 4-4"></path>
                        </svg>
                    </div>

                    {{-- Tugas --}}
                    <div class="flex items-center justify-center px-[10px]">
                        <span class="text-[14px] leading-[22px] text-[#565E74]">Tugas</span>
                    </div>

                    {{-- Approval --}}
                    <div class="flex items-center justify-end px-[10px] gap-[10px]">
                        <span class="text-[14px] leading-[22px] text-[#565E74]">Approval</span>

                        <svg class="w-[12px] h-[12px]" fill="none" stroke="#565E74" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="m8 9 4-4 4 4M8 15l4 4 4-4"></path>
                        </svg>
                    </div>

                    {{-- Aksi --}}
                    <div class="flex items-center justify-center px-[10px]">
                        <span class="text-[14px] leading-[22px] text-[#565E74]">Aksi</span>
                    </div>

                </div>

                {{-- TABLE ROW --}}
                <div class="grid grid-cols-[40%_20.5%_21.3%_18.2%] w-full min-h-[50px] bg-[#F6F1F1] border border-black/50">

                    {{-- Staff --}}
                    <div class="flex items-center justify-start px-[10px]">
                        <span class="text-[14px] leading-[22px] text-[#565E74]">Agung</span>
                    </div>

                    {{-- Tugas --}}
                    <div class="flex items-center justify-center px-[10px]">
                        <span class="text-[14px] leading-[22px] text-[#565E74]">Menulis poster</span>
                    </div>

                    {{-- Approval --}}
                    <div class="flex items-center justify-end px-[10px]">
                        <span class="text-[14px] leading-[22px] text-[#565E74]">Pending</span>
                    </div>

                    {{-- Aksi --}}
                    <div class="flex items-center justify-center px-[10px]">
                        <button type="button" class="text-[14px] leading-[22px] text-[#146C94] hover:underline">
                            Lihat
                        </button>
                    </div>

                </div>

                {{-- Empty table area --}}
                <div class="w-full min-h-[637px] bg-white"></div>

            </section>

        </main>

    </div>

</div>

</body>
</html>