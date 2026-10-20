@php
    $total = [
        'pesan' => $totalPesan ?? 0,
        'masuk' => $pesanMasuk ?? 0,
        'suratIzinMasuk' => $suratIzinMasuk ?? 0,
    ];
@endphp

<h2 class="text-2xl font-bold text-slate-800 flex items-center gap-2.5 mb-5"><span class="p-2 rounded-lg bg-green-100 text-green-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"></path></svg></span><span>Pusat Pesan & Komunikasi</span></h2>
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
            <span class="text-sm font-bold text-slate-500">Pesan Masuk</span>
            <span class="text-xl font-bold text-slate-800 mt-1">{{ $total['masuk'] }}</span>
        </div>
        <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"></path>
        </svg>
    </div>

    <a href="{{ route('hrd.suratIzin') }}"
        class="rounded-md drop-shadow-md bg-white p-4 flex items-center justify-between hover:shadow-lg transition focus:outline-none focus:ring-2 focus:ring-[#146C94]">
        <div class="flex flex-col">
            <span class="text-sm font-bold text-slate-500">Surat Izin Masuk</span>
            <span class="text-xl font-bold text-slate-800 mt-1">{{ $total['suratIzinMasuk'] }}</span>
        </div>
        <svg class="w-8 h-8 text-slate-800" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M7 3.75h7l4 4v12.5H7a2 2 0 0 1-2-2v-12.5a2 2 0 0 1 2-2zm7 0v4h4M8.5 12h7M8.5 15.5h7">
            </path>
        </svg>
    </a>

</div>
