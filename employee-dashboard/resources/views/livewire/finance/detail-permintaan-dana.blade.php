<div class="w-full"
    x-data="{
        toasts: [],
        toastSeq: 0,
        pushToast(detail) {
            const id = ++this.toastSeq;
            this.toasts.push({
                id,
                message: detail?.message || '',
                type: detail?.type || 'success'
            });
            setTimeout(() => this.removeToast(id), 4200);
        },
        removeToast(id) {
            this.toasts = this.toasts.filter((toast) => toast.id !== id);
        }
    }"
    @permintaan-detail-toast.window="pushToast($event.detail)"
    @keydown.escape.window="
        if ($wire.get('showApproveModal')) { $wire.closeApproveModal(); }
        else if ($wire.get('showRejectModal')) { $wire.closeRejectModal(); }
    ">

    <style>
        [x-cloak] { display: none !important; }
        @keyframes permintaan-detail-toast-in {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .permintaan-detail-toast { animation: permintaan-detail-toast-in 0.18s ease-out both; }
        @media (prefers-reduced-motion: reduce) {
            .permintaan-detail-toast { animation: none; }
        }
    </style>

    {{-- Toast --}}
    <div class="pointer-events-none fixed left-1/2 top-5 z-[9999] flex w-full max-w-sm -translate-x-1/2 flex-col items-stretch gap-2 px-4"
        aria-live="polite">
        <template x-for="toast in toasts" :key="toast.id">
            <div class="permintaan-detail-toast pointer-events-auto flex items-center gap-3 rounded-xl border bg-white px-4 py-3 shadow-lg"
                :class="toast.type === 'error' ? 'border-[#F5A3A3]' : 'border-[#E2E8EC]'">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full"
                    :class="toast.type === 'error' ? 'bg-[#FDE0E0] text-[#B91C1C]' : 'bg-[#D9F5E5] text-[#059669]'">
                    <svg x-show="toast.type !== 'error'" class="h-4 w-4" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"></path>
                    </svg>
                    <svg x-show="toast.type === 'error'" class="h-4 w-4" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z">
                        </path>
                    </svg>
                </span>
                <p class="min-w-0 flex-1 text-[13px] font-medium text-[#172B36]" x-text="toast.message"></p>
                <button type="button" @click="removeToast(toast.id)"
                    class="shrink-0 rounded p-1 text-[#4B5563] transition hover:bg-[#F5FAFC] hover:text-[#172B36]"
                    aria-label="Tutup notifikasi">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    {{-- Title + status badge on the same line --}}
    <div class="flex items-start gap-2.5">
        <span class="shrink-0 rounded-lg bg-[#E3F1F7] p-2 text-[#146C94]">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9Z">
                </path>
            </svg>
        </span>

        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-[24px] font-bold leading-tight text-[#172B36]">{{ $permintaan->judul_pengajuan }}</h1>
                <x-status-badge :status="$permintaan->status" :label="$status['label']" :palette="$status" dot />
            </div>

            {{-- Meta line --}}
            <p class="mt-2 text-[14px] text-[#4B5563]">
                Diajukan oleh {{ $permintaan->diajukanOleh?->karyawan?->nama ?? $permintaan->diajukanOleh?->username ?? '-' }}
                · {{ $permintaan->divisi?->nama_divisi }} ({{ $permintaan->divisi?->kode_divisi }})
                · {{ $this->tanggalIndonesia($permintaan->created_at) }}
            </p>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-8 min-[1000px]:grid-cols-[minmax(0,1fr)_360px]">
        {{-- Left column: plain text on page background --}}
        <div class="min-w-0">
            <section>
                <p class="text-[12px] text-[#4B5563]">Nominal diajukan</p>
                <p class="mt-1 text-[27px] font-semibold leading-tight tabular-nums text-[#172B36]">
                    {{ $this->rupiah($permintaan->nominal_diajukan) }}
                </p>
            </section>

            <hr class="my-6 border-0 border-t border-[#E2E8EC]">

            <section>
                <h2 class="text-[14px] font-semibold text-[#172B36]">Keperluan</h2>
                <p class="mt-2 text-[14px] leading-[1.6] text-[#172B36]">{{ $permintaan->keperluan }}</p>
            </section>

            <hr class="my-6 border-0 border-t border-[#E2E8EC]">

            <section>
                <h2 class="text-[14px] font-semibold text-[#172B36]">Lampiran</h2>

                @if ($lampiranList->isNotEmpty())
                    <div class="mt-3 divide-y divide-[#EEF2F5]">
                        @foreach ($lampiranList as $file)
                            <div class="flex items-center gap-3 py-3 first:pt-0 last:pb-0">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#E3F1F7] text-[#146C94]">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"
                                        viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M14.25 3.75H7.5A1.5 1.5 0 0 0 6 5.25v13.5a1.5 1.5 0 0 0 1.5 1.5h9a1.5 1.5 0 0 0 1.5-1.5V7.5l-3.75-3.75Zm0 0V7.5H18">
                                        </path>
                                    </svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-[13px] font-medium text-[#146C94]">{{ $file['name'] }}</p>
                                    <p class="mt-0.5 text-[12px] text-[#4B5563]">{{ $file['type'] }} · {{ $file['size'] }}</p>
                                </div>
                                <a href="{{ $file['url'] }}" download
                                    class="shrink-0 text-[12px] font-medium text-[#146C94] hover:underline">
                                    Unduh
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mt-3 flex items-start gap-2.5 rounded-lg border border-[#F5C85C] bg-[#FFFBEB] px-3.5 py-3 text-[12px] text-[#4B5563]">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-[#B45309]" fill="none" stroke="currentColor"
                            stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m0 3.75h.008M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z">
                            </path>
                        </svg>
                        <p>Tidak ada proposal atau RAB yang dilampirkan.</p>
                    </div>
                @endif
            </section>
        </div>

        {{-- Right column: decision panel --}}
        <aside class="self-start min-[1000px]:sticky min-[1000px]:top-6">
            <div class="rounded-xl border border-[#E2E8EC] bg-white p-4 shadow-sm">
                <h2 class="text-[16px] font-semibold text-[#172B36]">Keputusan</h2>
                <p class="mt-0.5 text-[12px] text-[#4B5563]">
                    Pagu {{ $permintaan->divisi?->nama_divisi }}, {{ $periodeLabel }}
                </p>

                <dl class="mt-4 space-y-2.5 text-[13px]">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-[#4B5563]">Pagu bulan ini</dt>
                        <dd class="text-right font-medium tabular-nums text-[#172B36]">{{ $this->rupiah($totalAlokasi) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-[#4B5563]">Sudah dicairkan</dt>
                        <dd class="text-right font-medium tabular-nums text-[#172B36]">{{ $this->rupiah($totalTerpakai) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-[#4B5563]">Sisa pagu</dt>
                        <dd class="text-right font-medium tabular-nums text-[#172B36]">{{ $this->rupiah($sisaPagu) }}</dd>
                    </div>

                    <div class="border-t border-[#EEF2F5] pt-2.5">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="font-semibold text-[#4B5563]">Sisa jika disetujui</dt>
                            <dd class="text-right font-semibold tabular-nums {{ $sisaSetelah < 0 ? 'text-[#B91C1C]' : 'text-[#172B36]' }}">
                                {{ $this->rupiah($sisaSetelah) }}
                            </dd>
                        </div>
                        @if ($sisaSetelah < 0)
                            <p class="mt-1 text-[12px] text-[#B91C1C]">
                                Melebihi sisa pagu sebesar {{ $this->rupiah(abs($sisaSetelah)) }}
                            </p>
                        @endif
                    </div>
                </dl>

                {{-- Usage bar --}}
                <div class="mt-4">
                    <div class="relative h-2 w-full overflow-hidden rounded-full bg-[#E2E8EC]" role="progressbar"
                        aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ min(100, $afterPercentLabel) }}"
                        aria-label="{{ $progressLabel }}">
                        <div class="absolute inset-y-0 left-0 bg-[#0D9488]" style="width: {{ $baseWidth }}%"></div>
                        @if ($extraWidth > 0)
                            <div class="absolute inset-y-0 bg-[#8FD3CB]"
                                style="left: {{ $baseWidth }}%; width: {{ $extraWidth }}%"></div>
                        @endif
                        @if ($overPercent > 0)
                            <div class="absolute inset-y-0 right-0 bg-[#DC2626]"
                                style="width: {{ min(100, $overPercent) }}%"></div>
                        @endif
                    </div>
                    <p class="mt-2 text-[12px] text-[#4B5563]">{{ $progressLabel }}</p>
                </div>

                @if ($canDecide)
                    <div class="mt-4 border-t border-[#E2E8EC] pt-4">
                        <label for="note" class="block text-[13px] font-medium text-[#172B36]">
                            Catatan <span class="font-normal text-[#4B5563]">(opsional)</span>
                        </label>
                        <textarea id="note" wire:model="note" rows="3" placeholder="Tambahkan catatan untuk pengaju"
                            class="mt-1 w-full rounded-lg border border-[#E2E8EC] bg-white px-3 py-2 text-[13px] text-[#172B36] focus:border-[#146C94] focus:outline-none focus:ring-2 focus:ring-[#146C94]"></textarea>
                    </div>

                    <div class="mt-4 flex items-center gap-2">
                        <button type="button" wire:click="openApproveModal"
                            class="inline-flex h-10 flex-1 items-center justify-center rounded-lg bg-[#047857] px-4 text-[13px] font-medium text-white transition hover:bg-[#036847] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#047857]">
                            Setujui
                        </button>
                        <button type="button" wire:click="openRejectModal"
                            class="inline-flex h-10 flex-1 items-center justify-center rounded-lg border border-[#FCA5A5] bg-white px-4 text-[13px] font-medium text-[#B91C1C] transition hover:bg-[#FEF2F2] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#B91C1C]">
                            Tolak
                        </button>
                    </div>

                    <p class="mt-2 text-[12px] text-[#4B5563]">Keputusan dicatat dan dikirim ke pengaju.</p>
                @elseif (! $permintaan->isMenunggu())
                    <div class="mt-4 border-t border-[#E2E8EC] pt-4">
                        <p class="text-[13px] text-[#4B5563]">
                            {{ $permintaan->isDitolak() ? 'Ditolak' : 'Disetujui' }} oleh {{ $actor }} · {{ $this->tanggalIndonesia($permintaan->updated_at) }}
                        </p>
                        @if ($permintaan->isDitolak() && $permintaan->catatan_finance)
                            <p class="mt-1 text-[12px] text-[#4B5563]">Alasan: {{ $permintaan->catatan_finance }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </aside>
    </div>

    {{-- Approve dialog --}}
    @if ($showApproveModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="background-color: rgba(15, 40, 55, 0.45);" wire:click.self="closeApproveModal"
            x-init="$nextTick(() => $refs.approveCancel?.focus())">
            <div class="w-full max-w-[460px] rounded-xl bg-white p-5 shadow-xl" role="dialog" aria-modal="true"
                aria-labelledby="detail-approve-title">
                <div class="flex items-start justify-between gap-4">
                    <h3 id="detail-approve-title" class="text-[16px] font-semibold text-[#172B36]">
                        Setujui pengajuan {{ $permintaan->nomor_pengajuan }}?
                    </h3>
                    <button type="button" wire:click="closeApproveModal" wire:loading.attr="disabled"
                        wire:target="approve" aria-label="Tutup dialog"
                        class="rounded-lg p-1 text-[#4B5563] transition hover:bg-[#F5FAFC] hover:text-[#172B36] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] disabled:cursor-wait disabled:opacity-60">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <p class="mt-2 text-[13px] leading-relaxed text-[#4B5563]">
                    <span class="font-medium text-[#172B36]">{{ $permintaan->judul_pengajuan }}</span> sebesar
                    <span class="font-semibold text-[#172B36]">{{ $this->rupiah($permintaan->nominal_diajukan) }}</span>
                    dari <span class="font-medium text-[#172B36]">{{ $permintaan->divisi?->nama_divisi }}</span> akan disetujui.
                </p>

                @if ($sisaSetelah < 0)
                    <div class="mt-3 flex items-start gap-2 rounded-lg border border-[#F5A3A3] bg-[#FDE0E0] px-3 py-2 text-[12px] text-[#B91C1C]">
                        <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"
                            viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m0 3.75h.008M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z">
                            </path>
                        </svg>
                        <p>Melebihi sisa pagu sebesar {{ $this->rupiah(abs($sisaSetelah)) }}.</p>
                    </div>
                @endif

                <div class="mt-5 flex items-center justify-end gap-2">
                    <button x-ref="approveCancel" type="button" wire:click="closeApproveModal"
                        wire:loading.attr="disabled" wire:target="approve"
                        class="inline-flex h-9 items-center rounded-lg border border-[#D5DEE3] bg-white px-4 text-[12px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] disabled:cursor-wait disabled:opacity-60">
                        Batal
                    </button>
                    <button type="button" wire:click="approve" wire:loading.attr="disabled" wire:target="approve"
                        class="inline-flex h-9 items-center rounded-lg bg-[#047857] px-4 text-[12px] font-semibold text-white shadow-sm transition hover:bg-[#036847] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#047857] disabled:cursor-wait disabled:opacity-60">
                        {{ $sisaSetelah < 0 ? 'Tetap setujui' : 'Setujui' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Reject dialog --}}
    @if ($showRejectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="background-color: rgba(15, 40, 55, 0.45);" wire:click.self="closeRejectModal"
            x-init="$nextTick(() => $refs.rejectCancel?.focus())">
            <div class="w-full max-w-[460px] rounded-xl bg-white p-5 shadow-xl" role="dialog" aria-modal="true"
                aria-labelledby="detail-reject-title">
                <div class="flex items-start justify-between gap-4">
                    <h3 id="detail-reject-title" class="text-[16px] font-semibold text-[#172B36]">
                        Tolak pengajuan {{ $permintaan->nomor_pengajuan }}?
                    </h3>
                    <button type="button" wire:click="closeRejectModal" wire:loading.attr="disabled"
                        wire:target="reject" aria-label="Tutup dialog"
                        class="rounded-lg p-1 text-[#4B5563] transition hover:bg-[#F5FAFC] hover:text-[#172B36] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] disabled:cursor-wait disabled:opacity-60">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <p class="mt-2 text-[13px] text-[#4B5563]">Pengaju akan menerima alasan penolakan.</p>

                <form wire:submit="reject" class="mt-4 space-y-4">
                    <div>
                        <label for="rejectReason" class="mb-1 block text-[12px] font-medium text-[#172B36]">
                            Alasan <span class="text-[#B91C1C]">*</span>
                        </label>
                        <textarea id="rejectReason" wire:model="rejectReason" rows="3" required
                            placeholder="Jelaskan alasan penolakan"
                            class="w-full rounded-lg border border-[#E2E8EC] bg-white px-3 py-2 text-[13px] text-[#172B36] focus:border-[#B91C1C] focus:outline-none focus:ring-2 focus:ring-[#B91C1C]"></textarea>
                        @error('rejectReason')
                            <p class="mt-1 text-[12px] text-[#B91C1C]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <button x-ref="rejectCancel" type="button" wire:click="closeRejectModal"
                            wire:loading.attr="disabled" wire:target="reject"
                            class="inline-flex h-9 items-center rounded-lg border border-[#D5DEE3] bg-white px-4 text-[12px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] disabled:cursor-wait disabled:opacity-60">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="reject"
                            class="inline-flex h-9 items-center rounded-lg bg-[#B91C1C] px-4 text-[12px] font-semibold text-white shadow-sm transition hover:bg-[#991B1B] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#B91C1C] disabled:cursor-wait disabled:opacity-60">
                            Tolak
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
