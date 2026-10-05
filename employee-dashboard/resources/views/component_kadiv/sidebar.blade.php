<aside id="sidebar"
    class="w-64 flex flex-col h-screen shrink-0 bg-gradient-to-b from-[#044564] from-50% to-[#19A7CE] transition-all duration-300 ease-in-out">

    <div
        class="h-16 flex items-center justify-between px-4 bg-white rounded-bl-3xl border-b border-r border-slate-200 shrink-0 box-border overflow-hidden">

        <div class="sidebar-text flex items-center gap-2.5 overflow-hidden transition-all duration-200">
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
            <span class="sidebar-text whitespace-nowrap transition-all duration-200">Dashboard</span>
        </a>

        {{-- Manajemen Staff --}}
        <a href="{{ url('/kadiv/manajemenStaff') }}"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->is('kadiv/manajemenStaff*') || request()->is('kadiv/detailManajemenStaff*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-200">Manajemen Staff</span>
        </a>

        {{-- Tugas --}}
        <a href="{{ url('/kadiv/tugas') }}"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->is('kadiv/tugas*') || request()->is('kadiv/detailTugas*')? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-200">Tugas</span>
        </a>

        {{-- Pesan --}}
        <a href="{{ url('/kadiv/pesan') }}"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->is('kadiv/pesan*') || request()->is('kadiv/detailPesan*') ? 'bg-white/40 text-white font-medium' : 'text-slate-300 hover:bg-white/20 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2m-3 12H7c-.55 0-1-.45-1-1s.45-1 1-1h10c.55 0 1 .45 1 1s-.45 1-1 1m0-3H7c-.55 0-1-.45-1-1s.45-1 1-1h10c.55 0 1 .45 1 1s-.45 1-1 1m0-3H7c-.55 0-1-.45-1-1s.45-1 1-1h10c.55 0 1 .45 1 1s-.45 1-1 1">
                </path>
            </svg>
            <span class="sidebar-text whitespace-nowrap transition-all duration-200">Pesan</span>
        </a>
    </nav>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const btnToggle = document.getElementById('btn-sidebar-toggle');
        const texts = document.querySelectorAll('.sidebar-text');

        if (btnToggle && sidebar) {
            btnToggle.addEventListener('click', function() {
                if (sidebar.classList.contains('w-64')) {
                    sidebar.classList.remove('w-64');
                    sidebar.classList.add('w-20');
                    texts.forEach(el => el.classList.add('hidden'));
                } else {
                    sidebar.classList.remove('w-20');
                    sidebar.classList.add('w-64');
                    texts.forEach(el => el.classList.remove('hidden'));
                }
            });
        }

        const profileBtn = document.getElementById('profile-dropdown-btn');
        const profileMenu = document.getElementById('profile-dropdown-menu');
        const dropdownArrow = document.getElementById('dropdown-arrow');

        if (profileBtn && profileMenu) {
            profileBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                profileMenu.classList.toggle('hidden');
                if (dropdownArrow) dropdownArrow.classList.toggle('rotate-180');
            });

            document.addEventListener('click', function(e) {
                if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
                    profileMenu.classList.add('hidden');
                    if (dropdownArrow) dropdownArrow.classList.remove('rotate-180');
                }
            });
        }
    });
</script>
