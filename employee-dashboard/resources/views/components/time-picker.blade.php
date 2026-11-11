@props([
    'id',
    'label',
    'model',
    'value' => '',
    'options' => [],
    'placeholder' => 'Pilih jam',
])

<div class="relative w-full"
    x-data="{
        open: false,
        top: 0,
        left: 0,
        width: 0,
        active: -1,
        listHeight: 168,
        toggle() {
            this.open ? this.close() : this.openList();
        },
        openList() {
            this.open = true;
            this.$nextTick(() => {
                this.position();
                const options = this.options();
                const selected = options.findIndex((option) => option.dataset.selected === '1');
                this.active = selected >= 0 ? selected : 0;
                this.scrollActive();
            });
        },
        close() {
            this.open = false;
            this.active = -1;
        },
        position() {
            const trigger = this.$refs.trigger;
            if (!trigger) return;
            const rect = trigger.getBoundingClientRect();
            const gap = 6;
            const height = this.listHeight;
            const below = window.innerHeight - rect.bottom - gap;
            const above = rect.top - gap;
            this.width = Math.max(rect.width, 120);
            this.left = Math.min(Math.max(8, rect.left), Math.max(8, window.innerWidth - this.width - 8));
            this.top = below >= height || below >= above
                ? rect.bottom + gap
                : Math.max(8, rect.top - height - gap);
        },
        options() {
            return this.$refs.list ? [...this.$refs.list.querySelectorAll('[role=option]')] : [];
        },
        move(delta) {
            if (!this.open) {
                this.openList();
                return;
            }
            const options = this.options();
            if (!options.length) return;
            this.active = (this.active + delta + options.length) % options.length;
            this.scrollActive();
        },
        scrollActive() {
            const option = this.options()[this.active];
            option?.scrollIntoView({ block: 'nearest' });
        },
        choose(element) {
            if (!element) return;
            this.$wire.set('{{ $model }}', element.dataset.value);
            this.close();
        }
    }"
    @keydown.escape.stop="close()"
    @click.outside="close()"
    @scroll.window.capture="open && position()"
    @resize.window="open && position()">

    <button type="button" id="{{ $id }}" x-ref="trigger" @click="toggle()"
        @keydown.arrow-down.prevent="move(1)"
        @keydown.arrow-up.prevent="move(-1)"
        @keydown.enter.prevent="open ? choose(options()[active]) : toggle()"
        @keydown.space.prevent="open ? choose(options()[active]) : toggle()"
        aria-haspopup="listbox" :aria-expanded="open ? 'true' : 'false'"
        :aria-activedescendant="open && active >= 0 ? '{{ $id }}-opt-' + active : null"
        aria-label="{{ $label }}"
        class="flex h-[42px] w-full items-center justify-between gap-1 rounded-lg border border-[#D5DEE3] bg-white px-3 text-left text-sm text-[#172B36] transition hover:border-[#B9C6CE] focus:border-[#146C94] focus:outline-none focus:ring-2 focus:ring-[#146C94]">
        <span class="{{ $value === '' ? 'text-[#8A96A3]' : '' }}">{{ $value !== '' ? $value : $placeholder }}</span>
        <svg class="h-3.5 w-3.5 shrink-0 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"></path>
        </svg>
    </button>

    <div x-ref="list" x-show="open" x-cloak
        :style="`top: ${top}px; left: ${left}px; width: ${width}px; max-height: ${listHeight}px;`"
        class="fixed z-[95] overflow-y-auto rounded-lg border border-[#E2E8EC] bg-white py-1 shadow-xl"
        role="listbox" aria-label="{{ $label }}">
        <button type="button" role="option" id="{{ $id }}-opt-0"
            data-value="" data-selected="{{ $value === '' ? '1' : '0' }}"
            aria-selected="{{ $value === '' ? 'true' : 'false' }}"
            @click="choose($el)" @mouseenter="active = 0"
            :class="active === 0 ? 'bg-[#E3F1F7]' : ''"
            class="block w-full px-2.5 py-1.5 text-left text-sm transition hover:bg-[#F1F5F9] {{ $value === '' ? 'font-medium text-[#0B4A66]' : 'text-[#172B36]' }}">
            Tanpa jam
        </button>
        @foreach ($options as $option)
            <button type="button" role="option" id="{{ $id }}-opt-{{ $loop->index + 1 }}"
                data-value="{{ $option }}" data-selected="{{ $value === $option ? '1' : '0' }}"
                aria-selected="{{ $value === $option ? 'true' : 'false' }}"
                @click="choose($el)" @mouseenter="active = {{ $loop->index + 1 }}"
                :class="active === {{ $loop->index + 1 }} ? 'bg-[#E3F1F7]' : ''"
                class="block w-full px-2.5 py-1.5 text-left text-sm {{ $value === $option ? 'font-medium text-[#0B4A66]' : 'text-[#172B36]' }}">
                {{ $option }}
            </button>
        @endforeach
    </div>
</div>
