@if (auth()->user()?->isDirektur())
    @include('component_direktur.sidebar')
@else
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

        {{-- To Do Harian --}}
        <a href="{{ url('/kadiv/jurnal') }}"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->is('kadiv/jurnal*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">To Do Harian</span>
        </a>

        {{-- Kalender Konten --}}
        @if (\App\Support\ContentCalendarAccess::allows(auth()->user()))
<a href="{{ url('/kalender-konten') }}"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->is('kalender-konten*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                <path d="M12 11.2l.85 1.95 1.95.85-1.95.85L12 16.8l-.85-1.95-1.95-.85 1.95-.85L12 11.2z" fill="currentColor" stroke="none"></path>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Kalender Konten</span>
        </a>
@endif

        {{-- Pesan --}}
        <a href="{{ url('/kadiv/pesan') }}"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->is('kadiv/pesan*') || request()->is('kadiv/detailPesan*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z">
                </path>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Pesan & Informasi</span>
        </a>

        {{-- Pusat Data & Arsip --}}
        <a href="{{ url('/kadiv/pusat-data') }}"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->is('kadiv/pusat-data*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Pusat Data</span>
        </a>

        {{-- Struktur Organisasi --}}
        <a href="{{ url('/kadiv/strukturOrganisasi') }}"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->is('kadiv/strukturOrganisasi*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21">
                </path>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-150">Struktur Organisasi</span>
        </a>

        {{-- Menu Keuangan (Kondisional: Kadiv Finance vs Kadiv Lain) --}}
        @if (auth()->user()?->isKadivFinance())
            @php
                $permintaanActive = request()->routeIs('kadiv.keuangan.manajemenDana*') || request()->routeIs('kadiv.keuangan.permintaan*') || request()->routeIs('kadiv.keuangan.laporan*');
                $jurnalUmumActive = request()->routeIs('kadiv.keuangan.jurnalUmum*');
                $financeGroups = [
                    'ringkasan' => [
                        'label' => 'Ringkasan',
                        'icon' => 'M3 13.5 9 7.5l4 4 7-7M21 4.5v5h-5',
                        'active' => request()->routeIs('kadiv.keuangan') || request()->routeIs('kadiv.keuangan.rekapDivisi*'),
                        'links' => [
                            ['label' => 'Ringkasan Keuangan', 'url' => route('kadiv.keuangan'), 'active' => request()->routeIs('kadiv.keuangan')],
                            ['label' => 'Rekap Laba/Rugi', 'url' => route('kadiv.keuangan.rekapDivisi'), 'active' => request()->routeIs('kadiv.keuangan.rekapDivisi*')],
                        ],
                    ],
                    'anggaran' => [
                        'label' => 'Anggaran & Kas',
                        'icon' => 'M3.75 7.5h16.5v9.75a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V7.5Zm0 0 2.25-3h12l2.25 3M15 12.75h2.25',
                        'active' => request()->routeIs('kadiv.keuangan.alokasi*') || request()->routeIs('kadiv.keuangan.arusKas*') || request()->routeIs('kadiv.keuangan.pemasukan*') || request()->routeIs('kadiv.keuangan.pengeluaran*'),
                        'links' => [
                            ['label' => 'Alokasi Anggaran', 'url' => route('kadiv.keuangan.alokasi'), 'active' => request()->routeIs('kadiv.keuangan.alokasi*')],
                            ['label' => 'Arus Kas', 'url' => route('kadiv.keuangan.arusKas'), 'active' => request()->routeIs('kadiv.keuangan.arusKas*') || request()->routeIs('kadiv.keuangan.pemasukan*') || request()->routeIs('kadiv.keuangan.pengeluaran*')],
                        ],
                    ],
                ];
            @endphp

            <div class="pt-3 border-t border-white/10">
                <div class="px-3 pb-1">
                    <p class="sidebar-text text-[10px] font-bold uppercase tracking-wider text-slate-300/80">
                        Keuangan Perusahaan
                    </p>
                </div>

                <div id="finance-groups" class="mt-1 space-y-0.5">
                    @foreach ($financeGroups as $groupKey => $group)
                        <button type="button" data-finance-group
                            data-finance-active="{{ $group['active'] ? 'true' : 'false' }}"
                            data-finance-target="finance-{{ $groupKey }}-menu"
                            data-finance-chevron="finance-{{ $groupKey }}-chevron"
                            aria-expanded="{{ $group['active'] ? 'true' : 'false' }}"
                            aria-controls="finance-{{ $groupKey }}-menu"
                            class="flex w-full items-center gap-3 rounded-lg px-3.5 py-2.5 text-left transition {{ $group['active'] ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }} focus:outline-none focus-visible:ring-2 focus-visible:ring-white/50">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="{{ $group['icon'] }}"></path>
                            </svg>
                            <span class="sidebar-text whitespace-nowrap">{{ $group['label'] }}</span>
                            <svg id="finance-{{ $groupKey }}-chevron"
                                class="sidebar-text ml-auto h-4 w-4 shrink-0 transition-transform {{ $group['active'] ? 'rotate-180' : '' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m6 9 6 6 6-6"></path>
                            </svg>
                        </button>

                        <div id="finance-{{ $groupKey }}-menu"
                            class="{{ $group['active'] ? '' : 'hidden' }} ml-[46px] space-y-0.5 border-l border-white/15 pl-3">
                            @foreach ($group['links'] as $link)
                                <a href="{{ $link['url'] }}"
                                    class="block rounded-md px-2 py-1.5 text-[13px] transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white/50 {{ $link['active'] ? 'bg-white/20 text-white font-medium' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                                    <span class="sidebar-text whitespace-nowrap">{{ $link['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endforeach

                    {{-- Jurnal Umum — tautan langsung, di luar grup yang dapat dibuka --}}
                    <a href="{{ route('kadiv.keuangan.jurnalUmum') }}" data-finance-direct="jurnal-umum"
                        class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-left transition {{ $jurnalUmumActive ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }} focus:outline-none focus-visible:ring-2 focus-visible:ring-white/50">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75c-1.5-1.1-3.6-1.5-6-1.5-.8 0-1.5.1-2.1.2v13c.6-.1 1.3-.2 2.1-.2 2.4 0 4.5.4 6 1.5 1.5-1.1 3.6-1.5 6-1.5.8 0 1.5.1 2.1.2v-13c-.6-.1-1.3-.2-2.1-.2-2.4 0-4.5.4-6 1.5Zm0 0v13"></path>
                        </svg>
                        <span class="sidebar-text whitespace-nowrap">Jurnal Umum</span>
                    </a>

                    <a href="{{ route('kadiv.keuangan.manajemenDana') }}" data-finance-direct="permintaan"
                        class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-left transition {{ $permintaanActive ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }} focus:outline-none focus-visible:ring-2 focus-visible:ring-white/50">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z">
                            </path>
                        </svg>
                        <span class="sidebar-text whitespace-nowrap">{{ 'Permintaan & Verifikasi' }}</span>
                    </a>
                </div>
            </div>

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
    const financeGroupButtons = document.querySelectorAll('[data-finance-group]');

    function setFinanceGroupExpanded(button, expanded) {
        const menu = document.getElementById(button.dataset.financeTarget);
        const chevron = document.getElementById(button.dataset.financeChevron);

        if (!menu) return;

        menu.classList.toggle('hidden', !expanded);
        button.setAttribute('aria-expanded', expanded ? 'true' : 'false');

        if (chevron) {
            chevron.classList.toggle('rotate-180', expanded);
        }
    }

    function closeAllFinanceGroups() {
        financeGroupButtons.forEach(button => setFinanceGroupExpanded(button, false));
    }

    function openActiveFinanceGroup() {
        const active = document.querySelector('[data-finance-group][data-finance-active="true"]');

        if (active) {
            setFinanceGroupExpanded(active, true);
        }
    }

    financeGroupButtons.forEach(button => {
        button.addEventListener('click', function() {
            const menu = document.getElementById(button.dataset.financeTarget);

            if (!menu) return;

            if (sidebar.classList.contains('w-20')) {
                setSidebarCollapsed(false);
                localStorage.setItem(sidebarStorageKey, 'false');
                closeAllFinanceGroups();
                setFinanceGroupExpanded(button, true);
                return;
            }

            setFinanceGroupExpanded(button, menu.classList.contains('hidden'));
        });
    });

    function setSidebarCollapsed(collapsed) {
        sidebar.classList.toggle('w-64', !collapsed);
        sidebar.classList.toggle('w-20', collapsed);
        texts.forEach(el => el.classList.toggle('hidden', collapsed));

        if (collapsed) {
            closeAllFinanceGroups();
        } else {
            openActiveFinanceGroup();
        }
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
@endif
