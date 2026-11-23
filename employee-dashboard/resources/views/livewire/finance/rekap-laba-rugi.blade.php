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

    {{-- Detail Berdasarkan Aktivitas Bisnis Utama (Operasional & Non-Operasional) --}}
    <div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-2">
        {{-- Card Aktivitas Operasional --}}
        <div class="rounded-xl border border-[#D0E2EC] bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between border-b border-[#EEF2F5] pb-3">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#E3F1F7] text-[#146C94]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-[14px] font-semibold text-[#172B36]">Aktivitas Operasional</h3>
                        <p class="text-[11px] text-[#64748B]">Pendapatan &amp; beban operasional bisnis utama</p>
                    </div>
                </div>
                <span class="rounded-md bg-[#F1F5F9] px-2 py-0.5 text-[11px] font-medium text-[#475569]">
                    Operational Core
                </span>
            </div>

            <div class="mt-3 space-y-2.5 text-[13px]">
                <div class="flex items-center justify-between">
                    <span class="text-[#4B5563]">Pendapatan Operasional</span>
                    <span class="font-medium tabular-nums text-[#172B36]">
                        {{ \App\Helpers\FormatAkuntansi::formatRupiah($grandPendapatanOperasional) }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex flex-col">
                        <span class="text-[#4B5563]">Beban Operasional (Operational Expense)</span>
                    </div>
                    <span class="font-medium tabular-nums text-[#172B36]">
                        {{ \App\Helpers\FormatAkuntansi::formatRupiah($grandBebanOperasional) }}
                    </span>
                </div>
                <div class="border-t border-[#EEF2F5] pt-2.5 flex items-center justify-between font-semibold">
                    <span class="text-[#172B36]">Laba Operasional (Operating Profit)</span>
                    <span class="tabular-nums {{ $grandLabaOperasional > 0 ? 'text-[#047857]' : ($grandLabaOperasional < 0 ? 'text-[#B91C1C]' : 'text-[#172B36]') }}">
                        {{ \App\Helpers\FormatAkuntansi::formatRupiah($grandLabaOperasional) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Card Aktivitas Non-Operasional --}}
        <div class="rounded-xl border border-[#D0E2EC] bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between border-b border-[#EEF2F5] pb-3">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#EAF0F6] text-[#475569]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-[14px] font-semibold text-[#172B36]">Aktivitas Non-Operasional</h3>
                        <p class="text-[11px] text-[#64748B]">Pendapatan &amp; beban di luar aktivitas utama</p>
                    </div>
                </div>
                <span class="rounded-md bg-[#F1F5F9] px-2 py-0.5 text-[11px] font-medium text-[#475569]">
                    Non-Operational
                </span>
            </div>

            @php
                $netNonOp = $grandPendapatanNonOperasional - $grandBebanNonOperasional;
            @endphp
            <div class="mt-3 space-y-2.5 text-[13px]">
                <div class="flex items-center justify-between">
                    <span class="text-[#4B5563]">Pendapatan Non-Operasional</span>
                    <span class="font-medium tabular-nums text-[#172B36]">
                        {{ \App\Helpers\FormatAkuntansi::formatRupiah($grandPendapatanNonOperasional) }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex flex-col">
                        <span class="text-[#4B5563]">Beban Non-Operasional</span>
                    </div>
                    <span class="font-medium tabular-nums text-[#172B36]">
                        {{ \App\Helpers\FormatAkuntansi::formatRupiah($grandBebanNonOperasional) }}
                    </span>
                </div>
                <div class="border-t border-[#EEF2F5] pt-2.5 flex items-center justify-between font-semibold">
                    <span class="text-[#172B36]">Laba / (Rugi) Non-Operasional</span>
                    <span class="tabular-nums {{ $netNonOp > 0 ? 'text-[#047857]' : ($netNonOp < 0 ? 'text-[#B91C1C]' : 'text-[#172B36]') }}">
                        {{ \App\Helpers\FormatAkuntansi::formatRupiah($netNonOp) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Table card --}}
    <div class="relative mt-5 overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">
        <x-table-loading-overlay target="previousMonth, nextMonth, openMonthPicker, selectMonth, setMinggu" />
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
                                    <p class="text-[11px] text-[#64748B]">Op: {{ \App\Helpers\FormatAkuntansi::formatRupiah($d->laba_operasional_rekap) }} &bull; Non-Op: {{ \App\Helpers\FormatAkuntansi::formatRupiah($d->pendapatan_non_operasional_rekap - $d->beban_non_operasional_rekap) }}</p>
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
