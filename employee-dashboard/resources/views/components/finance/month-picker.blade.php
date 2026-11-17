@php
    [$currentYear, $currentMonth] = array_map('intval', explode('-', $periode));
    $months = [
        1 => 'Jan',
        2 => 'Feb',
        3 => 'Mar',
        4 => 'Apr',
        5 => 'Mei',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Agu',
        9 => 'Sep',
        10 => 'Okt',
        11 => 'Nov',
        12 => 'Des',
    ];
@endphp

<div class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="background-color: rgba(15, 40, 55, 0.45);" wire:click.self="closeMonthPicker" x-data="{}"
    x-init="$nextTick(() => $refs.monthCancel?.focus())" @keydown.escape.window="$wire.closeMonthPicker()">
    <div class="w-full max-w-[340px] rounded-xl border border-[#E2E8EC] bg-white p-4 shadow-xl" role="dialog"
        aria-modal="true" aria-labelledby="month-picker-title">
        <div class="flex items-start justify-between gap-4">
            <h3 id="month-picker-title" class="text-[14px] font-semibold text-[#172B36]">Pilih bulan</h3>
            <button type="button" wire:click="closeMonthPicker" aria-label="Tutup dialog"
                class="rounded-lg p-1 text-[#4B5563] transition hover:bg-[#F5FAFC] hover:text-[#172B36] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="mt-4 flex items-center justify-between gap-2">
            <button type="button" wire:click="previousPickerYear" aria-label="Tahun sebelumnya"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-[#4B5563] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m15 19-7-7 7-7"></path>
                </svg>
            </button>
            <span class="text-[14px] font-semibold tabular-nums text-[#172B36]">{{ $pickerYear }}</span>
            <button type="button" wire:click="nextPickerYear" aria-label="Tahun berikutnya"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-[#4B5563] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"></path>
                </svg>
            </button>
        </div>

        <div class="mt-3 grid grid-cols-3 gap-1.5">
            @foreach ($months as $number => $label)
                @php $selected = $currentYear === $pickerYear && $currentMonth === $number; @endphp
                <button type="button" wire:click="selectMonth({{ $number }})"
                    @class([
                        'h-9 rounded-lg border text-[12px] font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]',
                        'border-[#146C94] bg-[#E3F1F7] text-[#146C94]' => $selected,
                        'border-transparent text-[#172B36] hover:bg-[#F5FAFC]' => ! $selected,
                    ])>
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="mt-4 flex justify-end">
            <button x-ref="monthCancel" type="button" wire:click="closeMonthPicker"
                class="inline-flex h-9 items-center rounded-lg border border-[#D5DEE3] bg-white px-4 text-[12px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                Batal
            </button>
        </div>
    </div>
</div>
