@props([
    'id',
    'label',
    'model',
    'value' => '',
    'placeholder' => 'Pilih tanggal',
])

@php
    $display = $value !== ''
        ? \Carbon\Carbon::parse($value)->locale('id')->translatedFormat('j M Y')
        : '';
@endphp

<div class="relative w-full" wire:key="content-date-picker-{{ $value }}"
    x-data="{
        open: false,
        top: 0,
        left: 0,
        view: null,
        months: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
        weekdays: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
        init() {
            const base = '{{ $value }}' !== '' ? new Date('{{ $value }}T00:00:00') : new Date();
            this.view = new Date(base.getFullYear(), base.getMonth(), 1);
        },
        iso(date) {
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return date.getFullYear() + '-' + month + '-' + day;
        },
        today() {
            return this.iso(new Date());
        },
        toggle() {
            this.open ? this.close() : this.openList();
        },
        openList() {
            // Position before showing, otherwise the popover is painted once at
            // the default 0,0 corner before the nextTick measurement lands.
            this.position();
            this.open = true;
            this.$nextTick(() => {
                this.position();
                const selected = this.$refs.grid?.querySelector('[data-selected=1]');
                (selected || this.$refs.grid?.querySelector('[data-day]'))?.focus();
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
            const width = 288;
            const height = 322;
            const below = window.innerHeight - rect.bottom - gap;
            const above = rect.top - gap;
            this.left = Math.min(Math.max(8, rect.left), Math.max(8, window.innerWidth - width - 8));
            this.top = below >= height || below >= above
                ? rect.bottom + gap
                : Math.max(8, rect.top - height - gap);
        },
        days() {
            const first = new Date(this.view.getFullYear(), this.view.getMonth(), 1);
            const offset = (first.getDay() + 6) % 7;
            const start = new Date(first);
            start.setDate(1 - offset);

            return Array.from({ length: 42 }, (_, index) => {
                const date = new Date(start);
                date.setDate(start.getDate() + index);
                return {
                    iso: this.iso(date),
                    day: date.getDate(),
                    outside: date.getMonth() !== this.view.getMonth(),
                    today: this.iso(date) === this.today(),
                    selected: this.iso(date) === '{{ $value }}',
                };
            });
        },
        moveView(delta) {
            this.view = new Date(this.view.getFullYear(), this.view.getMonth() + delta, 1);
        },
        pick(day) {
            // Hide the popover before the Livewire round-trip. The dynamic
            // wire:key on the root makes Livewire replace this component with
            // a fresh, closed one, so the old popover can never flash at the
            // top-left corner while the modal is being re-rendered.
            this.close();

            // Only notify Livewire when the date actually changes. Selecting the
            // pre-filled date (usually today) must not mark the form dirty or
            // leave the modal stuck behind a discard confirmation.
            if (day.iso !== '{{ $value }}') {
                this.$wire.set('{{ $model }}', day.iso);
            }
        },
        pickToday() {
            this.pick({ iso: this.today() });
        },
        gridKey(event) {
            const buttons = [...(this.$refs.grid?.querySelectorAll('[data-day]') || [])];
            const index = buttons.indexOf(document.activeElement);
            if (index < 0) return;

            let next = index;

            switch (event.key) {
                case 'ArrowRight': next = index + 1; break;
                case 'ArrowLeft': next = index - 1; break;
                case 'ArrowDown': next = index + 7; break;
                case 'ArrowUp': next = index - 7; break;
                case 'Home': next = index - (index % 7); break;
                case 'End': next = index + (6 - (index % 7)); break;
                case 'PageUp': event.preventDefault(); this.moveView(-1); return;
                case 'PageDown': event.preventDefault(); this.moveView(1); return;
                default: return;
            }

            event.preventDefault();

            if (next >= 0 && next < buttons.length) {
                buttons[next].focus();
            }
        }
    }"
    @click.outside="close()"
    @scroll.window.capture="open && position()"
    @resize.window="open && position()">

    <button type="button" id="{{ $id }}" x-ref="trigger" @click="toggle()"
        @keydown.arrow-down.prevent="openList()"
        @keydown.enter.prevent="openList()"
        @keydown.space.prevent="openList()"
        aria-haspopup="dialog" :aria-expanded="open ? 'true' : 'false'"
        aria-label="{{ $label }}"
        class="flex h-[42px] w-full items-center justify-between gap-2 rounded-lg border border-[#D5DEE3] bg-white px-3 text-left text-sm transition hover:border-[#B9C6CE] focus:border-[#146C94] focus:outline-none focus:ring-2 focus:ring-[#146C94]">
        <span class="truncate {{ $display !== '' ? 'text-[#172B36]' : 'text-[#8A96A3]' }}">
            {{ $display !== '' ? $display : $placeholder }}
        </span>
        <svg class="h-4 w-4 shrink-0 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z"></path>
        </svg>
    </button>

    <div x-show="open" x-cloak
        :style="`top: ${top}px; left: ${left}px; width: 288px;`"
        class="fixed z-[95] rounded-xl border border-[#E2E8EC] bg-white p-3 shadow-xl"
        role="dialog" aria-label="{{ $label }}"
        @keydown.escape.prevent.stop="close()">

        <div class="mb-2 flex items-center justify-between gap-2">
            <button type="button" @click="moveView(-1)" aria-label="Bulan sebelumnya"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-[#4B5563] transition hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"></path>
                </svg>
            </button>
            <span class="text-sm font-semibold text-[#172B36]" x-text="months[view.getMonth()] + ' ' + view.getFullYear()"></span>
            <button type="button" @click="moveView(1)" aria-label="Bulan berikutnya"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-[#4B5563] transition hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"></path>
                </svg>
            </button>
        </div>

        <div class="grid grid-cols-7 gap-1">
            <template x-for="weekday in weekdays" :key="weekday">
                <span class="flex h-7 items-center justify-center text-[11px] font-medium text-[#4B5563]" x-text="weekday"></span>
            </template>
        </div>

        <div class="mt-1 grid grid-cols-7 gap-1" x-ref="grid" @keydown="gridKey($event)">
            <template x-for="day in days()" :key="day.iso">
                <button type="button" data-day :data-selected="day.selected ? '1' : '0'"
                    @click="pick(day)"
                    :aria-label="day.iso"
                    :aria-pressed="day.selected ? 'true' : 'false'"
                    class="flex h-8 items-center justify-center rounded-md text-[13px] transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]"
                    :class="day.selected
                        ? 'bg-[#146C94] font-semibold text-white'
                        : (day.today
                            ? 'border border-[#146C94] font-semibold text-[#146C94] hover:bg-[#E3F1F7]'
                            : (day.outside
                                ? 'text-[#A0AAB4] hover:bg-slate-100'
                                : 'text-[#172B36] hover:bg-slate-100'))"
                    x-text="day.day"></button>
            </template>
        </div>

        <div class="mt-2 flex items-center justify-between border-t border-[#E8EDF2] pt-2">
            <button type="button" id="{{ $id }}-today" @click="pickToday()"
                class="rounded-md px-2 py-1 text-[12px] font-semibold text-[#146C94] transition hover:bg-[#E3F1F7] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                Hari ini
            </button>
            <button type="button" id="{{ $id }}-close" @click="close()"
                class="rounded-md px-2 py-1 text-[12px] font-medium text-[#4B5563] transition hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                Tutup
            </button>
        </div>
    </div>
</div>
