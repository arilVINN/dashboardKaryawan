<aside id="sidebar"
    class="w-64 flex flex-col h-screen shrink-0 bg-gradient-to-b from-[#044564] from-50% to-[#19A7CE] transition-all duration-150 ease-in-out">

    <div
        class="h-16 flex items-center justify-between px-4 bg-white rounded-bl-3xl border-b border-r border-slate-200 shrink-0 box-border overflow-hidden">

        <div class="sidebar-text flex items-center gap-2.5 overflow-hidden transition-all duration-150">
            <img src="{{ asset('gambar/silindo.png') }}" alt="Logo" class="w-10 h-10 object-contain shrink-0">
            <div class="flex flex-col leading-tight whitespace-nowrap">
                <h1 class="text-sm font-bold text-[#2A4B6A] tracking-wide">PT SILINDO</h1>
                <p class="text-[9px] font-medium text-[#1CA4BA] tracking-tight">PT SINERGI ILMIAH INDONESIA</p>
            </div>
        </div>

        <button type="button" id="btn-sidebar-toggle"
            class="p-1.5 text-[#2A4B6A] hover:bg-slate-100 rounded-lg transition shrink-0 cursor-pointer mx-auto">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                </path>
            </svg>
        </button>
    </div>

    <nav class="flex-1 p-3 space-y-2 overflow-y-auto">

        {{-- Dashboard --}}
        <a href="{{ url('/kadiv/dashboard') }}"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->is('/') || request()->is('kadiv/dashboard*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                </path>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Dashboard</span>
        </a>

        {{-- Manajemen Staff --}}
        <a href="{{ url('/kadiv/manajemenStaff') }}"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->is('kadiv/manajemenStaff*') || request()->is('kadiv/detailManajemenStaff*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Manajemen Staff</span>
        </a>

        {{-- Bagan Organisasi --}}
        <a href="{{ url('/kadiv/struktur-organisasi') }}"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->is('kadiv/struktur-organisasi*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15M8.25 9.75h7.5m-7.5 4.5h7.5" />
                <rect x="3" y="3" width="18" height="18" rx="3" />
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Bagan Organisasi</span>
        </a>

        {{-- Tugas --}}
        <a href="{{ url('/kadiv/tugas') }}"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->is('kadiv/tugas*') || request()->is('kadiv/detailTugas*')? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <rect x="3" y="4" width="6" height="6" rx="1" stroke="currentColor" stroke-width="2"></rect>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 5h8M13 12h8M13 19h8M3 17l2 2 4-4"></path>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Tugas</span>
        </a>

        {{-- Kalender --}}
        <a href="{{ url('/kadiv/kalender') }}"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->is('kadiv/kalender*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Kalender</span>
        </a>

        {{-- Pesan --}}
        <a href="{{ url('/kadiv/pesan') }}"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->is('kadiv/pesan*') || request()->is('kadiv/detailPesan*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z">
                </path>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Pesan</span>
        </a>

        {{-- Pusat Data & Arsip --}}
        <a href="{{ url('/kadiv/pusat-data') }}"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->is('kadiv/pusat-data*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Pusat Data</span>
        </a>

        {{-- Menu Keuangan (Kondisional: Kadiv Finance vs Kadiv Lain) --}}
        @if (auth()->user()?->isKadivFinance())
            <div class="pt-3 pb-1 border-t border-white/10">
                <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-300/80 sidebar-text">Keuangan Perusahaan</p>
            </div>

            {{-- Ringkasan Keuangan --}}
            <a href="{{ route('kadiv.keuangan') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->routeIs('kadiv.keuangan') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="sidebar-text whitespace-nowrap transition-all duration-150">Ringkasan Keuangan</span>
            </a>

            {{-- Rekap Laba/Rugi Divisi --}}
            <a href="{{ route('kadiv.keuangan.rekapDivisi') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->routeIs('kadiv.keuangan.rekapDivisi*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="sidebar-text whitespace-nowrap transition-all duration-150">Rekap Laba/Rugi</span>
            </a>

            {{-- Arus Kas (Kas Masuk + Kas Keluar) --}}
            <a href="{{ route('kadiv.keuangan.arusKas') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->routeIs('kadiv.keuangan.arusKas*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <span class="sidebar-text whitespace-nowrap transition-all duration-150">Arus Kas</span>
            </a>

            {{-- Permintaan & Verifikasi --}}
            <a href="{{ route('kadiv.keuangan.manajemenDana') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->routeIs('kadiv.keuangan.manajemenDana*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <span class="sidebar-text whitespace-nowrap transition-all duration-150">Permintaan & Verifikasi</span>
            </a>
        @else
            <div class="pt-3 pb-1 border-t border-white/10">
                <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-300/80 sidebar-text">Keuangan Divisi</p>
            </div>

            {{-- Pengajuan Dana --}}
            <a href="{{ route('kadiv.pengajuanDana') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->routeIs('kadiv.pengajuanDana*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <span class="sidebar-text whitespace-nowrap transition-all duration-150">Pengajuan Dana</span>
            </a>

            {{-- Laporan Laba/Rugi Mingguan --}}
            <a href="{{ route('kadiv.laporanLabaRugi') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->routeIs('kadiv.laporanLabaRugi*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="sidebar-text whitespace-nowrap transition-all duration-150">Laporan Laba / Rugi</span>
            </a>
        @endif
    </nav>
</aside>

<script>
(function () {
    const sidebar = document.getElementById('sidebar');
    const btnToggle = document.getElementById('btn-sidebar-toggle');
    const texts = document.querySelectorAll('.sidebar-text');
    const sidebarStorageKey = 'sidebar-collapsed';

    function setSidebarCollapsed(collapsed) {
        sidebar.classList.toggle('w-64', !collapsed);
        sidebar.classList.toggle('w-20', collapsed);
        texts.forEach(el => el.classList.toggle('hidden', collapsed));
    }

    if (btnToggle && sidebar) {
        setSidebarCollapsed(localStorage.getItem(sidebarStorageKey) === 'true');

        btnToggle.addEventListener('click', function() {
            const collapsed = !sidebar.classList.contains('w-20');
            setSidebarCollapsed(collapsed);
            localStorage.setItem(sidebarStorageKey, String(collapsed));
        });
    }
})();
</script>
