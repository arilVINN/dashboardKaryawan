@php
    $total = [
        'pesan' => '30',
        'belum_dibaca' => '6',
        'selesai' => '50',
    ];
@endphp

<h2 class="text-xl font-bold text-slate-800">Pusat Pesan & Komunikasi</h2>
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2 pb-2">
    <div class="rounded-md drop-shadow-md bg-white p-4 flex items-center justify-between">
        <div class="flex flex-col">
            <span class="text-sm font-bold text-slate-500">Total Pesan</span>
            <span class="text-xl font-bold text-slate-800 mt-1">{{ $total['pesan'] }}</span>
        </div>
        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75">
            </path>
        </svg>

    </div>

    <div class="rounded-md drop-shadow-md bg-white p-4 flex items-center justify-between">
        <div class="flex flex-col">
            <span class="text-sm font-bold text-slate-500">Belum Dibaca</span>
            <span class="text-xl font-bold text-slate-800 mt-1">{{ $total['belum_dibaca'] }}</span>
        </div>
        <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"></path>
        </svg>
    </div>

    <div class="rounded-md drop-shadow-md bg-white p-4 flex items-center justify-between">
        <div class="flex flex-col">
            <span class="text-sm font-bold text-slate-500">Selesai</span>
            <span class="text-xl font-bold text-slate-800 mt-1">{{ $total['selesai'] }}</span>
        </div>
        <svg class="w-8 h-8 text-slate-800" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 0 1 9 9v.375M10.125 2.25A3.375 3.375 0 0 1 13.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 0 1 3.375 3.375M9 15l2.25 2.25L15 12">
            </path>
        </svg>


    </div>

</div>
