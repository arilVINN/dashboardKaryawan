@php
    $birthdayEmployees = collect($birthdayEmployees ?? []);
    $birthdayPopupKey = 'birthday-popup:' . (auth()->user()?->id_user ?? 'user') . ':' . now()->toDateString();
    $roleName = strtolower(auth()->user()?->role?->nama_role ?? '');
    $messageUrl = $roleName === 'hrd'
        ? route('hrd.pesan')
        : ($roleName === 'kadiv' ? route('kadiv.pesan') : route('pesan'));
@endphp

@if ($birthdayEmployees->isNotEmpty())
    <div id="birthday-popup" data-dismiss-key="{{ $birthdayPopupKey }}"
        class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm"
        role="dialog" aria-modal="true" aria-labelledby="birthday-popup-title">
        <div class="relative w-full max-w-lg overflow-hidden rounded-[1.75rem] bg-white shadow-2xl shadow-slate-950/30">
            <div class="relative overflow-hidden bg-gradient-to-br from-sky-500 via-blue-700 to-blue-200 px-7 pb-8 pt-7 text-center text-white">
                <span class="absolute -right-10 -top-14 h-44 w-44 rounded-full border-[24px] border-white/5" aria-hidden="true"></span>
                <span class="absolute -bottom-16 -left-12 h-36 w-36 rounded-full bg-cyan-300/10 blur-xl" aria-hidden="true"></span>
                <button type="button" data-birthday-popup-close aria-label="Tutup pop-up"
                    class="absolute right-4 top-4 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/70">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12M18 6 6 18" />
                    </svg>
                </button>
                <div class="relative mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-3xl border border-white/20 bg-white/10 shadow-lg shadow-blue-950/20 backdrop-blur-sm">
                    <svg class="h-11 w-11 text-white" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53 0L12 2.845l.265.265Zm-3 0a.375.375 0 1 1-.53 0L9 2.845l.265.265Zm6 0a.375.375 0 1 1-.53 0L15 2.845l.265.265Z" />
                    </svg>
                </div>
                <p class="relative text-[11px] font-semibold uppercase tracking-[0.24em] text-sky-100">Selamat!!!</p>
                <h2 id="birthday-popup-title" class="relative mt-2 text-2xl font-bold tracking-tight">Ada yang berulang tahun!</h2>
                <p class="relative mt-2 text-sm text-blue-100">Selamat Ulang Tahun untuk  tim kita. Yuk, kirim ucapan terbaikmu.</p>
            </div>

            <div class="max-h-72 space-y-3 overflow-y-auto bg-slate-50 px-6 py-5">
                @foreach ($birthdayEmployees as $employee)
                    <div class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-3 shadow-sm shadow-slate-200/60">
                        <div class="relative h-14 w-14 shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-sky-100 to-indigo-100 ring-1 ring-slate-200">
                            @if ($employee->foto)
                                <img src="{{ asset('storage/' . $employee->foto) }}" alt="Foto profil {{ $employee->nama }}"
                                    class="h-full w-full object-cover" loading="lazy"
                                    onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden')">
                            @endif
                            <span class="{{ $employee->foto ? 'hidden ' : '' }}flex h-full w-full items-center justify-center text-lg font-bold text-indigo-700"
                                aria-hidden="true">{{ mb_strtoupper(mb_substr($employee->nama, 0, 1)) }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-slate-900">{{ $employee->nama }}</p>
                            <p class="mt-0.5 truncate text-xs text-slate-500">{{ $employee->divisi?->nama_divisi ?? 'Karyawan PT Silindo' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-slate-100 bg-white px-6 py-5">
                <a href="{{ $messageUrl }}" data-birthday-popup-close
                    class="flex min-h-11 flex-1 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-sky-700 to-blue-400 px-4 text-sm font-semibold text-white shadow-sm transition hover:from-sky-800 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
                    <span>Kirim ucapan</span>
                </a>
                <button type="button" data-birthday-popup-close
                    class="min-h-11 rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2">
                    Nanti saja
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const popup = document.getElementById('birthday-popup');
            if (!popup) return;

            const dismissKey = popup.dataset.dismissKey;
            try {
                if (localStorage.getItem(dismissKey) === '1') return;
            } catch (error) {
                console.error('Gagal memeriksa status pop-up ulang tahun:', error);
            }

            popup.classList.remove('hidden');
            popup.classList.add('flex');

            const closePopup = () => {
                try {
                    localStorage.setItem(dismissKey, '1');
                } catch (error) {
                    console.error('Gagal menyimpan status pop-up ulang tahun:', error);
                }

                popup.classList.add('hidden');
                popup.classList.remove('flex');
                window.dispatchEvent(new CustomEvent('birthday-popup-closed'));
            };

            popup.querySelectorAll('[data-birthday-popup-close]').forEach((button) => {
                button.addEventListener('click', closePopup);
            });
            popup.addEventListener('click', (event) => {
                if (event.target === popup) closePopup();
            });
        });
    </script>
@endif
