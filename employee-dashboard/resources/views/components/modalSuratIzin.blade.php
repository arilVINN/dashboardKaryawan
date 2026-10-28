<div id="modalSuratIzin"
     class="opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center p-4
            bg-[rgba(86,94,116,0.4)] backdrop-blur-sm
            transition-opacity duration-150 ease-in-out">

    <form data-modal-panel
          action="{{ route($routeSurat) }}"
          method="POST"
          enctype="multipart/form-data"
          class="bg-white rounded-xl w-full max-w-[420px] max-h-[90vh] flex flex-col
                 shadow-[0_8px_16px_rgba(0,0,0,0.12)]
                 scale-95 translate-y-2 transition-all duration-150 ease-in-out">
        @csrf

        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#E2E8F0] bg-[#F8FAFC] rounded-t-xl">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-[#D9EEF5] text-[#146C94] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M9 8h1m5 12H7a2 2 0 01-2-2V6a2 2 0 012-2h6l5 5v11a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-black">Kirim Surat Izin</h3>
            </div>
            <button type="button" data-modal-close="modalSuratIzin"
                    class="text-[#565E74] hover:text-black text-2xl leading-none cursor-pointer">&times;</button>
        </div>

        {{-- BODY --}}
        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">

            <div>
                <label class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">JUDUL</label>
                <input type="text" name="judul_surat" required placeholder="Contoh: Izin Sakit"
                       class="w-full h-10 rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] placeholder:text-[#94A3B8] outline-none focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]">
                @error('judul_surat') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">TANGGAL</label>
                <input type="date" name="tanggal_izin" required
                       class="w-full h-10 rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] outline-none cursor-pointer focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]">
                @error('tanggal_izin') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="bg-[#F1F5F9] rounded-lg p-4">
                <label for="fileSurat"
                       class="flex flex-col items-center justify-center text-center gap-1 h-[90px] rounded-lg border-2 border-dashed border-[#CBD5E1] bg-white cursor-pointer hover:border-[#19A7CE] transition">
                    <span id="namaFileSurat" class="text-sm font-bold text-[#283044] px-3 truncate max-w-full">Pilih File (Bukti Surat)</span>
                    <span class="text-xs text-[#94A3B8]">Maks 20 MB</span>
                </label>
                <input id="fileSurat" type="file" name="file" class="hidden">
            </div>

            <div>
                <label class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">KETERANGAN</label>
                <textarea name="keterangan" rows="3" placeholder="Opsional"
                          class="w-full resize-none rounded-lg border border-[#CBD5E1] px-3 py-2.5 text-sm text-[#283044] placeholder:text-[#94A3B8] outline-none focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]"></textarea>
            </div>
        </div>

        {{-- FOOTER --}}
        <div class="flex items-center justify-between px-6 py-4 border-t border-[#E2E8F0]">
            <button type="button" data-modal-close="modalSuratIzin"
                    class="h-9 px-4 bg-[#E8E7E9] rounded-lg text-sm text-[#333335] hover:bg-[#D9D9D9] transition cursor-pointer">
                Batal
            </button>
            <button type="submit"
                    class="h-9 px-4 bg-[#146C94] rounded-lg text-sm text-white font-bold flex items-center gap-2 hover:bg-[#0f5878] transition cursor-pointer">
                Kirim Surat
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M2 21l21-9L2 3v7l15 2-15 2z"/></svg>
            </button>
        </div>
    </form>
</div>

<script>
(function () {
    if (!window.__modalReady) {
        window.__modalReady = true;
        window.setModal = function (id, show) {
            const modal = document.getElementById(id);
            if (!modal) return;
            const panel = modal.querySelector('[data-modal-panel]');
            modal.classList.toggle('opacity-0', !show);
            modal.classList.toggle('pointer-events-none', !show);
            modal.classList.toggle('opacity-100', show);
            if (panel) {
                panel.classList.toggle('scale-95', !show);
                panel.classList.toggle('translate-y-2', !show);
                panel.classList.toggle('scale-100', show);
                panel.classList.toggle('translate-y-0', show);
            }
            document.body.classList.toggle('overflow-hidden', show);
        };
        document.addEventListener('click', function (e) {
            const open  = e.target.closest('[data-modal-open]');
            const close = e.target.closest('[data-modal-close]');
            if (open)  return setModal(open.dataset.modalOpen, true);
            if (close) return setModal(close.dataset.modalClose, false);
            if (e.target.matches('[id^="modal"]')) setModal(e.target.id, false);
        });
    }

    const input = document.getElementById('fileSurat');
    const label = document.getElementById('namaFileSurat');
    input.addEventListener('change', function () {
        const f = this.files[0];
        if (!f) { label.textContent = 'Pilih File (opsional)'; return; }
        if (f.size > 20 * 1024 * 1024) {
            alert('Ukuran file maksimal 20 MB');
            this.value = '';
            label.textContent = 'Pilih File (opsional)';
            return;
        }
        label.textContent = f.name;
    });

    @if ($errors->any())
        setModal('modalSuratIzin', true);
    @endif
})();
</script>