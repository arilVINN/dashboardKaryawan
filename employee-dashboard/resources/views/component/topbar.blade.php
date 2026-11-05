@php
    $topbarUser = auth()->user();
    $topbarEmployee = $topbarUser?->karyawan;
    $pageRole = request()->is('hrd/*') ? 'hrd' : (request()->is('kadiv/*') ? 'kadiv' : 'staff');
    $topbarNama = $topbarEmployee?->nama ?? $topbarUser?->username ?? 'Guest';
    $topbarDivisi = $topbarEmployee?->divisi?->nama_divisi ?? $topbarUser?->role?->nama_role ?? ucfirst($pageRole);
    $topbarRole = $topbarUser?->role?->nama_role ?? $pageRole;
    $topbarInitial = $topbarUser ? mb_strtoupper(mb_substr($topbarNama, 0, 1)) : 'U';
    $topbarFotoUrl = $topbarEmployee?->foto ? '/storage/'.$topbarEmployee->foto : null;
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

        <div class="relative">
            <button id="profile-dropdown-btn" type="button"
                class="flex items-center gap-2 cursor-pointer p-1 rounded-lg hover:bg-slate-100 transition focus:outline-none">
                <div id="topbar-inisial"
                    class="{{ $topbarFotoUrl ? 'hidden ' : '' }}w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold text-sm shrink-0">
                    {{ $topbarInitial }}
                </div>
                <img id="topbar-foto" alt="Foto profil"
                    @if ($topbarFotoUrl) src="{{ $topbarFotoUrl }}" @endif
                    class="{{ $topbarFotoUrl ? '' : 'hidden ' }}w-8 h-8 rounded-full object-cover shrink-0">
                <svg class="w-4 h-4 text-slate-500 transition-transform duration-150" id="dropdown-arrow" fill="none"
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
    if (@json($pageRole === 'kadiv')) {
        const profileBtn = document.getElementById('profile-dropdown-btn');
        const profileMenu = document.getElementById('profile-dropdown-menu');
        const dropdownArrow = document.getElementById('dropdown-arrow');

        if (profileBtn && profileMenu) {
            profileBtn.addEventListener('click', function (event) {
                event.stopPropagation();
                profileMenu.classList.toggle('hidden');
                dropdownArrow?.classList.toggle('rotate-180');
            });

            document.addEventListener('click', function (event) {
                if (!profileBtn.contains(event.target) && !profileMenu.contains(event.target)) {
                    profileMenu.classList.add('hidden');
                    dropdownArrow?.classList.remove('rotate-180');
                }
            });
        }
    }

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
        if (profile.foto_url) {
            const topbarFoto = document.getElementById('topbar-foto');
            topbarFoto.setAttribute('src', profile.foto_url);
            topbarFoto.classList.remove('hidden');
            document.getElementById('topbar-inisial').classList.add('hidden');
        }
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
        localStorage.removeItem('staff_token');
        HTMLFormElement.prototype.submit.call(form);
    });
</script>
<!-- Toast container tersedia di semua halaman staff via topbar -->
<div id="toast-container" class="fixed top-5 left-1/2 -translate-x-1/2 z-[9999] flex flex-col items-center gap-3 pointer-events-none"></div>

@php
    $bannerError = session('forbidden') ?? session('error') ?? session('warning');
@endphp

@if ($bannerError)
<script>
    (function () {
        function escapeToastHtml(str) {
            return String(str || '').replace(/[&<>"']/g, function (m) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[m];
            });
        }

        function showBannerToast() {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = [
                'pointer-events-auto flex items-center justify-between gap-4 px-6 py-3.5 rounded-none shadow-xl',
                'bg-red-50 border border-red-200 text-red-800 text-sm font-medium',
                'opacity-0 -translate-y-2 transition-all duration-300'
            ].join(' ');

            toast.innerHTML = `
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full bg-red-100 flex items-center justify-center shrink-0 text-red-600">
                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <span class="font-semibold text-slate-800">${escapeToastHtml(@json($bannerError))}</span>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-700 font-bold text-lg leading-none focus:outline-none ml-2 transition">&times;</button>
            `;

            const btnClose = toast.querySelector('button');
            const closeToast = () => {
                toast.classList.add('opacity-0', '-translate-y-2');
                toast.addEventListener('transitionend', () => toast.remove(), { once: true });
            };
            btnClose?.addEventListener('click', closeToast);

            container.appendChild(toast);

            // Animate in
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    toast.classList.remove('opacity-0', '-translate-y-2');
                });
            });

            // Auto-dismiss after 6 seconds
            setTimeout(closeToast, 6000);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', showBannerToast);
        } else {
            showBannerToast();
        }
    })();
</script>
@endif
