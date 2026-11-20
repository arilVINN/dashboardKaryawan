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
    @permintaan-toast.window="pushToast($event.detail)"
    @keydown.escape.window="
        if ($wire.get('showApproveModal')) { $wire.closeApproveModal(); }
        else if ($wire.get('showRejectModal')) { $wire.closeRejectModal(); }
    ">

    <style>
        [x-cloak] { display: none !important; }
        @keyframes permintaan-toast-in {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .permintaan-toast { animation: permintaan-toast-in 0.18s ease-out both; }
        @media (prefers-reduced-motion: reduce) {
            .permintaan-toast { animation: none; }
        }
    </style>

    {{-- Toast --}}
    <div class="pointer-events-none fixed left-1/2 top-5 z-[9999] flex w-full max-w-sm -translate-x-1/2 flex-col items-stretch gap-2 px-4"
        aria-live="polite">
        <template x-for="toast in toasts" :key="toast.id">
            <div class="permintaan-toast pointer-events-auto flex items-center gap-3 rounded-xl border bg-white px-4 py-3 shadow-lg"
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

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <x-page-heading title="Permintaan Dana Masuk" icon="document" />
            <p class="mt-1 text-[13px] text-[#4B5563]">
                Review dan berikan persetujuan pengajuan anggaran operasional dari kepala divisi.
            </p>
        </div>

        @if ($pendingCount > 0)
            <button type="button" wire:click="filterMenunggu" aria-label="Tampilkan pengajuan menunggu"
                class="inline-flex h-8 shrink-0 items-center gap-2 self-start rounded-full border border-[#F5C85C] bg-[#FEF0C3] px-3.5 text-[13px] font-medium text-[#172B36] transition hover:bg-[#FDF3D3] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#F5C85C]">
                <svg class="h-4 w-4 text-[#B45309]" fill="none" stroke="currentColor" stroke-width="1.8"
                    viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="8.25"></circle>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5V12l3 1.5"></path>
                </svg>
                <span>{{ $pendingCount }} menunggu · Rp {{ number_format($pendingSum, 0, ',', '.') }}</span>
            </button>
        @endif
    </div>

    {{-- Filters --}}
    <div class="mt-4 flex flex-wrap items-center gap-3">
        <select wire:model.live="status" aria-label="Filter status"
            class="h-9 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] font-medium text-[#172B36] focus:outline-none focus:ring-2 focus:ring-[#146C94] sm:w-56">
            <option value="">Semua status</option>
            <option value="menunggu">Menunggu</option>
            <option value="disetujui">Disetujui</option>
            <option value="ditolak">Ditolak</option>
            <option value="dicairkan">Dicairkan</option>
        </select>

        <select wire:model.live="divisiId" aria-label="Filter divisi"
            class="h-9 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] font-medium text-[#172B36] focus:outline-none focus:ring-2 focus:ring-[#146C94] sm:w-64">
            <option value="">Semua divisi</option>
            @foreach ($divisiList as $div)
                <option value="{{ $div->id_divisi }}">{{ $div->nama_divisi }} ({{ $div->kode_divisi }})</option>
            @endforeach
        </select>

        @if ($hasActiveFilter)
            <button type="button" wire:click="resetFilters"
                class="text-[12px] font-medium text-[#146C94] hover:underline">
                Reset
            </button>
        @endif
    </div>

    {{-- Table --}}
    <div class="mt-5 overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">
        @if ($permintaan->isEmpty())
            <div class="flex flex-col items-center justify-center px-6 py-12 text-center">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#E3F1F7] text-[#146C94]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5v10.5a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V6.75Zm0 0L12 12.75l8.25-6">
                        </path>
                    </svg>
                </span>
                <p class="mt-3 text-[14px] font-semibold text-[#172B36]">Tidak ada pengajuan</p>
                <p class="mt-1 max-w-md text-[13px] text-[#4B5563]">
                    @if ($hasActiveFilter)
                        Coba ubah filter status atau divisi.
                    @else
                        Pengajuan dana yang masuk akan muncul di sini.
                    @endif
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1040px] border-collapse text-left">
                    <thead class="bg-[#EAF4F8] text-[#334155]">
                        <tr>
                            <th scope="col" class="whitespace-nowrap px-4 py-3 text-[12px] font-medium">Pengajuan</th>
                            <th scope="col" class="whitespace-nowrap px-4 py-3 text-[12px] font-medium">Divisi / pengaju</th>
                            <th scope="col" class="whitespace-nowrap px-4 py-3 text-right text-[12px] font-medium">Nominal</th>
                            <th scope="col" class="whitespace-nowrap px-4 py-3 text-[12px] font-medium">Tanggal pengajuan</th>
                            <th scope="col" class="whitespace-nowrap px-4 py-3 text-[12px] font-medium">Status</th>
                            <th scope="col" class="whitespace-nowrap px-4 py-3 text-right text-[12px] font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EEF2F5]">
                        @foreach ($permintaan as $p)
                            @php
                                $meta = $this->statusMeta($p->status);
                                $canDecide = $p->isMenunggu() && auth()->user()?->can('approve', $p);
                                $detailUrl = route('kadiv.keuangan.permintaan.show', $p->id_kpd);
                            @endphp
                            <tr wire:key="permintaan-{{ $p->id_kpd }}" class="h-[60px] transition hover:bg-[#F5FAFC]">
                                <td class="px-4 py-2 align-middle">
                                    <p class="max-w-[280px] truncate text-[13px] font-medium leading-tight text-[#172B36]"
                                        title="{{ $p->judul_pengajuan }}">{{ $p->judul_pengajuan }}</p>
                                    @if (trim((string) $p->keperluan) !== '' && $p->keperluan !== $p->judul_pengajuan)
                                        <p class="mt-0.5 max-w-[280px] truncate text-[12px] leading-tight text-[#4B5563]"
                                            title="{{ $p->keperluan }}">{{ $p->keperluan }}</p>
                                    @endif
                                </td>

                                <td class="px-4 py-2 align-middle">
                                    <p class="text-[13px] font-medium text-[#172B36]">{{ $p->divisi?->nama_divisi ?? '-' }}</p>
                                    <p class="mt-0.5 text-[12px] text-[#4B5563]">
                                        {{ $p->diajukanOleh?->karyawan?->nama ?? $p->diajukanOleh?->username ?? '-' }}
                                    </p>
                                </td>

                                <td class="px-4 py-2 text-right align-middle text-[13px] font-medium tabular-nums text-[#172B36]">
                                    {{ $this->rupiah($p->nominal_diajukan) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-2 align-middle">
                                    <p class="text-[13px] font-medium text-[#172B36]">{{ $p->created_at?->format('d M Y') }}</p>
                                    <p class="mt-0.5 text-[12px] text-[#4B5563]">{{ $p->created_at?->format('H:i') }}</p>
                                </td>

                                <td class="px-4 py-2 align-middle">
                                    <x-status-badge :status="$p->status" :label="$meta['label']" :palette="$meta" dot />
                                    @if ($p->status !== \App\Models\KPermintDana::STATUS_MENUNGGU && $p->disetujuiOleh)
                                        <p class="mt-1 text-[12px] text-[#4B5563]">
                                            oleh {{ $p->disetujuiOleh?->karyawan?->nama ?? $p->disetujuiOleh?->username }}, {{ ($p->tanggal_disetujui ?? $p->updated_at)?->format('d M') }}
                                        </p>
                                    @endif
                                </td>

                                <td class="px-4 py-2 text-right align-middle">
                                    <div class="inline-flex items-center justify-end gap-1.5">
                                        <a href="{{ $detailUrl }}"
                                            class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-[#D5DEE3] bg-white px-3 text-[12px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                                            <svg class="h-3.5 w-3.5 text-[#4B5563]" fill="none" stroke="currentColor"
                                                stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z">
                                                </path>
                                                <circle cx="12" cy="12" r="2.25"></circle>
                                            </svg>
                                            <span>Detail</span>
                                        </a>

                                        @if ($canDecide)
                                            <button type="button" wire:click="openApproveModal('{{ $p->id_kpd }}')"
                                                class="inline-flex h-8 items-center rounded-lg bg-[#047857] px-3 text-[12px] font-medium text-white transition hover:bg-[#036847] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#047857]">
                                                Setujui
                                            </button>
                                            <button type="button" wire:click="openRejectModal('{{ $p->id_kpd }}')"
                                                class="inline-flex h-8 items-center rounded-lg border border-[#FCA5A5] bg-white px-3 text-[12px] font-medium text-[#B91C1C] transition hover:bg-[#FEF2F2] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#B91C1C]">
                                                Tolak
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-2 border-t border-[#EEF2F5] px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-[12px] text-[#4B5563]">
                    Menampilkan {{ $permintaan->firstItem() }}-{{ $permintaan->lastItem() }} dari {{ $permintaan->total() }} pengajuan
                </p>
                @if ($permintaan->hasPages())
                    <div>{{ $permintaan->links() }}</div>
                @endif
            </div>
        @endif
    </div>

    {{-- Approve dialog --}}
    @if ($showApproveModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="background-color: rgba(15, 40, 55, 0.45);" wire:click.self="closeApproveModal"
            x-init="$nextTick(() => $refs.approveCancel?.focus())">
            <div class="w-full max-w-[460px] rounded-xl bg-white p-5 shadow-xl" role="dialog" aria-modal="true"
                aria-labelledby="approve-title">
                <div class="flex items-start justify-between gap-4">
                    <h3 id="approve-title" class="text-[16px] font-semibold text-[#172B36]">
                        Setujui pengajuan {{ $approveNomor }}?
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
                    <span class="font-medium text-[#172B36]">{{ $approveJudul }}</span> sebesar
                    <span class="font-semibold text-[#172B36]">Rp {{ number_format((int) $approveNominal, 0, ',', '.') }}</span>
                    dari <span class="font-medium text-[#172B36]">{{ $approveDivisi }}</span> akan disetujui.
                </p>

                <div class="mt-5 flex items-center justify-end gap-2">
                    <button x-ref="approveCancel" type="button" wire:click="closeApproveModal"
                        wire:loading.attr="disabled" wire:target="approve"
                        class="inline-flex h-9 items-center rounded-lg border border-[#D5DEE3] bg-white px-4 text-[12px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] disabled:cursor-wait disabled:opacity-60">
                        Batal
                    </button>
                    <button type="button" wire:click="approve" wire:loading.attr="disabled" wire:target="approve"
                        class="inline-flex h-9 items-center rounded-lg bg-[#047857] px-4 text-[12px] font-semibold text-white shadow-sm transition hover:bg-[#036847] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#047857] disabled:cursor-wait disabled:opacity-60">
                        Setujui
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
                aria-labelledby="reject-title">
                <div class="flex items-start justify-between gap-4">
                    <h3 id="reject-title" class="text-[16px] font-semibold text-[#172B36]">
                        Tolak pengajuan {{ $rejectNomor }}?
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
