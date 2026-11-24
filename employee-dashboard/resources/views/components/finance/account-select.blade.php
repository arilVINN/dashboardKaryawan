@props([
    'name',
    'value' => '',
    'groupedAccounts' => [],
    'placeholder' => 'Pilih akun',
    'allowClear' => true,
    'class' => '',
    'id' => null,
])

@php
    $elementId = $id ?? 'account-select-' . str_replace(['.', '[', ']'], '-', $name);

    $flatList = [];
    foreach ($groupedAccounts as $kelompok => $accounts) {
        foreach ($accounts as $akun) {
            $flatList[] = [
                'id' => (string) $akun->id,
                'kode' => $akun->kode,
                'nama' => $akun->nama,
                'kelompok' => $kelompok,
                'label' => $akun->kode . ' ' . $akun->nama,
                'search' => strtolower($akun->kode . ' ' . $akun->nama . ' ' . $kelompok),
            ];
        }
    }

    $selectedAkun = collect($flatList)->firstWhere('id', (string) $value);
    $initialLabel = $selectedAkun ? $selectedAkun['label'] : '';
    $groupsList = array_values(array_unique(array_column($flatList, 'kelompok')));
    $optionsJson = json_encode($flatList);
    $groupsJson = json_encode($groupsList);
@endphp

<div class="relative w-full"
     x-data="{
        open: false,
        top: 0,
        left: 0,
        width: 0,
        maxHeight: 260,
        search: '{{ addslashes($initialLabel) }}',
        selectedVal: '{{ (string) $value }}',
        selectedLabel: '{{ addslashes($initialLabel) }}',
        isSearching: false,
        placeholder: '{{ $placeholder }}',
        options: {{ $optionsJson }},
        groups: {{ $groupsJson }},
        init() {
            this.syncFromVal('{{ (string) $value }}');
            this.$watch('$wire.{{ $name }}', (val) => {
                this.syncFromVal(val);
            });
        },
        syncFromVal(val) {
            this.selectedVal = (val !== null && val !== undefined && val !== '') ? String(val) : '';
            const found = this.options.find(o => o.id === this.selectedVal);
            this.selectedLabel = found ? found.label : '';
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
        get activeGroups() {
            const filtered = this.filteredOptions;
            return this.groups.filter(g => filtered.some(o => o.kelompok === g));
        },
        groupItems(group) {
            return this.filteredOptions.filter(o => o.kelompok === group);
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
            this.search = this.selectedLabel;
        },
        onInput() {
            this.isSearching = true;
            if (!this.open) {
                this.openList();
                this.isSearching = true;
            } else {
                this.position();
            }
        },
        choose(opt) {
            const val = opt ? opt.id : '';
            const lbl = opt ? opt.label : '';
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
             const padding = 8;
            const height = 260;
            const below = window.innerHeight - rect.bottom - gap - padding;
            const above = rect.top - gap - padding;
            this.width = Math.min(
                rect.width,
                window.innerWidth - (padding * 2)
            );
            this.left = Math.min(
                Math.max(padding, rect.left),
                window.innerWidth - this.width - padding
            );

            const openBelow = below >= 180 || below >= above;
            this.maxHeight = Math.max(
                40,
                Math.min(height, openBelow ? below : above)
            );
            this.top = openBelow
            ? rect.bottom + gap
            : Math.max(padding, rect.top - this.maxHeight - gap);
        }
     }"
     @click.outside="close()"
     @scroll.window.capture="open && position()"
     @resize.window="open && position()">

    {{-- Native select for Livewire test assertions & standard form sync --}}
    <select x-ref="hiddenSelect" wire:model.live="{{ $name }}" class="sr-only" tabindex="-1" aria-hidden="true">
        <option value="">{{ $placeholder }}</option>
        @foreach ($groupedAccounts as $kelompok => $accounts)
            <optgroup label="{{ $kelompok }}">
                @foreach ($accounts as $akun)
                    <option value="{{ $akun->id }}">{{ $akun->kode }} {{ $akun->nama }}</option>
                @endforeach
            </optgroup>
        @endforeach
    </select>

    {{-- Searchable Input trigger (SS 3) --}}
    <div x-ref="trigger" class="relative flex items-center w-full">
        <input type="text"
               x-ref="input"
               x-model="search"
               @focus="openList()"
               @click="openList()"
               @input="onInput()"
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

    {{-- Dropdown Menu (SS 3) --}}
    <template x-teleport="body">
    <div x-ref="list" x-show="open" x-cloak
        @click.stop
         :style="`top: ${top}px; left: ${left}px; width: ${width}px; max-height: ${maxHeight}px;`"
         class="fixed z-[9999] overflow-y-auto rounded-lg border border-[#E2E8EC] bg-white py-1 shadow-2xl text-[12px]">

        {{-- Default empty option --}}
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

        {{-- Grouped Accounts (Matching SS 3) --}}
        <template x-for="grp in activeGroups" :key="grp">
            <div>
                <div class="bg-[#F8FAFC] px-3 py-1 text-[11px] font-bold text-[#172B36] border-y border-[#EEF2F5] select-none"
                     x-text="grp">
                </div>
                <template x-for="opt in groupItems(grp)" :key="opt.id">
                    <button type="button"
                            @click="choose(opt)"
                            :class="selectedVal === opt.id ? 'bg-[#EAF4F8] text-[#146C94] font-medium' : 'text-[#172B36] hover:bg-[#F1F5F9]'"
                            class="flex w-full items-center justify-between px-4 py-1.5 text-left transition">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-[#4B5563] text-[11.5px]" x-text="opt.kode"></span>
                            <span x-text="opt.nama"></span>
                        </div>
                        <template x-if="selectedVal === opt.id">
                            <svg class="h-3.5 w-3.5 text-[#146C94]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path>
                            </svg>
                        </template>
                    </button>
                </template>
            </div>
        </template>

        <template x-if="filteredOptions.length === 0">
            <div class="px-3 py-2 text-center text-[#94A3B8] text-[12px]">
                Akun tidak ditemukan
            </div>
        </template>
    </div>
    </template>
</div>
