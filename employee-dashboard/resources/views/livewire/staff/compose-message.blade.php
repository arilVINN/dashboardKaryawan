<div id="modalBuatPesan" wire:click.self="closeModal"
    class="fixed inset-0 z-50 flex items-center justify-center p-4
           bg-[rgba(86,94,116,0.4)] backdrop-blur-sm
           transition-opacity duration-150 ease-in-out
           {{ $showModal ? 'opacity-100' : 'opacity-0 pointer-events-none' }}">

    <form wire:submit="send"
        class="bg-white rounded-xl w-full max-w-[460px] max-h-[90vh] flex flex-col
               shadow-[0_8px_16px_rgba(0,0,0,0.12)]
               transition-all duration-150 ease-in-out
               {{ $showModal ? 'scale-100 translate-y-0' : 'scale-95 translate-y-2' }}">
        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#E2E8F0] bg-[#F8FAFC] rounded-t-xl">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-[#DCEEFE] text-[#2563EB] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-black">Buat Pesan</h3>
            </div>
            <button type="button" wire:click="closeModal"
                class="text-[#565E74] hover:text-black text-2xl leading-none cursor-pointer">&times;</button>
        </div>

        {{-- BODY --}}
        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-4">
            <div>
                <label for="composeDivisi" class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">
                    DIVISI
                </label>
                <select id="composeDivisi" wire:model.live="divisiId"
                    class="w-full h-10 rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] bg-white outline-none focus:border-[#146C94] focus:ring-1 focus:ring-[#146C94]">
                    <option value="">Pilih divisi</option>
                    @foreach ($divisis as $divisi)
                        <option value="{{ $divisi->id_divisi }}">{{ $divisi->nama_divisi }}</option>
                    @endforeach
                </select>
                @error('divisiId')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="composePenerima" class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">
                    PENERIMA
                </label>
                <select id="composePenerima" wire:model="recipientUserId"
                    @disabled($divisiId === '')
                    class="w-full h-10 rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] bg-white outline-none focus:border-[#146C94] focus:ring-1 focus:ring-[#146C94] disabled:bg-slate-50 disabled:text-slate-400">
                    <option value="">
                        {{ $divisiId === '' ? 'Pilih divisi terlebih dahulu' : 'Pilih penerima' }}
                    </option>
                    @foreach ($recipients as $recipient)
                        <option value="{{ $recipient->id_user }}">
                            {{ $recipient->karyawan?->nama ?? $recipient->username }}
                            — {{ ucfirst($recipient->role?->nama_role ?? '') }}
                        </option>
                    @endforeach
                </select>
                @if ($divisiId !== '' && $recipients->isEmpty())
                    <p class="text-xs text-[#4B5563] mt-1">Belum ada penerima di divisi ini.</p>
                @endif
                @error('recipientUserId')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="composeJudul" class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">
                    TOPIK
                </label>
                <input id="composeJudul" type="text" wire:model="judul" maxlength="200"
                    placeholder="Tulis topik pesan..."
                    class="w-full h-10 rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] bg-white outline-none focus:border-[#146C94] focus:ring-1 focus:ring-[#146C94]">
                @error('judul')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="composeIsi" class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">
                    ISI PESAN
                </label>
                <textarea id="composeIsi" wire:model="isi" rows="5" maxlength="5000"
                    placeholder="Tuliskan pesan..."
                    class="w-full rounded-lg border border-[#CBD5E1] px-3 py-2 text-sm text-[#283044] bg-white outline-none focus:border-[#146C94] focus:ring-1 focus:ring-[#146C94]"></textarea>
                @error('isi')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- FOOTER --}}
        <div class="px-6 py-4 border-t border-[#E2E8F0] flex items-center justify-end gap-2">
            <button type="button" wire:click="closeModal"
                class="h-10 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-300 transition">
                Batal
            </button>
            <button type="submit" wire:loading.attr="disabled"
                class="h-10 px-4 rounded-lg bg-[#0097B2] text-white text-sm font-medium hover:bg-[#008199] transition disabled:opacity-50">
                <span wire:loading.remove wire:target="send">Kirim</span>
                <span wire:loading wire:target="send">Mengirim...</span>
            </button>
        </div>
    </form>
</div>
