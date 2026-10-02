@php

    $isCompact = $compact ?? false;
    $cellPadding = $isCompact ? 'px-4 py-3' : 'px-6 py-4';

    // Data dummy tugas
    $Tugas = [
        [
            'judul' => 'Slicing UI Dashboard Staff',
            'tenggat' => '30/09/2026',
            'status' => 'Sudah di-Acc',
            'status_class' => 'bg-[#CCF4DB] text-emerald-700',
            'link' => url('/tugas/detail/1'),
        ],
        [
            'judul' => 'Integrasi API Autentikasi',
            'tenggat' => '02/10/2026',
            'status' => 'Berjalan',
            'status_class' => 'bg-[#C0E7FF] text-blue-700',
            'link' => url('/tugas/detail/2'),
        ],
        [
            'judul' => 'Perbaikan Responsif Mobile',
            'tenggat' => '05/10/2026',
            'status' => 'Baru',
            'status_class' => 'bg-[#C0E7FF] text-blue-700',

            'link' => url('/tugas/detail/3'),
        ],
        [
            'judul' => 'Testing Flow Notifikasi & Pesan',
            'tenggat' => '08/10/2026',
            'status' => 'Menunggu Acc',
            'status_class' => 'bg-[#FFF7ED] text-[#C2410C]',
            'link' => url('/tugas/detail/4'),
        ],
        [
            'judul' => 'Testing Flow',
            'tenggat' => '08/10/2026',
            'status' => 'Telat',
            'status_class' => 'bg-red-100 text-red-700',
            'link' => url('/tugas/detail/4'),
        ],
    ];
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-5">

    <div class="rounded-xl border border-slate-100 shadow-sm bg-white p-5 flex flex-col w-full max-w-sm">

        <div class="flex justify-start">
            <span class="text-[11px] font-semibold bg-slate-100 text-slate-600 px-3 py-1 rounded-lg">
                Tugas
            </span>
        </div>

        <div class="mt-4 flex flex-col">
            <h3 class="text-lg font-bold text-slate-900">{{ $Tugas[0]['judul'] }}</h3>
            <p class="text-sm text-slate-600 mt-1.5 leading-relaxed">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque pharetra ut lectus vel luctus.
            </p>
        </div>

        <div class="flex justify-between items-center mt-8">

            <div class="flex items-center gap-2 text-slate-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                    </path>
                </svg>
                <span class="text-xs font-medium">24 Sep 2026</span>
            </div>

            <div
                class="flex items-center gap-1.5 bg-[#eefcf3] text-green-600 border border-green-200 px-2.5 py-1 rounded-md">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-xs font-bold">{{ $Tugas[0]['status'] }}</span>
            </div>

        </div>
    </div>
    <div class="rounded-xl border border-slate-100 shadow-sm bg-white p-5 flex flex-col w-full max-w-sm">

        <div class="flex justify-start">
            <span class="text-[11px] font-semibold bg-slate-100 text-slate-600 px-3 py-1 rounded-lg">
                Tugas
            </span>
        </div>

        <div class="mt-4 flex flex-col">
            <h3 class="text-lg font-bold text-slate-900">{{ $Tugas[1]['judul'] }}</h3>
            <p class="text-sm text-slate-600 mt-1.5 leading-relaxed">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque pharetra ut lectus vel luctus.
            </p>
        </div>

        <div class="flex justify-between items-center mt-8">

            <div class="flex items-center gap-2 text-slate-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                    </path>
                </svg>
                <span class="text-xs font-medium">24 Sep 2026</span>
            </div>

            <div
                class="flex items-center gap-1.5 bg-[#eefcf3] text-green-600 border border-green-200 px-2.5 py-1 rounded-md">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-xs font-bold">{{ $Tugas[1]['status'] }}</span>
            </div>

        </div>
    </div>
    <div class="rounded-xl border border-slate-100 shadow-sm bg-white p-5 flex flex-col w-full max-w-sm">

        <div class="flex justify-start">
            <span class="text-[11px] font-semibold bg-slate-100 text-slate-600 px-3 py-1 rounded-lg">
                Tugas
            </span>
        </div>

        <div class="mt-4 flex flex-col">
            <h3 class="text-lg font-bold text-slate-900">{{ $Tugas[2]['judul'] }}</h3>
            <p class="text-sm text-slate-600 mt-1.5 leading-relaxed">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque pharetra ut lectus vel luctus.
            </p>
        </div>

        <div class="flex justify-between items-center mt-8">

            <div class="flex items-center gap-2 text-slate-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                    </path>
                </svg>
                <span class="text-xs font-medium">24 Sep 2026</span>
            </div>

            <div
                class="flex items-center gap-1.5 bg-[#eefcf3] text-green-600 border border-green-200 px-2.5 py-1 rounded-md">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-xs font-bold">{{ $Tugas[2]['status'] }}</span>
            </div>

        </div>
    </div>



</div>
