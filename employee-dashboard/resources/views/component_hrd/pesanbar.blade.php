@php
    $total = [
        'pesan' => $totalPesan ?? 0,
        'masuk' => $pesanMasuk ?? 0,
        'suratIzinMasuk' => $suratIzinMasuk ?? 0,
    ];
@endphp

<h2 class="text-2xl font-bold text-slate-800 flex items-center gap-2.5 mb-5">
    <span class="p-2 rounded-lg bg-orange-100 text-orange-500">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75">
            </path>
        </svg>
    </span>
    <span>
        Pusat Pesan & Komunikasi
    </span>
</h2>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2 pb-2">
    <div class="rounded-xl drop-shadow-md bg-white p-4 flex items-center justify-between">
        <div class="flex flex-col">
            <span class="text-xs font-bold uppercase tracking-widest text-slate-700">Total Pesan</span>
            <span class="text-2xl font-bold text-slate-900 leading-none mt-1">{{ $total['pesan'] }}</span>
            <div class="flex justify-between items-center pt-0.5">
                <span class="text-xs text-slate-400 mt-1">Semua Pesan</span>
            </div>
        </div>
        <svg class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 p-2" fill="none" stroke="currentColor"
            stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M21.75 9v.906a2.25 2.25 0 0 1-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 0 0 1.183 1.981l6.478 3.488m8.839 2.51-4.66-2.51m0 0-1.023-.55a2.25 2.25 0 0 0-2.134 0l-1.022.55m0 0-4.661 2.51m16.5 1.615a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V8.844a2.25 2.25 0 0 1 1.183-1.981l7.5-4.039a2.25 2.25 0 0 1 2.134 0l7.5 4.039a2.25 2.25 0 0 1 1.183 1.98V19.5Z">
            </path>
        </svg>
    </div>

    <div class="rounded-xl drop-shadow-md bg-white p-4 flex items-center justify-between">
        <div class="flex flex-col">
            <span class="text-xs font-bold uppercase tracking-widest text-slate-700">Pesan Masuk</span>
            <span class="text-2xl font-bold text-slate-900 leading-none mt-1">{{ $total['masuk'] }}</span>
            <div class="flex justify-between items-center pt-0.5">
                <span class="text-xs text-slate-400 mt-1">Semua Pesan yang Masuk</span>
            </div>
        </div>
        <svg class="w-10 h-10 rounded-lg bg-green-100 text-green-600 p-2" fill="none" stroke="currentColor"
            stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75">
            </path>
        </svg>
    </div>

    <a href="{{ route('hrd.suratIzin') }}"
        class="rounded-xl drop-shadow-md bg-white p-4 flex items-center justify-between hover:shadow-lg transition focus:outline-none focus:ring-2 focus:ring-[#146C94] gap-9">
        <div class="flex flex-col">
            <span class="text-xs font-bold uppercase tracking-widest text-slate-700">Surat Izin Masuk</span>
            <span class="text-2xl font-bold text-slate-900 leading-none mt-1">{{ $total['suratIzinMasuk'] }}</span>
            <div class="flex justify-between items-center pt-0.5">
                <span class="text-xs text-slate-400 mt-1">Lihat Detail</span>
            </div>
        </div>
        <svg class="w-10 h-10 rounded-lg bg-violet-100 text-violet-600 p-2" fill="none" stroke="currentColor"
            stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 3.75H6.912a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M12 3v8.25m0 0-3-3m3 3 3-3">
            </path>
        </svg>
    </a>

</div>
