@php
    $name = $name ?? 'tanggal';
    $id = $id ?? 'date-'.$name;
    $label = $label ?? 'Tanggal';
    $required = $required ?? false;
    $optional = $optional ?? false;
    $max = $max ?? '';
    $minProperty = $minProperty ?? '';
@endphp

<div class="date-picker" data-date-picker data-name="{{ $name }}" data-max="{{ $max }}" data-min-property="{{ $minProperty }}">
    <label class="block text-[13px] font-medium text-[#172B36] mb-1.5" for="{{ $id }}">
        {{ $label }}
        @if ($required)
            <span class="text-[#DC2626]">*</span>
        @elseif ($optional)
            <span class="text-[#4B5563]">(opsional)</span>
        @endif
    </label>
    <div class="relative">
        <input id="{{ $id }}" type="text" data-date-display readonly autocomplete="off"
               placeholder="10 Nov 2026" aria-label="{{ $label }}"
               wire:blur="validateOnly('{{ $name }}')"
               class="w-full h-9 pl-3 pr-10 text-[13px] text-[#172B36] bg-white border border-[#D5DEE3] rounded-lg outline-none transition cursor-pointer focus:ring-2 focus:ring-[#146C94] focus:border-[#146C94] placeholder:text-[#6B7680]">
        <button type="button" data-kf-toggle aria-label="Pilih {{ strtolower($label) }}"
                class="absolute inset-y-0 right-0 flex w-9 items-center justify-center text-[#4B5563] hover:text-[#146C94]">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <rect x="3" y="5" width="18" height="16" rx="2" />
                <path stroke-linecap="round" d="M3 10h18M8 3v4m8-4v4" />
            </svg>
        </button>
        <input type="hidden" data-date-value wire:model.live="{{ $name }}">

        <div data-kf-popover
             class="absolute left-0 top-full z-30 mt-1 hidden w-64 rounded-xl border border-[#E2E8EC] bg-white p-2 shadow-xl">
            <div class="mb-1 flex items-center justify-between px-1">
                <button type="button" data-kf-prev aria-label="Bulan sebelumnya"
                        class="flex h-7 w-7 items-center justify-center rounded text-[#4B5563] hover:bg-[#F4F7F9]">‹</button>
                <span data-kf-bulan class="text-[12px] font-semibold text-[#172B36]"></span>
                <button type="button" data-kf-next aria-label="Bulan berikutnya"
                        class="flex h-7 w-7 items-center justify-center rounded text-[#4B5563] hover:bg-[#F4F7F9]">›</button>
            </div>
            <div class="grid grid-cols-7 gap-0.5 px-0.5 pb-1 text-center text-[11px] text-[#4B5563]">
                @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $hari)
                    <span>{{ $hari }}</span>
                @endforeach
            </div>
            <div data-kf-grid class="grid grid-cols-7 gap-0.5 text-center"></div>
        </div>
    </div>
</div>
