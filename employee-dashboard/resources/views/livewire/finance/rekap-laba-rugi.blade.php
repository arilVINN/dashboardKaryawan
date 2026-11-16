<div class="w-full">
    {{-- Header + outlined export --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <x-page-heading title="Rekap Laba / Rugi per Divisi" icon="analytics" />
        </div>

        @php
            $exportParams = ['periode' => $periode];
            if ($mingguKe !== '') {
                $exportParams['minggu_ke'] = $mingguKe;
            }
        @endphp

        <a href="{{ route('kadiv.keuangan.exportRekapDivisi', $exportParams) }}"
            class="inline-flex h-10 shrink-0 items-center gap-2 self-start rounded-lg border border-[#047857] bg-[#047857] px-4 text-[13px] font-medium text-white shadow-sm transition hover:bg-[#065F46] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#047857] focus-visible:ring-offset-2">
            <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" stroke-width="1.8"
                viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 10.5v6m0 0-3-3m3 3 3-3M6.75 3.75h10.5a1.5 1.5 0 0 1 1.5 1.5v13.5a1.5 1.5 0 0 1-1.5 1.5H6.75a1.5 1.5 0 0 1-1.5-1.5V5.25a1.5 1.5 0 0 1 1.5-1.5Z">
                </path>
            </svg>
            <span>Export ke Excel (.xlsx)</span>
        </a>
    </div>

    {{-- Toolbar: period switcher + week select --}}
    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <div
            class="inline-flex h-9 shrink-0 items-stretch overflow-hidden rounded-lg border border-[#D5DEE3] bg-white"
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

        <select wire:model.live="mingguKe" wire:loading.attr="disabled"
            aria-label="Pilih minggu laporan"
            class="h-9 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] font-medium text-[#172B36] focus:outline-none focus:ring-2 focus:ring-[#146C94] disabled:cursor-wait sm:w-[280px]">
            <option value="">Semua minggu (akumulasi bulan)</option>
            @for ($m = 1; $m <= 5; $m++)
                <option value="{{ $m }}">Minggu ke-{{ $m }}</option>
            @endfor
        </select>
    </div>

    {{-- Summary cards --}}
    <div class="mt-5 grid grid-cols-1 gap-4 min-[560px]:grid-cols-2 min-[900px]:grid-cols-4">
        <div class="flex min-h-[120px] flex-col justify-between gap-2 rounded-xl border border-[#E2E8EC] bg-white p-3.5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <p class="text-[13px] font-medium text-[#4B5563]">Total pendapatan</p>
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#E3F1F7] text-[#146C94]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m0 0-6 6m6-6 6 6"></path>
                    </svg>
                </span>
            </div>
            <p class="text-[24px] font-semibold leading-tight tabular-nums text-[#172B36]">
                {{ \App\Helpers\FormatAkuntansi::formatRupiah($grandPendapatan) }}
            </p>
        </div>

        <div class="flex min-h-[120px] flex-col justify-between gap-2 rounded-xl border border-[#E2E8EC] bg-white p-3.5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <p class="text-[13px] font-medium text-[#4B5563]">Total biaya / beban</p>
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#E3F1F7] text-[#146C94]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m0 0-6-6m6 6 6-6"></path>
                    </svg>
                </span>
            </div>
            <p class="text-[24px] font-semibold leading-tight tabular-nums text-[#172B36]">
                {{ \App\Helpers\FormatAkuntansi::formatRupiah($grandPengeluaran) }}
            </p>
        </div>

        <div class="flex min-h-[120px] flex-col justify-between gap-2 rounded-xl border border-[#E2E8EC] bg-white p-3.5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <p class="text-[13px] font-medium text-[#4B5563]">Total laba / (rugi)</p>
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#E3F1F7] text-[#146C94]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <circle cx="12" cy="12" r="8.25"></circle>
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M14.25 9.6c-.52-.72-1.36-1.1-2.25-1.1-1.24 0-2.25.9-2.25 2s1.01 2 2.25 2 2.25.9 2.25 2-1.01 2-2.25 2c-.89 0-1.73-.38-2.25-1.1M12 7.25v9.5">
                        </path>
                    </svg>
                </span>
            </div>
            <p class="text-[24px] font-semibold leading-tight tabular-nums {{ $grandLaba > 0 ? 'text-[#047857]' : ($grandLaba < 0 ? 'text-[#B91C1C]' : 'text-[#172B36]') }}">
                {{ \App\Helpers\FormatAkuntansi::formatRupiah($grandLaba) }}
            </p>
        </div>

        <div class="flex min-h-[120px] flex-col justify-between gap-2 rounded-xl border border-[#E2E8EC] bg-white p-3.5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <p class="text-[13px] font-medium text-[#4B5563]">Margin laba keseluruhan</p>
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#E3F1F7] text-[#146C94]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19 5-14 14"></path>
                        <circle cx="7.5" cy="7.5" r="1.5"></circle>
                        <circle cx="16.5" cy="16.5" r="1.5"></circle>
                    </svg>
                </span>
            </div>
            <p class="text-[24px] font-semibold leading-tight tabular-nums text-[#172B36]">
                {{ \App\Helpers\FormatAkuntansi::formatMargin($grandMargin) }}
            </p>
        </div>
    </div>

    {{-- Table card --}}
    <div class="mt-5 overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">
        <div class="px-4 py-4 sm:px-5">
            <h2 class="text-[14px] font-semibold text-[#172B36]">Per divisi</h2>
            <p class="mt-0.5 text-[12px] text-[#4B5563]">{{ $periodeSubtitle }}</p>
        </div>

        @if ($rekapDivisi->isEmpty())
            <div class="flex flex-col items-center justify-center border-t border-[#E2E8EC] px-6 py-12 text-center">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#E3F1F7] text-[#146C94]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4.5 20.25h15M6.75 16.5v-4.5m5.25 4.5V6.75m5.25 9.75v-7.5">
                        </path>
                    </svg>
                </span>
                <p class="mt-3 text-[14px] font-semibold text-[#172B36]">
                    Belum ada laporan keuangan untuk {{ $periodeLabel }}
                </p>
                <p class="mt-1 max-w-md text-[13px] text-[#4B5563]">
                    Laporan laba rugi yang sudah diverifikasi akan muncul di sini.
                </p>
            </div>
        @else
            <div class="overflow-x-auto border-t border-[#E2E8EC]">
                <table class="w-full min-w-[820px] border-collapse text-left">
                    <thead class="bg-[#EAF4F8] text-[#334155]">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-[12px] font-medium">Divisi</th>
                            <th scope="col" class="px-4 py-3 text-right text-[12px] font-medium">Pendapatan</th>
                            <th scope="col" class="px-4 py-3 text-right text-[12px] font-medium">Biaya / beban</th>
                            <th scope="col" class="px-4 py-3 text-right text-[12px] font-medium">Laba / (rugi)</th>
                            <th scope="col" class="px-4 py-3 text-right text-[12px] font-medium">Margin</th>
                            <th scope="col" class="px-4 py-3 text-right text-[12px] font-medium">Laporan</th>
                            <th scope="col" class="px-4 py-3 text-right text-[12px] font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EEF2F5]">
                        @foreach ($rekapDivisi as $d)
                            @php
                                $p = (float) ($d->total_pendapatan_rekap ?? 0);
                                $b = (float) ($d->total_pengeluaran_rekap ?? 0);
                                $lb = (float) ($d->laba_bersih_rekap ?? 0);
                                $m = \App\Helpers\FormatAkuntansi::hitungMargin($p, $b);
                                $countLap = $d->laporan_list ? $d->laporan_list->count() : 0;
                                $labaColor = $lb > 0 ? 'text-[#047857]' : ($lb < 0 ? 'text-[#B91C1C]' : 'text-[#172B36]');
                                $marginColor = $m < 0 ? 'text-[#B91C1C]' : 'text-[#172B36]';
                                $laporanParams = array_filter([
                                    'periode' => $periode,
                                    'minggu_ke' => $mingguKe,
                                    'divisi_id_divisi' => $d->id_divisi,
                                ], fn ($value) => $value !== '' && $value !== null);
                                $laporanUrl = route('kadiv.keuangan.laporan', $laporanParams);
                            @endphp
                            <tr wire:key="rekap-{{ $d->id_divisi }}"
                                class="h-[54px] cursor-pointer transition hover:bg-[#F5FAFC] focus:bg-[#F5FAFC] focus:outline-none"
                                tabindex="0" role="link" data-href="{{ $laporanUrl }}"
                                aria-label="Buka laporan {{ $d->nama_divisi }}"
                                onclick="window.location.href = this.dataset.href"
                                onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location.href = this.dataset.href; }">
                                <td class="px-4 py-2.5">
                                    <p class="text-[13px] font-medium text-[#172B36]">{{ $d->nama_divisi }}</p>
                                </td>
                                <td class="px-4 py-2.5 text-right text-[13px] font-medium tabular-nums text-[#172B36]">
                                    {{ \App\Helpers\FormatAkuntansi::formatRupiah($p) }}
                                </td>
                                <td class="px-4 py-2.5 text-right text-[13px] font-medium tabular-nums text-[#172B36]">
                                    {{ \App\Helpers\FormatAkuntansi::formatRupiah($b) }}
                                </td>
                                <td class="px-4 py-2.5 text-right text-[13px] font-medium tabular-nums {{ $labaColor }}">
                                    {{ \App\Helpers\FormatAkuntansi::formatRupiah($lb) }}
                                </td>
                                <td class="px-4 py-2.5 text-right text-[13px] font-medium tabular-nums {{ $marginColor }}">
                                    {{ \App\Helpers\FormatAkuntansi::formatMargin($m) }}
                                </td>
                                <td class="px-4 py-2.5 text-right text-[13px] font-medium tabular-nums text-[#172B36]">
                                    {{ $countLap }}
                                </td>
                                <td class="px-4 py-2.5 text-right align-middle">
                                    <a href="{{ $laporanUrl }}" onclick="event.stopPropagation()"
                                        class="text-[12px] font-medium text-[#146C94] transition hover:text-[#0f5878] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Quiet accounting note --}}
    <div class="mt-4 flex items-start gap-2.5 px-1 text-[12px] leading-relaxed text-[#4B5563]">
        <svg class="mt-0.5 h-4 w-4 shrink-0 text-[#146C94]" fill="none" stroke="currentColor" stroke-width="1.8"
            viewBox="0 0 24 24" aria-hidden="true">
            <circle cx="12" cy="12" r="8.25"></circle>
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10.75v5M12 7.75h.01"></path>
        </svg>
        <p>
            Rugi ditampilkan dalam kurung, misalnya (Rp 3.400.000). Margin = laba bersih &divide; pendapatan
            &times; 100%. Jika pendapatan atau biaya bernilai 0, margin ditampilkan 0,0%.
        </p>
    </div>
    @if ($showMonthPicker)
        @include('components.finance.month-picker')
    @endif
</div>
