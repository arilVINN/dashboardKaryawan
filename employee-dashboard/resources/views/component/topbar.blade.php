@php
    $topbarUser = auth()->user();
    $topbarEmployee = $topbarUser?->karyawan;
    $pageRole = request()->is('hrd/*') ? 'hrd' : (request()->is('kadiv/*') ? 'kadiv' : 'staff');
    $topbarNama = $topbarEmployee?->nama ?? $topbarUser?->username ?? 'Guest';
    $topbarDivisi = $topbarEmployee?->divisi?->nama_divisi ?? $topbarUser?->role?->nama_role ?? ucfirst($pageRole);
    $topbarRole = $topbarUser?->role?->nama_role ?? $pageRole;
    $topbarInitial = $topbarUser ? mb_strtoupper(mb_substr($topbarNama, 0, 1)) : 'U';
    $topbarProfileUrl = match ($pageRole) {
        'hrd' => route('hrd.profile'),
        'kadiv' => route('kadiv.profile'),
        default => url('/profile'),
    };
    $topbarProfileEndpoint = match ($pageRole) {
        'hrd' => '/api/hrd/profile',
        'kadiv' => '/api/kadiv/profile',
        default => '/api/staff/profile',
    };
@endphp

<header
    class="h-16 w-full bg-white border-b border-slate-200 px-8 flex items-center justify-end shrink-0 relative box-border">
    <div class="flex items-center gap-5">

        <button class="text-slate-400 hover:text-slate-600 transition p-1.5 rounded-full hover:bg-slate-100">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path
                    d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z" />
            </svg>
        </button>

        <div class="relative">
            <button id="profile-dropdown-btn" type="button"
                class="flex items-center gap-2 cursor-pointer p-1 rounded-lg hover:bg-slate-100 transition focus:outline-none">
                <div id="topbar-inisial"
                    class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold text-sm shrink-0">
                    {{ $topbarInitial }}
                </div>
                <svg class="w-4 h-4 text-slate-500 transition-transform duration-200" id="dropdown-arrow" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div id="profile-dropdown-menu"
                class="hidden absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-slate-200 py-2 z-50 transition-all">
                <div class="px-4 py-2 border-b border-slate-100">
                    <p id="topbar-nama" class="text-sm font-semibold text-slate-800">{{ $topbarNama }}</p>
                    <p id="topbar-divisi" class="text-xs text-slate-500">{{ $topbarDivisi }}</p>
                </div>
                <div class="py-1">
                    <a href="{{ $topbarProfileUrl }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Profil Saya
                    </a>
                </div>
                <form id="topbar-logout-form" method="POST" action="{{ route('logout') }}"
                    class="border-t border-slate-100 pt-1">
                    @csrf
                    <button type="button" id="btn-logout-topbar"
                        class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition text-left">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                        Keluar
                    </button>
                </form>
            </div>
        </div>

    </div>
</header>

<script>
    // Fallback token hanya bila halaman tidak dirender dari session (mis. alur token).
    (async function loadTopbar() {
        if ({{ $topbarUser ? 'true' : 'false' }}) return;

        const token = sessionStorage.getItem('staff_token');
        if (!token) return;

        const response = await fetch(@json($topbarProfileEndpoint), {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        const result = await response.json();
        if (!response.ok || !result.data) {
            throw new Error(result.message || 'Gagal memuat identitas pengguna.');
        }

        const profile = result.data;
        document.getElementById('topbar-nama').textContent = profile.nama || 'Pengguna';
        document.getElementById('topbar-divisi').textContent = @json(ucfirst($pageRole));
        document.getElementById('topbar-inisial').textContent = profile.nama
            ? profile.nama.charAt(0).toUpperCase()
            : 'U';
    })().catch(error => console.error('Gagal memuat identitas pengguna:', error));

    document.getElementById('btn-logout-topbar')?.addEventListener('click', async function () {
        const form = document.getElementById('topbar-logout-form');
        const token = sessionStorage.getItem('staff_token');
        this.disabled = true;

        if (token) {
            try {
                const response = await fetch('/api/logout', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json',
                    },
                });
                if (!response.ok) {
                    throw new Error('Gagal mengakhiri token login.');
                }
            } catch (error) {
                console.error('Gagal mencabut token login:', error);
            }
        }

        sessionStorage.removeItem('staff_token');
        HTMLFormElement.prototype.submit.call(form);
    });
</script>
