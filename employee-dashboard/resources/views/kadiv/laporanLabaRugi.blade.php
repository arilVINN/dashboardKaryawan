<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Laba / Rugi Mingguan - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#F4F7F9] flex h-screen overflow-hidden">

    @include('component_kadiv.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')

        <main class="flex-1 overflow-y-auto bg-[#F4F7F9] p-4 pb-10 sm:p-6 lg:p-8">
            @include('component.breadcrumbs', [
                'rootText' => 'Dashboard',
                'rootUrl' => url('/kadiv/dashboard'),
                'currentPage' => 'Laporan Laba / Rugi',
                'inline' => true,
            ])

            {{-- Header --}}
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <x-page-heading title="Laporan Laba / Rugi" icon="analytics" />
                    <p class="mt-1 text-[13px] text-[#4B5563]">
                        Kirim dan pantau status laporan laba rugi mingguan divisi Anda.
                    </p>
                </div>

                <button type="button" onclick="openModal('modal-kirim-laporan')"
                    class="inline-flex h-10 shrink-0 items-center gap-2 rounded-lg bg-[#0097B2] px-4 text-[13px] font-medium text-white shadow-sm transition hover:bg-[#008199] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] focus-visible:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14"></path>
                    </svg>
                    <span>Kirim laporan</span>
                </button>
            </div>

            {{-- Flash --}}
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

            @if (session('error'))
                <div
                    class="mt-4 flex items-start gap-2.5 rounded-lg border border-[#FECACA] bg-[#FEF2F2] px-3.5 py-2.5 text-[13px] text-[#991B1B]">
                    <svg class="mt-0.5 h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.707 7.293a1 1 0 0 0-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 1 0 1.414 1.414L10 11.414l1.293 1.293a1 1 0 0 0 1.414-1.414L11.414 10l1.293-1.293a1 1 0 0 0-1.414-1.414L10 8.586 8.707 7.293Z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if (isset($errors) && $errors->any())
                <div class="mt-4 rounded-lg border border-[#FECACA] bg-[#FEF2F2] px-3.5 py-2.5 text-[13px] text-[#991B1B]">
                    <p class="font-medium">Gagal mengirim laporan:</p>
                    <ul class="mt-1 list-inside list-disc space-y-0.5 text-[12px]">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $periodeDate = \Carbon\Carbon::createFromFormat('Y-m', $periode);
                $periodeLabel = $periodeDate->locale('id')->isoFormat('MMMM Y');
                $prevPeriode = $periodeDate->copy()->subMonthNoOverflow()->format('Y-m');
                $nextPeriode = $periodeDate->copy()->addMonthNoOverflow()->format('Y-m');
            @endphp

            <div class="mt-5 flex flex-wrap items-center gap-3">
                <div class="inline-flex h-9 shrink-0 items-stretch overflow-hidden rounded-lg border border-[#D5DEE3] bg-white">
                    <a href="{{ route('kadiv.laporanLabaRugi', ['periode' => $prevPeriode, 'minggu_ke' => $mingguKe]) }}"
                        aria-label="Periode sebelumnya"
                        class="inline-flex h-9 w-9 items-center justify-center text-[#4B5563] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#146C94]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m15 19-7-7 7-7"></path>
                        </svg>
                    </a>
                    <span
                        class="flex min-w-[140px] items-center justify-center border-x border-[#D5DEE3] px-3 text-[13px] font-medium text-[#172B36]">
                        {{ $periodeLabel }}
                    </span>
                    <a href="{{ route('kadiv.laporanLabaRugi', ['periode' => $nextPeriode, 'minggu_ke' => $mingguKe]) }}"
                        aria-label="Periode berikutnya"
                        class="inline-flex h-9 w-9 items-center justify-center text-[#4B5563] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#146C94]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"></path>
                        </svg>
                    </a>
                </div>

                <form method="GET" action="{{ route('kadiv.laporanLabaRugi') }}">
                    <input type="hidden" name="periode" value="{{ $periode }}">
                    <select name="minggu_ke" onchange="this.form.submit()" aria-label="Minggu"
                        class="h-9 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] font-medium text-[#172B36] focus:outline-none focus:ring-2 focus:ring-[#146C94] sm:w-[180px]">
                        <option value="">Semua minggu</option>
                        @for ($minggu = 1; $minggu <= 5; $minggu++)
                            <option value="{{ $minggu }}" @selected((string) $mingguKe === (string) $minggu)>
                                Minggu ke-{{ $minggu }}
                            </option>
                        @endfor
                    </select>
                </form>
            </div>

            {{-- Table --}}
            <div class="mt-4 overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1000px] border-collapse text-left">
                        <thead class="bg-[#EAF4F8] text-[#334155]">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-[12px] font-medium">Periode &amp; minggu</th>
                                <th scope="col" class="px-4 py-3 text-right text-[12px] font-medium">Pendapatan</th>
                                <th scope="col" class="px-4 py-3 text-right text-[12px] font-medium">Biaya / beban</th>
                                <th scope="col" class="px-4 py-3 text-right text-[12px] font-medium">Laba / (rugi)</th>
                                <th scope="col" class="px-4 py-3 text-right text-[12px] font-medium">Margin</th>
                                <th scope="col" class="px-4 py-3 text-center text-[12px] font-medium">Status</th>
                                <th scope="col" class="px-4 py-3 text-[12px] font-medium">Catatan finance</th>
                                <th scope="col" class="px-4 py-3 text-center text-[12px] font-medium">Lampiran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#EEF2F5]">
                            @forelse ($riwayat as $item)
                                <tr class="transition hover:bg-[#F5FAFC]">
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <x-date-range :start="$item->tanggal_mulai" :end="$item->tanggal_selesai"
                                            class="text-[13px] font-medium text-[#172B36]" />
                                        <div class="mt-1">
                                            <span
                                                class="inline-flex h-[22px] items-center rounded-md border border-[#E2E8F0] bg-[#F1F5F9] px-2 text-[11px] font-medium text-[#475569]">
                                                Minggu ke-{{ $item->minggu_ke }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-[13px] font-medium tabular-nums text-[#172B36]">
                                        <div>{{ \App\Helpers\FormatAkuntansi::formatRupiah($item->total_pendapatan) }}</div>
                                        <div class="text-[10px] text-[#64748B]">Op: {{ \App\Helpers\FormatAkuntansi::formatRupiah($item->pendapatan_operasional ?: $item->total_pendapatan) }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-[13px] font-medium tabular-nums text-[#172B36]">
                                        <div>{{ \App\Helpers\FormatAkuntansi::formatRupiah($item->total_pengeluaran) }}</div>
                                        <div class="text-[10px] text-[#64748B]">Op: {{ \App\Helpers\FormatAkuntansi::formatRupiah($item->beban_operasional ?: $item->total_pengeluaran) }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-[13px] font-semibold tabular-nums {{ $item->laba_bersih < 0 ? 'text-[#B91C1C]' : 'text-[#172B36]' }}">
                                        <div>{{ \App\Helpers\FormatAkuntansi::formatRupiah($item->laba_bersih) }}</div>
                                        <div class="text-[10px] font-normal text-[#64748B]">Op: {{ \App\Helpers\FormatAkuntansi::formatRupiah($item->laba_operasional ?: ($item->total_pendapatan - $item->total_pengeluaran)) }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-[13px] tabular-nums {{ $item->margin_persen < 0 ? 'text-[#B91C1C]' : 'text-[#4B5563]' }}">
                                        {{ \App\Helpers\FormatAkuntansi::formatMargin($item->margin_persen) }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        @if ($item->status_verifikasi === 'disetujui')
                                            <span
                                                class="inline-flex items-center rounded-md border border-[#7DD3C8] bg-[#D5F3F0] px-2 py-0.5 text-[12px] font-medium text-[#0D9488]">
                                                Terverifikasi
                                            </span>
                                        @elseif ($item->status_verifikasi === 'perlu_revisi')
                                            <span
                                                class="inline-flex items-center rounded-md border border-[#F5A3A3] bg-[#FDE0E0] px-2 py-0.5 text-[12px] font-medium text-[#B91C1C]">
                                                Perlu revisi
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center rounded-md border border-[#F5C85C] bg-[#FEF0C3] px-2 py-0.5 text-[12px] font-medium text-[#B45309]">
                                                Menunggu
                                            </span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <button type="button" onclick="openReportDetailModal(this)"
                                            data-date-range="{{ \App\Helpers\FormatTanggal::rentang($item->tanggal_mulai, $item->tanggal_selesai) }}"
                                            data-week="{{ $item->minggu_ke }}"
                                            data-income="{{ \App\Helpers\FormatAkuntansi::formatRupiah($item->total_pendapatan) }}"
                                            data-expense="{{ \App\Helpers\FormatAkuntansi::formatRupiah($item->total_pengeluaran) }}"
                                            data-pend-op="{{ \App\Helpers\FormatAkuntansi::formatRupiah($item->pendapatan_operasional ?: $item->total_pendapatan) }}"
                                            data-pend-non-op="{{ \App\Helpers\FormatAkuntansi::formatRupiah($item->pendapatan_non_operasional ?: 0) }}"
                                            data-beban-op="{{ \App\Helpers\FormatAkuntansi::formatRupiah($item->beban_operasional ?: $item->total_pengeluaran) }}"
                                            data-beban-non-op="{{ \App\Helpers\FormatAkuntansi::formatRupiah($item->beban_non_operasional ?: 0) }}"
                                            data-laba-op="{{ \App\Helpers\FormatAkuntansi::formatRupiah($item->laba_operasional ?: ($item->total_pendapatan - $item->total_pengeluaran)) }}"
                                            data-profit="{{ \App\Helpers\FormatAkuntansi::formatRupiah($item->laba_bersih) }}"
                                            data-margin="{{ \App\Helpers\FormatAkuntansi::formatMargin($item->margin_persen) }}"
                                            data-status="{{ $item->status_verifikasi }}"
                                            data-note="{{ $item->catatan_verifikasi }}"
                                            data-catatan-kadiv="{{ $item->catatan }}"
                                            data-file="{{ $item->bukti_laporan ? \App\Support\FileStorage::url($item->bukti_laporan) : '' }}"
                                            class="inline-flex h-8 items-center rounded-lg border border-[#D5DEE3] bg-white px-2.5 text-[12px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                                            {{ $item->catatan_verifikasi ? 'Lihat catatan' : 'Detail rincian' }}
                                        </button>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        @if ($item->bukti_laporan)
                                            <a href="{{ \App\Support\FileStorage::url($item->bukti_laporan) }}" target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-[#D5DEE3] bg-white px-2.5 text-[12px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"
                                                    viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z">
                                                    </path>
                                                </svg>
                                                Lihat
                                            </a>
                                        @else
                                            <span class="text-[12px] text-[#B45309]">Belum ada</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-10 text-center text-[12px] text-[#4B5563]">
                                        Belum ada laporan mingguan untuk divisi Anda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col gap-2 border-t border-[#E2E8EC] px-4 py-3 text-[12px] text-[#4B5563] sm:flex-row sm:items-center sm:justify-between">
                    <span>
                        @if ($riwayat->total() > 0)
                            Menampilkan {{ $riwayat->firstItem() }}-{{ $riwayat->lastItem() }} dari {{ $riwayat->total() }} laporan
                        @else
                            Belum ada laporan
                        @endif
                    </span>
                    @if ($riwayat->hasPages())
                        <div>{{ $riwayat->links() }}</div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    {{-- Modal kirim laporan --}}
    <div id="modal-kirim-laporan"
        class="modal-overlay-in fixed inset-0 z-50 hidden overflow-y-auto bg-[rgba(15,40,55,.45)] p-4">
        <div class="flex min-h-full items-center justify-center">
            <div class="modal-panel-in max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl border border-[#E2E8EC] bg-white p-5 shadow-xl">
            <div class="flex items-start justify-between gap-4 border-b border-[#E2E8EC] pb-4">
                <div>
                    <h3 class="text-[16px] font-semibold text-[#172B36]">Kirim laporan mingguan</h3>
                    <p class="mt-0.5 text-[12px] text-[#4B5563]">Laporan akan dikirim ke Finance untuk diverifikasi.</p>
                </div>
                <button type="button" onclick="closeModal('modal-kirim-laporan')" aria-label="Tutup dialog"
                    class="rounded-lg p-1 text-[#4B5563] transition hover:bg-[#F5FAFC] hover:text-[#172B36]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('kadiv.laporanLabaRugi.store') }}" enctype="multipart/form-data"
                class="mt-4 space-y-4">
                @csrf

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="periode" class="mb-1 block text-[12px] font-medium text-[#4B5563]">Periode bulan</label>
                        <input type="month" id="periode" name="periode" value="{{ $periode }}" required
                            class="h-9 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] text-[#172B36] focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                    </div>
                    <div>
                        <label for="minggu_ke" class="mb-1 block text-[12px] font-medium text-[#4B5563]">Minggu ke</label>
                        <select id="minggu_ke" name="minggu_ke" required
                            class="h-9 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] text-[#172B36] focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                            @for ($minggu = 1; $minggu <= 5; $minggu++)
                                <option value="{{ $minggu }}">Minggu ke-{{ $minggu }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="tanggal_mulai" class="mb-1 block text-[12px] font-medium text-[#4B5563]">Tanggal mulai</label>
                        <input type="date" id="tanggal_mulai" name="tanggal_mulai"
                            value="{{ now()->startOfWeek()->toDateString() }}" required
                            class="h-9 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] text-[#172B36] focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                    </div>
                    <div>
                        <label for="tanggal_selesai" class="mb-1 block text-[12px] font-medium text-[#4B5563]">Tanggal selesai</label>
                        <input type="date" id="tanggal_selesai" name="tanggal_selesai"
                            value="{{ now()->endOfWeek()->toDateString() }}" required
                            class="h-9 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] text-[#172B36] focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                    </div>
                </div>

                {{-- Detail Aktivitas Bisnis: Operasional & Non-Operasional --}}
                <div class="rounded-lg border border-[#D5DEE3] bg-[#F8FAFC] p-3 space-y-2.5">
                    <div class="flex items-center gap-2">
                        <span class="inline-block h-2 w-2 rounded-full bg-[#0097B2]"></span>
                        <h4 class="text-[12px] font-semibold text-[#172B36]">1. Aktivitas Operasional (Bisnis Utama)</h4>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label for="pendapatan_operasional" class="mb-1 block text-[11px] font-medium text-[#4B5563]">
                                Pendapatan Operasional (Rp)
                            </label>
                            <input type="number" id="pendapatan_operasional" name="pendapatan_operasional" min="0" step="1" value="0" required
                                placeholder="Penjualan buku / jasa utama"
                                oninput="calculateSummaryLabaRugi()"
                                class="h-9 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] text-[#172B36] focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                        </div>
                        <div>
                            <label for="beban_operasional" class="mb-1 block text-[11px] font-medium text-[#4B5563]">
                                Beban Operasional / OPEX (Rp)
                            </label>
                            <input type="number" id="beban_operasional" name="beban_operasional" min="0" step="1" value="0" required
                                placeholder="Gaji karyawan, sewa, ATK"
                                oninput="calculateSummaryLabaRugi()"
                                class="h-9 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] text-[#172B36] focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                            <p class="mt-0.5 text-[10px] text-[#64748B]">Contoh: Gaji karyawan &amp; beban operasional divisi</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-[#D5DEE3] bg-[#F8FAFC] p-3 space-y-2.5">
                    <div class="flex items-center gap-2">
                        <span class="inline-block h-2 w-2 rounded-full bg-[#64748B]"></span>
                        <h4 class="text-[12px] font-semibold text-[#172B36]">2. Aktivitas Non-Operasional</h4>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label for="pendapatan_non_operasional" class="mb-1 block text-[11px] font-medium text-[#4B5563]">
                                Pendapatan Non-Operasional (Rp)
                            </label>
                            <input type="number" id="pendapatan_non_operasional" name="pendapatan_non_operasional" min="0" step="1" value="0"
                                placeholder="Bunga bank, lain-lain"
                                oninput="calculateSummaryLabaRugi()"
                                class="h-9 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] text-[#172B36] focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                        </div>
                        <div>
                            <label for="beban_non_operasional" class="mb-1 block text-[11px] font-medium text-[#4B5563]">
                                Beban Non-Operasional (Rp)
                            </label>
                            <input type="number" id="beban_non_operasional" name="beban_non_operasional" min="0" step="1" value="0"
                                placeholder="Biaya admin bank, dsb."
                                oninput="calculateSummaryLabaRugi()"
                                class="h-9 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] text-[#172B36] focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                        </div>
                    </div>
                </div>

                {{-- Hidden sync inputs for backward compatibility --}}
                <input type="hidden" id="total_pendapatan" name="total_pendapatan" value="0">
                <input type="hidden" id="total_pengeluaran" name="total_pengeluaran" value="0">

                {{-- Live Summary Preview --}}
                <div class="rounded-lg border border-[#D0E2EC] bg-[#EAF4F8] p-3 text-[12px]">
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div>
                            <p class="text-[#64748B]">Total Pendapatan</p>
                            <p id="summary-total-pendapatan" class="font-semibold text-[#172B36]">Rp 0</p>
                        </div>
                        <div>
                            <p class="text-[#64748B]">Total Pengeluaran</p>
                            <p id="summary-total-pengeluaran" class="font-semibold text-[#172B36]">Rp 0</p>
                        </div>
                        <div>
                            <p class="text-[#64748B]">Laba / (Rugi) Bersih</p>
                            <p id="summary-laba-bersih" class="font-bold text-[#172B36]">Rp 0</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="bukti_laporan" class="mb-1 block text-[12px] font-medium text-[#4B5563]">
                        Upload file laporan / rekap (PDF/Excel, wajib)
                    </label>
                    <input type="file" id="bukti_laporan" name="bukti_laporan" accept=".pdf,.xlsx,.xls,.jpg,.jpeg,.png" required
                        class="w-full cursor-pointer text-[12px] text-[#4B5563] file:mr-3 file:rounded-lg file:border-0 file:bg-[#F5FAFC] file:px-4 file:py-2 file:text-[12px] file:font-medium file:text-[#172B36] hover:file:bg-[#EAF4F8]">
                </div>

                <div>
                    <label for="catatan" class="mb-1 block text-[12px] font-medium text-[#4B5563]">Catatan tambahan (opsional)</label>
                    <textarea id="catatan" name="catatan" rows="2" placeholder="Catatan operasional minggu ini..."
                        class="w-full rounded-lg border border-[#D5DEE3] bg-white px-3 py-2 text-[13px] text-[#172B36] placeholder:text-[#4B5563] focus:outline-none focus:ring-2 focus:ring-[#146C94]"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-[#E2E8EC] pt-4">
                    <button type="button" onclick="closeModal('modal-kirim-laporan')"
                        class="inline-flex h-9 items-center rounded-lg border border-[#D5DEE3] bg-white px-4 text-[12px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC]">
                        Batal
                    </button>
                    <button type="submit"
                        class="inline-flex h-9 items-center rounded-lg bg-[#0097B2] px-4 text-[12px] font-medium text-white shadow-sm transition hover:bg-[#008199]">
                        Kirim laporan
                    </button>
                </div>
            </form>
            </div>
        </div>
    </div>

    {{-- Modal detail laporan --}}
    <div id="modal-report-detail" class="modal-overlay-in fixed inset-0 z-50 hidden overflow-y-auto bg-[rgba(15,40,55,.45)] p-4">
        <div class="flex min-h-full items-center justify-center">
            <div class="modal-panel-in max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl border border-[#E2E8EC] bg-white p-5 shadow-xl">
                <div class="flex items-start justify-between gap-4 border-b border-[#E2E8EC] pb-4">
                    <div>
                        <h3 class="text-[16px] font-semibold text-[#172B36]">Detail laporan</h3>
                        <p id="report-detail-week" class="mt-0.5 text-[12px] text-[#4B5563]"></p>
                    </div>
                    <button type="button" onclick="closeModal('modal-report-detail')" aria-label="Tutup dialog"
                        class="rounded-lg p-1 text-[#4B5563] transition hover:bg-[#F5FAFC] hover:text-[#172B36]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="mt-4 space-y-4">
                    <div>
                        <p class="text-[12px] text-[#4B5563]">Periode</p>
                        <p id="report-detail-date" class="mt-0.5 text-[13px] font-medium text-[#172B36]"></p>
                    </div>

                    {{-- Rincian Berdasarkan Aktivitas Bisnis --}}
                    <div class="rounded-lg border border-[#D5DEE3] bg-[#F8FAFC] p-3 space-y-2">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-[#0097B2]">1. Aktivitas Operasional</p>
                        <div class="grid grid-cols-2 gap-2 text-[12px]">
                            <div>
                                <span class="text-[#64748B]">Pendapatan Operasional:</span>
                                <p id="report-detail-pend-op" class="font-medium tabular-nums text-[#172B36]"></p>
                            </div>
                            <div>
                                <span class="text-[#64748B]">Beban Operasional (OPEX):</span>
                                <p id="report-detail-beban-op" class="font-medium tabular-nums text-[#172B36]"></p>
                            </div>
                            <div class="col-span-2 border-t border-[#EEF2F5] pt-1.5 flex justify-between">
                                <span class="font-medium text-[#172B36]">Laba Operasional:</span>
                                <span id="report-detail-laba-op" class="font-semibold tabular-nums text-[#172B36]"></span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-[#D5DEE3] bg-[#F8FAFC] p-3 space-y-2">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-[#64748B]">2. Aktivitas Non-Operasional</p>
                        <div class="grid grid-cols-2 gap-2 text-[12px]">
                            <div>
                                <span class="text-[#64748B]">Pendapatan Non-Operasional:</span>
                                <p id="report-detail-pend-non-op" class="font-medium tabular-nums text-[#172B36]"></p>
                            </div>
                            <div>
                                <span class="text-[#64748B]">Beban Non-Operasional:</span>
                                <p id="report-detail-beban-non-op" class="font-medium tabular-nums text-[#172B36]"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Total Ringkasan --}}
                    <div class="grid grid-cols-2 gap-3 border-t border-[#E2E8EC] pt-2">
                        <div>
                            <p class="text-[12px] text-[#4B5563]">Total Pendapatan</p>
                            <p id="report-detail-income" class="mt-0.5 text-[13px] font-medium tabular-nums text-[#172B36]"></p>
                        </div>
                        <div>
                            <p class="text-[12px] text-[#4B5563]">Total Pengeluaran</p>
                            <p id="report-detail-expense" class="mt-0.5 text-[13px] font-medium tabular-nums text-[#172B36]"></p>
                        </div>
                        <div>
                            <p class="text-[12px] text-[#4B5563]">Laba / (rugi) Bersih</p>
                            <p id="report-detail-profit" class="mt-0.5 text-[13px] font-semibold tabular-nums text-[#172B36]"></p>
                        </div>
                        <div>
                            <p class="text-[12px] text-[#4B5563]">Margin</p>
                            <p id="report-detail-margin" class="mt-0.5 text-[13px] tabular-nums text-[#4B5563]"></p>
                        </div>
                    </div>

                    <div>
                        <p class="text-[12px] text-[#4B5563]">Status</p>
                        <span id="report-detail-status"
                            class="mt-1 inline-flex items-center rounded-md border px-2 py-0.5 text-[12px] font-medium"></span>
                    </div>

                    <div id="report-detail-catatan-kadiv-container">
                        <p class="text-[12px] text-[#4B5563]">Catatan pengirim</p>
                        <p id="report-detail-catatan-kadiv"
                            class="mt-1 whitespace-pre-line rounded-lg border border-[#E2E8EC] bg-[#F8FAFC] p-2.5 text-[12px] text-[#172B36]"></p>
                    </div>

                    <div>
                        <p class="text-[12px] text-[#4B5563]">Catatan finance</p>
                        <p id="report-detail-note"
                            class="mt-1 whitespace-pre-line rounded-lg border border-[#E2E8EC] bg-[#F8FAFC] p-3 text-[13px] text-[#172B36]"></p>
                    </div>

                    <div>
                        <p class="text-[12px] text-[#4B5563]">Lampiran</p>
                        <div class="mt-1">
                            <a id="report-detail-file" href="#" target="_blank" rel="noopener noreferrer"
                                class="hidden h-8 items-center rounded-lg border border-[#D5DEE3] bg-white px-2.5 text-[12px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                                Lihat lampiran
                            </a>
                            <span id="report-detail-file-empty" class="text-[12px] text-[#B45309]">Belum ada</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex justify-end border-t border-[#E2E8EC] pt-4">
                    <button type="button" onclick="closeModal('modal-report-detail')"
                        class="inline-flex h-9 items-center rounded-lg border border-[#D5DEE3] bg-white px-4 text-[12px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC]">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        function formatRupiahJs(num) {
            num = Number(num || 0);
            if (num < 0) {
                return '(Rp ' + Math.abs(num).toLocaleString('id-ID') + ')';
            }
            return 'Rp ' + num.toLocaleString('id-ID');
        }

        function calculateSummaryLabaRugi() {
            const pendOp = Number(document.getElementById('pendapatan_operasional')?.value || 0);
            const bebanOp = Number(document.getElementById('beban_operasional')?.value || 0);
            const pendNonOp = Number(document.getElementById('pendapatan_non_operasional')?.value || 0);
            const bebanNonOp = Number(document.getElementById('beban_non_operasional')?.value || 0);

            const totalPendapatan = pendOp + pendNonOp;
            const totalPengeluaran = bebanOp + bebanNonOp;
            const labaBersih = totalPendapatan - totalPengeluaran;

            document.getElementById('total_pendapatan').value = totalPendapatan;
            document.getElementById('total_pengeluaran').value = totalPengeluaran;

            document.getElementById('summary-total-pendapatan').textContent = formatRupiahJs(totalPendapatan);
            document.getElementById('summary-total-pengeluaran').textContent = formatRupiahJs(totalPengeluaran);

            const labaEl = document.getElementById('summary-laba-bersih');
            labaEl.textContent = formatRupiahJs(labaBersih);
            labaEl.className = 'font-bold ' + (labaBersih > 0 ? 'text-[#047857]' : (labaBersih < 0 ? 'text-[#B91C1C]' : 'text-[#172B36]'));
        }

        function openReportDetailModal(button) {
            const data = button.dataset;

            document.getElementById('report-detail-week').textContent = 'Minggu ke-' + data.week;
            document.getElementById('report-detail-date').textContent = data.dateRange;
            document.getElementById('report-detail-income').textContent = data.income;
            document.getElementById('report-detail-expense').textContent = data.expense;
            document.getElementById('report-detail-profit').textContent = data.profit;
            document.getElementById('report-detail-margin').textContent = data.margin;
            document.getElementById('report-detail-note').textContent = data.note || '-';

            // Rincian aktivitas bisnis
            document.getElementById('report-detail-pend-op').textContent = data.pendOp || data.income;
            document.getElementById('report-detail-beban-op').textContent = data.bebanOp || data.expense;
            document.getElementById('report-detail-laba-op').textContent = data.labaOp || data.profit;
            document.getElementById('report-detail-pend-non-op').textContent = data.pendNonOp || 'Rp 0';
            document.getElementById('report-detail-beban-non-op').textContent = data.bebanNonOp || 'Rp 0';
            document.getElementById('report-detail-catatan-kadiv').textContent = data.catatanKadiv || '-';

            const statusMap = {
                disetujui: ['Terverifikasi', 'border-[#7DD3C8] bg-[#D5F3F0] text-[#0D9488]'],
                perlu_revisi: ['Perlu revisi', 'border-[#F5A3A3] bg-[#FDE0E0] text-[#B91C1C]'],
                menunggu: ['Menunggu', 'border-[#F5C85C] bg-[#FEF0C3] text-[#B45309]'],
            };
            const meta = statusMap[data.status] || statusMap.menunggu;
            const status = document.getElementById('report-detail-status');
            status.textContent = meta[0];
            status.className = 'mt-1 inline-flex items-center rounded-md border px-2 py-0.5 text-[12px] font-medium ' + meta[1];

            const file = document.getElementById('report-detail-file');
            const empty = document.getElementById('report-detail-file-empty');
            if (data.file) {
                file.href = data.file;
                file.classList.remove('hidden');
                file.classList.add('inline-flex');
                empty.classList.add('hidden');
            } else {
                file.classList.add('hidden');
                file.classList.remove('inline-flex');
                empty.classList.remove('hidden');
            }

            openModal('modal-report-detail');
        }

            const statusMap = {
                disetujui: ['Terverifikasi', 'border-[#7DD3C8] bg-[#D5F3F0] text-[#0D9488]'],
                perlu_revisi: ['Perlu revisi', 'border-[#F5A3A3] bg-[#FDE0E0] text-[#B91C1C]'],
                menunggu: ['Menunggu', 'border-[#F5C85C] bg-[#FEF0C3] text-[#B45309]'],
            };
            const meta = statusMap[data.status] || statusMap.menunggu;
            const status = document.getElementById('report-detail-status');
            status.textContent = meta[0];
            status.className = 'mt-1 inline-flex items-center rounded-md border px-2 py-0.5 text-[12px] font-medium ' + meta[1];

            const file = document.getElementById('report-detail-file');
            const empty = document.getElementById('report-detail-file-empty');
            if (data.file) {
                file.href = data.file;
                file.classList.remove('hidden');
                file.classList.add('inline-flex');
                empty.classList.add('hidden');
            } else {
                file.classList.add('hidden');
                file.classList.remove('inline-flex');
                empty.classList.remove('hidden');
            }

            openModal('modal-report-detail');
        }
    </script>

</body>

</html>
