@php
    $bookingHasErrors = $errors->hasAny(['kategori', 'jenis', 'tanggal', 'jam_mulai', 'jam_selesai', 'keperluan']);
    $jenisPerKategori = \App\Http\Controllers\BookingController::JENIS_PER_KATEGORI;
@endphp

<div id="modalBooking"
    class="opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center p-4
        bg-[rgba(86,94,116,0.4)] backdrop-blur-sm transition-opacity duration-150 ease-in-out"
    role="dialog" aria-modal="true" aria-labelledby="modalBookingTitle">
    <form data-modal-panel method="POST" action="{{ route('booking.store') }}"
        class="flex max-h-[90vh] w-full max-w-[420px] flex-col overflow-hidden rounded-xl bg-white
            shadow-[0_8px_16px_rgba(0,0,0,0.12)] scale-95 translate-y-2 transition-all duration-150 ease-in-out">
        @csrf

        <div class="flex items-center justify-between border-b border-[#E2E8F0] bg-[#F8FAFC] px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#D9EEF5] text-[#146C94]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h2 id="modalBookingTitle" class="text-base font-bold text-black">Booking Kendaraan / Ruangan</h2>
                    <p class="mt-0.5 text-xs text-[#565E74]">Menunggu persetujuan HRD atau Direksi</p>
                </div>
            </div>
            <button type="button" data-modal-close="modalBooking" aria-label="Tutup"
                class="cursor-pointer text-2xl leading-none text-[#565E74] hover:text-black">&times;</button>
        </div>

        <div class="flex-1 space-y-4 overflow-y-auto px-6 py-5">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-1">
                <div>
                    <label for="bookingKategori" class="mb-2 block text-xs font-bold tracking-wide text-[#565E74]">KATEGORI</label>
                    <select name="kategori" id="bookingKategori" required
                        class="h-10 w-full rounded-lg border border-[#CBD5E1] bg-white px-3 text-sm text-[#283044] outline-none focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]">
                        @foreach (array_keys($jenisPerKategori) as $kategori)
                            <option value="{{ $kategori }}" @selected(old('kategori', 'Kendaraan') === $kategori)>{{ $kategori }}</option>
                        @endforeach
                    </select>
                    @error('kategori') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="bookingJenis" class="mb-2 block text-xs font-bold tracking-wide text-[#565E74]">JENIS</label>
                    <select name="jenis" id="bookingJenis" required
                        class="h-10 w-full rounded-lg border border-[#CBD5E1] bg-white px-3 text-sm text-[#283044] outline-none focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]">
                        @foreach ($jenisPerKategori as $kategori => $jenisOptions)
                            @foreach ($jenisOptions as $jenis)
                                <option value="{{ $jenis }}" data-category="{{ $kategori }}"
                                    @selected(old('jenis', 'Mobil') === $jenis)>{{ $jenis }}</option>
                            @endforeach
                        @endforeach
                    </select>
                    @error('jenis') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="bookingTanggal" class="mb-2 block text-xs font-bold tracking-wide text-[#565E74]">TANGGAL</label>
                <input id="bookingTanggal" type="date" name="tanggal" value="{{ old('tanggal') }}"
                    min="{{ now()->toDateString() }}" required
                    class="h-10 w-full cursor-pointer rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] outline-none focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]">
                @error('tanggal') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="bookingJamMulai" class="mb-2 block text-xs font-bold tracking-wide text-[#565E74]">JAM MULAI</label>
                    <input id="bookingJamMulai" type="time" name="jam_mulai" value="{{ old('jam_mulai') }}" required
                        class="h-10 w-full cursor-pointer rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] outline-none focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]">
                    @error('jam_mulai') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="bookingJamSelesai" class="mb-2 block text-xs font-bold tracking-wide text-[#565E74]">JAM SELESAI</label>
                    <input id="bookingJamSelesai" type="time" name="jam_selesai" value="{{ old('jam_selesai') }}" required
                        class="h-10 w-full cursor-pointer rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] outline-none focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]">
                    @error('jam_selesai') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="bookingKeperluan" class="mb-2 block text-xs font-bold tracking-wide text-[#565E74]">KEPERLUAN</label>
                <textarea id="bookingKeperluan" name="keperluan" rows="3" maxlength="500" required
                    class="w-full resize-none rounded-lg border border-[#CBD5E1] px-3 py-2 text-sm text-[#283044] outline-none placeholder:text-[#94A3B8] focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]"
                    placeholder="Jelaskan keperluan booking">{{ old('keperluan') }}</textarea>
                @error('keperluan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            @if ($bookingHasErrors)
                <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif
        </div>

        <div class="flex items-center justify-between border-t border-[#E2E8F0] px-6 py-4">
            <button type="button" data-modal-close="modalBooking"
                class="h-9 cursor-pointer rounded-lg bg-[#E8E7E9] px-4 text-sm text-[#333335] transition hover:bg-[#D9D9D9]">
                Batal
            </button>
            <button type="submit"
                class="flex h-9 cursor-pointer items-center gap-2 rounded-lg bg-[#146C94] px-4 text-sm font-bold text-white transition hover:bg-[#0f5878]">
                Kirim Booking
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M2 21l21-9L2 3v7l15 2-15 2z" />
                </svg>
            </button>
        </div>
    </form>
</div>

<script>
    (() => {
        const category = document.getElementById('bookingKategori');
        const type = document.getElementById('bookingJenis');
        if (!category || !type) return;

        const syncTypes = () => {
            const options = Array.from(type.options);
            options.forEach((option) => {
                option.hidden = option.dataset.category !== category.value;
            });
            if (type.selectedOptions[0]?.dataset.category !== category.value) {
                type.value = options.find((option) => option.dataset.category === category.value)?.value ?? '';
            }
        };

        category.addEventListener('change', syncTypes);
        syncTypes();
        @if ($bookingHasErrors)
            document.addEventListener('DOMContentLoaded', () => window.setModal('modalBooking', true), { once: true });
        @endif
    })();
</script>
