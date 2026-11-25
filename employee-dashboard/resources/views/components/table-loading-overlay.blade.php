@props([
    'target' => null,
    'text' => 'Memuat data...',
])

<div wire:loading.flex
     @if($target) wire:target="{{ $target }}" @endif
     class="fixed inset-0 z-50 items-center justify-center bg-[rgba(15,40,55,.45)] transition-all">
    <div class="flex flex-col items-center gap-3 rounded-2xl border border-slate-200/80 bg-white/95 px-7 py-6 shadow-2xl">
        <svg class="h-8 w-8 animate-spin text-[#146C94]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-[13.5px] font-semibold text-[#172B36]">{{ $text }}</span>
    </div>
</div>
