@props([
    'name',
    'value' => '',
    'divisions' => [],
    'placeholder' => 'Pilih divisi',
    'allowClear' => true,
    'class' => '',
    'id' => null,
])

@php
    $elementId = $id ?? 'divisi-select-' . str_replace(['.', '[', ']'], '-', $name);
    $divisionsColl = collect($divisions);
    $selectedDivisi = $divisionsColl->firstWhere('id', $value);
    $initialLabel = $selectedDivisi ? $selectedDivisi->nama . ' (' . $selectedDivisi->kode . ')' : '';
    if (!$selectedDivisi && $placeholder === 'Semua divisi') {
        $initialLabel = 'Semua divisi';
    }

    $optionsData = $divisionsColl->map(fn ($d) => [
        'id' => (string) $d->id,
        'kode' => $d->kode,
        'nama' => $d->nama,
        'label' => $d->nama . ' (' . $d->kode . ')',
        'search' => strtolower($d->nama . ' ' . $d->kode),
    ])->values()->all();

    $optionsJson = json_encode($optionsData);
@endphp

<div class="relative w-full"
     x-data="{
        open: false,
        top: 0,
        left: 0,
        width: 0,
        search: '{{ addslashes($initialLabel) }}',
        selectedVal: '{{ (string) $value }}',
        selectedLabel: '{{ addslashes($initialLabel) }}',
        isSearching: false,
        highlightedIndex: -1,
        placeholder: '{{ $placeholder }}',
        options: {{ $optionsJson }},
        init() {
            this.syncFromVal('{{ (string) $value }}');
            this.$watch('$wire.{{ $name }}', (val) => {
                this.syncFromVal(val);
            });
        },
        syncFromVal(val) {
            this.selectedVal = (val !== null && val !== undefined && val !== '') ? String(val) : '';
            const found = this.options.find(o => o.id === this.selectedVal);
            if (found) {
                this.selectedLabel = found.label;
            } else if (this.placeholder === 'Semua divisi' && !this.selectedVal) {
                this.selectedLabel = 'Semua divisi';
            } else {
                this.selectedLabel = '';
            }
            if (!this.isSearching) {
                this.search = this.selectedLabel;
            }
        },
        get filteredOptions() {
            if (!this.isSearching || !this.search.trim()) {
                return this.options;
            }
            const q = this.search.toLowerCase().trim();
            return this.options.filter(o => o.search.includes(q) || o.label.toLowerCase().includes(q));
        },
        toggle() {
            this.open ? this.close() : this.openList();
        },
        openList() {
            this.position();
            this.open = true;
            this.isSearching = false;
            this.search = this.selectedLabel;
            this.$nextTick(() => {
                this.position();
                this.$refs.input?.select();
            });
        },
        close() {
            this.open = false;
            this.isSearching = false;
            this.highlightedIndex = -1;
            this.search = this.selectedLabel;
        },
        onInput() {
            this.isSearching = true;
            this.highlightedIndex = 0;
            if (!this.open) {
                this.openList();
                this.isSearching = true;
            } else {
                this.position();
            }
        },
        choose(opt) {
            const val = opt ? opt.id : '';
            const lbl = opt ? opt.label : (this.placeholder === 'Semua divisi' ? 'Semua divisi' : '');
            this.selectedVal = val;
            this.selectedLabel = lbl;
            this.search = lbl;
            this.isSearching = false;
            this.close();
            if (this.$refs.hiddenSelect) {
                this.$refs.hiddenSelect.value = val;
                this.$refs.hiddenSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }
            $wire.set('{{ $name }}', val);
        },
        clear() {
            this.choose(null);
            this.$refs.input?.focus();
        },
        position() {
            const trigger = this.$refs.trigger;
            if (!trigger) return;
            const rect = trigger.getBoundingClientRect();
            const gap = 4;
            const height = 240;
            const below = window.innerHeight - rect.bottom - gap;
            const above = rect.top - gap;
            this.width = Math.max(rect.width, 200);
            this.left = Math.min(Math.max(8, rect.left), Math.max(8, window.innerWidth - this.width - 8));
            this.top = (below >= height || below >= above)
                ? rect.bottom + gap
                : Math.max(8, rect.top - height - gap);
        },
        move(delta) {
            const list = this.filteredOptions;
            if (!list.length) return;
            if (!this.open) {
                this.openList();
                return;
            }
            this.highlightedIndex = (this.highlightedIndex + delta + list.length) % list.length;
            this.$nextTick(() => {
                const el = this.$refs.list?.querySelector(`[data-index='${this.highlightedIndex}']`);
                el?.scrollIntoView({ block: 'nearest' });
            });
        },
        selectHighlighted() {
            const list = this.filteredOptions;
            if (this.open && list.length && this.highlightedIndex >= 0 && this.highlightedIndex < list.length) {
                this.choose(list[this.highlightedIndex]);
            } else if (!this.open) {
                this.openList();
            }
        }
     }"
     @click.outside="close()"
     @scroll.window.capture="open && position()"
     @resize.window="open && position()">

    {{-- Native select for Livewire test assertions & standard form sync --}}
    <select x-ref="hiddenSelect" wire:model.live="{{ $name }}" class="sr-only" tabindex="-1" aria-hidden="true">
        <option value="">{{ $placeholder }}</option>
        @foreach ($divisions as $divisi)
            <option value="{{ $divisi->id }}">{{ $divisi->nama }} ({{ $divisi->kode }})</option>
        @endforeach
    </select>

    {{-- Searchable Input trigger (SS 2) --}}
    <div x-ref="trigger" class="relative flex items-center w-full">
        <input type="text"
               x-ref="input"
               x-model="search"
               @focus="openList()"
               @click="openList()"
               @input="onInput()"
               @keydown.arrow-down.prevent="move(1)"
               @keydown.arrow-up.prevent="move(-1)"
               @keydown.enter.prevent="selectHighlighted()"
               @keydown.escape.prevent="close()"
               placeholder="{{ $placeholder }}"
               class="{{ $class }} pr-8 bg-white text-[#172B36] placeholder:text-[#94A3B8] border border-[#D5DEE3] focus:border-[#146C94] focus:outline-none transition cursor-pointer"
        />

        <div class="absolute right-2.5 flex items-center gap-1">
            <template x-if="selectedVal && '{{ $allowClear ? 'true' : 'false' }}' === 'true'">
                <button type="button" @click.stop="clear()" aria-label="Hapus pilihan"
                        class="text-[#94A3B8] hover:text-red-500 transition text-[15px] leading-none px-0.5">
                    &times;
                </button>
            </template>
            <button type="button" @click.stop="toggle()" tabindex="-1" class="text-[#64748B] focus:outline-none">
                <svg class="h-4 w-4 transition-transform duration-150" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"></path>
                </svg>
            </button>
        </div>
    </div>

    {{-- Dropdown Menu (SS 2) --}}
    <div x-ref="list" x-show="open" x-cloak
         :style="`top: ${top}px; left: ${left}px; width: ${width}px; max-height: 240px;`"
         class="fixed z-[9999] overflow-y-auto rounded-lg border border-[#E2E8EC] bg-white py-1 shadow-2xl text-[12.5px]">

        {{-- Default / Empty option --}}
        <button type="button"
                @click="choose(null)"
                :class="!selectedVal ? 'bg-[#F1F5F9] font-medium text-[#146C94]' : 'text-[#64748B] hover:bg-[#F8FAFC]'"
                class="flex w-full items-center justify-between px-3 py-1.5 text-left transition">
            <span>{{ $placeholder }}</span>
            <template x-if="!selectedVal">
                <svg class="h-3.5 w-3.5 text-[#146C94]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path>
                </svg>
            </template>
        </button>

        {{-- Options list --}}
        <template x-for="(opt, idx) in filteredOptions" :key="opt.id">
            <button type="button"
                    :data-index="idx"
                    @click="choose(opt)"
                    @mouseenter="highlightedIndex = idx"
                    :class="{
                        'bg-[#EAF4F8] text-[#146C94] font-medium': selectedVal === opt.id,
                        'bg-[#F1F5F9]': highlightedIndex === idx && selectedVal !== opt.id,
                        'text-[#172B36]': selectedVal !== opt.id
                    }"
                    class="flex w-full items-center justify-between px-3 py-1.5 text-left transition">
                <div class="flex items-center gap-1.5">
                    <span x-text="opt.nama"></span>
                    <span class="text-[#64748B] font-mono text-[11px]" x-text="'(' + opt.kode + ')'"></span>
                </div>
                <template x-if="selectedVal === opt.id">
                    <svg class="h-3.5 w-3.5 text-[#146C94]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path>
                    </svg>
                </template>
            </button>
        </template>

        <template x-if="filteredOptions.length === 0">
            <div class="px-3 py-2 text-center text-[#94A3B8] text-[12px]">
                Divisi tidak ditemukan
            </div>
        </template>
    </div>
</div>
