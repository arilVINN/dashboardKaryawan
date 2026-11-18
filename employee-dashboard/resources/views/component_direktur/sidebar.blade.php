<aside id="sidebar"
    class="w-64 flex flex-col h-screen shrink-0 bg-gradient-to-b from-[#044564] from-50% to-[#19A7CE] transition-all duration-150 ease-in-out">

    <style>
        #sidebar summary { list-style: none; }
        #sidebar summary::-webkit-details-marker { display: none; }
        #sidebar details[open] > summary .chev { transform: rotate(180deg); }
        #sidebar.w-20 .sidebar-submenu { display: none; }
    </style>

    {{-- Brand Header --}}
    <div class="h-16 flex items-center justify-between px-4 bg-white rounded-bl-3xl border-b border-r border-slate-200 shrink-0 box-border overflow-hidden">
        <div class="sidebar-text flex items-center gap-2.5 overflow-hidden transition-all duration-150">
            <img src="{{ asset('gambar/silindo.png') }}" alt="Logo" class="w-10 h-10 object-contain shrink-0">
            <div class="flex flex-col leading-tight whitespace-nowrap">
                <div class="flex items-center gap-1.5">
                    <h1 class="text-sm font-bold text-[#2A4B6A] tracking-wide">PT SILINDO</h1>
                    <span class="rounded border border-amber-300 bg-amber-100 px-1.5 py-0.5 text-[9px] font-bold text-amber-800">{{ auth()->user()?->isSuperAdmin() ? 'SUPERADMIN' : 'DIREKSI' }}</span>
                </div>
                <p class="text-[9px] font-medium text-[#1CA4BA] tracking-tight">{{ auth()->user()?->isSuperAdmin() ? 'PORTAL ADMINISTRATOR' : 'PORTAL EKSEKUTIF' }}</p>
            </div>
        </div>

        <button type="button" id="btn-sidebar-toggle"
            class="p-1.5 text-[#2A4B6A] hover:bg-slate-100 rounded-lg transition shrink-0 cursor-pointer mx-auto">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </div>

    @php
        $activeClass = 'bg-white/40 text-white font-medium';
        $idleClass = 'text-slate-300 hover:bg-white/20 hover:text-white';
        $groupActiveClass = 'text-white font-medium';
        $groupIdleClass = 'text-slate-300 hover:bg-white/20 hover:text-white';

        $persetujuanOpen = request()->routeIs('hrd.suratIzin*')
            || request()->routeIs('hrd.suratBooking*')
            || request()->routeIs('kadiv.keuangan.permintaan*')
            || request()->routeIs('kadiv.keuangan.manajemenDana*');

        $sdmOpen = request()->is('hrd/daftarKaryawan*')
            || request()->is('hrd/detailKaryawan*')
            || request()->is('hrd/manajemenDivisi*')
            || request()->is('hrd/detailDivisi*')
            || request()->is('hrd/strukturOrganisasi*')
            || request()->is('hrd/analitikKaryawan*')
            || request()->is('hrd/turnoverKaryawan*');

        $keuanganOpen = request()->is('kadiv/keuangan')
            || request()->is('kadiv/keuangan/rekap-divisi*')
            || request()->is('kadiv/keuangan/arus-kas*')
            || request()->is('kadiv/keuangan/alokasi*')
            || request()->is('kadiv/keuangan/pemasukan*')
            || request()->is('kadiv/keuangan/pengeluaran*')
            || request()->is('kadiv/keuangan/laporan*');

        $operasionalOpen = request()->is('kadiv/tugas*')
            || request()->is('kadiv/detailTugas*')
            || request()->is('kadiv/manajemenStaff*')
            || request()->is('kadiv/pengajuan-dana*')
            || request()->is('kadiv/laporan-laba-rugi*');

        $workspaceOpen = request()->is('tugas*')
            || request()->is('kalender*')
            || request()->is('jurnal*')
            || request()->is('pesan*')
            || request()->is('pusat-data*')
            || request()->is('profile*')
            || request()->is('kalender-konten*');
    @endphp

    <nav class="flex-1 p-3 space-y-2 overflow-y-auto">

        {{-- Dashboard --}}
        <a href="{{ route('direktur.dashboard') }}"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->routeIs('direktur.dashboard') ? $activeClass : $idleClass }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                </path>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Dashboard Direktur</span>
        </a>

        {{-- Persetujuan --}}
        <details class="sidebar-group" @if ($persetujuanOpen) open @endif>
            <summary class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg cursor-pointer transition {{ $persetujuanOpen ? $groupActiveClass : $groupIdleClass }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
                <span class="sidebar-text whitespace-nowrap transition-all duration-150">Persetujuan</span>
                <svg class="chev sidebar-text w-4 h-4 ml-auto transition-transform duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </summary>
            <div class="sidebar-submenu mt-1 ml-5 space-y-1 border-l border-white/15 pl-3">
                <a href="{{ route('kadiv.keuangan.manajemenDana') }}"
                    class="block px-3.5 py-2 rounded-lg transition {{ request()->routeIs('kadiv.keuangan.manajemenDana*') || request()->routeIs('kadiv.keuangan.permintaan*') ? $activeClass : $idleClass }}">
                    Permintaan Dana
                </a>
                <a href="{{ route('hrd.suratIzin') }}"
                    class="block px-3.5 py-2 rounded-lg transition {{ request()->routeIs('hrd.suratIzin*') ? $activeClass : $idleClass }}">
                    Surat Izin & Cuti
                </a>
                <a href="{{ url('/hrd/suratBooking') }}"
                    class="block px-3.5 py-2 rounded-lg transition {{ request()->routeIs('hrd.suratBooking*') ? $activeClass : $idleClass }}">
                    Booking Fasilitas
                </a>
            </div>
        </details>

        {{-- SDM & Organisasi --}}
        <details class="sidebar-group" @if ($sdmOpen) open @endif>
            <summary class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg cursor-pointer transition {{ $sdmOpen ? $groupActiveClass : $groupIdleClass }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
                <span class="sidebar-text whitespace-nowrap transition-all duration-150">SDM & Organisasi</span>
                <svg class="chev sidebar-text w-4 h-4 ml-auto transition-transform duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </summary>
            <div class="sidebar-submenu mt-1 ml-5 space-y-1 border-l border-white/15 pl-3">
                <a href="{{ url('/hrd/daftarKaryawan') }}"
                    class="block px-3.5 py-2 rounded-lg transition {{ request()->is('hrd/daftarKaryawan*') || request()->is('hrd/detailKaryawan*') ? $activeClass : $idleClass }}">
                    Manajemen Karyawan
                </a>
                <a href="{{ url('/hrd/manajemenDivisi') }}"
                    class="block px-3.5 py-2 rounded-lg transition {{ request()->is('hrd/manajemenDivisi*') || request()->is('hrd/detailDivisi*') ? $activeClass : $idleClass }}">
                    Manajemen Divisi
                </a>
                <a href="{{ url('/hrd/strukturOrganisasi') }}"
                    class="block px-3.5 py-2 rounded-lg transition {{ request()->is('hrd/strukturOrganisasi*') ? $activeClass : $idleClass }}">
                    Bagan Organisasi
                </a>
                <a href="{{ url('/hrd/analitikKaryawan') }}"
                    class="block px-3.5 py-2 rounded-lg transition {{ request()->is('hrd/analitikKaryawan*') || request()->is('hrd/turnoverKaryawan*') ? $activeClass : $idleClass }}">
                    Analitik SDM & Turnover
                </a>
            </div>
        </details>

        {{-- Keuangan --}}
        <details class="sidebar-group" @if ($keuanganOpen) open @endif>
            <summary class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg cursor-pointer transition {{ $keuanganOpen ? $groupActiveClass : $groupIdleClass }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span class="sidebar-text whitespace-nowrap transition-all duration-150">Keuangan</span>
                <svg class="chev sidebar-text w-4 h-4 ml-auto transition-transform duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </summary>
            <div class="sidebar-submenu mt-1 ml-5 space-y-1 border-l border-white/15 pl-3">
                <a href="{{ route('kadiv.keuangan') }}"
                    class="block px-3.5 py-2 rounded-lg transition {{ request()->routeIs('kadiv.keuangan') ? $activeClass : $idleClass }}">
                    Ringkasan Keuangan
                </a>
                <a href="{{ route('kadiv.keuangan.rekapDivisi') }}"
                    class="block px-3.5 py-2 rounded-lg transition {{ request()->routeIs('kadiv.keuangan.rekapDivisi*') ? $activeClass : $idleClass }}">
                    Rekap Laba / Rugi
                </a>
                <a href="{{ route('kadiv.keuangan.arusKas') }}"
                    class="block px-3.5 py-2 rounded-lg transition {{ request()->routeIs('kadiv.keuangan.arusKas*') ? $activeClass : $idleClass }}">
                    Arus Kas
                </a>
                <a href="{{ route('kadiv.keuangan.alokasi') }}"
                    class="block px-3.5 py-2 rounded-lg transition {{ request()->routeIs('kadiv.keuangan.alokasi*') ? $activeClass : $idleClass }}">
                    Pagu Anggaran
                </a>
            </div>
        </details>

        {{-- Operasional --}}
        <details class="sidebar-group" @if ($operasionalOpen) open @endif>
            <summary class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg cursor-pointer transition {{ $operasionalOpen ? $groupActiveClass : $groupIdleClass }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-6 9 2 2 4-4" />
                </svg>
                <span class="sidebar-text whitespace-nowrap transition-all duration-150">Operasional</span>
                <svg class="chev sidebar-text w-4 h-4 ml-auto transition-transform duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </summary>
            <div class="sidebar-submenu mt-1 ml-5 space-y-1 border-l border-white/15 pl-3">
                <a href="{{ url('/kadiv/tugas') }}"
                    class="block px-3.5 py-2 rounded-lg transition {{ request()->is('kadiv/tugas*') || request()->is('kadiv/detailTugas*') ? $activeClass : $idleClass }}">
                    Monitoring Tugas
                </a>
                <a href="{{ url('/kadiv/manajemenStaff') }}"
                    class="block px-3.5 py-2 rounded-lg transition {{ request()->is('kadiv/manajemenStaff*') ? $activeClass : $idleClass }}">
                    Manajemen Staf
                </a>
                <a href="{{ route('kadiv.pengajuanDana') }}"
                    class="block px-3.5 py-2 rounded-lg transition {{ request()->routeIs('kadiv.pengajuanDana*') ? $activeClass : $idleClass }}">
                    Pengajuan Dana
                </a>
            </div>
        </details>

        {{-- Workspace --}}
        <details class="sidebar-group" @if ($workspaceOpen) open @endif>
            <summary class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg cursor-pointer transition {{ $workspaceOpen ? $groupActiveClass : $groupIdleClass }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                <span class="sidebar-text whitespace-nowrap transition-all duration-150">Workspace</span>
                <svg class="chev sidebar-text w-4 h-4 ml-auto transition-transform duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </summary>
            <div class="sidebar-submenu mt-1 ml-5 space-y-1 border-l border-white/15 pl-3">
                <a href="{{ url('/tugas') }}"
                    class="block px-3.5 py-2 rounded-lg transition {{ request()->is('tugas*') ? $activeClass : $idleClass }}">
                    Tugas Saya
                </a>
                <a href="{{ url('/kalender') }}"
                    class="block px-3.5 py-2 rounded-lg transition {{ request()->is('kalender') ? $activeClass : $idleClass }}">
                    Kalender Kerja
                </a>
                <a href="{{ url('/jurnal') }}"
                    class="block px-3.5 py-2 rounded-lg transition {{ request()->is('jurnal*') ? $activeClass : $idleClass }}">
                    To Do Harian
                </a>
                @if (\App\Support\ContentCalendarAccess::allows(auth()->user()))
                    <a href="{{ url('/kalender-konten') }}"
                        class="block px-3.5 py-2 rounded-lg transition {{ request()->is('kalender-konten*') ? $activeClass : $idleClass }}">
                        Kalender Konten
                    </a>
                @endif
                <a href="{{ url('/pesan') }}"
                    class="block px-3.5 py-2 rounded-lg transition {{ request()->is('pesan*') ? $activeClass : $idleClass }}">
                    Pesan & Informasi
                </a>
                <a href="{{ url('/pusat-data') }}"
                    class="block px-3.5 py-2 rounded-lg transition {{ request()->is('pusat-data*') ? $activeClass : $idleClass }}">
                    Pusat Data & Arsip
                </a>
            </div>
        </details>

    </nav>
</aside>

<script>
(function () {
    const sidebar = document.getElementById('sidebar');
    const btnToggle = document.getElementById('btn-sidebar-toggle');
    const texts = document.querySelectorAll('.sidebar-text');
    const sidebarStorageKey = 'sidebar-collapsed';

    function setSidebarCollapsed(collapsed) {
        if (!sidebar) return;
        sidebar.classList.toggle('w-64', !collapsed);
        sidebar.classList.toggle('w-20', collapsed);
        texts.forEach(el => el.classList.toggle('hidden', collapsed));
    }

    if (btnToggle && sidebar) {
        setSidebarCollapsed(localStorage.getItem(sidebarStorageKey) === 'true');

        btnToggle.addEventListener('click', function () {
            const collapsed = !sidebar.classList.contains('w-20');
            setSidebarCollapsed(collapsed);
            localStorage.setItem(sidebarStorageKey, String(collapsed));
        });
    }
})();
</script>
