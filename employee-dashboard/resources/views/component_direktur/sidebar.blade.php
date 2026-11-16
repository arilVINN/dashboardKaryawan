<aside id="sidebar"
    class="w-64 flex flex-col h-screen shrink-0 bg-gradient-to-b from-[#044564] from-50% to-[#19A7CE] transition-all duration-150 ease-in-out">

    {{-- Brand Header --}}
    <div
        class="h-16 flex items-center justify-between px-4 bg-white rounded-bl-3xl border-b border-r border-slate-200 shrink-0 box-border overflow-hidden">
        <div class="sidebar-text flex items-center gap-2.5 overflow-hidden transition-all duration-150">
            <img src="{{ asset('gambar/silindo.png') }}" alt="Logo" class="w-10 h-10 object-contain shrink-0">
            <div class="flex flex-col leading-tight whitespace-nowrap">
                <div class="flex items-center gap-1.5">
                    <h1 class="text-sm font-bold text-[#2A4B6A] tracking-wide">PT SILINDO</h1>
                    <span class="px-1.5 py-0.5 text-[9px] font-bold bg-amber-100 text-amber-800 rounded border border-amber-300">DIREKSI</span>
                </div>
                <p class="text-[9px] font-medium text-[#1CA4BA] tracking-tight">PORTAL EKSEKUTIF</p>
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

    {{-- Navigation Menu --}}
    <nav class="flex-1 p-3 space-y-1.5 overflow-y-auto text-xs">

        {{-- ── 1. Dashboard Eksekutif ── --}}
        <a href="{{ route('direktur.dashboard') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('direktur.dashboard') ? 'bg-white/40 text-white font-semibold shadow-sm' : 'text-slate-200 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                </path>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150 font-medium">Dashboard Direktur</span>
        </a>

        {{-- ── 2. Pilar Personalia & HRD ── --}}
        <div class="pt-3 pb-1 border-t border-white/10">
            <p class="px-2.5 text-[9px] font-bold uppercase tracking-wider text-amber-200/90 sidebar-text flex items-center gap-1.5">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                Personalia & HRD
            </p>
        </div>

        <a href="{{ url('/hrd/daftarKaryawan') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->is('hrd/daftarKaryawan*') || request()->is('hrd/detailKaryawan*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Manajemen Karyawan</span>
        </a>

        <a href="{{ url('/hrd/manajemenDivisi') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->is('hrd/manajemenDivisi*') || request()->is('hrd/detailDivisi*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Manajemen Divisi</span>
        </a>

        <a href="{{ url('/hrd/struktur-organisasi') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->is('*struktur-organisasi*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15M8.25 9.75h7.5m-7.5 4.5h7.5" />
                <rect x="3" y="3" width="18" height="18" rx="3" />
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Bagan Organisasi</span>
        </a>

        <a href="{{ url('/hrd/suratIzin') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->is('hrd/suratIzin*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Persetujuan Surat Izin</span>
        </a>

        <a href="{{ url('/hrd/suratBooking') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->is('hrd/suratBooking*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Persetujuan Booking</span>
        </a>

        <a href="{{ url('/hrd/analitikKaryawan') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->is('hrd/analitikKaryawan*') || request()->is('hrd/turnoverKaryawan*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Analitik SDM & Turnover</span>
        </a>

        {{-- ── 3. Pilar Keuangan (Finance) ── --}}
        <div class="pt-3 pb-1 border-t border-white/10">
            <p class="px-2.5 text-[9px] font-bold uppercase tracking-wider text-amber-200/90 sidebar-text flex items-center gap-1.5">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Keuangan & Finance
            </p>
        </div>

        <a href="{{ route('kadiv.keuangan') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('kadiv.keuangan') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Ringkasan Keuangan</span>
        </a>

        <a href="{{ route('kadiv.keuangan.rekapDivisi') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('kadiv.keuangan.rekapDivisi*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Rekap Laba / Rugi</span>
        </a>

        <a href="{{ route('kadiv.keuangan.arusKas') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('kadiv.keuangan.arusKas*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Arus Kas Perusahaan</span>
        </a>

        <a href="{{ route('kadiv.keuangan.manajemenDana') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('kadiv.keuangan.manajemenDana*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Permintaan & Verifikasi</span>
        </a>

        <a href="{{ route('kadiv.keuangan.alokasi') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('kadiv.keuangan.alokasi*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Pagu Anggaran Divisi</span>
        </a>

        {{-- ── 4. Pilar Operasional (Kadiv) ── --}}
        <div class="pt-3 pb-1 border-t border-white/10">
            <p class="px-2.5 text-[9px] font-bold uppercase tracking-wider text-amber-200/90 sidebar-text flex items-center gap-1.5">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                Operasional Divisi
            </p>
        </div>

        <a href="{{ url('/kadiv/tugas') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->is('kadiv/tugas*') || request()->is('kadiv/detailTugas*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <rect x="3" y="4" width="6" height="6" rx="1" stroke="currentColor" stroke-width="2"></rect>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5h8M13 12h8M13 19h8M3 17l2 2 4-4"></path>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Monitoring Tugas Divisi</span>
        </a>

        <a href="{{ url('/kadiv/manajemenStaff') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->is('kadiv/manajemenStaff*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Manajemen Staf Divisi</span>
        </a>

        <a href="{{ route('kadiv.pengajuanDana') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('kadiv.pengajuanDana*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Pengajuan Dana Divisi</span>
        </a>

        {{-- ── 5. Workspace Pribadi (Staff) ── --}}
        <div class="pt-3 pb-1 border-t border-white/10">
            <p class="px-2.5 text-[9px] font-bold uppercase tracking-wider text-amber-200/90 sidebar-text flex items-center gap-1.5">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                Workspace Pribadi
            </p>
        </div>

        <a href="{{ url('/tugas') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->is('tugas') || request()->is('tugas/detail*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Tugas Saya</span>
        </a>

        <a href="{{ url('/kalender') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->is('kalender*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Kalender Kerja</span>
        </a>

        <a href="{{ url('/jurnal') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->is('jurnal*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Jurnal Harian</span>
        </a>

        @if (\App\Support\ContentCalendarAccess::allows(auth()->user()))
        <a href="{{ url('/kalender-konten') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->is('kalender-konten*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                <path d="M12 11.2l.85 1.95 1.95.85-1.95.85L12 16.8l-.85-1.95-1.95-.85 1.95-.85L12 11.2z" fill="currentColor" stroke="none"></path>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Kalender Konten</span>
        </a>
        @endif

        <a href="{{ url('/pesan') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->is('pesan*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Pesan & Informasi</span>
        </a>

        <a href="{{ url('/pusat-data') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->is('pusat-data*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Pusat Data & Arsip</span>
        </a>

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
