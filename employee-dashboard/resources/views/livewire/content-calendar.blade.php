<div class="relative w-full"
    x-data="{
        returnFocus: null,
        draggingId: null,
        dragOver: null,
        confirmOpen: false,
        toasts: [],
        toastSeq: 0,
        remember(el) {
            this.returnFocus = el ? el.id : null;
        },
        restoreFocus() {
            const id = this.returnFocus;
            this.returnFocus = null;
            this.$nextTick(() => {
                if (id) document.getElementById(id)?.focus();
            });
        },
        pushToast(detail) {
            const id = ++this.toastSeq;
            this.toasts.push({
                id,
                message: detail?.message || '',
                type: detail?.type || 'success',
                action: detail?.action || null
            });
            setTimeout(() => this.removeToast(id), 5200);
        },
        removeToast(id) {
            this.toasts = this.toasts.filter((toast) => toast.id !== id);
        },
        closeConfirm() {
            this.confirmOpen = false;
            this.$nextTick(() => this.$refs.deleteTrigger?.focus());
        }
    }"
    @content-toast.window="pushToast($event.detail)"
    @content-drawer-closed.window="restoreFocus()"
    @content-focus-title.window="$nextTick(() => $refs.titleInput?.focus())"
    @content-focus-drawer.window="$nextTick(() => document.querySelector('[data-content-drawer]')?.focus())"
    @content-calendar-refresh.window="$wire.$refresh()"
    @keydown.escape.window="if (confirmOpen) { closeConfirm() } else if (document.querySelector('[data-content-form]')) { $wire.closeForm() } else if (document.querySelector('[data-content-drawer]')) { $wire.closeDrawer() }">

    <style>
        [x-cloak] { display: none !important; }
        @keyframes content-toast-in {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .content-toast { animation: content-toast-in 0.18s ease-out both; }
        @media (prefers-reduced-motion: reduce) {
            .content-toast { animation: none; }
        }
    </style>

    {{-- Toast --}}
    <div class="pointer-events-none fixed left-1/2 top-5 z-[9999] flex w-full max-w-sm -translate-x-1/2 flex-col items-stretch gap-2 px-4" aria-live="polite">
        <template x-for="toast in toasts" :key="toast.id">
            <div class="content-toast pointer-events-auto flex items-center gap-3 rounded-xl border bg-white px-4 py-3 shadow-lg"
                :class="toast.type === 'error' ? 'border-red-200' : 'border-[#E2E8EC]'">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full"
                    :class="toast.type === 'error' ? 'bg-red-50 text-red-600' : 'bg-[#D9F5E5] text-[#059669]'">
                    <svg x-show="toast.type !== 'error'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 13 4 4L19 7"></path>
                    </svg>
                    <svg x-show="toast.type === 'error'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"></path>
                    </svg>
                </span>
                <p class="min-w-0 flex-1 text-sm font-medium text-[#172B36]" x-text="toast.message"></p>
                <button x-show="toast.action === 'undo'" type="button"
                    @click="$wire.undoMove(); removeToast(toast.id)"
                    class="shrink-0 text-sm font-semibold text-[#146C94] hover:underline">
                    Urungkan
                </button>
                <button type="button" @click="removeToast(toast.id)"
                    class="shrink-0 rounded p-1 text-[#64748B] hover:bg-slate-100 hover:text-[#172B36]"
                    aria-label="Tutup notifikasi">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    {{-- Toolbar row 1 --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex min-w-0 flex-wrap items-center gap-2">
            <button type="button" wire:click="previous" aria-label="Periode sebelumnya"
                class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-[#E2E8EC] bg-white text-[#4B5563] transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0097B2]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"></path>
                </svg>
            </button>
            <button type="button" wire:click="next" aria-label="Periode berikutnya"
                class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-[#E2E8EC] bg-white text-[#4B5563] transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0097B2]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"></path>
                </svg>
            </button>
            <button type="button" wire:click="goToday"
                class="inline-flex h-10 items-center rounded-lg border border-[#E2E8EC] bg-white px-3 text-sm font-medium text-[#4B5563] transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0097B2]">
                Hari ini
            </button>
            <h2 class="ml-1 truncate text-xl font-semibold text-[#172B36]">{{ $monthLabel }}</h2>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <div class="inline-flex rounded-lg border border-[#E2E8EC] bg-white p-0.5" role="group" aria-label="Tampilan kalender">
                @foreach (['bulan' => 'Bulan', 'minggu' => 'Minggu', 'daftar' => 'Daftar'] as $key => $label)
                    <button type="button" wire:click="setView('{{ $key }}')"
                        aria-pressed="{{ $view === $key ? 'true' : 'false' }}"
                        class="inline-flex h-9 items-center rounded-md px-3 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0097B2] {{ $view === $key ? 'bg-[#146C94] text-white' : 'text-[#4B5563] hover:bg-slate-50' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <button type="button" id="content-add-event" wire:click="openCreate()" @click="remember($el)"
                class="inline-flex h-10 items-center gap-2 rounded-lg bg-[#0097B2] px-4 text-sm font-semibold text-white transition hover:bg-[#0086A0] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0097B2] focus-visible:ring-offset-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"></path>
                </svg>
                Tambah event
            </button>
        </div>
    </div>

    {{-- Toolbar row 2 --}}
    <div class="mt-3 flex flex-wrap items-center gap-2">
        <select wire:model.live="division" aria-label="Filter divisi"
            class="h-10 rounded-lg border border-[#E2E8EC] bg-white px-3 text-sm text-[#172B36] focus:border-[#0097B2] focus:outline-none focus:ring-2 focus:ring-[#0097B2]/20">
            <option value="semua">Semua divisi</option>
            <option value="umum">Umum</option>
            @foreach ($divisionOptions as $divisionOption)
                <option value="{{ $divisionOption->id_divisi }}">{{ $divisionOption->nama_divisi }}</option>
            @endforeach
        </select>

        <div class="ml-auto flex flex-wrap items-center gap-1.5">
            <button type="button" wire:click="toggleHolidays"
                aria-pressed="{{ $showHolidays ? 'true' : 'false' }}"
                class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-[11px] font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0097B2] {{ $showHolidays ? 'border-[#E0D6A8] bg-[#F4F1E6] text-[#5C4D14]' : 'border-[#E2E8EC] bg-white text-[#4B5563] hover:bg-slate-50' }}">
                <span class="h-1.5 w-1.5 rounded-full bg-[#A39161]"></span>
                Hari Libur
            </button>
            <button type="button" wire:click="toggleInternal"
                aria-pressed="{{ $showInternal ? 'true' : 'false' }}"
                class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-[11px] font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0097B2] {{ $showInternal ? 'border-[#B6DCE8] bg-[#E3F1F7] text-[#0B4A66]' : 'border-[#E2E8EC] bg-white text-[#4B5563] hover:bg-slate-50' }}">
                <span class="h-1.5 w-1.5 rounded-full bg-[#0097B2]"></span>
                Event Internal
            </button>

            @if ($hasActiveFilters)
                <button type="button" wire:click="resetFilters"
                    class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-[11px] font-medium text-[#4B5563] transition hover:text-[#172B36] hover:underline hover:underline-offset-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0097B2]">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"></path>
                    </svg>
                    Reset filter
                </button>
            @endif
        </div>
    </div>

    <div class="mt-4 flex flex-col gap-4 min-[1100px]:flex-row min-[1100px]:items-start">
        <div class="min-w-0 flex-1">
            <div class="overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">
                @if ($loadError)
                    <div class="flex flex-col items-center justify-center gap-3 px-6 py-16 text-center">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"></path>
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-[#172B36]">Gagal memuat event</p>
                            <p class="mt-1 text-xs text-[#4B5563]">Periksa koneksi lalu coba lagi.</p>
                        </div>
                        <button type="button" wire:click="retry"
                            class="inline-flex h-9 items-center rounded-lg bg-[#0097B2] px-4 text-sm font-semibold text-white hover:bg-[#0086A0]">
                            Coba lagi
                        </button>
                    </div>
                @else
                    <div wire:loading.delay wire:target="previous,next,goToday,setView,division,toggleHolidays,toggleInternal,resetFilters"
                        class="p-4">
                        <div class="grid grid-cols-7 gap-px overflow-hidden rounded-lg bg-[#E2E8EC]">
                            @for ($i = 0; $i < 42; $i++)
                                <div class="h-28 bg-white p-2">
                                    <div class="ml-auto h-4 w-6 rounded bg-slate-100"></div>
                                    <div class="mt-3 h-4 w-full rounded bg-slate-100"></div>
                                    <div class="mt-2 h-4 w-2/3 rounded bg-slate-100"></div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <div wire:loading.remove wire:target="previous,next,goToday,setView,division,toggleHolidays,toggleInternal,resetFilters">
                        @if ($isEmpty && $view === 'daftar')
                            <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
                                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#E3F1F7] text-[#146C94]">
                                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z"></path>
                                    </svg>
                                </span>
                                <p class="mt-4 text-sm font-semibold text-[#172B36]">Belum ada event bulan ini</p>
                                <p class="mt-1 text-xs text-[#4B5563]">
                                    {{ $hasActiveFilters ? 'Coba ubah filter atau tambahkan event baru.' : 'Tambahkan event pertama untuk '.$monthLabel.'.' }}
                                </p>
                                <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                                    <button type="button" id="content-add-event-empty" wire:click="openCreate()" @click="remember($el)"
                                        class="inline-flex h-10 items-center gap-2 rounded-lg bg-[#0097B2] px-4 text-sm font-semibold text-white hover:bg-[#0086A0]">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"></path>
                                        </svg>
                                        Tambah event
                                    </button>
                                    @if ($hasActiveFilters)
                                        <button type="button" wire:click="resetFilters"
                                            class="inline-flex h-10 items-center rounded-lg border border-[#E2E8EC] bg-white px-4 text-sm font-medium text-[#4B5563] hover:bg-slate-50">
                                            Reset filter
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @elseif ($view === 'bulan')
                            <div class="overflow-x-auto">
                                <div class="min-w-[560px]">
                                    <div class="grid grid-cols-7 border-b border-[#E2E8EC] bg-[#EAF4F8]">
                                        @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $weekday)
                                            <div class="flex h-14 items-center justify-center text-xs font-medium text-[#4B5563]">{{ $weekday }}</div>
                                        @endforeach
                                    </div>

                                    <div class="grid grid-cols-7">
                                        @foreach ($monthWeeks as $week)
                                            @foreach ($week as $day)
                                                @php
                                                    $date = $day['date'];
                                                    $items = $day['items'];
                                                    $maxPills = 3;
                                                    $isSelectedCreate = $formOpen && ! $editingId && $start_date === $date;
                                                    $numberClass = $day['is_today']
                                                        ? 'bg-[#146C94] text-white'
                                                        : ($day['is_other_month'] ? 'text-slate-400' : 'text-[#172B36]');
                                                @endphp
                                                <div wire:key="month-day-{{ $date }}"
                                                    class="relative min-h-[148px] border-b border-r border-[#E2E8EC] p-1.5 transition-colors {{ $isSelectedCreate ? 'ring-2 ring-inset ring-[#0097B2]' : '' }}"
                                                    :class="dragOver === '{{ $date }}' ? 'bg-[#E3F1F7]/60 outline outline-2 -outline-offset-2 outline-dashed outline-[#0097B2]' : ''"
                                                    @dragover.prevent="dragOver = '{{ $date }}'"
                                                    @dragleave="if (dragOver === '{{ $date }}') dragOver = null"
                                                    @drop.prevent="if (draggingId) { const id = draggingId; draggingId = null; dragOver = null; $wire.moveEvent(id, '{{ $date }}'); }">
                                                    <button type="button" id="content-day-{{ $date }}"
                                                        @click="remember($el); $wire.openCreate('{{ $date }}')"
                                                        aria-label="Tambah event pada {{ $day['label'] }}"
                                                        class="absolute inset-0 z-0 rounded-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#0097B2]"></button>

                                                    <div class="pointer-events-none relative z-10 flex min-h-[128px] flex-col">
                                                        <div class="flex justify-end">
                                                            <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full px-1 text-[14px] font-medium {{ $numberClass }}">{{ $day['day'] }}</span>
                                                        </div>

                                                        <div class="mt-1 space-y-1" :class="draggingId ? 'pointer-events-none' : 'pointer-events-auto'">
                                                            @foreach (array_slice($items, 0, $maxPills) as $item)
                                                                @if ($item['type'] === 'holiday')
                                                                    <div wire:key="month-holiday-{{ $item['id'] }}-{{ $date }}"
                                                                        class="flex h-5 items-center gap-1 overflow-hidden rounded-lg border px-1.5 text-[11px] font-medium"
                                                                        style="background-color: {{ $item['colors']['fill'] }}; border-color: {{ $item['colors']['border'] }}; color: {{ $item['colors']['text'] }};"
                                                                        title="{{ $item['title'] }}">
                                                                        <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18M3 4h12l-2 4 2 4H3"></path>
                                                                        </svg>
                                                                        <span class="truncate">{{ $item['title'] }}</span>
                                                                    </div>
                                                                @else
                                                                    @php
                                                                        $tl = $item['is_start'] ? 8 : 0;
                                                                        $bl = $item['is_start'] ? 8 : 0;
                                                                        $tr = $item['is_end'] ? 8 : 0;
                                                                        $br = $item['is_end'] ? 8 : 0;
                                                                        $continues = ! $item['is_start'] || ! $item['is_end'];
                                                                    @endphp
                                                                    <button type="button" wire:key="month-pill-{{ $item['id'] }}-{{ $date }}"
                                                                        id="content-event-{{ $item['id'] }}-{{ $date }}"
                                                                        draggable="{{ $item['can_edit'] ? 'true' : 'false' }}"
                                                                        @click.stop="remember($el); $wire.openEvent({{ $item['id'] }})"
                                                                        @dragstart="draggingId = {{ $item['id'] }}; $event.dataTransfer.effectAllowed = 'move'; $event.dataTransfer.setData('text/plain', '{{ $item['id'] }}')"
                                                                        @dragend="draggingId = null; dragOver = null"
                                                                        class="flex h-5 w-full items-center gap-1 overflow-hidden border px-1.5 text-left text-[11px] text-[#172B36] transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0097B2] {{ $item['can_edit'] ? 'cursor-grab active:cursor-grabbing' : 'cursor-pointer' }} {{ $continues ? 'opacity-80' : '' }}"
                                                                        style="background-color: {{ $item['colors']['fill'] }}; border-color: {{ $item['colors']['border'] }}; border-radius: {{ $tl }}px {{ $tr }}px {{ $br }}px {{ $bl }}px;"
                                                                        :class="draggingId === {{ $item['id'] }} ? 'rotate-1 shadow-lg opacity-60' : ''"
                                                                        aria-label="Buka event {{ $item['title'] }}, {{ $item['date_label'] }}@if (! empty($item['time_label'])) pukul {{ $item['time_label'] }}@endif">
                                                                        @if (! empty($item['time_label_short']))
                                                                            <span class="shrink-0 font-semibold">{{ $item['time_label_short'] }}</span>
                                                                        @endif
                                                                        <span class="truncate">{{ $item['title'] }}</span>
                                                                    </button>
                                                                @endif
                                                            @endforeach

                                                            @if (count($items) > $maxPills)
                                                                <button type="button" wire:click="openDay('{{ $date }}')"
                                                                    class="text-[11px] font-semibold text-[#146C94] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0097B2]">
                                                                    +{{ count($items) - $maxPills }} lainnya
                                                                </button>
                                                            @endif

                                                            <template x-if="draggingId && dragOver === '{{ $date }}'">
                                                                <div class="h-5 rounded-lg border border-dashed border-[#0097B2] bg-[#E3F1F7]/70"></div>
                                                            </template>

                                                            @if ($isSelectedCreate)
                                                                <div class="flex h-5 items-center justify-center rounded-lg border border-dashed border-[#0097B2] bg-[#E3F1F7]/50 text-[11px] font-medium text-[#0B4A66]">
                                                                    + Event baru
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @elseif ($view === 'minggu')
                            <div class="overflow-x-auto">
                                <div class="grid min-w-[560px] grid-cols-7">
                                    @foreach ($weekDays as $day)
                                        @php
                                            $date = $day['date'];
                                            $isSelectedCreate = $formOpen && ! $editingId && $start_date === $date;
                                        @endphp
                                        <div wire:key="week-day-{{ $date }}"
                                            class="relative border-r border-[#E2E8EC] last:border-r-0 transition-colors {{ $isSelectedCreate ? 'ring-2 ring-inset ring-[#0097B2]' : '' }}"
                                            :class="dragOver === '{{ $date }}' ? 'bg-[#E3F1F7]/60 outline outline-2 -outline-offset-2 outline-dashed outline-[#0097B2]' : ''"
                                            @dragover.prevent="dragOver = '{{ $date }}'"
                                            @dragleave="if (dragOver === '{{ $date }}') dragOver = null"
                                            @drop.prevent="if (draggingId) { const id = draggingId; draggingId = null; dragOver = null; $wire.moveEvent(id, '{{ $date }}'); }">
                                            <button type="button" id="content-day-{{ $date }}"
                                                @click="remember($el); $wire.openCreate('{{ $date }}')"
                                                aria-label="Tambah event pada {{ $day['label'] }}"
                                                class="flex w-full flex-col items-center gap-1 border-b border-[#E2E8EC] bg-[#EAF4F8] px-2 py-3 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#0097B2]">
                                                <span class="text-xs font-medium text-[#4B5563]">{{ \Carbon\Carbon::parse($date)->locale('id')->translatedFormat('D') }}</span>
                                                <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full px-1 text-[14px] font-medium {{ $day['is_today'] ? 'bg-[#146C94] text-white' : ($day['is_other_month'] ? 'text-slate-400' : 'text-[#172B36]') }}">{{ $day['day'] }}</span>
                                            </button>

                                            <div class="min-h-[420px] space-y-1.5 p-2" :class="draggingId ? 'pointer-events-none' : 'pointer-events-auto'">
                                                @forelse ($day['items'] as $item)
                                                    @if ($item['type'] === 'holiday')
                                                        <div wire:key="week-holiday-{{ $item['id'] }}-{{ $date }}"
                                                            class="flex h-5 items-center gap-1 overflow-hidden rounded-lg border px-1.5 text-[11px] font-medium"
                                                            style="background-color: {{ $item['colors']['fill'] }}; border-color: {{ $item['colors']['border'] }}; color: {{ $item['colors']['text'] }};"
                                                            title="{{ $item['title'] }}">
                                                            <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18M3 4h12l-2 4 2 4H3"></path>
                                                            </svg>
                                                            <span class="truncate">{{ $item['title'] }}</span>
                                                        </div>
                                                    @else
                                                        @php
                                                            $tl = $item['is_start'] ? 8 : 0;
                                                            $bl = $item['is_start'] ? 8 : 0;
                                                            $tr = $item['is_end'] ? 8 : 0;
                                                            $br = $item['is_end'] ? 8 : 0;
                                                            $continues = ! $item['is_start'] || ! $item['is_end'];
                                                        @endphp
                                                        <button type="button" wire:key="week-pill-{{ $item['id'] }}-{{ $date }}"
                                                            id="content-event-{{ $item['id'] }}-{{ $date }}"
                                                            draggable="{{ $item['can_edit'] ? 'true' : 'false' }}"
                                                            @click.stop="remember($el); $wire.openEvent({{ $item['id'] }})"
                                                            @dragstart="draggingId = {{ $item['id'] }}; $event.dataTransfer.effectAllowed = 'move'; $event.dataTransfer.setData('text/plain', '{{ $item['id'] }}')"
                                                            @dragend="draggingId = null; dragOver = null"
                                                            class="flex h-5 w-full items-center gap-1 overflow-hidden border px-1.5 text-left text-[11px] text-[#172B36] transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0097B2] {{ $item['can_edit'] ? 'cursor-grab active:cursor-grabbing' : 'cursor-pointer' }} {{ $continues ? 'opacity-80' : '' }}"
                                                            style="background-color: {{ $item['colors']['fill'] }}; border-color: {{ $item['colors']['border'] }}; border-radius: {{ $tl }}px {{ $tr }}px {{ $br }}px {{ $bl }}px;"
                                                            :class="draggingId === {{ $item['id'] }} ? 'rotate-1 shadow-lg opacity-60' : ''"
                                                            aria-label="Buka event {{ $item['title'] }}, {{ $item['date_label'] }}@if (! empty($item['time_label'])) pukul {{ $item['time_label'] }}@endif">
                                                            @if (! empty($item['time_label']))
                                                                <span class="shrink-0 font-semibold">{{ $item['time_label'] }}</span>
                                                            @endif
                                                            <span class="truncate">{{ $item['title'] }}</span>
                                                        </button>
                                                    @endif
                                                @empty
                                                    <p class="px-1 pt-2 text-center text-[11px] text-[#94A3B8]">Tidak ada event</p>
                                                @endforelse

                                                <template x-if="draggingId && dragOver === '{{ $date }}'">
                                                    <div class="h-5 rounded-lg border border-dashed border-[#0097B2] bg-[#E3F1F7]/70"></div>
                                                </template>

                                                @if ($isSelectedCreate)
                                                    <div class="flex h-5 items-center justify-center rounded-lg border border-dashed border-[#0097B2] bg-[#E3F1F7]/50 text-[11px] font-medium text-[#0B4A66]">
                                                        + Event baru
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="divide-y divide-[#E2E8EC]">
                                @foreach ($listGroups as $group)
                                    <div wire:key="list-group-{{ $group['date'] }}">
                                        <div class="bg-[#F8FAFC] px-4 py-2 text-xs font-semibold uppercase tracking-wide text-[#4B5563]">
                                            {{ $group['label'] }}
                                        </div>
                                        @foreach ($group['items'] as $item)
                                            @if ($item['type'] === 'holiday')
                                                <div wire:key="list-holiday-{{ $item['id'] }}"
                                                    class="flex items-center gap-3 px-4 py-3"
                                                    style="background-color: {{ $item['colors']['fill'] }};">
                                                    <span class="flex h-5 w-5 items-center justify-center" style="color: {{ $item['colors']['text'] }};">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18M3 4h12l-2 4 2 4H3"></path>
                                                        </svg>
                                                    </span>
                                                    <span class="min-w-0 flex-1 truncate text-sm font-medium" style="color: {{ $item['colors']['text'] }};">{{ $item['title'] }}</span>
                                                    <span class="text-[11px] font-medium" style="color: {{ $item['colors']['text'] }};">Hari Libur</span>
                                                </div>
                                            @else
                                                <button type="button" wire:key="list-event-{{ $item['id'] }}"
                                                    id="content-event-list-{{ $item['id'] }}"
                                                    @click="remember($el); $wire.openEvent({{ $item['id'] }})"
                                                    class="flex w-full items-center gap-3 px-4 py-3 text-left transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#0097B2]">
                                                    <span class="h-2 w-2 shrink-0 rounded-full" style="background-color: {{ $item['colors']['dot'] }};"></span>
                                                    <span class="min-w-0 flex-1">
                                                        <span class="block truncate text-sm font-medium text-[#172B36]">{{ $item['title'] }}</span>
                                                        <span class="mt-0.5 block text-[11px] text-[#4B5563]">{{ $item['date_label'] }}@if (! empty($item['time_label'])) · {{ $item['time_label'] }}@endif · {{ $item['division_name'] }}</span>
                                                    </span>
                                                    <x-status-badge variant="content" dot size="md" :status="$item['status']" />
                                                </button>
                                            @endif
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

            </div>
        </div>
        {{-- Drawer --}}
        @if ($drawerOpen)
            @php
                $drawerColors = $division_id
                    ? ($divisionColors[$division_id] ?? \App\Support\DivisionPalette::UMUM)
                    : \App\Support\DivisionPalette::UMUM;
                $selectedColor = \App\Support\DivisionPalette::event($color)
                    ?? \App\Support\DivisionPalette::for($division_id, $divisionColors);
            @endphp
            {{-- Read-only peek --}}
            <aside data-content-drawer role="dialog" aria-modal="true" aria-labelledby="content-drawer-title" tabindex="-1"
                class="fixed inset-0 z-50 flex flex-col overflow-hidden bg-white shadow-2xl min-[1100px]:sticky min-[1100px]:top-4 min-[1100px]:z-auto min-[1100px]:max-h-[calc(100vh-8rem)] min-[1100px]:w-[440px] min-[1100px]:shrink-0 min-[1100px]:self-start min-[1100px]:rounded-xl min-[1100px]:border min-[1100px]:border-[#E2E8EC] min-[1100px]:shadow-sm">
                <div class="flex items-center justify-end gap-3 border-b border-[#E2E8EC] px-5 py-4">
                    <div class="flex items-center gap-3">
                        <button type="button" wire:click="closeDrawer" aria-label="Tutup panel event"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-[#64748B] transition hover:bg-slate-100 hover:text-[#172B36] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0097B2]">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto px-5 py-4">
                    <div class="flex items-start gap-3">
                        <span class="mt-1 h-[22px] w-[22px] shrink-0 rounded-md border" aria-hidden="true"
                            style="background-color: {{ $selectedColor['fill'] }}; border-color: {{ $selectedColor['border'] }};"></span>
                        <h2 id="content-drawer-title" class="min-w-0 flex-1 text-[22px] font-semibold leading-snug text-[#172B36]">{{ $title }}</h2>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <x-status-badge variant="content" dot size="md" :status="$formStatus" />
                    </div>

                    <dl class="mt-5 space-y-4">
                        <div>
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-[#64748B]">Tanggal</dt>
                            <dd class="mt-1 text-sm font-medium text-[#172B36]">
                                {{ $drawerDateLabel }}@if ($drawerTimeLabel)
                                    <span class="font-normal text-[#4B5563]">· {{ $drawerTimeLabel }}</span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-[#64748B]">Divisi</dt>
                            <dd class="mt-1 flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full" style="background-color: {{ $drawerColors['dot'] }};"></span>
                                <span class="text-sm font-medium text-[#172B36]">{{ $selectedDivisionName }}</span>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-[#64748B]">PIC</dt>
                            <dd class="mt-1">
                                @if ($selectedPicName)
                                    <span class="flex items-center gap-2">
                                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#E3F1F7] text-[10px] font-semibold text-[#146C94]">{{ $selectedPicInitials }}</span>
                                        <span class="text-sm font-medium text-[#172B36]">{{ $selectedPicName }}</span>
                                    </span>
                                @else
                                    <span class="text-sm text-[#4B5563]">Belum ditentukan</span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-[#64748B]">Deskripsi</dt>
                            <dd class="mt-1 whitespace-pre-line text-sm leading-relaxed text-[#172B36]">{{ $description !== '' ? $description : 'Belum ada deskripsi.' }}</dd>
                        </div>
                    </dl>

                    @if ($creatorName)
                        <p class="mt-5 text-xs text-[#4B5563]">
                            Dibuat oleh {{ $creatorName }}@if ($createdHuman) • {{ $createdHuman }}@endif
                        </p>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-[#E2E8EC] px-5 py-4">
                    <button type="button" wire:click="closeDrawer"
                        class="inline-flex h-10 items-center rounded-lg border border-[#E2E8EC] bg-white px-4 text-sm font-medium text-[#4B5563] transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0097B2]">
                        Tutup
                    </button>
                    @if (! $readOnly)
                        <button type="button" wire:click="editEvent"
                            class="inline-flex h-10 items-center gap-2 rounded-lg bg-[#0097B2] px-4 text-sm font-semibold text-white transition hover:bg-[#0086A0] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0097B2] focus-visible:ring-offset-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12v7.125A1.875 1.875 0 0 1 17.625 21H5.625A1.875 1.875 0 0 1 3.75 19.125V7.125A1.875 1.875 0 0 1 5.625 5.25H12"></path>
                            </svg>
                            Edit
                        </button>
                    @endif
                </div>
            </aside>
        @endif

        {{-- Create/edit dialog --}}
        @if ($formOpen)
            <div class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-4"
                role="dialog" aria-modal="true" aria-labelledby="content-form-heading">
                <div class="absolute inset-0 bg-[#172B36]/40" @click="! $wire.formDirty && $wire.closeForm()"></div>

                <div data-content-form tabindex="-1"
                    class="relative flex h-[100dvh] w-full flex-col overflow-hidden bg-white shadow-2xl sm:h-auto sm:max-h-[calc(100vh-2rem)] sm:max-w-[600px] sm:rounded-xl sm:border sm:border-[#E2E8EC]">
                    <div class="flex items-center justify-between gap-3 border-b border-[#E2E8EC] px-5 py-4">
                        <div class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#E3F1F7] text-[#0B7C97]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"></path>
                                </svg>
                            </span>
                            <h2 id="content-form-heading" class="text-sm font-semibold text-[#172B36]">
                                {{ $editingId ? 'Edit event' : 'Event baru' }}
                            </h2>
                        </div>

                        <div class="flex items-center gap-3">
                            <button type="button" wire:click="closeForm" aria-label="Tutup formulir event"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-[#64748B] transition hover:bg-slate-100 hover:text-[#172B36] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0097B2]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto px-5 py-4">
                        <label for="content-event-title" class="sr-only">Judul event</label>
                        <input id="content-event-title" x-ref="titleInput" type="text"
                            wire:model.live.debounce.300ms="title" placeholder="Judul event" maxlength="150"
                            class="w-full border-0 border-b border-transparent bg-transparent px-0 py-1 text-[22px] font-semibold leading-snug text-[#172B36] placeholder:text-[#94A3B8] focus:border-[#0097B2] focus:outline-none focus:ring-0">
                        @error('title')
                            <p class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>
                        @enderror

                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="content-start-date" class="text-[11px] font-medium uppercase tracking-wide text-[#64748B]">Mulai</label>
                                <input id="content-start-date" type="date" wire:model.live="start_date"
                                    class="mt-1 h-10 w-full rounded-lg border border-[#E2E8EC] bg-white px-2.5 text-sm text-[#172B36] focus:border-[#0097B2] focus:outline-none focus:ring-2 focus:ring-[#0097B2]/20">
                                @error('start_date')
                                    <p class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <span class="text-[11px] font-medium uppercase tracking-wide text-[#64748B]">Jam mulai</span>
                                <div class="mt-1">
                                    <x-time-picker id="content-start-time" label="Jam mulai"
                                        model="start_time" :value="$start_time" :options="$timeOptions" />
                                </div>
                                @error('start_time')
                                    <p class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="content-end-date" class="text-[11px] font-medium uppercase tracking-wide text-[#64748B]">Selesai (opsional)</label>
                                <input id="content-end-date" type="date" wire:model.live="end_date"
                                    class="mt-1 h-10 w-full rounded-lg border border-[#E2E8EC] bg-white px-2.5 text-sm text-[#172B36] focus:border-[#0097B2] focus:outline-none focus:ring-2 focus:ring-[#0097B2]/20">
                                @error('end_date')
                                    <p class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <span class="text-[11px] font-medium uppercase tracking-wide text-[#64748B]">Jam selesai</span>
                                <div class="mt-1">
                                    <x-time-picker id="content-end-time" label="Jam selesai"
                                        model="end_time" :value="$end_time" :options="$timeOptions" />
                                </div>
                                @error('end_time')
                                    <p class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="content-status" class="text-[11px] font-medium uppercase tracking-wide text-[#64748B]">Status</label>
                                <select id="content-status" wire:model.live="formStatus"
                                    class="mt-1 h-10 w-full rounded-lg border border-[#E2E8EC] bg-white px-2.5 text-sm text-[#172B36] focus:border-[#0097B2] focus:outline-none focus:ring-2 focus:ring-[#0097B2]/20">
                                    @foreach ($statusOptions as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('formStatus')
                                    <p class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="content-division" class="text-[11px] font-medium uppercase tracking-wide text-[#64748B]">Divisi</label>
                                <select id="content-division" wire:model.live="division_id"
                                    class="mt-1 h-10 w-full rounded-lg border border-[#E2E8EC] bg-white px-2.5 text-sm text-[#172B36] focus:border-[#0097B2] focus:outline-none focus:ring-2 focus:ring-[#0097B2]/20">
                                    <option value="">Umum</option>
                                    @foreach ($divisionOptions as $divisionOption)
                                        <option value="{{ $divisionOption->id_divisi }}">{{ $divisionOption->nama_divisi }}</option>
                                    @endforeach
                                </select>
                                @error('division_id')
                                    <p class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-5">
                            <label for="content-pic" class="text-[11px] font-medium uppercase tracking-wide text-[#64748B]">PIC</label>
                            <select id="content-pic" wire:model.live="pic_user_id"
                                class="mt-1 h-10 w-full rounded-lg border border-[#E2E8EC] bg-white px-2.5 text-sm text-[#172B36] focus:border-[#0097B2] focus:outline-none focus:ring-2 focus:ring-[#0097B2]/20">
                                <option value="">Pilih penanggung jawab</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id_user }}">{{ $user->karyawan?->nama ?? $user->username }}</option>
                                @endforeach
                            </select>
                            @error('pic_user_id')
                                <p class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-5">
                            <p class="text-[11px] font-medium uppercase tracking-wide text-[#64748B]">Warna event</p>
                            <div class="mt-2 flex flex-wrap items-center gap-2" role="radiogroup" aria-label="Warna event">
                                @foreach ($eventColors as $key => $palette)
                                    <button type="button" role="radio" aria-checked="{{ $color === $key ? 'true' : 'false' }}"
                                        wire:click="$set('color', '{{ $key }}')"
                                        aria-label="{{ $palette['label'] }}" title="{{ $palette['label'] }}"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-full border-2 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0097B2] focus-visible:ring-offset-2 {{ $color === $key ? 'ring-2 ring-[#0097B2] ring-offset-2' : '' }}"
                                        style="background-color: {{ $palette['fill'] }}; border-color: {{ $palette['border'] }};">
                                        @if ($color === $key)
                                            <svg class="h-4 w-4" style="color: {{ $palette['dot'] }};" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m5 12 4.5 4.5L19 7"></path>
                                            </svg>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                            <p class="mt-1.5 text-[11px] text-[#64748B]">{{ $selectedColorLabel ? 'Warna '.$selectedColorLabel : 'Mengikuti warna divisi' }}</p>
                            @error('color')
                                <p class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-5">
                            <label for="content-description" class="text-[11px] font-medium uppercase tracking-wide text-[#64748B]">Deskripsi</label>
                            <textarea id="content-description" wire:model.live.debounce.300ms="description" rows="4"
                                placeholder="Tambahkan tujuan, materi, atau catatan koordinasi..."
                                class="mt-1 w-full rounded-lg border border-[#E2E8EC] bg-white p-3 text-sm leading-relaxed text-[#172B36] placeholder:text-[#94A3B8] focus:border-[#0097B2] focus:outline-none focus:ring-2 focus:ring-[#0097B2]/20"></textarea>
                            @error('description')
                                <p class="mt-1 text-xs text-[#B91C1C]">{{ $message }}</p>
                            @enderror
                        </div>

                        @if (! $editingId)
                            <div class="mt-5 flex items-start gap-2 rounded-lg bg-[#E3F1F7] px-3 py-2.5 text-[13px] leading-relaxed text-[#0B4A66]">
                                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
                                </svg>
                                <p>Event ini akan terlihat oleh semua karyawan.</p>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-between gap-3 border-t border-[#E2E8EC] px-5 py-4">
                        <div>
                            @if ($editingId)
                                <button type="button" x-ref="deleteTrigger" @click="confirmOpen = true"
                                    class="text-sm font-medium text-[#B91C1C] transition hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#B91C1C]">
                                    Hapus
                                </button>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="closeForm"
                                class="inline-flex h-10 items-center rounded-lg border border-[#E2E8EC] bg-white px-4 text-sm font-medium text-[#4B5563] transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0097B2]">
                                Batal
                            </button>
                            <button type="button" wire:click="save"
                                class="inline-flex h-10 items-center rounded-lg bg-[#0097B2] px-4 text-sm font-semibold text-white transition hover:bg-[#0086A0] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0097B2] focus-visible:ring-offset-2">
                                {{ $editingId ? 'Simpan' : 'Tambah event' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Day popover --}}
    @if ($popoverDate)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/20 p-4" @click.self="$wire.closePopover()">
            <div class="w-full max-w-sm overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-xl"
                role="dialog" aria-modal="true" aria-labelledby="content-popover-title">
                <div class="flex items-center justify-between gap-3 border-b border-[#E2E8EC] px-4 py-3">
                    <h3 id="content-popover-title" class="text-sm font-semibold text-[#172B36]">
                        {{ \Carbon\Carbon::parse($popoverDate)->locale('id')->translatedFormat('l, j F Y') }}
                    </h3>
                    <button type="button" wire:click="closePopover" aria-label="Tutup daftar event"
                        class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-[#64748B] hover:bg-slate-100 hover:text-[#172B36]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="max-h-80 divide-y divide-[#E2E8EC] overflow-y-auto">
                    @forelse ($popoverItems as $item)
                        @if ($item['type'] === 'holiday')
                            <div class="flex items-center gap-2 px-4 py-3" style="background-color: {{ $item['colors']['fill'] }};">
                                <svg class="h-4 w-4 shrink-0" style="color: {{ $item['colors']['text'] }};" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18M3 4h12l-2 4 2 4H3"></path>
                                </svg>
                                <span class="min-w-0 flex-1 truncate text-sm font-medium" style="color: {{ $item['colors']['text'] }};">{{ $item['title'] }}</span>
                            </div>
                        @else
                            <button type="button" wire:click="openEvent({{ $item['id'] }})"
                                class="flex w-full items-center gap-2 px-4 py-3 text-left hover:bg-slate-50">
                                <span class="h-2 w-2 shrink-0 rounded-full" style="background-color: {{ $item['colors']['dot'] }};"></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-medium text-[#172B36]">{{ $item['title'] }}</span>
                                    @if (! empty($item['time_label']))
                                        <span class="mt-0.5 block text-[11px] text-[#4B5563]">{{ $item['time_label'] }}</span>
                                    @endif
                                </span>
                                <span class="shrink-0 text-[11px] text-[#4B5563]">{{ $item['division_name'] }}</span>
                            </button>
                        @endif
                    @empty
                        <p class="px-4 py-6 text-center text-sm text-[#4B5563]">Tidak ada event pada tanggal ini.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    {{-- Delete confirmation --}}
    @if ($formOpen && $editingId)
    <div x-show="confirmOpen" x-cloak
        class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/40 p-4 backdrop-blur-sm"
        @click.self="closeConfirm()">
        <div class="w-full max-w-md rounded-2xl border border-[#E2E8EC] bg-white p-6 shadow-xl"
            role="dialog" aria-modal="true" aria-labelledby="hapus-event-title"
            x-init="$watch('confirmOpen', (value) => { if (value) $nextTick(() => $refs.confirmCancel?.focus()) })">
            <h3 id="hapus-event-title" class="text-lg font-semibold text-[#172B36]">Hapus event?</h3>
            <p class="mt-2 text-sm leading-relaxed text-[#4B5563]">
                Event <span class="font-semibold text-[#172B36]">{{ $title }}</span> akan dihapus permanen.
                Tindakan ini tidak dapat dibatalkan.
            </p>
            <div class="mt-6 flex items-center justify-end gap-3">
                <button type="button" x-ref="confirmCancel" @click="closeConfirm()"
                    class="inline-flex h-10 items-center rounded-lg border border-[#E2E8EC] bg-white px-4 text-sm font-medium text-[#4B5563] transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0097B2]">
                    Batal
                </button>
                <button type="button" @click="confirmOpen = false; $wire.deleteEvent()"
                    class="inline-flex h-10 items-center rounded-lg bg-[#B91C1C] px-4 text-sm font-semibold text-white transition hover:bg-[#991B1B] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#B91C1C] focus-visible:ring-offset-2">
                    Hapus
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
