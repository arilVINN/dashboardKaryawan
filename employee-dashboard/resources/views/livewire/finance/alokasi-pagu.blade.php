<div class="w-full"
    x-data="{
        toasts: [],
        toastSeq: 0,
        formatRupiah(value) {
            const digits = String(value ?? '').replace(/\D/g, '');
            return digits ? Number(digits).toLocaleString('id-ID') : '';
        },
        updateRupiah(event, property) {
            const digits = event.target.value.replace(/\D/g, '');
            event.target.value = this.formatRupiah(digits);
            $wire.set(property, digits, false);
        },
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
    @alokasi-toast.window="pushToast($event.detail)"
    @keydown.escape.window="
        if ($wire.get('showEditModal')) { $wire.closeEditModal(); }
        else if ($wire.get('showCreateModal')) { $wire.closeCreateModal(); }
    ">

    <style>
        [x-cloak] { display: none !important; }
        @keyframes alokasi-toast-in {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .alokasi-toast { animation: alokasi-toast-in 0.18s ease-out both; }
        @media (prefers-reduced-motion: reduce) {
            .alokasi-toast { animation: none; }
        }
    </style>

    {{-- Toast --}}
    <div class="pointer-events-none fixed left-1/2 top-5 z-[9999] flex w-full max-w-sm -translate-x-1/2 flex-col items-stretch gap-2 px-4"
        aria-live="polite">
        <template x-for="toast in toasts" :key="toast.id">
            <div class="alokasi-toast pointer-events-auto flex items-center gap-3 rounded-xl border border-[#E2E8EC] bg-white px-4 py-3 shadow-lg">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#D9F5E5] text-[#059669]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"></path>
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
            <x-page-heading title="Alokasi Pagu Anggaran" icon="division" />
            <p class="mt-1 text-[13px] text-[#4B5563]">
                Tetapkan dan kelola pagu anggaran operasional bulanan untuk masing-masing divisi.
            </p>
        </div>

    </div>

    {{-- Period switcher --}}
    <div class="mt-4 flex flex-wrap items-center gap-3">
        <div class="inline-flex h-9 shrink-0 items-stretch overflow-hidden rounded-lg border border-[#D5DEE3] bg-white"
            wire:loading.class="opacity-60">
            <button type="button" wire:click="previous" wire:loading.attr="disabled" wire:target="previous"
                aria-label="Periode sebelumnya"
                class="inline-flex h-9 w-9 items-center justify-center text-[#4B5563] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#146C94] disabled:cursor-wait">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m15 19-7-7 7-7"></path>
                </svg>
            </button>
            <button type="button" wire:click="openMonthPicker" aria-label="Pilih bulan, {{ $periodeLabel }}"
                class="flex h-9 min-w-[138px] items-center justify-center gap-1 border-x border-[#D5DEE3] px-3 text-[13px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#146C94]">
                <span aria-live="polite">{{ $periodeLabel }}</span>
                <svg class="h-3.5 w-3.5 text-[#4B5563]" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"></path>
                </svg>
            </button>
            <button type="button" wire:click="next" wire:loading.attr="disabled" wire:target="next"
                aria-label="Periode berikutnya"
                class="inline-flex h-9 w-9 items-center justify-center text-[#4B5563] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#146C94] disabled:cursor-wait">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"></path>
                </svg>
            </button>
        </div>
        <button type="button" wire:click="openCreateModal"
            class="inline-flex h-10 shrink-0 items-center gap-2 ml-auto rounded-lg bg-[#0097B2] px-4 text-[13px] font-semibold text-white shadow-sm transition hover:bg-[#008199] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] focus-visible:ring-offset-2">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14"></path>
            </svg>
            <span>Tetapkan alokasi</span>
        </button>
    </div>

    {{-- Alokasi table --}}
    <div class="mt-5 overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">
        <div class="flex flex-col gap-2 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <h2 class="text-[14px] font-semibold text-[#172B36]">Alokasi per divisi</h2>
            <div class="flex items-baseline gap-2">
                <span class="text-[13px] text-[#4B5563]">Total pagu bulan ini</span>
                <span class="text-[15px] font-semibold tabular-nums text-[#172B36]">{{ $this->rupiah($totalPagu) }}</span>
            </div>
        </div>

        @if ($alokasi->isEmpty())
            <div class="flex flex-col items-center justify-center border-t border-[#E2E8EC] px-6 py-12 text-center">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#E3F1F7] text-[#146C94]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v12m-6-6h12M6.75 3.75h10.5a1.5 1.5 0 0 1 1.5 1.5v13.5a1.5 1.5 0 0 1-1.5 1.5H6.75a1.5 1.5 0 0 1-1.5-1.5V5.25a1.5 1.5 0 0 1 1.5-1.5Z">
                        </path>
                    </svg>
                </span>
                <p class="mt-3 text-[14px] font-semibold text-[#172B36]">
                    Belum ada alokasi untuk {{ $periodeLabel }}
                </p>
                <p class="mt-1 max-w-md text-[13px] text-[#4B5563]">
                    Tetapkan pagu per divisi agar penyerapan dana bisa dipantau.
                </p>
                <button type="button" wire:click="openCreateModal"
                    class="mt-4 inline-flex h-10 items-center gap-2 rounded-lg bg-[#0097B2] px-4 text-[13px] font-semibold text-white shadow-sm transition hover:bg-[#008199] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] focus-visible:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14"></path>
                    </svg>
                    <span>Tetapkan alokasi</span>
                </button>
            </div>
        @else
            <div class="overflow-x-auto border-t border-[#E2E8EC]">
                <table class="w-full min-w-[760px] border-collapse text-left">
                    <thead class="bg-[#EAF4F8] text-[#334155]">
                        <tr>
                            <th scope="col" class="whitespace-nowrap px-4 py-3 text-[12px] font-medium">Divisi</th>
                            <th scope="col" class="whitespace-nowrap px-4 py-3 text-right text-[12px] font-medium">Nominal pagu</th>
                            <th scope="col" class="whitespace-nowrap px-4 py-3 text-[12px] font-medium">Keterangan</th>
                            <th scope="col" class="whitespace-nowrap px-4 py-3 text-[12px] font-medium">Ditetapkan oleh</th>
                            <th scope="col" class="whitespace-nowrap px-4 py-3 text-right text-[12px] font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EEF2F5]">
                        @foreach ($alokasi as $item)
                            <tr wire:key="alokasi-{{ $item->id_kad }}" class="h-[56px]">
                                <td class="px-4 py-2.5">
                                    <p class="text-[13px] font-medium text-[#172B36]">{{ $item->divisi?->nama_divisi ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-2.5 text-right text-[13px] font-medium tabular-nums text-[#172B36]">
                                    {{ $this->rupiah($item->nominal_alokasi) }}
                                </td>
                                <td class="px-4 py-2.5 text-[13px] text-[#4B5563]">
                                    <span class="block max-w-[220px] truncate" title="{{ $item->keterangan ?? '-' }}">{{ $item->keterangan ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-[13px] text-[#4B5563]">
                                    {{ $item->createdBy?->karyawan?->nama ?? $item->createdBy?->username ?? 'Finance' }}
                                </td>
                                <td class="px-4 py-2.5 text-right">
                                    <button type="button" wire:click="openEditModal('{{ $item->id_kad }}')"
                                        class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-[#D5DEE3] bg-white px-3 text-[12px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                                        <svg class="h-3.5 w-3.5 text-[#4B5563]" fill="none" stroke="currentColor"
                                            stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.862 4.487Zm0 0L19.5 7.125">
                                            </path>
                                        </svg>
                                        <span>Edit</span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-[#EEF2F5] px-4 py-3 text-[12px] text-[#4B5563] sm:px-5">
                Menampilkan {{ $alokasi->count() }} dari {{ $totalDivisi }} divisi
            </div>
        @endif
    </div>

    {{-- Create dialog --}}
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(15, 40, 55, 0.45);"
            wire:click.self="closeCreateModal" x-init="$nextTick(() => $refs.createCancel?.focus())">
            <div class="w-full max-w-[460px] rounded-xl bg-white p-5 shadow-xl" role="dialog" aria-modal="true"
                aria-labelledby="create-alokasi-title">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 id="create-alokasi-title" class="text-[16px] font-semibold text-[#172B36]">Tetapkan alokasi</h3>
                        <p class="mt-0.5 text-[13px] text-[#4B5563]">{{ $periodeLabel }}</p>
                    </div>
                    <button type="button" wire:click="closeCreateModal" aria-label="Tutup dialog"
                        class="rounded-lg p-1 text-[#4B5563] transition hover:bg-[#F5FAFC] hover:text-[#172B36] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveCreate" class="mt-4 space-y-4">
                    @if ($divisiTersedia->isEmpty())
                        <div>
                            <p class="mb-1 text-[12px] font-medium text-[#172B36]">Divisi</p>
                            <p class="rounded-lg border border-[#E2E8EC] bg-[#F4F7F9] px-3 py-2 text-[12px] text-[#4B5563]">
                                Semua divisi sudah memiliki pagu untuk {{ $periodeLabel }}.
                            </p>
                        </div>
                    @else
                        <div>
                            <label for="createDivisiId" class="mb-1 block text-[12px] font-medium text-[#172B36]">Divisi</label>
                            <select id="createDivisiId" wire:model="createDivisiId"
                                class="h-10 w-full rounded-lg border border-[#E2E8EC] bg-white px-3 text-[13px] text-[#172B36] focus:border-[#146C94] focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                                <option value="">Pilih divisi</option>
                                @foreach ($divisiTersedia as $div)
                                    <option value="{{ $div->id_divisi }}">{{ $div->nama_divisi }} ({{ $div->kode_divisi }})</option>
                                @endforeach
                            </select>
                            @error('createDivisiId')
                                <p class="mt-1 text-[12px] text-[#B91C1C]">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div>
                        <label for="createNominal" class="mb-1 block text-[12px] font-medium text-[#172B36]">Nominal pagu</label>
                        <input id="createNominal" type="text" inputmode="numeric"
                            value="{{ $createNominal !== '' ? number_format((int) $createNominal, 0, ',', '.') : '' }}"
                            @input="updateRupiah($event, 'createNominal')" placeholder="0"
                            class="h-10 w-full rounded-lg border border-[#E2E8EC] bg-white px-3 text-[13px] tabular-nums text-[#172B36] focus:border-[#146C94] focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                        @error('createNominal')
                            <p class="mt-1 text-[12px] text-[#B91C1C]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="createKeterangan" class="mb-1 block text-[12px] font-medium text-[#172B36]">Keterangan</label>
                        <textarea id="createKeterangan" wire:model="createKeterangan" rows="3"
                            placeholder="Catatan peruntukan pagu..."
                            class="w-full rounded-lg border border-[#E2E8EC] bg-white px-3 py-2 text-[13px] text-[#172B36] focus:border-[#146C94] focus:outline-none focus:ring-2 focus:ring-[#146C94]"></textarea>
                        @error('createKeterangan')
                            <p class="mt-1 text-[12px] text-[#B91C1C]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3">
                        <button x-ref="createCancel" type="button" wire:click="closeCreateModal"
                            class="inline-flex h-9 items-center rounded-lg border border-[#D5DEE3] bg-white px-4 text-[12px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                            Batal
                        </button>
                        @if ($divisiTersedia->isNotEmpty())
                            <button type="submit" wire:loading.attr="disabled" wire:target="saveCreate"
                                class="inline-flex h-9 items-center rounded-lg bg-[#0097B2] px-4 text-[12px] font-semibold text-white shadow-sm transition hover:bg-[#008199] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] disabled:cursor-wait disabled:opacity-60">
                                Tetapkan alokasi
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Edit dialog --}}
    @if ($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(15, 40, 55, 0.45);"
            wire:click.self="closeEditModal" x-init="$nextTick(() => $refs.editCancel?.focus())">
            <div class="w-full max-w-[460px] rounded-xl bg-white p-5 shadow-xl" role="dialog" aria-modal="true"
                aria-labelledby="edit-alokasi-title">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 id="edit-alokasi-title" class="text-[16px] font-semibold text-[#172B36]">
                            Edit pagu: {{ $editDivisiNama }}
                        </h3>
                        <p class="mt-0.5 text-[13px] text-[#4B5563]">{{ $editPeriodeLabel }} · {{ $editDivisiKode }}</p>
                    </div>
                    <button type="button" wire:click="closeEditModal" aria-label="Tutup dialog"
                        class="rounded-lg p-1 text-[#4B5563] transition hover:bg-[#F5FAFC] hover:text-[#172B36] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveEdit" class="mt-4 space-y-4">
                    <div>
                        <div class="flex items-baseline justify-between gap-2">
                            <label for="editNominal" class="mb-1 block text-[12px] font-medium text-[#172B36]">Nominal pagu</label>
                            <span class="text-[12px] text-[#4B5563]">
                                Sebelumnya Rp {{ number_format((int) $editNominalLama, 0, ',', '.') }}
                            </span>
                        </div>
                        <input id="editNominal" type="text" inputmode="numeric"
                            value="{{ $editNominal !== '' ? number_format((int) $editNominal, 0, ',', '.') : '' }}"
                            @input="updateRupiah($event, 'editNominal')"
                            class="h-10 w-full rounded-lg border border-[#E2E8EC] bg-white px-3 text-[13px] tabular-nums text-[#172B36] focus:border-[#146C94] focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                        @error('editNominal')
                            <p class="mt-1 text-[12px] text-[#B91C1C]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="editKeterangan" class="mb-1 block text-[12px] font-medium text-[#172B36]">
                            Alasan perubahan <span class="text-[#B91C1C]">*</span>
                        </label>
                        <textarea id="editKeterangan" wire:model="editKeterangan" rows="3" required
                            placeholder="Contoh: penambahan kebutuhan lisensi"
                            class="w-full rounded-lg border border-[#E2E8EC] bg-white px-3 py-2 text-[13px] text-[#172B36] focus:border-[#146C94] focus:outline-none focus:ring-2 focus:ring-[#146C94]"></textarea>
                        @error('editKeterangan')
                            <p class="mt-1 text-[12px] text-[#B91C1C]">{{ $message }}</p>
                        @enderror
                    </div>

                    <p class="text-[12px] text-[#4B5563]">
                        Perubahan dicatat: siapa, kapan, nilai lama dan baru.
                    </p>

                    <div class="flex items-center justify-end gap-2 pt-3">
                        <button x-ref="editCancel" type="button" wire:click="closeEditModal"
                            class="inline-flex h-9 items-center rounded-lg border border-[#D5DEE3] bg-white px-4 text-[12px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveEdit"
                            class="inline-flex h-9 items-center rounded-lg bg-[#146C94] px-4 text-[12px] font-semibold text-white shadow-sm transition hover:bg-[#0F5A7C] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] disabled:cursor-wait disabled:opacity-60">
                            Simpan perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
    @if ($showMonthPicker)
        @include('components.finance.month-picker')
    @endif
</div>
