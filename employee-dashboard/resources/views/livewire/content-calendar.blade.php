<div class="relative w-full"
    x-data="{
        returnFocus: null,
        draggingId: null,
        dragOver: null,
        confirmOpen: false,
        closeConfirm: false,
        copied: false,
        copyTimer: null,
        saved: false,
        savedTimer: null,
        dirty: false,
        toasts: [],
        toastSeq: 0,
        remember(el) {
            this.returnFocus = el ? el.id : null;
        },
        restoreFocus() {
            const id = this.returnFocus;
            this.returnFocus = null;
            this.dirty = false;
            this.$nextTick(() => {
                if (id) document.getElementById(id)?.focus();
            });
        },
        modalOpen() {
            return !!document.querySelector('[data-content-modal]');
        },
        requestClose() {
            if (!this.modalOpen()) return;
            if (this.$wire.get('formOpen') && (this.$wire.get('formDirty') || this.dirty)) {
                this.closeConfirm = true;
                this.$nextTick(() => this.$refs.closeCancel?.focus());
                return;
            }
            this.$wire.closeModal(true);
        },
        confirmDiscard() {
            this.closeConfirm = false;
            this.$wire.closeModal(true);
        },
        openDelete() {
            this.confirmOpen = true;
            this.$nextTick(() => this.$refs.deleteCancel?.focus());
        },
        closeDelete() {
            this.confirmOpen = false;
        },
        trap(event) {
            const panel = document.querySelector('[data-content-modal] [data-modal-panel]');
            if (!panel) return;
            const focusable = [...panel.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex=\'-1\'])')]
                .filter((el) => el.offsetParent !== null);
            if (!focusable.length) return;
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        },
        copyText(value) {
            if (!value) return;
            const done = () => {
                this.copied = true;
                clearTimeout(this.copyTimer);
                this.copyTimer = setTimeout(() => this.copied = false, 2000);
            };
            const fallback = () => {
                try {
                    const temp = document.createElement('textarea');
                    temp.value = value;
                    temp.style.position = 'fixed';
                    temp.style.opacity = '0';
                    document.body.appendChild(temp);
                    temp.select();
                    document.execCommand('copy');
                    document.body.removeChild(temp);
                } catch (error) {
                    // Clipboard permissions can be denied; still give feedback.
                }
                done();
            };
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(value).then(done).catch(fallback);
            } else {
                fallback();
            }
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
        }
    }"
    @content-toast.window="pushToast($event.detail)"
    @content-saved.window="saved = true; clearTimeout(savedTimer); savedTimer = setTimeout(() => saved = false, 2000)"
    @content-modal-closed.window="restoreFocus()"
    @content-focus-title.window="dirty = false; $nextTick(() => $refs.titleInput?.focus())"
    @content-focus-modal.window="dirty = false; $nextTick(() => document.querySelector('[data-modal-panel]')?.focus())"
    @keydown.escape.window="
        if (closeConfirm) { closeConfirm = false; }
        else if (confirmOpen) { closeDelete(); }
        else if ($wire.get('popoverDate')) { $wire.closePopover(); }
        else if (modalOpen()) { requestClose(); }">

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

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-2">
        <div class="flex min-w-0 flex-wrap items-center gap-3">
            <div class="inline-flex items-stretch overflow-hidden rounded-lg border border-[#E2E8EC] bg-white">
                <button type="button" wire:click="previous" aria-label="Periode sebelumnya"
                    class="inline-flex h-10 w-10 items-center justify-center border-r border-[#E2E8EC] text-[#4B5563] transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#146C94]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"></path>
                    </svg>
                </button>
                <button type="button" wire:click="next" aria-label="Periode berikutnya"
                    class="inline-flex h-10 w-10 items-center justify-center border-r border-[#E2E8EC] text-[#4B5563] transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#146C94]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"></path>
                    </svg>
                </button>
                <button type="button" wire:click="goToday"
                    class="inline-flex h-10 items-center px-3 text-sm font-medium text-[#4B5563] transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#146C94]">
                    Hari ini
                </button>
            </div>
            <h2 class="truncate text-xl font-semibold text-[#172B36]">{{ $monthLabel }}</h2>
        </div>

        <div class="ml-auto flex flex-wrap items-center gap-2">
            <select wire:model.live="typeFilter" aria-label="Filter jenis konten"
                class="h-10 w-[125px] rounded-lg border border-[#E2E8EC] bg-white px-2 text-sm text-[#172B36] focus:border-[#146C94] focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                <option value="semua">Semua jenis</option>
                @foreach ($contentTypes as $type)
                    <option value="{{ $type['slug'] }}">{{ $type['name'] }}</option>
                @endforeach
            </select>

            <select wire:model.live="statusFilter" aria-label="Filter status"
                class="h-10 w-[142px] rounded-lg border border-[#E2E8EC] bg-white px-2 text-sm text-[#172B36] focus:border-[#146C94] focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                <option value="semua">Semua status</option>
                @foreach ($statusOptions as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>

            @if ($hasActiveFilters)
                <button type="button" wire:click="resetFilters"
                    class="rounded-md px-1.5 py-1 text-[12px] font-semibold text-[#146C94] transition hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                    Reset
                </button>
            @endif

            <div class="inline-flex rounded-lg border border-[#E2E8EC] bg-white p-0.5" role="group" aria-label="Tampilan kalender">
                @foreach (['bulan' => 'Bulan', 'minggu' => 'Minggu', 'daftar' => 'Daftar'] as $key => $label)
                    <button type="button" wire:click="setView('{{ $key }}')"
                        aria-pressed="{{ $view === $key ? 'true' : 'false' }}"
                        class="inline-flex h-9 items-center rounded-md px-2 text-[13px] font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] {{ $view === $key ? 'bg-[#146C94] text-white' : 'text-[#4B5563] hover:bg-slate-50' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <button type="button" id="content-add-event" wire:click="openCreate()" @click="remember($el)"
                class="inline-flex h-10 items-center gap-1.5 rounded-lg bg-[#0097B2] px-3 text-[13px] font-semibold text-white transition hover:bg-[#0086A0] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] focus-visible:ring-offset-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"></path>
                </svg>
                Tambah event
            </button>
        </div>
    </div>

    {{-- Calendar card --}}
    <div class="relative mt-4 overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">

        <div wire:loading.delay
            wire:target="previous,next,goToday,setView,typeFilter,statusFilter,resetFilters"
            class="absolute inset-0 z-30 flex flex-col bg-white p-3">
            <div class="grid flex-1 grid-cols-7 gap-2">
                @for ($i = 0; $i < 35; $i++)
                    <div class="min-h-[80px] animate-pulse rounded-lg bg-slate-100"></div>
                @endfor
            </div>
        </div>

        @if ($loadError)
            <div class="flex flex-1 flex-col items-center justify-center gap-3 px-6 py-16 text-center">
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
        @elseif ($isEmpty && $view !== 'daftar')
            <div class="flex flex-1 flex-col items-center justify-center gap-3 px-6 py-16 text-center">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#E3F1F7] text-[#146C94]">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z"></path>
                    </svg>
                </span>
                <div>
                    <p class="text-sm font-semibold text-[#172B36]">Belum ada event bulan ini</p>
                    <p class="mt-1 text-xs text-[#4B5563]">
                        {{ $hasActiveFilters ? 'Coba ubah filter atau tambahkan event baru.' : 'Tambahkan event pertama untuk '.$monthLabel.'.' }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-2">
                    <button type="button" id="content-add-event-empty" wire:click="openCreate()" @click="remember($el)"
                        class="inline-flex h-10 items-center gap-2 rounded-lg bg-[#0097B2] px-4 text-sm font-semibold text-white hover:bg-[#0086A0]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
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
                <div class="min-w-[760px]">
                    <div class="grid grid-cols-7 border-b border-[#E2E8EC] bg-[#EAF4F8]">
                        @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $weekday)
                            <div class="flex h-12 items-center justify-center text-[12px] font-medium text-[#4B5563]">{{ $weekday }}</div>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-7">
                        @foreach ($monthWeeks as $week)
                            @foreach ($week as $day)
                                @php
                                    $date = $day['date'];
                                    $isSelectedCreate = $formOpen && ! $editingId && $event_date === $date;
                                    $numberClass = $day['is_today']
                                        ? 'bg-[#146C94] text-white'
                                        : ($day['is_other_month'] ? 'text-[#7B8794]' : 'text-[#172B36]');
                                @endphp
                                <div wire:key="month-day-{{ $date }}"
                                    class="relative min-h-[96px] border-b border-r border-[#E2E8EC] p-1.5 transition-colors {{ $isSelectedCreate ? 'ring-2 ring-inset ring-[#146C94]' : '' }}"
                                    :class="dragOver === '{{ $date }}' ? 'bg-[#E3F1F7]/60 outline outline-2 -outline-offset-2 outline-dashed outline-[#146C94]' : ''"
                                    @dragover.prevent="dragOver = '{{ $date }}'"
                                    @dragleave="if (dragOver === '{{ $date }}') dragOver = null"
                                    @drop.prevent="if (draggingId) { const id = draggingId; draggingId = null; dragOver = null; $wire.moveEvent(id, '{{ $date }}'); }">
                                    <button type="button" id="content-day-{{ $date }}"
                                        @click="remember($el); $wire.openCreate('{{ $date }}')"
                                        aria-label="Tambah event pada {{ $day['label'] }}"
                                        class="absolute inset-0 z-0 rounded-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#146C94]"></button>

                                    <div class="pointer-events-none relative z-10 flex flex-col gap-1">
                                        <div class="flex justify-end">
                                            <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full px-1 text-[16px] font-medium {{ $numberClass }}">{{ $day['day'] }}</span>
                                        </div>

                                        <div class="flex flex-col gap-1" :class="draggingId ? 'pointer-events-none' : 'pointer-events-auto'">
                                            @foreach (array_slice($day['items'], 0, 2) as $item)
                                                <x-content-event-card :event="$item" :show-date="true" :lines="1"
                                                    wire:key="month-card-{{ $item['id'] }}-{{ $date }}"
                                                    :draggable="$item['can_edit'] ? 'true' : 'false'"
                                                    @click="remember($el)"
                                                    @dragstart="draggingId = {{ $item['id'] }}; $event.dataTransfer.effectAllowed = 'move'; $event.dataTransfer.setData('text/plain', '{{ $item['id'] }}')"
                                                    @dragend="draggingId = null; dragOver = null" />
                                            @endforeach

                                            @if (count($day['items']) > 2)
                                                <button type="button" wire:click="openDay('{{ $date }}')"
                                                    class="w-fit text-[11px] font-semibold text-[#146C94] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                                                    +{{ count($day['items']) - 2 }} lainnya
                                                </button>
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
                <div class="flex min-w-[760px] flex-col">
                    <div class="grid grid-cols-7 border-b border-[#E2E8EC] bg-[#EAF4F8]">
                        @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $weekday)
                            <div class="flex h-10 items-center justify-center text-[12px] font-medium text-[#4B5563]">{{ $weekday }}</div>
                        @endforeach
                    </div>

                    <div class="grid min-h-[520px] grid-cols-7">
                        @foreach ($weekDays as $day)
                            @php
                                $date = $day['date'];
                                $isSelectedCreate = $formOpen && ! $editingId && $event_date === $date;
                                $tint = $day['is_today'] ? 'bg-[#F3FAFD]' : ($day['is_weekend'] ? 'bg-[#FAFBFC]' : 'bg-white');
                                $numberClass = $day['is_today'] ? 'bg-[#146C94] text-white' : 'text-[#172B36]';
                            @endphp
                            <div wire:key="week-day-{{ $date }}"
                                class="group flex flex-col border-r border-[#E2E8EC] last:border-r-0 {{ $tint }} transition-colors {{ $isSelectedCreate ? 'ring-2 ring-inset ring-[#146C94]' : '' }}"
                                :class="dragOver === '{{ $date }}' ? 'outline outline-2 -outline-offset-2 outline-dashed outline-[#146C94]' : ''"
                                @dragover.prevent="dragOver = '{{ $date }}'"
                                @dragleave="if (dragOver === '{{ $date }}') dragOver = null"
                                @drop.prevent="if (draggingId) { const id = draggingId; draggingId = null; dragOver = null; $wire.moveEvent(id, '{{ $date }}'); }">
                                <div class="flex items-center justify-end px-2 pb-1 pt-2">
                                    <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full px-1 text-[16px] font-medium {{ $numberClass }}">{{ $day['day'] }}</span>
                                </div>

                                <div class="space-y-2 p-2 pt-1">
                                    @foreach ($day['items'] as $item)
                                        <x-content-event-card :event="$item" :show-date="false" :lines="2"
                                            wire:key="week-card-{{ $item['id'] }}"
                                            :draggable="$item['can_edit'] ? 'true' : 'false'"
                                            @click="remember($el)"
                                            @dragstart="draggingId = {{ $item['id'] }}; $event.dataTransfer.effectAllowed = 'move'; $event.dataTransfer.setData('text/plain', '{{ $item['id'] }}')"
                                            @dragend="draggingId = null; dragOver = null" />
                                    @endforeach

                                    @if (empty($day['items']))
                                        <button type="button" id="content-day-add-{{ $date }}" wire:click="openCreate('{{ $date }}')" @click="remember($el)"
                                            class="w-full rounded-md border border-dashed border-[#D5DEE3] px-2 py-1.5 text-[11px] font-medium text-[#8A96A3] opacity-0 transition group-hover:opacity-100 hover:border-[#146C94] hover:text-[#146C94] focus:opacity-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                                            + Tambah
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="min-h-0 flex-1 overflow-y-auto">
                @if ($isEmpty)
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
                    </div>
                @else
                    <div class="divide-y divide-[#E2E8EC]">
                        @foreach ($listGroups as $group)
                            <div wire:key="list-group-{{ $group['date'] }}">
                                <div class="bg-[#F8FAFC] px-4 py-2 text-xs font-semibold uppercase tracking-wide text-[#4B5563]">
                                    {{ $group['label'] }}
                                </div>
                                @foreach ($group['items'] as $item)
                                    <button type="button" wire:key="list-event-{{ $item['id'] }}"
                                        id="content-event-list-{{ $item['id'] }}"
                                        @click="remember($el); $wire.openEvent({{ $item['id'] }})"
                                        class="flex w-full items-center gap-3 px-4 py-3 text-left transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#146C94]">
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-sm font-medium text-[#172B36]">{{ $item['title'] }}</span>
                                            <span class="mt-1 flex flex-wrap items-center gap-1.5 text-[11px] text-[#4B5563]">
                                                <span>{{ $item['date_label'] }}</span>
                                                @if (! empty($item['time_label']))
                                                    <span>· {{ $item['time_label'] }}</span>
                                                @endif
                                                <x-content-type-tag :name="$item['type_name']" :fill="$item['type_fill']" :border="$item['type_border']" />
                                            </span>
                                        </span>
                                        <x-status-badge variant="content" dot :status="$item['status']" />
                                    </button>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Day popover --}}
    @if ($popoverDate)
        <div class="fixed inset-0 z-[75] flex items-center justify-center bg-slate-900/40 p-4" @click.self="$wire.closePopover()">
            <div role="dialog" aria-modal="true" aria-labelledby="day-popover-title" tabindex="-1"
                class="flex max-h-[80vh] w-full max-w-md flex-col overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-xl">
                <div class="flex items-center justify-between gap-3 border-b border-[#E8EDF2] px-4 py-3">
                    <h3 id="day-popover-title" class="text-sm font-semibold text-[#172B36]">
                        {{ \Carbon\Carbon::parse($popoverDate)->locale('id')->translatedFormat('l, j F Y') }}
                    </h3>
                    <button type="button" wire:click="closePopover" aria-label="Tutup daftar event"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-[#64748B] transition hover:bg-slate-100 hover:text-[#172B36] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="flex-1 space-y-2 overflow-y-auto p-4">
                    @foreach ($popoverItems as $item)
                        <x-content-event-card :event="$item" :show-date="false" :lines="2"
                            wire:key="popover-card-{{ $item['id'] }}"
                            suffix="popover"
                            @click="remember($el)" />
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Detail / create modal --}}
    @if ($detailOpen || $formOpen)
        @php
            $typeSelectOptions = collect($contentTypes)->map(fn ($type) => [
                'value' => $type['slug'],
                'label' => $type['name'],
                'fill' => $type['fill'],
                'border' => $type['border'],
                'kind' => 'tag',
            ])->values()->all();

            $statusSelectOptions = collect($statusOptions)->map(function ($label, $key) {
                $colors = \App\Support\StatusPalette::content($key);

                return [
                    'value' => $key,
                    'label' => $label,
                    'fill' => $colors['bg'] ?? '#F1F5F9',
                    'border' => $colors['border'] ?? '#CBD5E1',
                    'dot' => $colors['dot'] ?? '#94A3B8',
                    'kind' => 'status',
                ];
            })->values()->all();

            $selectedType = $contentTypes[$content_type] ?? null;
            $selectedStatusColors = \App\Support\StatusPalette::content($formStatus);
        @endphp

        <div data-content-modal
            class="fixed inset-0 z-[80] flex items-start justify-center overflow-y-auto bg-[rgba(15,40,55,0.45)] p-4 sm:py-8"
            @click.self="requestClose()"
            @input="if ($wire.get('formOpen')) dirty = true"
            @change="if ($wire.get('formOpen')) dirty = true"
            @keydown.tab="trap($event)">
            <div data-modal-panel role="dialog" aria-modal="true" aria-labelledby="content-modal-title" tabindex="-1"
                class="flex max-h-[90vh] w-[92vw] max-w-[980px] flex-col overflow-hidden rounded-[14px] bg-white shadow-2xl focus:outline-none">

                {{-- Top row --}}
                <div class="flex items-center justify-between gap-3 px-7 pt-5">
                    @if ($formOpen)
                        <span class="inline-flex h-7 items-center rounded-full border border-[#E2E8EC] bg-[#F1F5F9] px-3 text-[12px] font-medium text-[#4B5563]">
                            Event baru
                        </span>
                    @else
                        <span></span>
                    @endif

                    <div class="flex items-center gap-1.5">
                        @if ($detailOpen)
                            <span x-show="saved" x-cloak class="inline-flex items-center gap-1 text-[12px] font-medium text-[#047857]">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 13 4 4L19 7"></path>
                                </svg>
                                Tersimpan
                            </span>
                            @if ($content_url !== '')
                                <button type="button" @click="copyText($refs.modalLink?.value ?? $refs.modalLinkText?.textContent)"
                                    aria-label="Salin link konten"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-[#64748B] transition hover:bg-slate-100 hover:text-[#172B36] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 6H18a3 3 0 0 1 0 6h-2M10.5 18H6a3 3 0 0 1 0-6h2M9 12h6"></path>
                                    </svg>
                                </button>
                            @endif
                        @endif
                        <button type="button" @click="requestClose()" aria-label="Tutup modal"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-[#64748B] transition hover:bg-slate-100 hover:text-[#172B36] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto px-7 pb-6 pt-2">
                    {{-- Title --}}
                    @if ($readOnly)
                        <h2 id="content-modal-title" class="max-w-[820px] text-[28px] font-bold leading-[1.25] text-[#172B36]">{{ $title }}</h2>
                    @else
                        <label for="content-modal-title" class="sr-only">Judul event</label>
                        <input id="content-modal-title" x-ref="titleInput" type="text" wire:model.blur.live="title"
                            maxlength="150" placeholder="Judul event" autocomplete="off"
                            class="w-full max-w-[820px] border-0 bg-transparent p-0 text-[28px] font-bold leading-[1.25] text-[#172B36] placeholder:text-[#8A96A3] focus:outline-none focus:ring-0">
                        @error('title')
                            <p class="mt-1 text-[12px] text-[#B91C1C]">{{ $message }}</p>
                        @enderror
                    @endif

                    {{-- Properties --}}
                    <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <div class="min-w-0">
                            <p class="mb-1.5 flex items-center gap-1.5 text-[13px] text-[#4B5563]">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z"></path>
                                </svg>
                                Tanggal
                            </p>
                            @if ($readOnly)
                                <div class="flex h-[42px] items-center rounded-lg border border-[#D5DEE3] bg-white px-3 text-sm text-[#172B36]">
                                    {{ $event_date ? \Carbon\Carbon::parse($event_date)->locale('id')->translatedFormat('j M Y') : '—' }}
                                </div>
                            @else
                                <x-date-picker id="content-date" label="Tanggal" model="event_date" :value="$event_date" />
                                @error('event_date')
                                    <p class="mt-1 text-[12px] text-[#B91C1C]">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>

                        <div class="min-w-0">
                            <p class="mb-1.5 flex items-center gap-1.5 text-[13px] text-[#4B5563]">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5h16v4H4zM4 13h9v6H4zM16 13h4v6h-4z"></path>
                                </svg>
                                Jenis konten
                            </p>
                            @if ($readOnly)
                                <div class="flex h-[42px] items-center rounded-lg border border-[#D5DEE3] bg-white px-2.5">
                                    @if ($selectedType)
                                        <x-content-type-tag :name="$selectedType['name']" :fill="$selectedType['fill']" :border="$selectedType['border']" />
                                    @else
                                        <span class="text-sm text-[#4B5563]">—</span>
                                    @endif
                                </div>
                            @else
                                <x-property-select id="content-type" label="Jenis konten" model="content_type"
                                    :value="$content_type" :options="$typeSelectOptions" placeholder="Pilih jenis" />
                                @error('content_type')
                                    <p class="mt-1 text-[12px] text-[#B91C1C]">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>

                        <div class="min-w-0">
                            <p class="mb-1.5 flex items-center gap-1.5 text-[13px] text-[#4B5563]">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18Zm0 4v4l3 2"></path>
                                </svg>
                                Status
                            </p>
                            @if ($readOnly)
                                <div class="flex h-[42px] items-center rounded-lg border border-[#D5DEE3] bg-white px-2.5">
                                    <x-status-badge variant="content" dot :status="$formStatus" />
                                </div>
                            @else
                                <x-property-select id="content-status" label="Status" model="formStatus"
                                    :value="$formStatus" :options="$statusSelectOptions" placeholder="Pilih status" />
                                @error('formStatus')
                                    <p class="mt-1 text-[12px] text-[#B91C1C]">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>

                        <div class="min-w-0">
                            <p class="mb-1.5 flex items-center gap-1.5 text-[13px] text-[#4B5563]">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path>
                                </svg>
                                Jam posting
                            </p>
                            @if ($readOnly)
                                <div class="flex h-[42px] items-center rounded-lg border border-[#D5DEE3] bg-white px-3 text-sm text-[#172B36]">
                                    {{ $post_time !== '' ? $post_time : '—' }}
                                </div>
                            @else
                                <x-time-picker id="content-time" label="Jam posting" model="post_time"
                                    :value="$post_time" :options="$this->timeOptions()" placeholder="Pilih jam" />
                                @error('post_time')
                                    <p class="mt-1 text-[12px] text-[#B91C1C]">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>
                    </div>

                    @if ($saveError)
                        <p class="mt-3 text-[12px] font-medium text-[#B91C1C]">{{ $saveError }}</p>
                    @endif

                    {{-- Description --}}
                    <div class="mt-6 border-t border-[#E8EDF2] pt-5">
                        <label for="content-description" class="block text-[14px] font-semibold text-[#172B36]">Deskripsi</label>
                        @if ($readOnly)
                            <p class="mt-2 max-w-[820px] whitespace-pre-line text-[15px] leading-[1.7] {{ $description !== '' ? 'text-[#172B36]' : 'text-[#4B5563]' }}">
                                {{ $description !== '' ? $description : 'Belum ada deskripsi.' }}
                            </p>
                        @else
                            <textarea id="content-description" wire:model.blur.live="description" rows="4"
                                placeholder="Tulis tujuan konten, pesan utama, dan brief visual..."
                                class="mt-2 w-full max-w-[820px] resize-y rounded-lg border border-[#D5DEE3] bg-white p-3 text-[15px] leading-[1.7] text-[#172B36] placeholder:text-[#8A96A3] focus:border-[#146C94] focus:outline-none focus:ring-2 focus:ring-[#146C94] {{ $formOpen ? 'min-h-[150px]' : 'min-h-[120px]' }}"></textarea>
                            @error('description')
                                <p class="mt-1 text-[12px] text-[#B91C1C]">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>

                    {{-- Link konten --}}
                    <div class="mt-5">
                        <p class="mb-1.5 flex items-center gap-1.5 text-[14px] font-semibold text-[#172B36]">
                            <svg class="h-4 w-4 text-[#4B5563]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.5 6H18a3 3 0 0 1 0 6h-2M10.5 18H6a3 3 0 0 1 0-6h2M9 12h6"></path>
                            </svg>
                            Link konten
                        </p>

                        @if ($readOnly)
                            <div class="flex h-[46px] items-center gap-2 rounded-lg border border-[#E8EDF2] bg-white px-3">
                                <svg class="h-4 w-4 shrink-0 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.5 6H18a3 3 0 0 1 0 6h-2M10.5 18H6a3 3 0 0 1 0-6h2M9 12h6"></path>
                                </svg>
                                @if ($content_url !== '')
                                    <a x-ref="modalLinkText" href="{{ $content_url }}" target="_blank" rel="noopener noreferrer"
                                        class="min-w-0 flex-1 truncate text-sm text-[#146C94] hover:underline">{{ $content_url }}</a>
                                    <button type="button" @click="copyText('{{ $content_url }}')"
                                        class="shrink-0 rounded-md px-2 py-1 text-[12px] font-semibold text-[#146C94] hover:bg-[#E3F1F7] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                                        <span x-show="!copied">Salin</span>
                                        <span x-show="copied" x-cloak>Tersalin</span>
                                    </button>
                                    <a href="{{ $content_url }}" target="_blank" rel="noopener noreferrer"
                                        class="shrink-0 rounded-md px-2 py-1 text-[12px] font-semibold text-[#146C94] hover:bg-[#E3F1F7] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                                        Buka
                                    </a>
                                @else
                                    <span class="text-sm text-[#4B5563]">Belum ada link</span>
                                @endif
                            </div>
                        @else
                            <div class="flex h-[46px] items-center gap-2 rounded-lg border border-[#D5DEE3] bg-white px-3 focus-within:border-[#146C94] focus-within:ring-2 focus-within:ring-[#146C94]">
                                <svg class="h-4 w-4 shrink-0 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.5 6H18a3 3 0 0 1 0 6h-2M10.5 18H6a3 3 0 0 1 0-6h2M9 12h6"></path>
                                </svg>
                                <input id="content-url" x-ref="modalLink" type="url" wire:model.blur.live="content_url"
                                    placeholder="https://" autocomplete="off"
                                    class="h-full min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-[#172B36] placeholder:text-[#8A96A3] focus:outline-none focus:ring-0">
                                @if ($content_url !== '')
                                    <button type="button" @click="copyText($refs.modalLink?.value)"
                                        class="shrink-0 rounded-md px-2 py-1 text-[12px] font-semibold text-[#146C94] hover:bg-[#E3F1F7] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                                        <span x-show="!copied">Salin</span>
                                        <span x-show="copied" x-cloak>Tersalin</span>
                                    </button>
                                    <a href="{{ $content_url }}" target="_blank" rel="noopener noreferrer"
                                        class="shrink-0 rounded-md px-2 py-1 text-[12px] font-semibold text-[#146C94] hover:bg-[#E3F1F7] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                                        Buka
                                    </a>
                                @endif
                            </div>
                            @error('content_url')
                                <p class="mt-1 text-[12px] text-[#B91C1C]">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>

                    @if ($formOpen)
                        <div class="mt-5 flex items-start gap-2 rounded-lg bg-[#E3F1F7] px-3 py-2.5 text-[13px] leading-relaxed text-[#0B4A66]">
                            <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.04 12.32a1 1 0 0 1 0-.64C3.42 7.51 7.36 4.5 12 4.5s8.58 3.01 9.96 7.18a1 1 0 0 1 0 .64C20.58 16.49 16.64 19.5 12 19.5S3.42 16.49 2.04 12.32Z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
                            </svg>
                            <span>Event ini akan terlihat oleh semua karyawan.</span>
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-[#E8EDF2] px-7 py-4">
                    @if ($formOpen)
                        <span></span>
                        <div class="ml-auto flex items-center gap-2">
                            <button type="button" @click="requestClose()"
                                class="inline-flex h-10 items-center rounded-lg border border-[#E2E8EC] bg-white px-4 text-sm font-medium text-[#4B5563] transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                                Batal
                            </button>
                            <button type="button" wire:click="save"
                                class="inline-flex h-10 items-center rounded-lg bg-[#0097B2] px-4 text-sm font-semibold text-white transition hover:bg-[#0086A0] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] focus-visible:ring-offset-2">
                                Tambah event
                            </button>
                        </div>
                    @else
                        <p class="text-[12px] text-[#4B5563]">
                            Dibuat oleh {{ $creatorName }}@if ($creatorAgo !== '')<span aria-hidden="true"> • </span>{{ $creatorAgo }}@endif
                        </p>
                        <div class="flex items-center gap-2">
                            @if ($canDelete)
                                <button type="button" @click="openDelete()"
                                    class="rounded-md px-2 py-1.5 text-[13px] font-semibold text-[#B91C1C] transition hover:bg-red-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#B91C1C]">
                                    Hapus
                                </button>
                            @endif
                            <button type="button" @click="requestClose()"
                                class="inline-flex h-9 items-center rounded-lg border border-[#E2E8EC] bg-white px-4 text-sm font-medium text-[#4B5563] transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                                Tutup
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    @if ($canDelete)
    {{-- Delete confirmation --}}
    <div x-show="confirmOpen" x-cloak
        class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 p-4"
        @click.self="closeDelete()">
        <div role="dialog" aria-modal="true" aria-labelledby="hapus-event-title"
            class="w-full max-w-md rounded-xl border border-[#E2E8EC] bg-white p-6 shadow-xl">
            <h3 id="hapus-event-title" class="text-lg font-semibold text-[#172B36]">Hapus event?</h3>
            <p class="mt-2 text-sm leading-relaxed text-[#4B5563]">
                Event &ldquo;{{ $title }}&rdquo; akan dihapus permanen.
            </p>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" x-ref="deleteCancel" @click="closeDelete()"
                    class="inline-flex h-10 items-center rounded-lg border border-[#E2E8EC] bg-white px-4 text-sm font-medium text-[#4B5563] transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                    Batal
                </button>
                <button type="button" wire:click="deleteEvent" @click="confirmOpen = false"
                    class="inline-flex h-10 items-center rounded-lg bg-[#B91C1C] px-4 text-sm font-semibold text-white transition hover:bg-[#991B1B] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#B91C1C] focus-visible:ring-offset-2">
                    Hapus
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Discard-changes confirmation --}}
    <div x-show="closeConfirm" x-cloak
        class="fixed inset-0 z-[110] flex items-center justify-center bg-slate-900/40 p-4"
        @click.self="closeConfirm = false">
        <div role="alertdialog" aria-modal="true" aria-labelledby="discard-title"
            class="w-full max-w-sm rounded-xl border border-[#E2E8EC] bg-white p-6 shadow-xl">
            <h3 id="discard-title" class="text-lg font-semibold text-[#172B36]">Buang perubahan?</h3>
            <p class="mt-2 text-sm leading-relaxed text-[#4B5563]">Perubahan yang belum disimpan akan hilang.</p>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" x-ref="closeCancel" @click="closeConfirm = false"
                    class="inline-flex h-10 items-center rounded-lg border border-[#E2E8EC] bg-white px-4 text-sm font-medium text-[#4B5563] transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                    Lanjut mengedit
                </button>
                <button type="button" @click="confirmDiscard()"
                    class="inline-flex h-10 items-center rounded-lg bg-[#B91C1C] px-4 text-sm font-semibold text-white transition hover:bg-[#991B1B] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#B91C1C] focus-visible:ring-offset-2">
                    Buang
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
