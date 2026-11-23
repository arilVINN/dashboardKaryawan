@props([
    'id',
    'label',
    'model',
    'value' => '',
    'placeholder' => 'Pilih',
    'options' => [],
    'footerAction' => '',
    'footerLabel' => '',
    'deleteMethod' => '',
    'deleteKind' => '',
])

@php
    $selected = collect($options)->firstWhere('value', $value);
@endphp

<div class="relative w-full"
    x-data="{
        open: false,
        top: 0,
        left: 0,
        width: 0,
        height: 240,
        toggle() {
            this.open ? this.close() : this.openList();
        },
        openList() {
            // Measure first so the list never flashes at the default corner.
            this.position();
            this.open = true;
            this.$nextTick(() => {
                this.position();
                this.items()[0]?.focus();
            });
        },
        close() {
            this.open = false;
        },
        position() {
            const trigger = this.$refs.trigger;
            if (!trigger) return;
            const rect = trigger.getBoundingClientRect();
            const gap = 6;
            const below = window.innerHeight - rect.bottom - gap;
            const above = rect.top - gap;
            this.width = Math.max(rect.width, 200);
            this.height = Math.min(this.items().length * 36 + (this.$refs.footer ? 48 : 8), 240);
            this.left = Math.min(Math.max(8, rect.left), Math.max(8, window.innerWidth - this.width - 8));
            this.top = below >= this.height || below >= above
                ? rect.bottom + gap
                : Math.max(8, rect.top - this.height - gap);
        },
        items() {
            return this.$refs.list ? [...this.$refs.list.querySelectorAll('[role=option]')] : [];
        },
        choose(option) {
            if (!option) return;
            this.$wire.set('{{ $model }}', option.dataset.value);
            this.close();
        },
        move(delta) {
            const items = this.items();
            if (!items.length) return;
            const index = items.indexOf(document.activeElement);
            const next = index < 0 ? 0 : (index + delta + items.length) % items.length;
            items[next].focus();
        }
    }"
    @click.outside="close()"
    @scroll.window.capture="open && position()"
    @resize.window="open && position()">

    <button type="button" id="{{ $id }}" x-ref="trigger" @click="toggle()"
        @keydown.arrow-down.prevent="openList()"
        @keydown.arrow-up.prevent="openList()"
        @keydown.enter.prevent="openList()"
        @keydown.space.prevent="openList()"
        aria-haspopup="listbox" :aria-expanded="open ? 'true' : 'false'"
        aria-label="{{ $label }}"
        class="flex h-[42px] w-full items-center justify-between gap-2 rounded-lg border border-[#D5DEE3] bg-white px-2.5 text-left text-sm transition hover:border-[#B9C6CE] focus:border-[#146C94] focus:outline-none focus:ring-2 focus:ring-[#146C94]">
        @if ($selected)
            @if (($selected['kind'] ?? 'plain') === 'tag')
                <span class="inline-flex h-[22px] items-center rounded-md border px-2 text-[11px] font-semibold"
                    style="background-color: {{ $selected['fill'] }}; border-color: {{ $selected['border'] }}; color: {{ $selected['text'] ?? '#172B36' }};">
                    {{ $selected['label'] }}
                </span>
            @elseif (($selected['kind'] ?? 'plain') === 'status')
                <span class="inline-flex h-[22px] items-center gap-1.5 rounded-full border px-2 text-[11px] font-semibold"
                    style="background-color: {{ $selected['fill'] }}; border-color: {{ $selected['border'] }}; color: {{ $selected['text'] ?? '#172B36' }};">
                    <span class="h-1.5 w-1.5 shrink-0 rounded-full" style="background-color: {{ $selected['dot'] }};" aria-hidden="true"></span>
                    {{ $selected['label'] }}
                </span>
            @else
                <span class="truncate text-[#172B36]">{{ $selected['label'] }}</span>
            @endif
        @else
            <span class="truncate text-[#8A96A3]">{{ $placeholder }}</span>
        @endif

        <svg class="h-4 w-4 shrink-0 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 9 6 6 6-6"></path>
        </svg>
    </button>

    <div x-ref="list" x-show="open" x-cloak
        :style="`top: ${top}px; left: ${left}px; width: ${width}px; max-height: ${height}px;`"
        class="fixed z-[95] overflow-y-auto rounded-lg border border-[#E2E8EC] bg-white py-1 shadow-xl"
        role="listbox" aria-label="{{ $label }}"
        @keydown.arrow-down.prevent="move(1)"
        @keydown.arrow-up.prevent="move(-1)"
        @keydown.escape.prevent.stop="close()">

        @foreach ($options as $option)
            <div role="option" tabindex="-1" data-value="{{ $option['value'] }}"
                aria-selected="{{ $option['value'] === $value ? 'true' : 'false' }}"
                @keydown.enter.prevent="choose($el)"
                @keydown.space.prevent="choose($el)"
                class="group flex w-full items-center gap-1 px-2.5 py-1.5 text-left transition hover:bg-[#F1F5F9] focus:bg-[#E3F1F7] focus:outline-none">
                <button type="button" tabindex="-1" @click="choose($el.closest('[role=option]'))"
                    class="flex min-w-0 flex-1 items-center text-left focus:outline-none">
                    @if (($option['kind'] ?? 'plain') === 'tag')
                        <span class="inline-flex h-[22px] items-center rounded-md border px-2 text-[11px] font-semibold"
                            style="background-color: {{ $option['fill'] }}; border-color: {{ $option['border'] }}; color: {{ $option['text'] ?? '#172B36' }};">
                            {{ $option['label'] }}
                        </span>
                    @elseif (($option['kind'] ?? 'plain') === 'status')
                        <span class="inline-flex h-[22px] items-center gap-1.5 rounded-full border px-2 text-[11px] font-semibold"
                            style="background-color: {{ $option['fill'] }}; border-color: {{ $option['border'] }}; color: {{ $option['text'] ?? '#172B36' }};">
                            <span class="h-1.5 w-1.5 shrink-0 rounded-full" style="background-color: {{ $option['dot'] }};" aria-hidden="true"></span>
                            {{ $option['label'] }}
                        </span>
                    @else
                        <span class="truncate text-sm text-[#172B36]">{{ $option['label'] }}</span>
                    @endif
                </button>

                @if ($deleteMethod !== '')
                    <button type="button" @click.stop
                        wire:click="{{ $deleteMethod }}('{{ $deleteKind }}', '{{ $option['value'] }}')"
                        aria-label="Hapus {{ $option['label'] }}" title="Hapus {{ $option['label'] }}"
                        class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-md text-[#8A96A3] opacity-0 transition hover:bg-red-50 hover:text-[#B91C1C] focus:opacity-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#B91C1C] group-hover:opacity-100">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                @endif
            </div>
        @endforeach

        @if ($footerAction !== '')
            <div x-ref="footer" class="mt-1 border-t border-[#E8EDF2] pt-1">
                <button type="button" wire:click="{{ $footerAction }}"
                    class="flex w-full items-center gap-1.5 rounded-md px-2.5 py-1.5 text-left text-[12px] font-semibold text-[#146C94] transition hover:bg-[#E3F1F7] focus:bg-[#E3F1F7] focus:outline-none">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"></path>
                    </svg>
                    {{ $footerLabel }}
                </button>
            </div>
        @endif
    </div>
</div>
