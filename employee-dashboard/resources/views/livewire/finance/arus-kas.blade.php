<div class="w-full">
    {{-- Header --}}
    <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <x-page-heading title="Arus Kas" icon="analytics" />
            <p class="mt-1 text-[13px] text-[#4B5563]">
                Riwayat kas masuk dan kas keluar PT Silindo.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="openModal('modal-tambah-pemasukan')"
                class="inline-flex h-10 items-center gap-2 rounded-lg bg-[#146C94] px-4 text-[14px] font-medium text-white shadow-sm transition hover:bg-[#0F5A7C] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] focus-visible:ring-offset-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m0 0-6 6m6-6 6 6"></path>
                </svg>
                <span>Catat kas masuk</span>
            </button>
            <button type="button" onclick="openModal('modal-tambah-pengeluaran')"
                class="inline-flex h-10 items-center gap-2 rounded-lg border border-[#D5DEE3] bg-white px-4 text-[14px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] focus-visible:ring-offset-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m0 0-6-6m6 6 6-6"></path>
                </svg>
                <span>Catat kas keluar</span>
            </button>
        </div>
    </div>

    {{-- Flash alerts --}}
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

    {{-- Summary cards --}}
    <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="flex min-h-[92px] flex-col justify-center rounded-xl border border-[#E2E8EC] bg-white p-3.5 shadow-sm">
            <p class="text-[13px] font-medium text-[#4B5563]">Total kas masuk</p>
            <p class="mt-1 text-[24px] font-semibold tabular-nums text-[#172B36]">
                {{ \App\Helpers\FormatAkuntansi::formatRupiah($totalMasuk) }}
            </p>
        </div>
        <div class="flex min-h-[92px] flex-col justify-center rounded-xl border border-[#E2E8EC] bg-white p-3.5 shadow-sm">
            <p class="text-[13px] font-medium text-[#4B5563]">Total kas keluar</p>
            <p class="mt-1 text-[24px] font-semibold tabular-nums text-[#172B36]">
                {{ \App\Helpers\FormatAkuntansi::formatRupiah($totalKeluar) }}
            </p>
        </div>
        <div class="flex min-h-[92px] flex-col justify-center rounded-xl border border-[#E2E8EC] bg-white p-3.5 shadow-sm">
            <p class="text-[13px] font-medium text-[#4B5563]">Saldo bersih bulan ini</p>
            <p class="mt-1 text-[24px] font-semibold tabular-nums {{ $saldo > 0 ? 'text-[#047857]' : ($saldo < 0 ? 'text-[#B91C1C]' : 'text-[#172B36]') }}">
                {{ \App\Helpers\FormatAkuntansi::formatRupiah($saldo) }}
            </p>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="mt-5 flex flex-wrap items-center gap-3">
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
            <span aria-live="polite"
                class="flex min-w-[140px] items-center justify-center border-x border-[#D5DEE3] px-3 text-[13px] font-medium text-[#172B36]">
                {{ $periodeLabel }}
            </span>
            <button type="button" wire:click="next" wire:loading.attr="disabled" wire:target="next"
                aria-label="Periode berikutnya"
                class="inline-flex h-9 w-9 items-center justify-center text-[#4B5563] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#146C94] disabled:cursor-wait">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"></path>
                </svg>
            </button>
        </div>

        @if ($tab === 'masuk')
            <select wire:model.live="kategori" aria-label="Kategori"
                class="h-9 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] font-medium text-[#172B36] focus:outline-none focus:ring-2 focus:ring-[#146C94] sm:w-[220px]">
                <option value="">Semua kategori</option>
                @foreach ($kategoriList as $kat)
                    <option value="{{ $kat }}">{{ $kat }}</option>
                @endforeach
            </select>
        @endif

        <div class="relative w-full sm:w-64">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#4B5563]"
                fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"></path>
            </svg>
            <input type="search" wire:model.live.debounce.300ms="search"
                placeholder="Cari kode atau keterangan..." aria-label="Cari transaksi"
                class="h-9 w-full rounded-lg border border-[#D5DEE3] bg-white pl-9 pr-3 text-[13px] font-medium text-[#172B36] placeholder:text-[#4B5563] focus:outline-none focus:ring-2 focus:ring-[#146C94]">
        </div>
    </div>

    {{-- Tabs + table --}}
    <div class="mt-4 overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">
        <div class="flex border-b border-[#E2E8EC] bg-white">
            <button type="button" wire:click="$set('tab', 'masuk')"
                class="-mb-px flex items-center gap-2 border-b-2 px-4 py-3 text-[14px] font-medium transition {{ $tab === 'masuk' ? 'border-[#146C94] text-[#146C94]' : 'border-transparent text-[#4B5563] hover:text-[#172B36]' }}">
                Kas masuk
                <span class="text-[12px] font-normal text-[#4B5563]">{{ $pemasukan->total() }}</span>
            </button>
            <button type="button" wire:click="$set('tab', 'keluar')"
                class="-mb-px flex items-center gap-2 border-b-2 px-4 py-3 text-[14px] font-medium transition {{ $tab === 'keluar' ? 'border-[#146C94] text-[#146C94]' : 'border-transparent text-[#4B5563] hover:text-[#172B36]' }}">
                Kas keluar
                <span class="text-[12px] font-normal text-[#4B5563]">{{ $pengeluaran->total() }}</span>
            </button>
        </div>

        @if ($tab === 'masuk')
            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px] border-collapse text-left">
                    <thead class="bg-[#EAF4F8] text-[#334155]">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-[12px] font-medium">Kode & tanggal</th>
                            <th scope="col" class="px-4 py-3 text-[12px] font-medium">Kategori</th>
                            <th scope="col" class="px-4 py-3 text-[12px] font-medium">Divisi terkait</th>
                            <th scope="col" class="px-4 py-3 text-[12px] font-medium">Keterangan</th>
                            <th scope="col" class="px-4 py-3 text-right text-[12px] font-medium">Nominal</th>
                            <th scope="col" class="px-4 py-3 text-center text-[12px] font-medium">Bukti</th>
                            <th scope="col" class="px-4 py-3 text-center text-[12px] font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EEF2F5]">
                        @forelse ($pemasukan as $item)
                            <tr wire:key="masuk-{{ $item->id_kp }}"
                                wire:click="openTransactionDetail('masuk', '{{ $item->id_kp }}')"
                                wire:keydown.enter="openTransactionDetail('masuk', '{{ $item->id_kp }}')"
                                tabindex="0"
                                class="cursor-pointer transition hover:bg-[#F5FAFC] focus:bg-[#F5FAFC] focus:outline-none">
                                <td class="px-4 py-3">
                                    <div class="font-mono text-[12px] font-semibold text-[#172B36]">
                                        {{ $item->kode_transaksi }}
                                    </div>
                                    <div class="mt-0.5 text-[12px] text-[#4B5563]">
                                        {{ \Carbon\Carbon::parse($item->tanggal_masuk)->locale('id')->isoFormat('D MMM Y') }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex h-[22px] items-center rounded-md border border-[#E2E8F0] bg-[#F1F5F9] px-2 text-[11px] font-medium text-[#475569]">
                                        {{ $item->kategori_pemasukan }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-[12px] font-medium text-[#172B36]">
                                    {{ $item->divisi?->nama_divisi ?? 'Umum / Pusat' }}
                                </td>
                                <td class="max-w-xs px-4 py-3 text-[12px] text-[#4B5563]">
                                    <div class="truncate">{{ $item->keterangan ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-right text-[13px] font-medium tabular-nums {{ $item->nominal < 0 ? 'text-[#B91C1C]' : 'text-[#172B36]' }}">
                                    {{ \App\Helpers\FormatAkuntansi::formatRupiah($item->nominal) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($item->bukti_dokumen)
                                        <a href="{{ \App\Support\FileStorage::url($item->bukti_dokumen) }}" target="_blank"
                                            rel="noopener noreferrer" onclick="event.stopPropagation()"
                                            aria-label="Lihat bukti"
                                            class="inline-flex items-center justify-center rounded p-1 text-[#146C94] transition hover:text-[#0F5A7C]">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"
                                                viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13">
                                                </path>
                                            </svg>
                                        </a>
                                    @else
                                        <span class="text-[12px] text-[#B45309]">Belum ada</span>
                                    @endif
                                </td>
                                                            <td class="px-4 py-3 text-center">
                                    <button type="button"
                                        wire:click.stop="openTransactionDetail('masuk', '{{ $item->id_kp }}')"
                                        class="text-[12px] font-medium text-[#146C94] transition hover:text-[#0f5878] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                                        Detail
                                    </button>
                                </td>
</tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-[12px] text-[#4B5563]">
                                    Belum ada transaksi kas masuk untuk periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            @if ($permintaanSiapCair->count() > 0)
                <div class="border-b border-[#E2E8EC] bg-[#F5FAFC] p-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-[#146C94]"></span>
                        <h3 class="text-[12px] font-semibold text-[#172B36]">
                            Antrean pencairan dana (sudah di-ACC)
                        </h3>
                        <span
                            class="rounded-full bg-[#E3F1F7] px-2 py-0.5 text-[12px] font-medium text-[#146C94]">
                            {{ $permintaanSiapCair->count() }} siap dicairkan
                        </span>
                    </div>
                    <div class="mt-2 divide-y divide-[#E2E8EC]">
                        @foreach ($permintaanSiapCair as $kpd)
                            <div class="flex flex-col gap-2 py-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-mono text-[12px] font-semibold text-[#172B36]">
                                            {{ $kpd->nomor_pengajuan }}
                                        </span>
                                        <span class="text-[12px] text-[#4B5563]">&middot;</span>
                                        <span class="text-[12px] font-medium text-[#172B36]">
                                            {{ $kpd->divisi?->nama_divisi }}
                                        </span>
                                    </div>
                                    <p class="mt-0.5 text-[13px] font-medium text-[#172B36]">
                                        {{ $kpd->judul_pengajuan }}
                                    </p>
                                    <p class="text-[12px] text-[#4B5563]">
                                        Nominal:
                                        <span class="font-medium text-[#172B36]">
                                            {{ \App\Helpers\FormatAkuntansi::formatRupiah($kpd->nominal_diajukan) }}
                                        </span>
                                    </p>
                                </div>
                                <button type="button"
                                    onclick="openCairkanModal('{{ $kpd->id_kpd }}', '{{ $kpd->nomor_pengajuan }}', '{{ $kpd->nominal_diajukan }}', '{{ addslashes($kpd->judul_pengajuan) }}')"
                                    class="inline-flex h-9 shrink-0 items-center rounded-lg bg-[#0097B2] px-3 text-[12px] font-medium text-white shadow-sm transition hover:bg-[#008199] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] focus-visible:ring-offset-2">
                                    Cairkan dana
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px] border-collapse text-left">
                    <thead class="bg-[#EAF4F8] text-[#334155]">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-[12px] font-medium">Kode & tanggal cair</th>
                            <th scope="col" class="px-4 py-3 text-[12px] font-medium">Divisi penerima</th>
                            <th scope="col" class="px-4 py-3 text-[12px] font-medium">Keperluan / keterangan</th>
                            <th scope="col" class="px-4 py-3 text-[12px] font-medium">Metode</th>
                            <th scope="col" class="px-4 py-3 text-right text-[12px] font-medium">Nominal</th>
                            <th scope="col" class="px-4 py-3 text-center text-[12px] font-medium">Bukti</th>
                            <th scope="col" class="px-4 py-3 text-center text-[12px] font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EEF2F5]">
                        @forelse ($pengeluaran as $out)
                            <tr wire:key="keluar-{{ $out->id_kpengeluaran }}"
                                wire:click="openTransactionDetail('keluar', '{{ $out->id_kpengeluaran }}')"
                                wire:keydown.enter="openTransactionDetail('keluar', '{{ $out->id_kpengeluaran }}')"
                                tabindex="0"
                                class="cursor-pointer transition hover:bg-[#F5FAFC] focus:bg-[#F5FAFC] focus:outline-none">
                                <td class="px-4 py-3">
                                    <div class="font-mono text-[12px] font-semibold text-[#172B36]">
                                        {{ $out->kode_pengeluaran }}
                                    </div>
                                    <div class="mt-0.5 text-[12px] text-[#4B5563]">
                                        {{ \Carbon\Carbon::parse($out->tanggal_cair)->locale('id')->isoFormat('D MMM Y') }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-[12px] font-medium text-[#172B36]">
                                    {{ $out->divisi?->nama_divisi }}
                                </td>
                                <td class="max-w-xs px-4 py-3 text-[12px] text-[#4B5563]">
                                    <div class="truncate">{{ $out->keterangan ?? '-' }}</div>
                                    @if ($out->permintaanDana)
                                        <span class="mt-0.5 inline-block font-mono text-[12px] text-[#146C94]">
                                            Ref: {{ $out->permintaanDana->nomor_pengajuan }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex h-[22px] items-center rounded-md border border-[#E2E8F0] bg-[#F1F5F9] px-2 text-[11px] font-medium text-[#475569]">
                                        {{ $out->metode_pembayaran }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-[13px] font-medium tabular-nums {{ $out->nominal < 0 ? 'text-[#B91C1C]' : 'text-[#172B36]' }}">
                                    {{ \App\Helpers\FormatAkuntansi::formatRupiah($out->nominal) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($out->bukti_pencairan)
                                        <a href="{{ \App\Support\FileStorage::url($out->bukti_pencairan) }}" target="_blank"
                                            rel="noopener noreferrer" onclick="event.stopPropagation()"
                                            aria-label="Lihat bukti"
                                            class="inline-flex items-center justify-center rounded p-1 text-[#146C94] transition hover:text-[#0F5A7C]">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"
                                                viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13">
                                                </path>
                                            </svg>
                                        </a>
                                    @else
                                        <span class="text-[12px] text-[#B45309]">Belum ada</span>
                                    @endif
                                </td>
                                                            <td class="px-4 py-3 text-center">
                                    <button type="button"
                                        wire:click.stop="openTransactionDetail('keluar', '{{ $out->id_kpengeluaran }}')"
                                        class="text-[12px] font-medium text-[#146C94] transition hover:text-[#0f5878] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                                        Detail
                                    </button>
                                </td>
</tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-[12px] text-[#4B5563]">
                                    Belum ada transaksi kas keluar untuk periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        @php $paginator = $tab === 'masuk' ? $pemasukan : $pengeluaran; @endphp
        <div
            class="flex flex-col gap-2 border-t border-[#E2E8EC] px-4 py-3 text-[12px] text-[#4B5563] sm:flex-row sm:items-center sm:justify-between">
            <span>
                @if ($paginator->total() > 0)
                    Menampilkan {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} dari {{ $paginator->total() }} transaksi
                @else
                    Belum ada transaksi
                @endif
            </span>
            @if ($paginator->hasPages())
                <div>{{ $paginator->links() }}</div>
            @endif
        </div>
    </div>

    {{-- Detail transaksi --}}
    @if ($showTransactionDetail && $transactionDetail)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="background-color: rgba(15, 40, 55, 0.45);" wire:click.self="closeTransactionDetail">
            <div class="w-full max-w-[520px] rounded-xl border border-[#E2E8EC] bg-white p-5 shadow-xl"
                role="dialog" aria-modal="true" aria-labelledby="transaction-detail-title">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 id="transaction-detail-title" class="text-[16px] font-semibold text-[#172B36]">
                            Detail transaksi
                        </h3>
                        <p class="mt-0.5 text-[12px] text-[#4B5563]">
                            Kas {{ $transactionDetail['type'] === 'masuk' ? 'masuk' : 'keluar' }}
                        </p>
                    </div>
                    <button type="button" wire:click="closeTransactionDetail" aria-label="Tutup detail transaksi"
                        class="rounded-lg p-1 text-[#4B5563] transition hover:bg-[#F5FAFC] hover:text-[#172B36] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"
                            viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <dl class="mt-4 divide-y divide-[#EEF2F5] text-[13px]">
                    <div class="flex justify-between gap-4 py-2">
                        <dt class="text-[#4B5563]">Kode</dt>
                        <dd class="text-right font-medium text-[#172B36]">{{ $transactionDetail['kode'] }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-2">
                        <dt class="text-[#4B5563]">Tanggal</dt>
                        <dd class="text-right font-medium text-[#172B36]">{{ $transactionDetail['tanggal'] }}</dd>
                    </div>
                    @if ($transactionDetail['kategori'])
                        <div class="flex justify-between gap-4 py-2">
                            <dt class="text-[#4B5563]">Kategori</dt>
                            <dd class="text-right font-medium text-[#172B36]">{{ $transactionDetail['kategori'] }}</dd>
                        </div>
                    @endif
                    @if ($transactionDetail['metode'])
                        <div class="flex justify-between gap-4 py-2">
                            <dt class="text-[#4B5563]">Metode</dt>
                            <dd class="text-right font-medium text-[#172B36]">{{ $transactionDetail['metode'] }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between gap-4 py-2">
                        <dt class="text-[#4B5563]">Divisi</dt>
                        <dd class="text-right font-medium text-[#172B36]">{{ $transactionDetail['divisi'] }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-2">
                        <dt class="text-[#4B5563]">Keterangan</dt>
                        <dd class="max-w-[300px] text-right font-medium text-[#172B36]">
                            {{ $transactionDetail['keterangan'] }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4 py-2">
                        <dt class="text-[#4B5563]">Nominal</dt>
                        <dd class="text-right font-semibold tabular-nums text-[#172B36]">
                            {{ \App\Helpers\FormatAkuntansi::formatRupiah($transactionDetail['nominal']) }}
                        </dd>
                    </div>
                    @if ($transactionDetail['referensi'])
                        <div class="flex justify-between gap-4 py-2">
                            <dt class="text-[#4B5563]">Referensi</dt>
                            <dd class="text-right font-medium text-[#172B36]">{{ $transactionDetail['referensi'] }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between gap-4 py-2">
                        <dt class="text-[#4B5563]">Bukti</dt>
                        <dd class="text-right">
                            @if ($transactionDetail['bukti'])
                                <a href="{{ \App\Support\FileStorage::url($transactionDetail['bukti']) }}" target="_blank"
                                    rel="noopener noreferrer" aria-label="Lihat bukti"
                                    class="inline-flex items-center gap-1.5 font-medium text-[#146C94] hover:underline">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"
                                        viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13">
                                        </path>
                                    </svg>
                                    Lihat bukti
                                </a>
                            @else
                                <span class="text-[12px] text-[#B45309]">Belum ada</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    @endif

    {{-- MODAL TAMBAH PEMASUKAN --}}
    <div id="modal-tambah-pemasukan" class="modal-overlay-in fixed inset-0 z-50 hidden bg-[rgba(15,40,55,.45)] flex items-center justify-center p-4">
        <div class="modal-panel-in bg-white rounded-xl max-w-lg w-full p-6 shadow-xl border border-[#E2E8EC]">
            <div class="flex items-center justify-between pb-4 border-b border-[#E2E8EC]">
                <h3 class="text-[16px] font-semibold text-[#172B36]">Catat kas masuk baru</h3>
                <button type="button" onclick="closeModal('modal-tambah-pemasukan')" class="text-[#4B5563] hover:text-[#4B5563] text-xl font-bold">&times;</button>
            </div>
            <form method="POST" action="{{ route('kadiv.keuangan.pemasukan.store') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-medium text-[#172B36] mb-1">Tanggal masuk</label>
                        <input type="date" name="tanggal_masuk" value="{{ now()->toDateString() }}" required
                            class="w-full text-sm px-3 py-2 bg-[#F4F7F9] border border-[#E2E8EC] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-[#172B36] mb-1">Kategori pemasukan</label>
                        <select name="kategori_pemasukan" required
                            class="w-full text-sm px-3 py-2 bg-[#F4F7F9] border border-[#E2E8EC] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($kategoriList as $kat)
                                <option value="{{ $kat }}">{{ $kat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-medium text-[#172B36] mb-1">Nominal (Rp)</label>
                        <input type="number" name="nominal" min="1" step="1" placeholder="Contoh: 5000000" required
                            class="w-full text-sm px-3 py-2 bg-[#F4F7F9] border border-[#E2E8EC] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-[#172B36] mb-1">Divisi terkait (opsional)</label>
                        <select name="divisi_id_divisi"
                            class="w-full text-sm px-3 py-2 bg-[#F4F7F9] border border-[#E2E8EC] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                            <option value="">-- Umum / Tanpa Divisi --</option>
                            @foreach ($divisiList as $div)
                                <option value="{{ $div->id_divisi }}">{{ $div->nama_divisi }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-[#172B36] mb-1">Keterangan / Sumber Dana</label>
                    <textarea name="keterangan" rows="2" placeholder="Penjelasan penerimaan dana kas..."
                        class="w-full text-sm px-3 py-2 bg-[#F4F7F9] border border-[#E2E8EC] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#146C94]"></textarea>
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-[#172B36] mb-1">Upload bukti (PDF/JPG/PNG, max 5MB)</label>
                    <input type="file" name="bukti_dokumen" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full text-xs text-[#4B5563] file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#F5FAFC] file:text-[#172B36] hover:file:bg-[#E2E8EC] cursor-pointer">
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-[#E2E8EC]">
                    <button type="button" onclick="closeModal('modal-tambah-pemasukan')"
                        class="px-4 py-2 text-[13px] font-medium text-[#4B5563] bg-[#F5FAFC] hover:bg-[#EAF4F8] rounded-xl transition">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-xs font-semibold text-white bg-[#0097B2] hover:bg-[#008199] rounded-xl shadow-sm transition">Simpan kas masuk</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL CAIRKAN DANA --}}
    <div id="modal-cairkan" class="modal-overlay-in fixed inset-0 z-50 hidden bg-[rgba(15,40,55,.45)] flex items-center justify-center p-4">
        <div class="modal-panel-in bg-white rounded-xl max-w-lg w-full p-6 shadow-xl border border-[#E2E8EC]">
            <div class="flex items-center justify-between pb-4 border-b border-[#E2E8EC]">
                <h3 class="text-[16px] font-semibold text-[#172B36]">Cairkan permintaan dana</h3>
                <button type="button" onclick="closeModal('modal-cairkan')" class="text-[#4B5563] hover:text-[#4B5563] text-xl font-bold">&times;</button>
            </div>
            <form id="form-cairkan" method="POST" action="" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <div class="p-3 bg-[#F4F7F9] rounded-xl border border-[#E2E8EC] text-xs">
                    <p class="text-[#4B5563]">Nomor pengajuan: <span id="cairkan-no" class="font-mono font-bold text-[#172B36]"></span></p>
                    <p class="text-[#4B5563] mt-1">Keperluan: <span id="cairkan-judul" class="font-semibold text-[#172B36]"></span></p>
                    <p class="text-[#4B5563] mt-1">Nominal: <span id="cairkan-nominal" class="font-bold text-[#047857] text-sm"></span></p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-medium text-[#172B36] mb-1">Tanggal cair</label>
                        <input type="date" name="tanggal_cair" value="{{ now()->toDateString() }}" required
                            class="w-full text-sm px-3 py-2 bg-[#F4F7F9] border border-[#E2E8EC] rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-[#172B36] mb-1">Metode pembayaran</label>
                        <select name="metode_pembayaran" required
                            class="w-full text-sm px-3 py-2 bg-[#F4F7F9] border border-[#E2E8EC] rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="Transfer Bank">Transfer Bank</option>
                            <option value="Kas Kecil">Kas Kecil</option>
                            <option value="Talangan Direktur">Talangan Direktur</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-[#172B36] mb-1">Upload bukti (opsional)</label>
                    <input type="file" name="bukti_pencairan" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full text-xs text-[#4B5563] file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#F5FAFC] file:text-[#172B36] hover:file:bg-[#E2E8EC] cursor-pointer">
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-[#172B36] mb-1">Catatan pencairan (opsional)</label>
                    <textarea name="keterangan" rows="2" class="w-full text-sm px-3 py-2 bg-[#F4F7F9] border border-[#E2E8EC] rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-[#E2E8EC]">
                    <button type="button" onclick="closeModal('modal-cairkan')"
                        class="px-4 py-2 text-[13px] font-medium text-[#4B5563] bg-[#F5FAFC] hover:bg-[#EAF4F8] rounded-xl transition">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-xs font-semibold text-white bg-[#0097B2] hover:bg-[#008199] rounded-xl shadow-sm transition">Konfirmasi cairkan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL TAMBAH PENGELUARAN LANGSUNG --}}
    <div id="modal-tambah-pengeluaran" class="modal-overlay-in fixed inset-0 z-50 hidden bg-[rgba(15,40,55,.45)] flex items-center justify-center p-4">
        <div class="modal-panel-in bg-white rounded-xl max-w-lg w-full p-6 shadow-xl border border-[#E2E8EC]">
            <div class="flex items-center justify-between pb-4 border-b border-[#E2E8EC]">
                <h3 class="text-[16px] font-semibold text-[#172B36]">Catat kas keluar langsung</h3>
                <button type="button" onclick="closeModal('modal-tambah-pengeluaran')" class="text-[#4B5563] hover:text-[#4B5563] text-xl font-bold">&times;</button>
            </div>
            <form method="POST" action="{{ route('kadiv.keuangan.pengeluaran.store') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-medium text-[#172B36] mb-1">Divisi terkait</label>
                        <select name="divisi_id_divisi" required
                            class="w-full text-sm px-3 py-2 bg-[#F4F7F9] border border-[#E2E8EC] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                            <option value="">-- Pilih Divisi --</option>
                            @foreach ($divisiList as $div)
                                <option value="{{ $div->id_divisi }}">{{ $div->nama_divisi }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-[#172B36] mb-1">Nominal (Rp)</label>
                        <input type="number" name="nominal" min="1" step="1" placeholder="Contoh: 250000" required
                            class="w-full text-sm px-3 py-2 bg-[#F4F7F9] border border-[#E2E8EC] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-medium text-[#172B36] mb-1">Tanggal keluar</label>
                        <input type="date" name="tanggal_cair" value="{{ now()->toDateString() }}" required
                            class="w-full text-sm px-3 py-2 bg-[#F4F7F9] border border-[#E2E8EC] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-[#172B36] mb-1">Metode pembayaran</label>
                        <select name="metode_pembayaran" required
                            class="w-full text-sm px-3 py-2 bg-[#F4F7F9] border border-[#E2E8EC] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                            <option value="Kas Kecil">Kas Kecil</option>
                            <option value="Transfer Bank">Transfer Bank</option>
                            <option value="Talangan Direktur">Talangan Direktur</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-[#172B36] mb-1">Keperluan / keterangan</label>
                    <textarea name="keterangan" rows="2" placeholder="Biaya air galon, listrik, konsumsi..."
                        class="w-full text-sm px-3 py-2 bg-[#F4F7F9] border border-[#E2E8EC] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#146C94]"></textarea>
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-[#172B36] mb-1">Upload bukti (PDF/JPG/PNG)</label>
                    <input type="file" name="bukti_pencairan" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full text-xs text-[#4B5563] file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#F5FAFC] file:text-[#172B36] hover:file:bg-[#E2E8EC] cursor-pointer">
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-[#E2E8EC]">
                    <button type="button" onclick="closeModal('modal-tambah-pengeluaran')"
                        class="px-4 py-2 text-[13px] font-medium text-[#4B5563] bg-[#F5FAFC] hover:bg-[#EAF4F8] rounded-xl transition">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-xs font-semibold text-white bg-[#0097B2] hover:bg-[#008199] rounded-xl shadow-sm transition">Simpan pengeluaran</button>
                </div>
            </form>
        </div>
    </div>

    @script
        <script>
            window.openModal = function (id) {
                document.getElementById(id)?.classList.remove('hidden');
            };

            window.closeModal = function (id) {
                document.getElementById(id)?.classList.add('hidden');
            };

            window.openCairkanModal = function (idKpd, noReq, nominal, judul) {
                const form = document.getElementById('form-cairkan');
                if (form) {
                    form.action = '/kadiv/keuangan/pengeluaran/cairkan/' + idKpd;
                }

                document.getElementById('cairkan-no').textContent = noReq;
                document.getElementById('cairkan-judul').textContent = judul;
                document.getElementById('cairkan-nominal').textContent = 'Rp ' + parseInt(nominal).toLocaleString('id-ID');
                window.openModal('modal-cairkan');
            };
        </script>
    @endscript
</div>
