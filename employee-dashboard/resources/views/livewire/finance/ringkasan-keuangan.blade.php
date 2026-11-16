<div class="w-full bg-[#F4F7F9]">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <x-page-heading title="Ringkasan Keuangan" icon="analytics" />
        </div>

    </div>

    {{-- Action buttons --}}
    <div class="mt-4 flex flex-wrap items-center gap-2.5">
        {{-- Period switcher --}}
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

        <div class="ml-auto flex flex-wrap items-center gap-2.5">
            <a href="{{ route('kadiv.keuangan.arusKas', ['tab' => 'keluar', 'periode' => $periode]) }}"
                class="inline-flex h-10 items-center gap-2 rounded-lg border border-[#D5DEE3] bg-white px-4 text-[13px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] focus-visible:ring-offset-2">
                <svg class="h-4 w-4 text-[#4B5563]" fill="none" stroke="currentColor" stroke-width="1.8"
                    viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.75 7.5h16.5v9.75a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V7.5Zm0 0 2.25-3h12l2.25 3M15 12.75h2.25">
                    </path>
                </svg>
                <span>Pencairan dana</span>
            </a>

            <a href="{{ route('kadiv.keuangan.manajemenDana', ['tab' => 'laporan', 'periode' => $periode]) }}"
                class="inline-flex h-10 items-center gap-2 rounded-lg border border-[#D5DEE3] bg-white px-4 text-[13px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] focus-visible:ring-offset-2">
                <svg class="h-4 w-4 text-[#4B5563]" fill="none" stroke="currentColor" stroke-width="1.8"
                    viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 11.25M6.75 3.75h10.5a1.5 1.5 0 0 1 1.5 1.5v13.5a1.5 1.5 0 0 1-1.5 1.5H6.75a1.5 1.5 0 0 1-1.5-1.5V5.25a1.5 1.5 0 0 1 1.5-1.5Z">
                    </path>
                </svg>
                <span>Verifikasi laporan laba rugi</span>
            </a>
            <a href="{{ route('kadiv.keuangan.arusKas', ['tab' => 'masuk', 'periode' => $periode]) }}"
                class="inline-flex h-10 items-center rounded-lg bg-[#0097B2] px-4 text-[13px] font-semibold text-white shadow-sm transition hover:bg-[#008199] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] focus-visible:ring-offset-2">
                <span>+ Catat kas masuk</span>
            </a>

        </div>
    </div>

    @if (session('success'))
        <div
            class="mt-4 flex items-start gap-2.5 rounded-lg border border-[#BBF7D0] bg-[#F0FDF4] px-3.5 py-2.5 text-[13px] text-[#166534]">
            <svg class="mt-0.5 h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.707-9.293a1 1 0 0 0-1.414-1.414L9 10.586 7.707 9.293a1 1 0 0 0-1.414 1.414l2 2a1 1 0 0 0 1.414 0l4-4Z"
                    clip-rule="evenodd"></path>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- KPI Cards --}}
    <div class="mt-5 grid grid-cols-1 gap-4 min-[560px]:grid-cols-2 min-[900px]:grid-cols-4">
        {{-- Total kas masuk --}}
        <div class="flex min-h-[132px] flex-col justify-between rounded-xl border border-[#E2E8EC] bg-white p-3.5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <p class="text-[13px] font-medium text-[#4B5563]">Total kas masuk</p>
                <span
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#E3F1F7] text-[#146C94]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m0 0-6 6m6-6 6 6"></path>
                    </svg>
                </span>
            </div>
            <div>
                <p class="mt-2 text-[24px] font-semibold leading-tight tabular-nums text-[#172B36]">
                    {{ $this->rupiah($totalKasMasuk) }}
                </p>
            </div>
        </div>

        {{-- Total kas keluar --}}
        <div class="flex min-h-[132px] flex-col justify-between rounded-xl border border-[#E2E8EC] bg-white p-3.5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <p class="text-[13px] font-medium text-[#4B5563]">Total kas keluar</p>
                <span
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#E3F1F7] text-[#146C94]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m0 0-6-6m6 6 6-6"></path>
                    </svg>
                </span>
            </div>
            <div>
                <p class="mt-2 text-[24px] font-semibold leading-tight tabular-nums text-[#172B36]">
                    {{ $this->rupiah($totalKasKeluar) }}
                </p>
            </div>
        </div>

        {{-- Saldo kas bersih --}}
        @php $saldoNegatif = $sisaKas < 0; @endphp
        <div class="flex min-h-[132px] flex-col justify-between rounded-xl border border-[#E2E8EC] bg-white p-3.5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <p class="text-[13px] font-medium text-[#4B5563]">Saldo kas bersih</p>
                <span
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#E3F1F7] text-[#146C94]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <circle cx="12" cy="12" r="8.25"></circle>
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M14.25 9.6c-.52-.72-1.36-1.1-2.25-1.1-1.24 0-2.25.9-2.25 2s1.01 2 2.25 2 2.25.9 2.25 2-1.01 2-2.25 2c-.89 0-1.73-.38-2.25-1.1M12 7.25v9.5">
                        </path>
                    </svg>
                </span>
            </div>
            <div>
                <p class="mt-2 text-[24px] font-semibold leading-tight tabular-nums {{ $saldoNegatif ? 'text-[#B91C1C]' : 'text-[#172B36]' }}">
                    {{ $this->rupiah($sisaKas) }}
                </p>
                <p class="mt-1 text-[12px] text-[#4B5563]">Selisih arus kas bulan ini</p>
            </div>
        </div>

        {{-- Pengajuan menunggu --}}
        @php $adaPengajuan = $permintaanMenunggu > 0; @endphp
        <div @class([
            'flex min-h-[132px] flex-col justify-between rounded-xl border p-3.5 shadow-sm',
            'border-[#F5C85C] bg-[#FFFBEB]' => $adaPengajuan,
            'border-[#E2E8EC] bg-white' => ! $adaPengajuan,
        ])>
            <div class="flex items-start justify-between gap-3">
                <p class="text-[13px] font-medium text-[#4B5563]">Pengajuan menunggu</p>
                <span
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#FEF0C3] text-[#B45309]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <circle cx="12" cy="12" r="8.25"></circle>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5V12l3 1.5"></path>
                    </svg>
                </span>
            </div>
            <div>
                <p class="mt-2 text-[24px] font-semibold leading-tight tabular-nums text-[#172B36]">
                    {{ $permintaanMenunggu }}
                </p>
                @if ($adaPengajuan)
                    <a href="{{ route('kadiv.keuangan.manajemenDana', ['tab' => 'permintaan', 'status' => 'menunggu']) }}"
                        class="mt-1 inline-flex items-center gap-1 text-[12px] font-medium text-[#146C94] hover:underline">
                        Review pengajuan <span aria-hidden="true">&rarr;</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Pagu anggaran operasional divisi --}}
    <div class="mt-5 overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">
        <div class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <div class="min-w-0">
                <h2 class="text-[14px] font-semibold text-[#172B36]">Pagu anggaran operasional divisi</h2>
                <p class="mt-0.5 text-[12px] text-[#4B5563]">
                    Batas anggaran dan penyerapan dana, {{ $periodeLabel }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('kadiv.keuangan.rekapDivisi', ['periode' => $periode]) }}"
                    class="inline-flex items-center text-[13px] font-medium text-[#146C94] hover:underline">
                    Lihat rekap laba rugi <span aria-hidden="true">&rarr;</span>
                </a>
                <a href="{{ route('kadiv.keuangan.alokasi', ['periode' => $periode]) }}"
                    class="inline-flex h-9 items-center rounded-lg border border-[#D5DEE3] bg-white px-3.5 text-[13px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] focus-visible:ring-offset-2">
                    Atur pagu
                </a>
            </div>
        </div>

        @if ($rekapDivisi->isEmpty())
            {{-- Empty state: tidak menampilkan baris Rp 0 / 0% --}}
            <div class="flex flex-col items-center justify-center border-t border-[#E2E8EC] px-6 py-12 text-center">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#E3F1F7] text-[#146C94]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11 3.75h2m-1 0v16.5m-7.5-4.5h15M5.25 7.5h13.5M6.75 20.25h10.5a1.5 1.5 0 0 0 1.5-1.5V5.25a1.5 1.5 0 0 0-1.5-1.5H6.75a1.5 1.5 0 0 0-1.5 1.5v13.5a1.5 1.5 0 0 0 1.5 1.5Z">
                        </path>
                    </svg>
                </span>
                <p class="mt-3 text-[14px] font-semibold text-[#172B36]">
                    Belum ada pagu untuk {{ $periodeLabel }}
                </p>
                <p class="mt-1 max-w-md text-[13px] text-[#4B5563]">
                    Tetapkan pagu per divisi agar penyerapan dana bisa dipantau.
                </p>
                <a href="{{ route('kadiv.keuangan.alokasi', ['periode' => $periode]) }}"
                    class="mt-4 inline-flex h-10 items-center rounded-lg bg-[#0097B2] px-4 text-[13px] font-semibold text-white shadow-sm transition hover:bg-[#008199] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] focus-visible:ring-offset-2">
                    Atur pagu
                </a>
            </div>
        @else
            <div class="overflow-x-auto border-t border-[#E2E8EC]">
                <table class="w-full min-w-[780px] border-collapse text-left">
                    <thead class="bg-[#EAF4F8] text-[#334155]">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-[12px] font-medium">Divisi</th>
                            <th scope="col" class="px-4 py-3 text-right text-[12px] font-medium">Pagu ditetapkan</th>
                            <th scope="col" class="px-4 py-3 text-right text-[12px] font-medium">Dana dicairkan</th>
                            <th scope="col" class="px-4 py-3 text-right text-[12px] font-medium">Sisa pagu</th>
                            <th scope="col" class="px-4 py-3 text-right text-[12px] font-medium">Penyerapan</th>
                            <th scope="col" class="px-4 py-3 text-right text-[12px] font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8EC]">
                        @foreach ($rekapDivisi as $d)
                            @php
                                $alokasi = (float) ($d->total_alokasi ?? 0);
                                $terpakai = (float) ($d->total_terpakai ?? 0);
                                $sisa = (float) ($d->sisa_pagu ?? 0);
                                $persen = $this->persenPenyerapan($alokasi, $terpakai);
                                $barColor = $persen >= 100 ? '#DC2626' : ($persen >= 80 ? '#D97706' : '#0D9488');
                                $sisaMerah = $sisa < 0 || $sisa < ($alokasi * 0.1);
                                $transaksiUrl = route('kadiv.keuangan.arusKas', [
                                    'tab' => 'keluar',
                                    'periode' => $periode,
                                    'divisi_id_divisi' => $d->id_divisi,
                                ]);
                            @endphp
                            <tr wire:key="pagu-{{ $d->id_divisi }}"
                                class="cursor-pointer transition hover:bg-[#F5FAFC] focus:bg-[#F5FAFC] focus:outline-none"
                                tabindex="0" role="link" data-href="{{ $transaksiUrl }}"
                                aria-label="Buka transaksi {{ $d->nama_divisi }}"
                                onclick="window.location.href = this.dataset.href"
                                onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location.href = this.dataset.href; }">
                                <td class="px-4 py-3">
                                    <p class="text-[13px] font-medium text-[#172B36]">{{ $d->nama_divisi }}</p>
                                </td>
                                <td class="px-4 py-3 text-right text-[13px] tabular-nums text-[#172B36]">
                                    {{ $this->rupiah($alokasi) }}
                                </td>
                                <td class="px-4 py-3 text-right text-[13px] tabular-nums text-[#172B36]">
                                    {{ $this->rupiah($terpakai) }}
                                </td>
                                <td class="px-4 py-3 text-right text-[13px] font-medium tabular-nums {{ $sisaMerah ? 'text-[#B91C1C]' : 'text-[#172B36]' }}">
                                    {{ $this->rupiah($sisa) }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2.5">
                                        <div role="progressbar" aria-label="{{ $persen }}% terserap"
                                            aria-valuemin="0" aria-valuemax="100"
                                            aria-valuenow="{{ min(100, max(0, $persen)) }}"
                                            class="h-1.5 w-20 overflow-hidden rounded-full bg-[#E2E8EC]">
                                            <div class="h-full rounded-full"
                                                style="width: {{ min(100, max(0, $persen)) }}%; background-color: {{ $barColor }};">
                                            </div>
                                        </div>
                                        <span class="w-10 text-right text-[12px] font-medium tabular-nums text-[#172B36]">
                                            {{ $persen }}%
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right align-middle">
                                    <a href="{{ $transaksiUrl }}" onclick="event.stopPropagation()"
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
    @if ($showMonthPicker)
        @include('components.finance.month-picker')
    @endif
</div>
