@php
    $user = session('user_session');
@endphp

<header class="h-16 flex items-center justify-between px-6 bg-white border-b border-slate-200 shrink-0">

    {{-- KIRI: Judul halaman (opsional) --}}
    <div class="flex items-center gap-3">
        {{-- Kalau kamu mau tampilkan judul halaman di sini, aktifkan bagian ini --}}
        {{-- <h2 class="text-lg font-bold text-[#2A4B6A]">@yield('page-title', 'Dashboard')</h2> --}}
    </div>

    {{-- KANAN: Notifikasi + Avatar --}}
    <div class="flex items-center gap-4">

        {{-- Lonceng notifikasi --}}
        <button type="button"
                id="btn-notif"
                class="relative p-2 text-[#2A4B6A] hover:bg-slate-100 rounded-lg transition cursor-pointer">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            {{-- Badge notifikasi (kalau ada notif baru) --}}
            {{-- <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span> --}}
        </button>

        {{-- Avatar + dropdown --}}
        <div class="relative">
            <button type="button"
                    id="profile-dropdown-btn"
                    class="flex items-center gap-2 cursor-pointer">
                <div id="kadiv-topbar-initial" class="w-9 h-9 rounded-full bg-[#19A7CE] flex items-center justify-center text-white font-bold text-sm">
                    {{ strtoupper(substr($user['nama'] ?? 'U', 0, 1)) }}
                </div>

                <svg id="dropdown-arrow"
                     class="w-4 h-4 text-[#2A4B6A] transition-transform duration-200"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            {{-- Dropdown menu --}}
            <div id="profile-dropdown-menu"
                 class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-slate-200 overflow-hidden z-50">

                <div class="px-4 py-3 border-b border-slate-100">
                    <p id="kadiv-topbar-name" class="text-sm font-bold text-[#2A4B6A] truncate">
                        {{ $user['nama'] ?? 'User' }}
                    </p>
                    <p id="kadiv-topbar-email" class="text-xs text-slate-500 truncate">
                        {{ $user['email'] ?? '' }}
                    </p>
                </div>

                <a href="{{ url('/kadiv/profile') }}"
                   class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition">
                    Profil
                </a>

                <a id="kadiv-logout" href="{{ url('/login') }}"
                   class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition border-t border-slate-100">
                    Logout
                </a>
            </div>
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const profileBtn = document.getElementById('profile-dropdown-btn');
        const profileMenu = document.getElementById('profile-dropdown-menu');
        const dropdownArrow = document.getElementById('dropdown-arrow');
        const token = sessionStorage.getItem('staff_token');

        if (token) {
            fetch('/api/kadiv/profile', {
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            })
                .then(async response => {
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.message || 'Gagal memuat identitas Kadiv.');
                    return result.data;
                })
                .then(profile => {
                    document.getElementById('kadiv-topbar-initial').textContent =
                        (profile.nama || 'U').charAt(0).toUpperCase();
                    document.getElementById('kadiv-topbar-name').textContent = profile.nama || 'User';
                    document.getElementById('kadiv-topbar-email').textContent = profile.email || '';
                })
                .catch(error => console.error('Gagal memuat identitas Kadiv:', error));
        }

        document.getElementById('kadiv-logout').addEventListener('click', async function (event) {
            event.preventDefault();
            try {
                if (token) {
                    const response = await fetch('/api/logout', {
                        method: 'POST',
                        headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                    });
                    if (!response.ok) throw new Error('Logout backend gagal: ' + response.status);
                }
            } catch (error) {
                console.error('Gagal melakukan logout Kadiv:', error);
            } finally {
                sessionStorage.removeItem('staff_token');
                window.location.href = this.href;
            }
        });

        if (profileBtn && profileMenu) {

            profileBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                profileMenu.classList.toggle('hidden');
                if (dropdownArrow) {
                    dropdownArrow.classList.toggle('rotate-180');
                }
            });

            document.addEventListener('click', function (e) {
                if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
                    profileMenu.classList.add('hidden');
                    if (dropdownArrow) {
                        dropdownArrow.classList.remove('rotate-180');
                    }
                }
            });
        }

    });
</script>