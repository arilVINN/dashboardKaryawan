<div class="flex flex-col gap-3 w-full mt-2">
    <div class="flex justify-between items-center mb-2">
        @if ($withIcon ?? false)
            <h2 class="text-lg font-semibold text-[#172B36] flex items-center gap-2">
                <span class="shrink-0 rounded-lg bg-[#E3F1F7] p-1.5 text-[#146C94]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"></path>
                    </svg>
                </span>
                <span>Daftar Pesan</span>
            </h2>
        @else
            <h2 class="text-lg font-semibold text-[#172B36]">Daftar Pesan</h2>
        @endif
        <button type="button" onclick="bukaModalPesanHrd()"
            class="text-sm px-4 py-2 bg-[#0097B2] text-white rounded-lg font-medium hover:bg-[#008199] transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
            </svg>
            Buat Pesan
        </button>
    </div>

    <livewire:hrd.pesan-table />

    <livewire:hrd.compose-message />
</div>
