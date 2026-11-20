<div id="formKirimPesanHrd" class="hr-compose-root" aria-label="Kirim Pesan">
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background-color: rgba(15, 40, 55, .45);"
             wire:click.self="requestClose">
            <div class="relative w-[500px] max-w-[92vw] overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-2xl"
                 role="dialog" aria-modal="true" aria-labelledby="composeTitle"
                 data-compose-panel>

                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-[#E2E8EC] px-5 py-4">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#E3F1F7] text-[#146C94]">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                            </svg>
                        </span>
                        <h2 id="composeTitle" class="text-[16px] font-semibold text-[#172B36]">Kirim pesan</h2>
                    </div>
                    <button type="button" id="composeCloseBtn" wire:click="requestClose"
                            class="flex h-8 w-8 items-center justify-center rounded-lg text-[#64748B] transition hover:bg-[#F1F5F9] hover:text-[#172B36] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]"
                            aria-label="Tutup">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Server-level error banner: keeps everything the user typed --}}
                @if ($errorMessage !== '')
                    <div class="mx-5 mt-4 rounded-lg border border-[#F5A3A3] bg-[#FDE0E0] px-3 py-2 text-[12px] leading-relaxed text-[#B91C1C]"
                         role="alert">{{ $errorMessage }}</div>
                @endif

                <div class="max-h-[65vh] space-y-4 overflow-y-auto px-5 py-4">
                    {{-- Recipient combobox --}}
                    <div class="relative">
                        <label for="composeRecipientSearch" class="mb-1.5 block text-[13px] font-medium text-[#172B36]">
                            Penerima <span class="text-[#DC2626]">*</span>
                        </label>

                        @if ($recipientUserId && $recipient)
                            <div class="flex h-10 items-center gap-2.5 rounded-lg border border-[#D5DEE3] bg-white px-2.5">
                                @if ($recipient['foto'])
                                    <img src="{{ $recipient['foto'] }}" alt=""
                                         class="h-7 w-7 shrink-0 rounded-full border border-[#E2E8EC] object-cover">
                                @else
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#AFD3E2] text-[12px] font-semibold text-[#0B4A66]">
                                        {{ $recipient['initials'] }}
                                    </span>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-[13px] font-medium text-[#172B36]">{{ $recipient['nama'] }}</p>
                                    <p class="truncate text-[12px] text-[#4B5563]">{{ $recipient['jabatan'] }} · {{ $recipient['divisi'] }}</p>
                                </div>
                                <button type="button" wire:click="clearRecipient" aria-label="Ganti penerima"
                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-[#64748B] transition hover:bg-[#F1F5F9] hover:text-[#172B36] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        @else
                            <div class="relative">
                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[#94A3B8]">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle cx="11" cy="11" r="7"></circle>
                                        <path stroke-linecap="round" d="m20 20-3.5-3.5"></path>
                                    </svg>
                                </span>
                                <input id="composeRecipientSearch" type="text" autocomplete="off"
                                       wire:model.live.debounce.250ms="search" wire:focus="openList"
                                       role="combobox" aria-expanded="{{ $listOpen ? 'true' : 'false' }}"
                                       aria-controls="composeRecipientList" aria-autocomplete="list"
                                       placeholder="Cari nama, jabatan, atau divisi"
                                       class="h-10 w-full rounded-lg border border-[#D5DEE3] bg-white pl-9 pr-3 text-[13px] text-[#172B36] outline-none placeholder:text-[#94A3B8] focus:border-[#146C94] focus:ring-2 focus:ring-[#146C94]">
                            </div>

                            @if ($listOpen)
                                <div id="composeRecipientList" role="listbox"
                                     class="absolute z-10 mt-1 max-h-[280px] w-full overflow-y-auto rounded-lg border border-[#E2E8EC] bg-white py-1 shadow-lg">
                                    @if (trim($search) === '' && count($this->recent) > 0)
                                        <p class="px-3 pb-1 pt-2 text-[11px] font-medium uppercase tracking-wide text-[#94A3B8]">Terakhir dihubungi</p>
                                        @foreach ($this->recent as $option)
                                            <button type="button" role="option" id="composeOption-{{ $loop->index }}"
                                                    wire:click="selectRecipient('{{ $option['id'] }}')"
                                                    class="compose-option flex min-h-[44px] w-full items-center gap-2.5 px-3 text-left transition hover:bg-[#F5FAFC] focus:outline-none">
                                                @if ($option['foto'])
                                                    <img src="{{ $option['foto'] }}" alt=""
                                                         class="h-7 w-7 shrink-0 rounded-full border border-[#E2E8EC] object-cover">
                                                @else
                                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#AFD3E2] text-[12px] font-semibold text-[#0B4A66]">{{ $option['initials'] }}</span>
                                                @endif
                                                <span class="min-w-0">
                                                    <span class="block truncate text-[13px] font-medium text-[#172B36]">{{ $option['nama'] }}</span>
                                                    <span class="block truncate text-[12px] text-[#4B5563]">{{ $option['jabatan'] }} · {{ $option['divisi'] }}</span>
                                                </span>
                                            </button>
                                        @endforeach
                                        <div class="my-1 border-t border-[#EEF2F5]"></div>
                                    @endif

                                    @forelse ($this->results as $index => $option)
                                        <button type="button" role="option" id="composeOption-{{ $index }}"
                                                wire:click="selectRecipient('{{ $option['id'] }}')"
                                                class="compose-option flex min-h-[44px] w-full items-center gap-2.5 px-3 text-left transition hover:bg-[#F5FAFC] focus:outline-none">
                                            @if ($option['foto'])
                                                <img src="{{ $option['foto'] }}" alt=""
                                                     class="h-7 w-7 shrink-0 rounded-full border border-[#E2E8EC] object-cover">
                                            @else
                                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#AFD3E2] text-[12px] font-semibold text-[#0B4A66]">{{ $option['initials'] }}</span>
                                            @endif
                                            <span class="min-w-0">
                                                <span class="block truncate text-[13px] font-medium text-[#172B36]">{{ $option['nama'] }}</span>
                                                <span class="block truncate text-[12px] text-[#4B5563]">{{ $option['jabatan'] }} · {{ $option['divisi'] }}</span>
                                            </span>
                                        </button>
                                    @empty
                                        <p class="px-3 py-3 text-[12px] text-[#4B5563]">Penerima tidak ditemukan</p>
                                    @endforelse

                                    @if (count($this->results) >= 20)
                                        <p class="border-t border-[#EEF2F5] px-3 py-2 text-[12px] text-[#4B5563]">Ketik untuk mempersempit hasil</p>
                                    @endif
                                </div>
                            @endif
                        @endif

                        @error('recipientUserId')
                            <p class="mt-1 text-[12px] text-[#B91C1C]">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Message body --}}
                    <div>
                        <label for="composeIsi" class="mb-1.5 block text-[13px] font-medium text-[#172B36]">
                            Isi pesan <span class="text-[#DC2626]">*</span>
                        </label>
                        <textarea id="composeIsi" rows="5" maxlength="2000" wire:model.live="isi"
                                  placeholder="Tulis pesan Anda..."
                                  class="block w-full resize-none overflow-hidden rounded-lg border border-[#D5DEE3] bg-white px-3 py-2 text-[13px] leading-[22px] text-[#172B36] outline-none placeholder:text-[#94A3B8] focus:border-[#146C94] focus:ring-2 focus:ring-[#146C94]"></textarea>
                        @error('isi')
                            <p class="mt-1 text-[12px] text-[#B91C1C]">{{ $message }}</p>
                        @enderror
                        <div class="mt-1.5 flex items-center justify-between gap-3 text-[12px] text-[#4B5563]">
                            <span>Ctrl + Enter untuk mengirim</span>
                            <span class="tabular-nums {{ strlen($isi) >= 2000 ? 'text-[#B91C1C]' : '' }}">{{ strlen($isi) }} / 2000</span>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-2 border-t border-[#E2E8EC] px-5 py-4">
                    <button type="button" wire:click="requestClose" @if ($sending) disabled @endif
                            class="inline-flex h-9 items-center rounded-lg border border-[#D5DEE3] bg-white px-4 text-[13px] font-medium text-[#172B36] transition hover:bg-[#F8FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] disabled:cursor-not-allowed disabled:opacity-60">
                        Batal
                    </button>
                    <button type="button" id="composeSendBtn" wire:click="send"
                            @if (! $recipientUserId || trim($isi) === '' || $sending) disabled @endif
                            class="inline-flex h-9 items-center gap-2 rounded-lg bg-[#146C94] px-4 text-[13px] font-medium text-white transition hover:bg-[#0F5678] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                        @if ($sending)
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Mengirim...
                        @else
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                            </svg>
                            Kirim pesan
                        @endif
                    </button>
                </div>

                {{-- Discard confirmation --}}
                @if ($confirmDiscard)
                    <div class="absolute inset-0 flex items-center justify-center bg-[rgba(15,40,55,.45)] p-4">
                        <div class="w-full max-w-[320px] rounded-xl border border-[#E2E8EC] bg-white p-5 shadow-xl"
                             role="alertdialog" aria-modal="true" aria-labelledby="composeDiscardTitle">
                            <p id="composeDiscardTitle" class="text-[14px] font-medium text-[#172B36]">Buang pesan ini?</p>
                            <p class="mt-1 text-[12px] text-[#4B5563]">Perubahan yang belum dikirim akan hilang.</p>
                            <div class="mt-4 flex justify-end gap-2">
                                <button type="button" id="composeDiscardCancel" wire:click="cancelDiscard"
                                        class="inline-flex h-9 items-center rounded-lg border border-[#D5DEE3] bg-white px-4 text-[13px] font-medium text-[#172B36] transition hover:bg-[#F8FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                                    Batal
                                </button>
                                <button type="button" wire:click="confirmDiscard"
                                        class="inline-flex h-9 items-center rounded-lg bg-[#B91C1C] px-4 text-[13px] font-medium text-white transition hover:bg-[#991B1B] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#B91C1C]">
                                    Buang
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <style>
        .compose-option.is-active { background-color: #E3F1F7; }
    </style>

    @script
    <script>
        (function () {
            window.__hrComposeActive = 0;

            window.bukaModalPesanHrd = function () {
                window.__hrComposeTrigger = document.activeElement;
                Livewire.dispatch('open-compose-message');
            };

            function optionElements() {
                const list = document.getElementById('composeRecipientList');
                return list ? Array.from(list.querySelectorAll('[role="option"]')) : [];
            }

            function syncActiveOption() {
                const input = document.getElementById('composeRecipientSearch');
                const options = optionElements();
                if (!input || !options.length) {
                    if (input) input.removeAttribute('aria-activedescendant');
                    return;
                }
                if (window.__hrComposeActive >= options.length) window.__hrComposeActive = 0;
                options.forEach(function (option, index) {
                    option.classList.toggle('is-active', index === window.__hrComposeActive);
                });
                input.setAttribute('aria-activedescendant', options[window.__hrComposeActive].id);
            }

            function sizeTextarea() {
                const area = document.getElementById('composeIsi');
                if (!area) return;
                area.style.height = 'auto';
                area.style.height = Math.min(area.scrollHeight, 228) + 'px';
            }

            function initialise() {
                window.__hrComposeActive = 0;
                syncActiveOption();
                sizeTextarea();

                const discardCancel = document.getElementById('composeDiscardCancel');
                if (discardCancel && ! window.__hrComposeConfirmFocused) {
                    window.__hrComposeConfirmFocused = true;
                    discardCancel.focus();
                } else if (! discardCancel) {
                    window.__hrComposeConfirmFocused = false;
                }
            }

            document.addEventListener('livewire:update', initialise);
            document.addEventListener('DOMContentLoaded', initialise);
            initialise();

            window.addEventListener('compose-message-closed', function () {
                if (window.__hrComposeTrigger && typeof window.__hrComposeTrigger.focus === 'function') {
                    window.__hrComposeTrigger.focus();
                }
            });

            window.addEventListener('compose-focus-cancel', function () {
                document.getElementById('composeDiscardCancel')?.focus();
            });

            document.addEventListener('keydown', function (event) {
                const panel = document.querySelector('[data-compose-panel]');
                if (!panel) return;

                if (event.key === 'Escape') {
                    event.preventDefault();
                    if (document.getElementById('composeDiscardCancel')) {
                        document.getElementById('composeDiscardCancel').click();
                    } else if (document.getElementById('composeRecipientList')) {
                        Livewire.dispatch('compose-close-list');
                    } else {
                        document.getElementById('composeCloseBtn')?.click();
                    }
                    return;
                }

                if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
                    event.preventDefault();
                    document.getElementById('composeSendBtn')?.click();
                    return;
                }

                const input = document.getElementById('composeRecipientSearch');
                if (input && document.activeElement === input) {
                    const options = optionElements();
                    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                        event.preventDefault();
                        if (!options.length) return;
                        const step = event.key === 'ArrowDown' ? 1 : -1;
                        window.__hrComposeActive = (window.__hrComposeActive + step + options.length) % options.length;
                        syncActiveOption();
                        options[window.__hrComposeActive].scrollIntoView({ block: 'nearest' });
                        return;
                    }
                    if (event.key === 'Enter' && options.length) {
                        event.preventDefault();
                        options[window.__hrComposeActive].click();
                        return;
                    }
                }

                if (event.key === 'Tab') {
                    const focusables = Array.from(panel.querySelectorAll(
                        'button:not([disabled]), input:not([disabled]), textarea:not([disabled]), [href], [tabindex]:not([tabindex="-1"])'
                    )).filter(function (element) { return element.offsetParent !== null; });
                    if (!focusables.length) return;
                    const first = focusables[0];
                    const last = focusables[focusables.length - 1];
                    if (event.shiftKey && document.activeElement === first) {
                        event.preventDefault();
                        last.focus();
                    } else if (!event.shiftKey && document.activeElement === last) {
                        event.preventDefault();
                        first.focus();
                    }
                }
            });

            document.addEventListener('input', function (event) {
                if (event.target && event.target.id === 'composeIsi') sizeTextarea();
            });
        })();
    </script>
    @endscript
</div>
