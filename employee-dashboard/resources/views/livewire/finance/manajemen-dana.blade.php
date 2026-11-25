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
    @laporan-toast.window="pushToast($event.detail)"
    @keydown.escape.window="
        if ($wire.get('showVerifikasiModal')) { $wire.closeVerifikasi(); }
        else if ($wire.get('showReportDetail')) { $wire.closeReportDetail(); }
    ">

    <style>
        [x-cloak] { display: none !important; }
        @keyframes laporan-toast-in {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .laporan-toast { animation: laporan-toast-in 0.18s ease-out both; }
        @media (prefers-reduced-motion: reduce) {
            .laporan-toast { animation: none; }
        }
    </style>

    {{-- Toast --}}
    <div class="pointer-events-none fixed left-1/2 top-5 z-[9999] flex w-full max-w-sm -translate-x-1/2 flex-col items-stretch gap-2 px-4"
        aria-live="polite">
        <template x-for="toast in toasts" :key="toast.id">
            <div class="laporan-toast pointer-events-auto flex items-center gap-3 rounded-xl border bg-white px-4 py-3 shadow-lg"
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

    <div class="relative overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">
        <x-table-loading-overlay target="tab, setTab, status, divisiId, previous, next, resetFilters, previousPage, nextPage, gotoPage" />
        {{-- Tabs --}}
        <div class="flex items-end gap-1 border-b border-[#E2E8EC] px-2 sm:px-4" role="tablist">
            <button type="button" wire:click="setTab('permintaan')"
                class="-mb-px inline-flex h-11 items-center gap-2 border-b-2 px-3 text-[14px] font-medium transition {{ $tab === 'permintaan' ? 'border-[#146C94] text-[#146C94]' : 'border-transparent text-[#4B5563] hover:text-[#172B36]' }}"
                aria-selected="{{ $tab === 'permintaan' ? 'true' : 'false' }}" role="tab">
                Permintaan dana
                <span class="text-[12px] font-normal text-[#4B5563]">{{ $jumlahMenungguPermintaan }}</span>
            </button>
            <button type="button" wire:click="setTab('laporan')"
                class="-mb-px inline-flex h-11 items-center gap-2 border-b-2 px-3 text-[14px] font-medium transition {{ $tab === 'laporan' ? 'border-[#146C94] text-[#146C94]' : 'border-transparent text-[#4B5563] hover:text-[#172B36]' }}"
                aria-selected="{{ $tab === 'laporan' ? 'true' : 'false' }}" role="tab">
                Verifikasi laporan L/R
                <span class="text-[12px] font-normal text-[#4B5563]">{{ $jumlahMenungguLaporan }}</span>
            </button>
        </div>

        {{-- Filters --}}
        <div class="flex flex-wrap items-center gap-2 border-b border-[#EEF2F5] px-4 py-3">
            @if ($tab === 'permintaan')
                <select wire:model.live="status" aria-label="Filter status"
                    class="h-9 rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] font-medium text-[#172B36] focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                    <option value="">Semua status</option>
                    <option value="menunggu">Menunggu</option>
                    <option value="disetujui">Disetujui</option>
                    <option value="ditolak">Ditolak</option>
                    <option value="dicairkan">Dicairkan</option>
                </select>
            @else
                <div class="inline-flex h-9 items-center rounded-lg border border-[#D5DEE3] bg-white">
                    <button type="button" wire:click="previous" aria-label="Periode sebelumnya"
                        class="flex h-full w-9 items-center justify-center rounded-l-lg text-[18px] leading-none text-[#4B5563] transition hover:bg-[#F5FAFC] hover:text-[#172B36]">&lsaquo;</button>
                    <span class="min-w-[118px] border-x border-[#EEF2F5] px-2 text-center text-[13px] font-medium text-[#172B36]">
                        {{ $this->periodeLabel() }}
                    </span>
                    <button type="button" wire:click="next" aria-label="Periode berikutnya"
                        class="flex h-full w-9 items-center justify-center rounded-r-lg text-[18px] leading-none text-[#4B5563] transition hover:bg-[#F5FAFC] hover:text-[#172B36]">&rsaquo;</button>
                </div>

                <select wire:model.live="status" aria-label="Filter status"
                    class="h-9 rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] font-medium text-[#172B36] focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                    <option value="">Semua status</option>
                    <option value="menunggu">Menunggu</option>
                    <option value="disetujui">Terverifikasi</option>
                    <option value="perlu_revisi">Perlu revisi</option>
                </select>
            @endif

            <select wire:model.live="divisiId" aria-label="Filter divisi"
                class="h-9 rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] font-medium text-[#172B36] focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                <option value="">Semua divisi</option>
                @foreach ($divisiList as $div)
                    <option value="{{ $div->id_divisi }}">{{ $div->nama_divisi }} ({{ $div->kode_divisi }})</option>
                @endforeach
            </select>

            @if ($hasActiveFilter)
                <button type="button" wire:click="resetFilters"
                    class="text-[12px] font-medium text-[#146C94] transition hover:underline">
                    Reset
                </button>
            @endif
        </div>

        @if ($tab === 'permintaan')
            {{-- Table: Permintaan Dana --}}
            <div class="overflow-x-auto">
                <table class="w-full min-w-[880px] border-collapse text-left">
                    <thead class="bg-[#EAF4F8] text-[#334155]">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-[12px] font-medium">Pengajuan</th>
                            <th scope="col" class="px-4 py-3 text-[12px] font-medium">Divisi / pengaju</th>
                            <th scope="col" class="px-4 py-3 text-right text-[12px] font-medium tabular-nums">Nominal</th>
                            <th scope="col" class="px-4 py-3 text-[12px] font-medium">Status</th>
                            <th scope="col" class="w-[84px] px-4 py-3 text-right text-[12px] font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EEF2F5]">
                        @forelse ($permintaan as $p)
                            @php
                                $meta = $this->statusMetaPermintaan($p->status);
                                $detailUrl = route('kadiv.keuangan.permintaan.show', $p->id_kpd);
                            @endphp
                            <tr wire:key="permintaan-{{ $p->id_kpd }}"
                                class="cursor-pointer transition hover:bg-[#F5FAFC]"
                                onclick="window.location.href='{{ $detailUrl }}'"
                                onkeydown="if (event.key === 'Enter') { window.location.href='{{ $detailUrl }}'; }"
                                tabindex="0" role="link" aria-label="Buka pengajuan {{ $p->nomor_pengajuan }}">
                                <td class="px-4 py-3 align-middle">
                                    <p class="max-w-[320px] truncate text-[13px] font-medium text-[#172B36]"
                                        title="{{ $p->judul_pengajuan }}">{{ $p->judul_pengajuan }}</p>
                                    <p class="mt-0.5 text-[12px] text-[#4B5563]">
                                        {{ $p->nomor_pengajuan }} &middot; {{ $this->tanggalPengajuan($p->created_at) }}
                                    </p>
                                </td>
                                <td class="px-4 py-3 align-middle">
                                    <p class="text-[13px] text-[#172B36]">{{ $p->divisi?->nama_divisi ?? '-' }}</p>
                                    <p class="mt-0.5 text-[12px] text-[#4B5563]">
                                        {{ $p->diajukanOleh?->karyawan?->nama ?? $p->diajukanOleh?->username ?? '-' }}
                                    </p>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right align-middle text-[13px] font-medium tabular-nums text-[#172B36]">
                                    {{ $this->rupiah($p->nominal_diajukan) }}
                                </td>
                                <td class="px-4 py-3 align-middle">
                                    <x-status-badge :status="$p->status" :label="$meta['label']" :palette="$meta" />
                                </td>
                                <td class="px-4 py-3 text-right align-middle">
                                    <a href="{{ $detailUrl }}" onclick="event.stopPropagation()"
                                        class="inline-flex h-8 items-center rounded-lg border border-[#D5DEE3] bg-white px-3 text-[12px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-[12px] text-[#4B5563]">
                                    Tidak ada permintaan dana ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-2 border-t border-[#E2E8EC] px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-[12px] text-[#4B5563]">
                    Menampilkan {{ $permintaan->firstItem() ?? 0 }}-{{ $permintaan->lastItem() ?? 0 }} dari {{ $permintaan->total() }} pengajuan
                </p>
                @if ($permintaan->hasPages())
                    <div>{{ $permintaan->withQueryString()->links() }}</div>
                @endif
            </div>
        @else
            {{-- Table: Verifikasi Laporan L/R --}}
            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] border-collapse text-left">
                    <thead class="bg-[#EAF4F8] text-[#334155]">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-[12px] font-medium">Divisi / pengirim</th>
                            <th scope="col" class="px-4 py-3 text-[12px] font-medium">Periode</th>
                            <th scope="col" class="px-4 py-3 text-right text-[12px] font-medium tabular-nums">Pendapatan</th>
                            <th scope="col" class="px-4 py-3 text-right text-[12px] font-medium tabular-nums">Pengeluaran</th>
                            <th scope="col" class="px-4 py-3 text-right text-[12px] font-medium tabular-nums">Laba / (rugi)</th>
                            <th scope="col" class="px-4 py-3 text-right text-[12px] font-medium tabular-nums">Margin</th>
                            <th scope="col" class="px-4 py-3 text-[12px] font-medium">Status</th>
                            <th scope="col" class="w-[168px] px-4 py-3 text-right text-[12px] font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EEF2F5]">
                        @forelse ($laporan as $lap)
                            @php
                                $meta = $this->statusMetaLaporan($lap->status_verifikasi);
                                $canVerify = $lap->isMenunggu() && auth()->user()?->can('verify', $lap);
                            @endphp
                            <tr wire:key="laporan-{{ $lap->id_klr }}"
                                wire:click="openReportDetail('{{ $lap->id_klr }}')"
                                wire:keydown.enter="openReportDetail('{{ $lap->id_klr }}')"
                                tabindex="0" role="button"
                                class="cursor-pointer transition hover:bg-[#F5FAFC]">
                                <td class="px-4 py-3 align-middle">
                                    <p class="text-[13px] font-medium text-[#172B36]">{{ $lap->divisi?->nama_divisi ?? '-' }}</p>
                                    <p class="mt-0.5 text-[12px] text-[#4B5563]">
                                        {{ $lap->pengirim?->karyawan?->nama ?? $lap->pengirim?->username ?? '-' }}
                                    </p>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 align-middle">
                                    <p class="text-[13px] text-[#172B36]">Minggu {{ $lap->minggu_ke }}</p>
                                    <p class="mt-0.5 text-[12px] text-[#4B5563]">
                                        {{ $this->formatRange($lap->tanggal_mulai ? \Carbon\Carbon::parse($lap->tanggal_mulai) : null, $lap->tanggal_selesai ? \Carbon\Carbon::parse($lap->tanggal_selesai) : null) }}
                                    </p>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right align-middle text-[13px] font-medium tabular-nums text-[#172B36]">
                                    {{ $this->rupiah($lap->total_pendapatan) }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right align-middle text-[13px] font-medium tabular-nums text-[#172B36]">
                                    {{ $this->rupiah($lap->total_pengeluaran) }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right align-middle text-[13px] font-semibold tabular-nums {{ $lap->laba_bersih < 0 ? 'text-[#B91C1C]' : 'text-[#047857]' }}">
                                    {{ $this->rupiah($lap->laba_bersih) }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right align-middle text-[13px] tabular-nums {{ $lap->margin_persen < 0 ? 'text-[#B91C1C]' : 'text-[#172B36]' }}">
                                    {{ $this->margin($lap->margin_persen) }}
                                </td>
                                <td class="px-4 py-3 align-middle">
                                    <x-status-badge :status="$lap->status_verifikasi" :label="$meta['label']" :palette="$meta" />
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right align-middle">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button"
                                            wire:click.stop="openReportDetail('{{ $lap->id_klr }}')"
                                            class="inline-flex h-8 items-center rounded-lg border border-[#D5DEE3] bg-white px-3 text-[12px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                                            Detail
                                        </button>
                                        @if ($canVerify)
                                            <button type="button"
                                                wire:click.stop="openVerifikasi('{{ $lap->id_klr }}')"
                                                class="inline-flex h-8 items-center rounded-lg bg-[#146C94] px-3 text-[12px] font-medium text-white shadow-sm transition hover:bg-[#0F5878] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                                                Verifikasi
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-10 text-center text-[12px] text-[#4B5563]">
                                    Belum ada laporan laba rugi yang masuk.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-2 border-t border-[#E2E8EC] px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-[12px] text-[#4B5563]">
                    Menampilkan {{ $laporan->firstItem() ?? 0 }}-{{ $laporan->lastItem() ?? 0 }} dari {{ $laporan->total() }} laporan
                </p>
                @if ($laporan->hasPages())
                    <div>{{ $laporan->withQueryString()->links() }}</div>
                @endif
            </div>
        @endif
    </div>

    {{-- Dialog verifikasi laporan --}}
    @if ($showVerifikasiModal && $verifikasiData)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="background-color: rgba(15, 40, 55, 0.45);" wire:click.self="closeVerifikasi"
            x-init="$nextTick(() => $refs.verifikasiCancel?.focus())">
            <div class="w-full max-w-[460px] rounded-xl bg-white p-5 shadow-xl" role="dialog" aria-modal="true"
                aria-labelledby="verifikasi-title">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 id="verifikasi-title" class="text-[16px] font-semibold text-[#172B36]">
                            Verifikasi laporan {{ $verifikasiData['divisi'] }}, Minggu {{ $verifikasiData['minggu'] }}?
                        </h3>
                        <p class="mt-1 text-[12px] text-[#4B5563]">{{ $verifikasiData['range'] }}</p>
                    </div>
                    <button type="button" wire:click="closeVerifikasi" wire:loading.attr="disabled"
                        wire:target="verifikasi" aria-label="Tutup dialog"
                        class="rounded-lg p-1 text-[#4B5563] transition hover:bg-[#F5FAFC] hover:text-[#172B36] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] disabled:cursor-wait disabled:opacity-60">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <dl class="mt-4 grid grid-cols-2 gap-3 rounded-lg border border-[#E2E8EC] bg-[#F8FAFB] p-3">
                    <div>
                        <dt class="text-[12px] text-[#4B5563]">Laba / (rugi)</dt>
                        <dd class="mt-1 text-[14px] font-semibold tabular-nums {{ $verifikasiData['laba_negatif'] ? 'text-[#B91C1C]' : 'text-[#047857]' }}">
                            {{ $verifikasiData['laba'] }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[12px] text-[#4B5563]">Margin</dt>
                        <dd class="mt-1 text-[14px] font-semibold tabular-nums {{ $verifikasiData['margin_negatif'] ? 'text-[#B91C1C]' : 'text-[#172B36]' }}">
                            {{ $verifikasiData['margin'] }}
                        </dd>
                    </div>
                </dl>

                <div class="mt-4">
                    <label for="catatanVerifikasi" class="mb-1 block text-[12px] font-medium text-[#172B36]">
                        Catatan <span class="font-normal text-[#4B5563]">(opsional)</span>
                    </label>
                    <textarea id="catatanVerifikasi" wire:model="catatanVerifikasi" rows="3"
                        placeholder="Tambahkan catatan verifikasi"
                        class="w-full rounded-lg border border-[#D5DEE3] bg-white px-3 py-2 text-[13px] text-[#172B36] placeholder:text-[#4B5563] focus:border-[#146C94] focus:outline-none focus:ring-2 focus:ring-[#146C94]"></textarea>
                    @error('catatanVerifikasi')
                        <p class="mt-1 text-[12px] text-[#B91C1C]">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-5 flex items-center justify-end gap-2">
                    <button x-ref="verifikasiCancel" type="button" wire:click="closeVerifikasi"
                        wire:loading.attr="disabled" wire:target="verifikasi"
                        class="inline-flex h-9 items-center rounded-lg border border-[#D5DEE3] bg-white px-4 text-[12px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] disabled:cursor-wait disabled:opacity-60">
                        Batal
                    </button>
                    <button type="button" wire:click="verifikasi" wire:loading.attr="disabled"
                        wire:target="verifikasi"
                        class="inline-flex h-9 items-center rounded-lg bg-[#146C94] px-4 text-[12px] font-semibold text-white shadow-sm transition hover:bg-[#0F5878] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] disabled:cursor-wait disabled:opacity-60">
                        Verifikasi
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Dialog detail laporan --}}
    @if ($showReportDetail && $reportDetail)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="background-color: rgba(15, 40, 55, 0.45);" wire:click.self="closeReportDetail"
            x-init="$nextTick(() => $refs.detailClose?.focus())">
            <div class="w-full max-w-[480px] rounded-xl bg-white p-5 shadow-xl" role="dialog" aria-modal="true"
                aria-labelledby="detail-laporan-title">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 id="detail-laporan-title" class="text-[16px] font-semibold text-[#172B36]">
                            Detail laporan {{ $reportDetail['divisi'] }}
                        </h3>
                        <p class="mt-1 text-[12px] text-[#4B5563]">
                            Minggu {{ $reportDetail['minggu'] }} &middot; {{ $reportDetail['range'] }}
                        </p>
                    </div>
                    <button x-ref="detailClose" type="button" wire:click="closeReportDetail" aria-label="Tutup dialog"
                        class="rounded-lg p-1 text-[#4B5563] transition hover:bg-[#F5FAFC] hover:text-[#172B36] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <dl class="mt-4 grid grid-cols-2 gap-3 rounded-lg border border-[#E2E8EC] bg-[#F8FAFB] p-3">
                    <div>
                        <dt class="text-[12px] text-[#4B5563]">Pendapatan</dt>
                        <dd class="mt-1 text-[13px] font-medium tabular-nums text-[#172B36]">{{ $reportDetail['pendapatan'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-[12px] text-[#4B5563]">Pengeluaran</dt>
                        <dd class="mt-1 text-[13px] font-medium tabular-nums text-[#172B36]">{{ $reportDetail['pengeluaran'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-[12px] text-[#4B5563]">Laba / (rugi)</dt>
                        <dd class="mt-1 text-[13px] font-semibold tabular-nums {{ $reportDetail['laba_negatif'] ? 'text-[#B91C1C]' : 'text-[#047857]' }}">{{ $reportDetail['laba'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-[12px] text-[#4B5563]">Margin</dt>
                        <dd class="mt-1 text-[13px] font-semibold tabular-nums {{ $reportDetail['margin_negatif'] ? 'text-[#B91C1C]' : 'text-[#172B36]' }}">{{ $reportDetail['margin'] }}</dd>
                    </div>
                </dl>

                <div class="mt-3 flex items-center justify-between gap-3">
                    <x-status-badge :status="$reportDetail['status']" :label="$reportDetail['status_meta']['label']"
                        :palette="$reportDetail['status_meta']" />
                    @if ($reportDetail['bukti'])
                        <a href="{{ \App\Support\FileStorage::url($reportDetail['bukti']) }}" target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex h-8 items-center rounded-lg border border-[#D5DEE3] bg-white px-3 text-[12px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC]">
                            Lihat bukti
                        </a>
                    @endif
                </div>

                <div class="mt-3">
                    <p class="text-[12px] text-[#4B5563]">Pengirim</p>
                    <p class="mt-0.5 text-[13px] text-[#172B36]">{{ $reportDetail['pengirim'] }}</p>
                </div>

                @if ($reportDetail['catatan'])
                    <div class="mt-3">
                        <p class="text-[12px] text-[#4B5563]">Catatan verifikasi</p>
                        <p class="mt-0.5 text-[13px] text-[#172B36]">{{ $reportDetail['catatan'] }}</p>
                    </div>
                @endif

                <div class="mt-5 flex justify-end">
                    <button type="button" wire:click="closeReportDetail"
                        class="inline-flex h-9 items-center rounded-lg border border-[#D5DEE3] bg-white px-4 text-[12px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
