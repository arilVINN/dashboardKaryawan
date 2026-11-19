<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Laporan Laba/Rugi - PT Silindo</title>
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
                'parentText' => 'Keuangan',
                'parentUrl' => route('kadiv.keuangan'),
                'currentPage' => 'Verifikasi Laporan L/R',
                'inline' => true,
            ])

            {{-- Header --}}
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <x-page-heading title="Verifikasi Laporan Laba / Rugi Divisi" icon="tasks" />
                    <p class="mt-1 text-[13px] text-[#4B5563]">
                        Review dan verifikasi laporan mingguan yang dikirim oleh kepala divisi.
                    </p>
                </div>
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

            {{-- Toolbar --}}
            <form method="GET" action="{{ route('kadiv.keuangan.laporan') }}"
                class="mt-5 flex flex-wrap items-center gap-3">
                <input type="month" name="periode" value="{{ $periode }}" onchange="this.form.submit()"
                    aria-label="Periode"
                    class="h-9 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] font-medium text-[#172B36] focus:outline-none focus:ring-2 focus:ring-[#146C94] sm:w-44">

                <select name="divisi_id_divisi" onchange="this.form.submit()" aria-label="Divisi"
                    class="h-9 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] font-medium text-[#172B36] focus:outline-none focus:ring-2 focus:ring-[#146C94] sm:w-56">
                    <option value="">Semua divisi</option>
                    @foreach ($divisiList as $div)
                        <option value="{{ $div->id_divisi }}" @selected($divisiId === $div->id_divisi)>
                            {{ $div->nama_divisi }}
                        </option>
                    @endforeach
                </select>

                <select name="status" onchange="this.form.submit()" aria-label="Status"
                    class="h-9 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] font-medium text-[#172B36] focus:outline-none focus:ring-2 focus:ring-[#146C94] sm:w-48">
                    <option value="">Semua status</option>
                    <option value="menunggu" @selected($status === 'menunggu')>Menunggu</option>
                    <option value="disetujui" @selected($status === 'disetujui')>Disetujui</option>
                    <option value="perlu_revisi" @selected($status === 'perlu_revisi')>Perlu revisi</option>
                </select>

                @if (request()->hasAny(['periode', 'divisi_id_divisi', 'status']))
                    <a href="{{ route('kadiv.keuangan.laporan') }}"
                        class="inline-flex h-9 items-center rounded-lg border border-[#D5DEE3] bg-white px-3 text-[12px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC]">
                        Reset
                    </a>
                @endif
            </form>

            {{-- Table --}}
            <div class="mt-4 overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1100px] border-collapse text-left">
                        <thead class="bg-[#EAF4F8] text-[#334155]">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-[12px] font-medium">Divisi &amp; pengirim</th>
                                <th scope="col" class="px-4 py-3 text-[12px] font-medium">Periode &amp; minggu</th>
                                <th scope="col" class="px-4 py-3 text-right text-[12px] font-medium">Pendapatan</th>
                                <th scope="col" class="px-4 py-3 text-right text-[12px] font-medium">Pengeluaran</th>
                                <th scope="col" class="px-4 py-3 text-right text-[12px] font-medium">Laba / (rugi)</th>
                                <th scope="col" class="px-4 py-3 text-right text-[12px] font-medium">Margin</th>
                                <th scope="col" class="px-4 py-3 text-center text-[12px] font-medium">Status</th>
                                <th scope="col" class="px-4 py-3 text-center text-[12px] font-medium">Bukti &amp; aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#EEF2F5]">
                            @forelse ($laporan as $lap)
                                <tr class="transition hover:bg-[#F5FAFC]">
                                    <td class="px-4 py-3">
                                        <div class="text-[13px] font-medium text-[#172B36]">
                                            {{ $lap->divisi?->nama_divisi }}
                                        </div>
                                        <div class="mt-0.5 text-[12px] text-[#4B5563]">
                                            {{ $lap->pengirim?->karyawan?->nama ?? $lap->pengirim?->username }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <x-date-range :start="$lap->tanggal_mulai" :end="$lap->tanggal_selesai"
                                            class="text-[13px] font-medium text-[#172B36]" />
                                        <div class="mt-1">
                                            <span
                                                class="inline-flex h-[22px] items-center rounded-md border border-[#E2E8F0] bg-[#F1F5F9] px-2 text-[11px] font-medium text-[#475569]">
                                                Minggu ke-{{ $lap->minggu_ke }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-[13px] font-medium tabular-nums text-[#172B36]">
                                        <div>{{ \App\Helpers\FormatAkuntansi::formatRupiah($lap->total_pendapatan) }}</div>
                                        <div class="text-[10px] text-[#64748B]">Op: {{ \App\Helpers\FormatAkuntansi::formatRupiah($lap->pendapatan_operasional ?: $lap->total_pendapatan) }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-[13px] font-medium tabular-nums text-[#172B36]">
                                        <div>{{ \App\Helpers\FormatAkuntansi::formatRupiah($lap->total_pengeluaran) }}</div>
                                        <div class="text-[10px] text-[#64748B]">Op: {{ \App\Helpers\FormatAkuntansi::formatRupiah($lap->beban_operasional ?: $lap->total_pengeluaran) }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-[13px] font-semibold tabular-nums {{ $lap->laba_bersih < 0 ? 'text-[#B91C1C]' : 'text-[#172B36]' }}">
                                        <div>{{ \App\Helpers\FormatAkuntansi::formatRupiah($lap->laba_bersih) }}</div>
                                        <div class="text-[10px] font-normal text-[#64748B]">Op: {{ \App\Helpers\FormatAkuntansi::formatRupiah($lap->laba_operasional ?: ($lap->total_pendapatan - $lap->total_pengeluaran)) }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-[13px] tabular-nums {{ $lap->margin_persen < 0 ? 'text-[#B91C1C]' : 'text-[#4B5563]' }}">
                                        {{ \App\Helpers\FormatAkuntansi::formatMargin($lap->margin_persen) }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        @if ($lap->status_verifikasi === 'disetujui')
                                            <span
                                                class="inline-flex items-center rounded-md border border-[#7DD3C8] bg-[#D5F3F0] px-2 py-0.5 text-[12px] font-medium text-[#0D9488]">
                                                Disetujui
                                            </span>
                                        @elseif ($lap->status_verifikasi === 'perlu_revisi')
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
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        <div class="inline-flex items-center gap-2">
                                            @if ($lap->bukti_laporan)
                                                <a href="{{ asset('storage/'.$lap->bukti_laporan) }}" target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-[#D5DEE3] bg-white px-2.5 text-[12px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"
                                                        viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13">
                                                        </path>
                                                    </svg>
                                                    Lihat
                                                </a>
                                            @endif

                                            <button type="button"
                                                onclick="openVerifModal(this)"
                                                data-id="{{ $lap->id_klr }}"
                                                data-divisi="{{ $lap->divisi?->nama_divisi }}"
                                                data-minggu="{{ $lap->minggu_ke }}"
                                                data-status="{{ $lap->status_verifikasi }}"
                                                data-catatan-verif="{{ $lap->catatan_verifikasi ?? '' }}"
                                                data-catatan-kadiv="{{ $lap->catatan ?? '' }}"
                                                data-pend-op="{{ \App\Helpers\FormatAkuntansi::formatRupiah($lap->pendapatan_operasional ?: $lap->total_pendapatan) }}"
                                                data-beban-op="{{ \App\Helpers\FormatAkuntansi::formatRupiah($lap->beban_operasional ?: $lap->total_pengeluaran) }}"
                                                data-laba-op="{{ \App\Helpers\FormatAkuntansi::formatRupiah($lap->laba_operasional ?: ($lap->total_pendapatan - $lap->total_pengeluaran)) }}"
                                                data-pend-non-op="{{ \App\Helpers\FormatAkuntansi::formatRupiah($lap->pendapatan_non_operasional ?: 0) }}"
                                                data-beban-non-op="{{ \App\Helpers\FormatAkuntansi::formatRupiah($lap->beban_non_operasional ?: 0) }}"
                                                data-total-pend="{{ \App\Helpers\FormatAkuntansi::formatRupiah($lap->total_pendapatan) }}"
                                                data-total-peng="{{ \App\Helpers\FormatAkuntansi::formatRupiah($lap->total_pengeluaran) }}"
                                                data-laba-bersih="{{ \App\Helpers\FormatAkuntansi::formatRupiah($lap->laba_bersih) }}"
                                                class="inline-flex h-8 items-center rounded-lg bg-[#0097B2] px-2.5 text-[12px] font-medium text-white shadow-sm transition hover:bg-[#008199] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] focus-visible:ring-offset-2">
                                                Verifikasi
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-10 text-center text-[12px] text-[#4B5563]">
                                        Belum ada laporan laba rugi divisi yang masuk.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col gap-2 border-t border-[#E2E8EC] px-4 py-3 text-[12px] text-[#4B5563] sm:flex-row sm:items-center sm:justify-between">
                    <span>
                        @if ($laporan->total() > 0)
                            Menampilkan {{ $laporan->firstItem() }}-{{ $laporan->lastItem() }} dari {{ $laporan->total() }} laporan
                        @else
                            Belum ada laporan
                        @endif
                    </span>
                    @if ($laporan->hasPages())
                        <div>{{ $laporan->withQueryString()->links() }}</div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    {{-- Modal verifikasi --}}
    <div id="modal-verif" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/50 p-4 backdrop-blur-sm">
        <div class="flex min-h-full items-center justify-center">
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl border border-[#E2E8EC] bg-white p-5 shadow-xl">
                <div class="flex items-start justify-between gap-4 border-b border-[#E2E8EC] pb-4">
                    <div>
                        <h3 class="text-[16px] font-semibold text-[#172B36]">Verifikasi laporan divisi</h3>
                        <p id="verif-desc" class="mt-0.5 text-[12px] text-[#4B5563]"></p>
                    </div>
                    <button type="button" onclick="closeModal('modal-verif')" aria-label="Tutup dialog"
                        class="rounded-lg p-1 text-[#4B5563] transition hover:bg-[#F5FAFC] hover:text-[#172B36]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Preview Rincian Aktivitas Bisnis --}}
                <div class="mt-4 space-y-3">
                    <div class="rounded-lg border border-[#D5DEE3] bg-[#F8FAFC] p-3 space-y-2">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-[#0097B2]">1. Aktivitas Operasional</p>
                        <div class="grid grid-cols-2 gap-2 text-[12px]">
                            <div>
                                <span class="text-[#64748B]">Pendapatan Operasional:</span>
                                <p id="verif-pend-op" class="font-medium tabular-nums text-[#172B36]"></p>
                            </div>
                            <div>
                                <span class="text-[#64748B]">Beban Operasional (OPEX):</span>
                                <p id="verif-beban-op" class="font-medium tabular-nums text-[#172B36]"></p>
                            </div>
                            <div class="col-span-2 border-t border-[#EEF2F5] pt-1.5 flex justify-between">
                                <span class="font-medium text-[#172B36]">Laba Operasional:</span>
                                <span id="verif-laba-op" class="font-semibold tabular-nums text-[#172B36]"></span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-[#D5DEE3] bg-[#F8FAFC] p-3 space-y-2">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-[#64748B]">2. Aktivitas Non-Operasional</p>
                        <div class="grid grid-cols-2 gap-2 text-[12px]">
                            <div>
                                <span class="text-[#64748B]">Pendapatan Non-Operasional:</span>
                                <p id="verif-pend-non-op" class="font-medium tabular-nums text-[#172B36]"></p>
                            </div>
                            <div>
                                <span class="text-[#64748B]">Beban Non-Operasional:</span>
                                <p id="verif-beban-non-op" class="font-medium tabular-nums text-[#172B36]"></p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 rounded-lg border border-[#D0E2EC] bg-[#EAF4F8] p-2.5 text-center text-[12px]">
                        <div>
                            <p class="text-[#64748B]">Total Pendapatan</p>
                            <p id="verif-total-pend" class="font-semibold text-[#172B36]"></p>
                        </div>
                        <div>
                            <p class="text-[#64748B]">Total Pengeluaran</p>
                            <p id="verif-total-peng" class="font-semibold text-[#172B36]"></p>
                        </div>
                        <div>
                            <p class="text-[#64748B]">Laba Bersih</p>
                            <p id="verif-laba-bersih" class="font-bold text-[#172B36]"></p>
                        </div>
                    </div>

                    <div id="verif-catatan-kadiv-box">
                        <p class="text-[11px] text-[#64748B]">Catatan dari pengirim:</p>
                        <p id="verif-catatan-kadiv" class="mt-0.5 rounded-md border border-[#E2E8EC] bg-[#F8FAFC] p-2 text-[12px] text-[#172B36]"></p>
                    </div>
                </div>

                <form id="form-verif" method="POST" action="" class="mt-4 space-y-4 border-t border-[#E2E8EC] pt-4">
                    @csrf

                    <div>
                        <label for="status_verifikasi" class="mb-1 block text-[12px] font-medium text-[#4B5563]">
                            Status keputusan
                        </label>
                        <select id="status_verifikasi" name="status_verifikasi" required
                            class="h-9 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] text-[#172B36] focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                            <option value="disetujui">Setujui &amp; verifikasi laporan</option>
                            <option value="perlu_revisi">Kembalikan (perlu revisi)</option>
                        </select>
                    </div>

                    <div>
                        <label for="catatan_verifikasi" class="mb-1 block text-[12px] font-medium text-[#4B5563]">
                            Catatan / catatan revisi
                        </label>
                        <textarea id="catatan_verifikasi" name="catatan_verifikasi" rows="3"
                            placeholder="Berikan arahan perbaikan jika meminta revisi..."
                            class="w-full rounded-lg border border-[#D5DEE3] bg-white px-3 py-2 text-[13px] text-[#172B36] placeholder:text-[#4B5563] focus:outline-none focus:ring-2 focus:ring-[#146C94]"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-[#E2E8EC] pt-4">
                        <button type="button" onclick="closeModal('modal-verif')"
                            class="inline-flex h-9 items-center rounded-lg border border-[#D5DEE3] bg-white px-4 text-[12px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC]">
                            Batal
                        </button>
                        <button type="submit"
                            class="inline-flex h-9 items-center rounded-lg bg-[#0097B2] px-4 text-[12px] font-medium text-white shadow-sm transition hover:bg-[#008199]">
                            Simpan verifikasi
                        </button>
                    </div>
                </form>
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

        function openVerifModal(button) {
            const data = button.dataset;
            const form = document.getElementById('form-verif');
            form.action = '/kadiv/keuangan/laporan/' + data.id + '/verifikasi';
            document.getElementById('verif-desc').textContent = data.divisi + ' - Minggu ke-' + data.minggu;
            
            // Set data values
            document.getElementById('verif-pend-op').textContent = data.pendOp;
            document.getElementById('verif-beban-op').textContent = data.bebanOp;
            document.getElementById('verif-laba-op').textContent = data.labaOp;
            document.getElementById('verif-pend-non-op').textContent = data.pendNonOp;
            document.getElementById('verif-beban-non-op').textContent = data.bebanNonOp;
            document.getElementById('verif-total-pend').textContent = data.totalPend;
            document.getElementById('verif-total-peng').textContent = data.totalPeng;
            document.getElementById('verif-laba-bersih').textContent = data.labaBersih;
            document.getElementById('verif-catatan-kadiv').textContent = data.catatanKadiv || '-';

            if (data.status === 'perlu_revisi' || data.status === 'disetujui') {
                document.getElementById('status_verifikasi').value = data.status;
            } else {
                document.getElementById('status_verifikasi').value = 'disetujui';
            }
            document.getElementById('catatan_verifikasi').value = data.catatanVerif || '';
            openModal('modal-verif');
        }
    </script>

</body>

</html>
