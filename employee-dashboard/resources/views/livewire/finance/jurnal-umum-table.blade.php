<div class="space-y-4"
     x-data="{}"
     x-on:keydown.escape.window="
        if ($wire.showCoaPanel) { $wire.closeCoaPanel(); }
        else if ($wire.showDivisionDialog) { $wire.closeDivisionDialog(); }
        else if ($wire.showManualModal) { $wire.closeManualModal(); }
        else if ($wire.editingLineId) { $wire.closeLineEdit(); }
        else if ($wire.deletingLineId) { $wire.cancelDeleteLine(); }
     ">

    {{-- ── Header ─────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-[24px] font-bold leading-tight text-[#172B36]">Jurnal Umum</h1>
            <p class="mt-1 text-[13px] text-[#4B5563]">Catatan transaksi keuangan dengan sistem debit dan kredit.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @can('create', \App\Models\JurnalEntry::class)
                <button id="manualTrigger" type="button" wire:click="openManualModal"
                    class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-[#146C94] px-3.5 text-[13px] font-medium text-white transition hover:bg-[#105A7A] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]/40">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                    </svg>
                    Tambah jurnal
                </button>
            @endcan

            <button id="divisionTrigger" type="button" wire:click="toggleDivisionDialog"
                aria-expanded="{{ $showDivisionDialog ? 'true' : 'false' }}"
                class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] font-medium text-[#334155] transition hover:border-[#146C94] hover:text-[#146C94] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]/30">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path stroke-linecap="round" d="M12 11v5m0-8h.01"></path>
                </svg>
                Panduan kode divisi
            </button>

            <button id="coaTrigger" type="button" wire:click="toggleCoaPanel"
                aria-expanded="{{ $showCoaPanel ? 'true' : 'false' }}"
                class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] font-medium text-[#334155] transition hover:border-[#146C94] hover:text-[#146C94] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]/30">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75c-1.5-1.1-3.6-1.5-6-1.5-.8 0-1.5.1-2.1.2v13c.6-.1 1.3-.2 2.1-.2 2.4 0 4.5.4 6 1.5 1.5-1.1 3.6-1.5 6-1.5.8 0 1.5.1 2.1.2v-13c-.6-.1-1.3-.2-2.1-.2-2.4 0-4.5.4-6 1.5Zm0 0v13"></path>
                </svg>
                Daftar akun (COA)
            </button>
        </div>
    </div>

    {{-- ── Toolbar: periode + divisi + reset ──────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="relative">
            <div class="flex h-9 items-stretch overflow-hidden rounded-lg border border-[#D5DEE3] bg-white">
                <button type="button" wire:click="previousMonth" aria-label="Bulan sebelumnya"
                    class="w-9 text-[16px] text-[#4B5563] transition hover:bg-[#F5FAFC] hover:text-[#146C94]">&lsaquo;</button>
                <button type="button" wire:click="openMonthPicker" aria-expanded="{{ $showMonthPicker ? 'true' : 'false' }}"
                    class="min-w-[128px] border-x border-[#D5DEE3] px-3 text-[13px] font-medium text-[#334155] transition hover:text-[#146C94]">
                    {{ $this->periodeLabel }}
                </button>
                <button type="button" wire:click="nextMonth" aria-label="Bulan berikutnya"
                    class="w-9 text-[16px] text-[#4B5563] transition hover:bg-[#F5FAFC] hover:text-[#146C94]">&rsaquo;</button>
            </div>

            @if ($showMonthPicker)
                @php
                    $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                @endphp
                <div class="absolute left-0 top-full z-30 mt-1 w-64 rounded-lg border border-[#E2E8EC] bg-white p-3 shadow-lg">
                    <div class="mb-2 flex items-center justify-between">
                        <button type="button" wire:click="previousPickerYear" class="px-2 text-[14px] text-[#4B5563] hover:text-[#146C94]">&lsaquo;</button>
                        <span class="text-[13px] font-semibold text-[#334155]">{{ $pickerYear }}</span>
                        <button type="button" wire:click="nextPickerYear" class="px-2 text-[14px] text-[#4B5563] hover:text-[#146C94]">&rsaquo;</button>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        @foreach ($namaBulan as $index => $nama)
                            <button type="button" wire:click="selectMonth({{ $index + 1 }})"
                                class="rounded-md px-1 py-1.5 text-[12px] transition {{ (int) explode('-', $periode)[1] === $index + 1 ? 'bg-[#EAF4F8] font-semibold text-[#146C94]' : 'text-[#334155] hover:bg-[#F5FAFC]' }}">
                                {{ substr($nama, 0, 3) }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="min-w-[200px]">
            <x-finance.divisi-select
                name="divisiFilter"
                :value="$divisiFilter"
                :divisions="$divisions"
                placeholder="Semua divisi"
                class="h-9 min-w-[200px] rounded-lg px-3 text-[13px]"
            />
        </div>

        @if ($this->hasActiveFilter)
            <button type="button" wire:click="resetFilters"
                class="text-[13px] font-medium text-[#146C94] transition hover:underline">Reset</button>
        @endif
    </div>

    {{-- ── Feedback ───────────────────────────────────────────────────── --}}
    @if ($feedbackMessage)
        <div class="rounded-lg border px-3 py-2 text-[13px] {{ $feedbackType === 'error' ? 'border-red-200 bg-red-50 text-red-700' : 'border-teal-200 bg-teal-50 text-teal-800' }}"
             role="status">
            {{ $feedbackMessage }}
        </div>
    @endif

    {{-- ── Journal card ───────────────────────────────────────────────── --}}
    <div class="overflow-hidden rounded-xl border border-[#E2E8EC] bg-white">
        <div class="max-h-[62vh] overflow-auto rounded-t-xl">
            <table class="w-full min-w-[980px] border-collapse text-[13px]">
                <thead class="sticky top-0 z-10 bg-[#EAF4F8] text-[12px] font-medium text-[#334155]">
                    <tr>
                        <th scope="col" class="whitespace-nowrap px-3 py-2.5 text-left font-medium">Tanggal</th>
                        <th scope="col" class="px-3 py-2.5 text-left font-medium">Keterangan / nama akun</th>
                        <th scope="col" class="whitespace-nowrap px-3 py-2.5 text-left font-medium">Ref</th>
                        <th scope="col" title="Kode divisi" class="whitespace-nowrap px-3 py-2.5 text-left font-medium">Kode</th>
                        <th scope="col" class="whitespace-nowrap px-3 py-2.5 text-right font-medium">Debit (Rp)</th>
                        <th scope="col" class="whitespace-nowrap px-3 py-2.5 text-right font-medium">Kredit (Rp)</th>
                        <th scope="col" class="whitespace-nowrap px-3 py-2.5 text-right font-medium">Aksi</th>
                    </tr>
                </thead>

                @forelse ($entries as $entry)
                    @php
                        $lines = $entry->lines->sortByDesc(fn ($line) => $line->debit > 0)->values();
                        $transactionLabel = 'Transaksi ' . ($entry->ref_code ?? '#' . $entry->id);
                    @endphp
                    <tbody class="border-b border-[#E2E8EC] last:border-b-0" aria-label="{{ $transactionLabel }}">
                        @foreach ($lines as $lineIndex => $line)
                            <tr class="min-h-[30px] transition hover:bg-[#F5FAFC]"
                                @if ($lineIndex === 0) aria-label="{{ $transactionLabel }}" @endif>
                                <td class="whitespace-nowrap px-3 py-1 text-[#4B5563]">
                                    @if ($lineIndex === 0)
                                        {{ $entry->entry_date->locale('id')->translatedFormat('j M Y') }}
                                    @endif
                                </td>
                                <td class="px-3 py-1 text-[#172B36]">
                                    <span class="{{ $line->credit > 0 ? 'block pl-[18px]' : '' }} {{ $lineIndex === 0 ? 'font-medium' : '' }}">
                                        @if ($entry->source_url)
                                            <a href="{{ $entry->source_url }}" class="hover:text-[#146C94] hover:underline">{{ $line->account?->kode }} {{ $line->account?->nama }}</a>
                                        @else
                                            {{ $line->account?->kode }} {{ $line->account?->nama }}
                                        @endif
                                        @if ($line->keterangan)
                                            <span class="mt-0.5 block text-[11.5px] font-normal text-[#4B5563]">{{ $line->keterangan }}</span>
                                        @endif
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-1 font-mono text-[12px] text-[#4B5563]">
                                    {{ $line->account?->kode }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-1 font-mono text-[12px] text-[#4B5563]">
                                    @if ($lineIndex === 0)
                                        {{ $entry->divisi?->kode ?? '-' }}
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-1 text-right tabular-nums {{ $line->debit > 0 ? 'text-[#172B36]' : 'text-[#4B5563]' }}">
                                    {{ $line->debit > 0 ? number_format($line->debit, 0, ',', '.') : '-' }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-1 text-right tabular-nums {{ $line->credit > 0 ? 'text-[#172B36]' : 'text-[#4B5563]' }}">
                                    {{ $line->credit > 0 ? number_format($line->credit, 0, ',', '.') : '-' }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-1 text-right">
                                    @can('update', \App\Models\JurnalEntry::class)
                                        <button type="button" wire:click="openLineEdit({{ $line->id }})"
                                            class="text-[12px] font-medium text-[#146C94] hover:underline">Ubah</button>
                                    @endcan
                                    @can('delete', \App\Models\JurnalEntry::class)
                                        <span class="mx-1 text-[#CBD5E1]">|</span>
                                        <button type="button" wire:click="askDeleteLine({{ $line->id }})"
                                            class="text-[12px] font-medium text-[#B91C1C] hover:underline">Hapus</button>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                @empty
                    <tbody>
                        <tr>
                            <td colspan="7" class="px-4 py-14 text-center">
                                <p class="text-[14px] font-medium text-[#334155]">Belum ada jurnal untuk {{ $this->periodeLabel }}</p>
                                <p class="mx-auto mt-1 max-w-md text-[12px] text-[#4B5563]">
                                    Jurnal dibuat otomatis dari transaksi kas masuk, kas keluar, dan pencairan dana.
                                </p>
                            </td>
                        </tr>
                    </tbody>
                @endforelse
            </table>
        </div>

        {{-- Total row — selalu terlihat di bawah kartu --}}
        <div class="sticky bottom-0 z-10 flex flex-wrap items-center justify-between gap-3 rounded-b-xl border-t border-[#E2E8EC] bg-[#F8FAFC] px-4 py-2.5">
            <div class="flex items-center gap-2">
                <span class="text-[13px] font-semibold text-[#172B36]">Total</span>
                @if ($totals['debit'] === $totals['credit'])
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-[#7DD3C8] bg-[#D5F3F0] px-2 py-0.5 text-[11px] font-medium text-[#0D9488]">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#0D9488]"></span>
                        Seimbang
                    </span>
                @else
                    <span class="inline-flex items-center rounded-full border border-[#F5A3A3] bg-[#FDE0E0] px-2 py-0.5 text-[11px] font-medium text-[#B91C1C]">
                        Tidak seimbang: selisih Rp {{ number_format($totals['selisih'] ?? abs($totals['debit'] - $totals['credit']), 0, ',', '.') }}
                    </span>
                @endif
            </div>
            <div class="flex items-center gap-6 text-[13px] font-semibold tabular-nums text-[#172B36]">
                <span class="w-28 text-right">{{ number_format($totals['debit'], 0, ',', '.') }}</span>
                <span class="w-28 text-right">{{ number_format($totals['credit'], 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    {{-- ── Footer ─────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <span class="text-[12.5px] text-[#4B5563]">
            Menampilkan {{ $entries->firstItem() ?? 0 }}-{{ $entries->lastItem() ?? 0 }} dari {{ $entries->total() }} transaksi
        </span>
        @if ($entries->hasPages())
            {{ $entries->links() }}
        @endif
    </div>

    {{-- ── COA side panel (read-only, no backdrop) ────────────────────── --}}
    @if ($showCoaPanel)
        <div class="fixed inset-y-0 right-0 z-40 flex w-[400px] max-w-full flex-col border-l border-[#E2E8EC] bg-white shadow-xl max-[899px]:w-full"
             role="dialog" aria-label="Daftar akun (COA)" aria-modal="false">
            <div class="flex items-center justify-between border-b border-[#E2E8EC] px-4 py-3">
                <h2 class="text-[15px] font-semibold text-[#172B36]">Daftar akun (COA)</h2>
                <button type="button" wire:click="closeCoaPanel" aria-label="Tutup"
                    class="flex h-8 w-8 items-center justify-center rounded-lg text-[18px] text-[#4B5563] transition hover:bg-[#F5FAFC] hover:text-[#172B36]">&times;</button>
            </div>

            <div class="border-b border-[#EEF2F5] p-3">
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#94A3B8]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path stroke-linecap="round" d="m20 20-3.5-3.5"></path>
                    </svg>
                    <input type="text" wire:model.live.debounce.300ms="coaSearch"
                        placeholder="Cari kode atau nama akun"
                        class="h-9 w-full rounded-lg border border-[#D5DEE3] bg-white pl-9 pr-3 text-[13px] text-[#172B36] placeholder:text-[#94A3B8] focus:border-[#146C94] focus:outline-none">
                </div>
            </div>

            <div class="flex-1 overflow-y-auto">
                @forelse ($this->coaGroups as $kelompok => $accounts)
                    <div class="sticky top-0 z-10 bg-[#EAF4F8] px-4 py-1.5 text-[12px] font-semibold text-[#334155]">{{ $kelompok }}</div>
                    @foreach ($accounts as $akun)
                        <div class="flex min-h-[36px] items-center justify-between gap-3 border-b border-[#EEF2F5] px-4 py-2">
                            <span class="text-[13px] text-[#172B36]">{{ $akun->nama }}</span>
                            <span class="font-mono text-[12px] text-[#4B5563]">{{ $akun->kode }}</span>
                        </div>
                    @endforeach
                @empty
                    <p class="p-4 text-[13px] text-[#4B5563]">Akun tidak ditemukan</p>
                @endforelse
            </div>
        </div>
    @endif

    {{-- ── Panduan kode divisi (dialog) ───────────────────────────────── --}}
    @if ($showDivisionDialog)
        <div class="modal-overlay-in fixed inset-0 z-50 flex items-center justify-center p-4 bg-[rgba(15,40,55,.45)]"
             wire:click.self="closeDivisionDialog">
            <div class="modal-panel-in w-full max-w-[400px] overflow-hidden rounded-xl bg-white shadow-xl"
                 role="dialog" aria-modal="true" aria-label="Panduan kode divisi">
                <div class="border-b border-[#E2E8EC] px-4 py-3">
                    <h2 class="text-[15px] font-semibold text-[#172B36]">Panduan kode divisi</h2>
                </div>
                <div class="max-h-[60vh] overflow-y-auto">
                    <table class="w-full text-[13px]">
                        <thead class="bg-[#EAF4F8] text-[12px] font-medium text-[#334155]">
                            <tr>
                                <th scope="col" class="px-4 py-2 text-left font-medium">Nama divisi</th>
                                <th scope="col" class="px-4 py-2 text-left font-medium">Kode</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($divisions as $divisi)
                                <tr class="border-b border-[#EEF2F5]">
                                    <td class="px-4 py-2 text-[#172B36]">{{ $divisi->nama }}</td>
                                    <td class="px-4 py-2 font-mono text-[12px] text-[#4B5563]">{{ $divisi->kode }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-4 py-4 text-center text-[13px] text-[#4B5563]">Belum ada kode divisi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-end px-4 py-3">
                    <button type="button" wire:click="closeDivisionDialog"
                        class="h-9 rounded-lg border border-[#D5DEE3] bg-white px-4 text-[13px] font-medium text-[#334155] transition hover:border-[#146C94] hover:text-[#146C94]">Tutup</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Manual entry modal ─────────────────────────────────────────── --}}
    @if ($showManualModal)
        <div class="modal-overlay-in fixed inset-0 z-50 flex items-center justify-center p-4 bg-[rgba(15,40,55,.45)]"
             wire:click.self="closeManualModal">
            <div class="modal-panel-in flex max-h-[90vh] w-full max-w-[720px] flex-col overflow-hidden rounded-xl bg-white shadow-xl"
                 role="dialog" aria-modal="true" aria-label="Tambah jurnal">
                <div class="flex items-center justify-between border-b border-[#E2E8EC] px-4 py-3">
                    <h2 class="text-[15px] font-semibold text-[#172B36]">Tambah jurnal</h2>
                    <button type="button" wire:click="closeManualModal" aria-label="Tutup"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-[18px] text-[#4B5563] hover:bg-[#F5FAFC]">&times;</button>
                </div>

                <div class="space-y-4 overflow-y-auto px-4 py-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-[12px] font-medium text-[#334155]">Divisi <span class="text-red-500">*</span></label>
                            <x-finance.divisi-select
                                name="manualDivisiId"
                                :value="$manualDivisiId"
                                :divisions="$divisions"
                                placeholder="Pilih divisi"
                                class="h-9 w-full rounded-lg px-3 text-[13px]"
                            />
                            @error('manualDivisiId') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[12px] font-medium text-[#334155]">Tanggal <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="manualTanggal"
                                class="h-9 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] text-[#172B36] focus:border-[#146C94] focus:outline-none">
                            @error('manualTanggal') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <div class="mb-1 flex items-center justify-between">
                            <span class="text-[12px] font-medium text-[#334155]">Baris jurnal</span>
                            <button type="button" wire:click="addLine"
                                class="text-[12px] font-medium text-[#146C94] hover:underline">+ Tambah baris</button>
                        </div>

                        <div class="overflow-hidden rounded-lg border border-[#E2E8EC]">
                            <table class="w-full text-[12px]">
                                <thead class="bg-[#EAF4F8] text-[#334155]">
                                    <tr>
                                        <th scope="col" class="px-2 py-2 text-left font-medium">Akun</th>
                                        <th scope="col" class="w-28 px-2 py-2 text-right font-medium">Debit</th>
                                        <th scope="col" class="w-28 px-2 py-2 text-right font-medium">Kredit</th>
                                        <th scope="col" class="w-9 px-2 py-2"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($manualLines as $index => $line)
                                        <tr class="border-t border-[#EEF2F5] align-top" wire:key="line-{{ $manualLineIds[$index] }}">
                                            <td class="px-2 py-2">
                                                <x-finance.account-select
                                                    name="manualLines.{{ $index }}.account_id"
                                                    :value="$line['account_id'] ?? ''"
                                                    :groupedAccounts="$this->activeAccounts"
                                                    placeholder="Pilih akun"
                                                    class="h-8 w-full rounded-md px-2 text-[12px]"
                                                    wire:key="acc-sel-{{ $manualLineIds[$index] }}"
                                                />
                                                <input type="text" wire:model.live.debounce.300ms="manualLines.{{ $index }}.keterangan"
                                                    @if (blank($line['account_id'] ?? null)) disabled @endif
                                                    maxlength="500"
                                                    placeholder="{{ filled($line['account_id'] ?? null) ? 'Keterangan akun ini' : 'Pilih akun terlebih dahulu' }}"
                                                    class="mt-1 h-8 w-full rounded-md border border-[#D5DEE3] bg-white px-2 text-[12px] text-[#172B36] placeholder:text-[#94A3B8] focus:border-[#146C94] focus:outline-none disabled:bg-slate-50 disabled:text-[#94A3B8]">
                                            </td>
                                            <td class="px-2 py-2">
                                                <input type="number" min="0" step="1" wire:model.live="manualLines.{{ $index }}.debit"
                                                    class="h-8 w-full rounded-md border border-[#D5DEE3] px-2 text-right tabular-nums focus:border-[#146C94] focus:outline-none">
                                            </td>
                                            <td class="px-2 py-2">
                                                <input type="number" min="0" step="1" wire:model.live="manualLines.{{ $index }}.credit"
                                                    class="h-8 w-full rounded-md border border-[#D5DEE3] px-2 text-right tabular-nums focus:border-[#146C94] focus:outline-none">
                                            </td>
                                            <td class="px-2 py-2">
                                                <button type="button" wire:click="removeLine({{ $index }})" aria-label="Hapus baris"
                                                    class="flex h-8 w-8 items-center justify-center rounded-md text-[16px] text-[#94A3B8] hover:bg-red-50 hover:text-red-600">&times;</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="flex items-center justify-between rounded-lg bg-[#F8FAFC] px-3 py-2.5">
                        <div class="flex items-center gap-3 text-[12px]">
                            <span class="text-[#4B5563]">Debit: <strong class="tabular-nums text-[#172B36]">{{ number_format($this->manualTotals['debit'], 0, ',', '.') }}</strong></span>
                            <span class="text-[#4B5563]">Kredit: <strong class="tabular-nums text-[#172B36]">{{ number_format($this->manualTotals['credit'], 0, ',', '.') }}</strong></span>
                            @if ($this->manualTotals['balanced'])
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-[#7DD3C8] bg-[#D5F3F0] px-2 py-0.5 text-[11px] font-medium text-[#0D9488]">
                                    <span class="h-1.5 w-1.5 rounded-full bg-[#0D9488]"></span> Seimbang
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full border border-[#F5A3A3] bg-[#FDE0E0] px-2 py-0.5 text-[11px] font-medium text-[#B91C1C]">Tidak seimbang</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-[#E2E8EC] px-4 py-3">
                    <button type="button" wire:click="closeManualModal"
                        class="h-9 rounded-lg border border-[#D5DEE3] bg-white px-4 text-[13px] font-medium text-[#334155] hover:border-[#146C94]">Batal</button>
                    <button type="button" wire:click="saveManual" wire:loading.attr="disabled"
                        class="inline-flex h-9 items-center justify-center rounded-lg bg-[#146C94] px-4 text-[13px] font-medium text-white transition hover:bg-[#105A7A] disabled:cursor-not-allowed disabled:opacity-50">
                        <span wire:loading.remove wire:target="saveManual">Simpan</span>
                        <span wire:loading wire:target="saveManual">Menyimpan...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Ubah baris jurnal ───────────────────────────────────────────── --}}
    @if ($editingLineId)
        <div class="modal-overlay-in fixed inset-0 z-50 flex items-center justify-center p-4 bg-[rgba(15,40,55,.45)]"
             wire:click.self="closeLineEdit">
            <div class="modal-panel-in w-full max-w-[420px] rounded-xl bg-white shadow-xl" role="dialog" aria-modal="true" aria-label="Ubah baris jurnal">
                <div class="border-b border-[#E2E8EC] px-4 py-3">
                    <h2 class="text-[15px] font-semibold text-[#172B36]">Ubah baris jurnal</h2>
                </div>
                <div class="space-y-3 px-4 py-4">
                    <div>
                        <label class="mb-1 block text-[12px] font-medium text-[#334155]">Akun <span class="text-red-500">*</span></label>
                        <x-finance.account-select
                            name="editAccountId"
                            :value="$editAccountId"
                            :groupedAccounts="$this->activeAccounts"
                            placeholder="Pilih akun"
                            class="h-9 w-full rounded-lg px-3 text-[13px]"
                        />
                        @error('editAccountId') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-[12px] font-medium text-[#334155]">Keterangan <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="editKeterangan" maxlength="500"
                            class="h-9 w-full rounded-lg border border-[#D5DEE3] px-3 text-[13px] focus:border-[#146C94] focus:outline-none">
                        @error('editKeterangan') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-[12px] font-medium text-[#334155]">Debit</label>
                            <input type="number" min="0" step="1" wire:model.live="editDebit"
                                class="h-9 w-full rounded-lg border border-[#D5DEE3] px-3 text-right tabular-nums focus:border-[#146C94] focus:outline-none">
                        </div>
                        <div>
                            <label class="mb-1 block text-[12px] font-medium text-[#334155]">Kredit</label>
                            <input type="number" min="0" step="1" wire:model.live="editCredit"
                                class="h-9 w-full rounded-lg border border-[#D5DEE3] px-3 text-right tabular-nums focus:border-[#146C94] focus:outline-none">
                        </div>
                    </div>
                    @error('editDebit') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                    <p class="text-[11px] text-[#4B5563]">Satu baris hanya boleh berisi debit atau kredit.</p>
                </div>
                <div class="flex justify-end gap-2 border-t border-[#E2E8EC] px-4 py-3">
                    <button type="button" wire:click="closeLineEdit"
                        class="h-9 rounded-lg border border-[#D5DEE3] bg-white px-4 text-[13px] font-medium text-[#334155]">Batal</button>
                    <button type="button" wire:click="saveLine"
                        class="h-9 rounded-lg bg-[#146C94] px-4 text-[13px] font-medium text-white hover:bg-[#105A7A]">Simpan</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Hapus baris jurnal ──────────────────────────────────────────── --}}
    @if ($deletingLineId)
        <div class="modal-overlay-in fixed inset-0 z-50 flex items-center justify-center p-4 bg-[rgba(15,40,55,.45)]">
            <div class="modal-panel-in w-full max-w-[380px] rounded-xl bg-white p-4 shadow-xl" role="dialog" aria-modal="true" aria-label="Hapus baris jurnal">
                <h2 class="text-[15px] font-semibold text-[#172B36]">Hapus baris jurnal?</h2>
                <p class="mt-2 text-[13px] text-[#4B5563]">Baris yang dihapus akan dicatat pada log audit. Total jurnal dapat menjadi tidak seimbang.</p>
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" wire:click="cancelDeleteLine"
                        class="h-9 rounded-lg border border-[#D5DEE3] bg-white px-4 text-[13px] font-medium text-[#334155]">Batal</button>
                    <button type="button" wire:click="deleteLine"
                        class="h-9 rounded-lg bg-[#B91C1C] px-4 text-[13px] font-medium text-white hover:bg-[#991B1B]">Hapus</button>
                </div>
            </div>
        </div>
    @endif

    @script
        <script>
            $wire.on('focus-trigger', ({ id }) => {
                document.getElementById(id)?.focus();
            });
        </script>
    @endscript
</div>
