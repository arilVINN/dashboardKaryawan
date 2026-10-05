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
    @include('component_kadiv.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        {{-- TOPBAR --}}
        @include('component_kadiv.topbar')

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
                        id="openModal"
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
                        add tugas
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

    @php
        $daftarTugas = [
            ['divisi' => 'Content Writer',    'tugas' => 'Membuat artikel website', 'keterangan' => 'Sedang dikerjakan'],
            ['divisi' => 'Digital Marketing', 'tugas' => 'Membuat konten promosi',  'keterangan' => 'Menunggu pemeriksaan'],
            ['divisi' => 'IT Support',        'tugas' => 'Pemeriksaan sistem',      'keterangan' => 'Selesai'],
            ['divisi' => 'Human Resource',    'tugas' => 'Rekap data karyawan',     'keterangan' => 'Sedang dikerjakan'],
            ['divisi' => 'Content Writer',    'tugas' => 'Review artikel',          'keterangan' => 'Menunggu revisi'],
        ];
    @endphp

    @foreach ($daftarTugas as $item)
        <a href="{{ url('/kadiv/detailTugas/' . $loop->iteration) }}"
           class="grid grid-cols-[40%_20.5%_21.3%_18.2%]
                  min-h-[64px]
                  border-b border-[#E5E7EB]
                  text-[14px]
                  leading-[22px]
                  text-[#565E74]
                  hover:bg-[#F8FAFC]
                  transition-colors duration-150
                  cursor-pointer">

            <div class="flex items-center px-[20px]">
                {{ $item['divisi'] }}
            </div>

            <div class="flex items-center px-[20px]">
                {{ $item['tugas'] }}
            </div>

            <div class="flex items-center px-[20px]">
                {{ $item['keterangan'] }}
            </div>

            <div></div>

         </a>
         @endforeach

        </div>

            <div></div>

            </div>

            </div>

            </div>

            </div>

        </main>

    </div>
    {{-- MODAL TAMBAH TUGAS --}}
    @include('component_kadiv.addTugas')
</body>
</html>