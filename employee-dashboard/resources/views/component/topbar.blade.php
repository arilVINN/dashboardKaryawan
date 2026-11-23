@php
    $topbarUser = auth()->user();
    $topbarEmployee = $topbarUser?->karyawan;
    // The shared Kalender Konten page is not under /hrd or /kadiv, so derive the
    // role from the account instead of the URL; otherwise the profile dropdown
    // never initialises for Kadiv and profile links fall back to the staff route.
    $pageRole = match (strtolower((string) $topbarUser?->role?->nama_role)) {
        'direktur', 'direksi', 'superadmin' => 'direktur',
        'hrd' => 'hrd',
        'kadiv' => 'kadiv',
        default => 'staff',
    };
    $topbarNama = $topbarEmployee?->nama ?? $topbarUser?->username ?? 'Guest';
    $topbarDivisi = $topbarEmployee?->divisi?->nama_divisi ?? $topbarUser?->role?->nama_role ?? ucfirst($pageRole);
    $topbarRole = $topbarUser?->role?->nama_role ?? $pageRole;
    $topbarInitial = $topbarUser ? mb_strtoupper(mb_substr($topbarNama, 0, 1)) : 'U';
    $topbarFotoUrl = $topbarEmployee?->foto ? '/storage/'.$topbarEmployee->foto : null;
    $topbarProfileUrl = match ($pageRole) {
        'direktur' => url('/profile'),
        'hrd' => route('hrd.profile'),
        'kadiv' => route('kadiv.profile'),
        default => url('/profile'),
    };
    $topbarProfileEndpoint = match ($pageRole) {
        'direktur' => '/api/staff/profile',
        'hrd' => '/api/hrd/profile',
        'kadiv' => '/api/kadiv/profile',
        default => '/api/staff/profile',
    };
@endphp

<header
    class="h-16 w-full bg-white border-b border-slate-200 px-8 flex items-center justify-between shrink-0 relative z-30 box-border">
    <div class="flex items-center gap-4">
        @if (!empty($headerTitle))
            <h2 class="text-base font-bold text-slate-800">{{ $headerTitle }}</h2>
        @endif
    </div>
    <div class="flex items-center gap-5">

        <!-- Tombol Notifikasi -->
        <button type="button" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition" title="Notifikasi">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
        </button>

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
    (function initProfileDropdown() {
        const setup = function () {
            const profileBtn = document.getElementById('profile-dropdown-btn');
            const profileMenu = document.getElementById('profile-dropdown-menu');
            const dropdownArrow = document.getElementById('dropdown-arrow');

            if (!profileBtn || !profileMenu || profileBtn._dropdownInitialized) return;
            profileBtn._dropdownInitialized = true;

            profileBtn.addEventListener('click', function (event) {
                event.stopPropagation();
                const isHidden = profileMenu.classList.contains('hidden');
                profileMenu.classList.toggle('hidden', !isHidden);
                dropdownArrow?.classList.toggle('rotate-180', isHidden);
            });

            document.addEventListener('click', function (event) {
                if (!profileBtn.contains(event.target) && !profileMenu.contains(event.target)) {
                    profileMenu.classList.add('hidden');
                    dropdownArrow?.classList.remove('rotate-180');
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !profileMenu.classList.contains('hidden')) {
                    profileMenu.classList.add('hidden');
                    dropdownArrow?.classList.remove('rotate-180');
                }
            });
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setup);
        } else {
            setup();
        }
    })();

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

<script>
    (function () {
        function escapeAppToast(value) {
            return String(value ?? '').replace(/[&<>"']/g, function (character) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[character];
            });
        }

        window.appToast = function (message, type) {
            const container = document.getElementById('toast-container');
            if (!container || !message) return;

            const isError = type === 'error';
            const border = isError ? '#F5A3A3' : '#86D9AE';
            const iconBg = isError ? '#FDE0E0' : '#D9F5E5';
            const iconColor = isError ? '#DC2626' : '#059669';
            const iconPath = isError ? 'M6 18 18 6M6 6l12 12' : 'm5 13 4 4L19 7';

            const toast = document.createElement('div');
            toast.className = 'pointer-events-auto flex w-80 max-w-[calc(100vw-2rem)] items-center gap-3 rounded-xl border bg-white px-4 py-3 shadow-lg opacity-0 -translate-y-2 transition-all duration-200';
            toast.style.borderColor = border;
            toast.setAttribute('role', isError ? 'alert' : 'status');
            toast.innerHTML = `
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full" style="background-color: ${iconBg}; color: ${iconColor};">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="${iconPath}" /></svg>
                </span>
                <span class="min-w-0 flex-1 text-[13px] font-medium text-[#172B36]">${escapeAppToast(message)}</span>
                <button type="button" class="shrink-0 text-[#94A3B8] transition hover:text-[#172B36] focus:outline-none" aria-label="Tutup notifikasi">&times;</button>
            `;

            const close = function () {
                toast.classList.add('opacity-0', '-translate-y-2');
                toast.addEventListener('transitionend', function () { toast.remove(); }, { once: true });
            };
            toast.querySelector('button').addEventListener('click', close);

            container.appendChild(toast);
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    toast.classList.remove('opacity-0', '-translate-y-2');
                });
            });
            setTimeout(close, 4000);
        };

        // Show a toast after a full-page reload (result cannot survive in JS).
        window.appToastPending = function (message, type) {
            sessionStorage.setItem('appToastPending', JSON.stringify({ message: message, type: type }));
            window.location.reload();
        };

        function flushPendingToast() {
            const raw = sessionStorage.getItem('appToastPending');
            if (!raw) return;
            sessionStorage.removeItem('appToastPending');
            try {
                const payload = JSON.parse(raw);
                window.appToast(payload.message, payload.type);
            } catch (error) {
                // Ignore malformed payloads.
            }
        }

        function registerLivewireToast() {
            if (!window.Livewire) return false;
            window.Livewire.on('app-toast', function (payload) {
                window.appToast(payload.message, payload.type);
            });
            return true;
        }

        if (!registerLivewireToast()) {
            document.addEventListener('livewire:init', registerLivewireToast);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', flushPendingToast);
        } else {
            flushPendingToast();
        }
    })();
</script>

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
