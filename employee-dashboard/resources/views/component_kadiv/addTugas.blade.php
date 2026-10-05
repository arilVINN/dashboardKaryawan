<div id="messageModal"
     class="hidden fixed inset-0 z-50 bg-[rgba(86,94,116,0.4)] flex items-center justify-center p-4">

    <div class="bg-white rounded-xl w-full max-w-[469px] max-h-[calc(100vh-40px)] overflow-y-auto shadow-[0_8px_16px_rgba(0,0,0,0.12)]">

        {{-- Modal Header --}}
        <div class="bg-[#F2F6FA] rounded-t-lg px-[30px] py-5 flex items-center gap-4">

            <div class="bg-[#AFD3E2] rounded-lg p-2 flex items-center justify-center">
                <svg class="w-[18px] h-[18px] text-[#283044]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                </svg>
            </div>

            <h2 class="flex-1 text-xl leading-7 font-bold text-black">Buat Tugas Baru</h2>

            <button type="button"
                    id="closeModal"
                    class="w-6 h-6 flex items-center justify-center text-[#565E74] hover:text-black">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 6l12 12M18 6 6 18"></path>
                </svg>
            </button>

        </div>

        {{-- Modal Body --}}
        <div class="px-[30px] pt-0 pb-0">
            <div class="py-7 flex flex-col gap-[22px]">

                {{-- Judul Tugas --}}
                <div class="flex flex-col">
                    <label class="pb-[6px] text-base leading-6 font-bold text-[#565E74]">JUDUL TUGAS</label>
                    <input type="text"
                           placeholder="Body"
                           class="w-full h-[37px] rounded-lg border border-[#CBD5E1] px-[11px] text-sm text-[#283044] placeholder:text-[#94A3B8] outline-none focus:border-[#19A7CE]">
                </div>

                {{-- Tujuan Penerima --}}
                <div class="flex flex-col">
                    <label class="pb-[6px] text-base leading-6 font-bold text-[#565E74]">TUJUAN PENERIMA</label>
                    <input type="text"
                           placeholder="Body"
                           class="w-full h-[37px] rounded-lg border border-[#CBD5E1] px-[11px] text-sm text-[#283044] placeholder:text-[#94A3B8] outline-none focus:border-[#19A7CE]">
                </div>

                {{-- Deskripsi --}}
                <div class="flex flex-col">
                    <label class="pb-[6px] text-base leading-6 font-bold text-[#565E74]">DESKRIPSI</label>
                    <textarea rows="3"
                              placeholder="Body"
                              class="w-full h-[103px] resize-none rounded-lg border border-[#CBD5E1] px-[11px] py-2 text-sm text-[#283044] placeholder:text-[#94A3B8] outline-none focus:border-[#19A7CE]"></textarea>
                </div>

                {{-- Tanggal Tenggat --}}
                <div class="flex flex-col">
                    <label class="pb-[6px] text-base leading-6 font-bold text-[#565E74]">TANGGAL TENGGAT</label>
                    <input type="date"
                           class="w-full h-[42px] rounded-lg border border-[#CBD5E1] px-[11px] text-sm text-[#283044] outline-none focus:border-[#19A7CE]">
                </div>

                {{-- Lampiran --}}
                <div class="bg-[rgba(205,218,236,0.25)] rounded-lg p-5 flex flex-col gap-4">

                    <label class="text-base leading-6 font-bold text-[#565E74]">LAMPIRAN</label>

                    <label class="bg-white rounded-lg border border-dashed border-[#AFD3E2] px-3 py-5 flex flex-col items-center justify-center cursor-pointer hover:bg-[#F8FBFD] transition">

                        <svg class="w-6 h-6 text-[#565E74] mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 3v12m0-12 4 4m-4-4L8 7M5 13v5a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3v-5"></path>
                        </svg>

                        <span class="text-base leading-6 font-bold text-[#565E74]">Unggah File</span>
                        <span class="text-[10px] leading-5 text-[#131B2E]">Mendukung File (Maks 5 MB)</span>

                        <input type="file" class="hidden">
                    </label>

                </div>

            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="px-[30px] pb-[30px] flex items-center justify-between">

            <button type="button"
                    id="cancelModal"
                    class="h-8 bg-[#E8E7E9] rounded-lg px-3 flex items-center justify-center text-base leading-6 text-[#333335] hover:bg-[#D9D9D9] transition">
                Batal
            </button>

            <button type="button"
                    class="h-8 bg-[#004B6C] rounded-lg px-3 flex items-center justify-center gap-2 text-base leading-6 font-bold text-white hover:bg-[#003D58] transition">
                Kirim Tugas
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="m5 12 14-7-7 14-2-6-5-1Z"></path>
                </svg>
            </button>

        </div>

    </div>
</div>
<style>
    #messageModal button,
    #messageModal label {
        cursor: pointer;
    }
</style>

{{-- Script Modal --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const openModal    = document.getElementById('openModal');
        const messageModal = document.getElementById('messageModal');
        const closeModal   = document.getElementById('closeModal');
        const cancelModal  = document.getElementById('cancelModal');

        if (!openModal || !messageModal) return;

        openModal.addEventListener('click', () => {
            messageModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        });

        closeModal?.addEventListener('click', () => {
            messageModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        });

        cancelModal?.addEventListener('click', () => {
            messageModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        });

        messageModal.addEventListener('click', (event) => {
            if (event.target === messageModal) {
                messageModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        });
    });
</script>