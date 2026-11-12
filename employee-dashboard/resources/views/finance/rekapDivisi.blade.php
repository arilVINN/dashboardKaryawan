<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Laba / Rugi Divisi - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">

    @include('component_kadiv.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')

        <main class="flex-1 overflow-y-auto p-8 pb-10">

            {{-- Header & Export Button --}}
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Rekap Laba / Rugi Per Divisi</h1>
                    <p class="text-sm text-slate-500 mt-1">Laporan kinerja finansial mingguan dan bulanan seluruh divisi operasional PT Silindo.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('kadiv.keuangan.exportRekapDivisi', request()->query()) }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Export ke Excel (.xlsx)</span>
                    </a>
                </div>
            </div>

            {{-- Filter Panel (Auto-refresh on change) --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm mb-6">
                <form id="filterForm" method="GET" action="{{ route('kadiv.keuangan.rekapDivisi') }}" class="flex flex-col sm:flex-row items-end gap-4">
                    <div class="w-full sm:w-64">
                        <label for="periode" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Periode Bulan</label>
                        <input type="month" id="periode" name="periode" value="{{ $periode }}"
                            onchange="this.form.submit()"
                            class="w-full text-sm font-medium text-slate-700 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE] cursor-pointer transition">
                    </div>

                    <div class="w-full sm:w-72">
                        <label for="minggu_ke" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Minggu Ke</label>
                        <select id="minggu_ke" name="minggu_ke"
                            onchange="this.form.submit()"
                            class="w-full text-sm font-medium text-slate-700 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE] cursor-pointer transition">
                            <option value="">Semua Minggu (Akumulasi Bulan)</option>
                            @for ($m = 1; $m <= 5; $m++)
                                <option value="{{ $m }}" {{ (string)$mingguKe === (string)$m ? 'selected' : '' }}>Minggu Ke-{{ $m }}</option>
                            @endfor
                        </select>
                    </div>

                    @if (request()->has('periode') || request()->has('minggu_ke'))
                        <div>
                            <a href="{{ route('kadiv.keuangan.rekapDivisi') }}"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-medium rounded-xl transition h-[38px]">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span>Reset</span>
                            </a>
                        </div>
                    @endif
                </form>
            </div>

            {{-- Hitung Total Akumulasi --}}
            @php
                $grandPendapatan = 0;
                $grandPengeluaran = 0;
                $grandLaba = 0;

                foreach ($rekapDivisi as $row) {
                    $grandPendapatan += (float) ($row->total_pendapatan_rekap ?? 0);
                    $grandPengeluaran += (float) ($row->total_pengeluaran_rekap ?? 0);
                    $grandLaba += (float) ($row->laba_bersih_rekap ?? 0);
                }

                $grandMargin = \App\Helpers\FormatAkuntansi::hitungMargin($grandPendapatan, $grandPengeluaran);
            @endphp

            {{-- Summary Totals Bar --}}
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-sm">
                    <p class="text-xs font-semibold text-slate-400 uppercase">Total Pendapatan</p>
                    <p class="text-lg font-bold text-emerald-600 mt-1">Rp {{ number_format($grandPendapatan, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-sm">
                    <p class="text-xs font-semibold text-slate-400 uppercase">Total Biaya / Beban</p>
                    <p class="text-lg font-bold text-rose-600 mt-1">Rp {{ number_format($grandPengeluaran, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-sm">
                    <p class="text-xs font-semibold text-slate-400 uppercase">Total Laba / (Rugi)</p>
                    <p class="text-lg font-bold mt-1 {{ $grandLaba >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ \App\Helpers\FormatAkuntansi::formatRupiah($grandLaba) }}
                    </p>
                </div>
                <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-sm">
                    <p class="text-xs font-semibold text-slate-400 uppercase">Margin Laba Keseluruhan</p>
                    <p class="text-lg font-bold mt-1 {{ $grandMargin > 0 ? 'text-emerald-600' : ($grandMargin < 0 ? 'text-rose-600' : 'text-slate-600') }}">
                        {{ \App\Helpers\FormatAkuntansi::formatMargin($grandMargin) }}
                    </p>
                </div>
            </div>

            {{-- Table Rekap --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-8">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-50/75">
                                <th class="py-3.5 px-4">Nama Divisi</th>
                                <th class="py-3.5 px-4 text-center">Periode & Minggu</th>
                                <th class="py-3.5 px-4 text-right">Pendapatan</th>
                                <th class="py-3.5 px-4 text-right">Biaya / Beban</th>
                                <th class="py-3.5 px-4 text-right">Laba / (Rugi)</th>
                                <th class="py-3.5 px-4 text-center">Margin %</th>
                                <th class="py-3.5 px-4 text-center">Jumlah Laporan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($rekapDivisi as $d)
                                @php
                                    $p = (float) ($d->total_pendapatan_rekap ?? 0);
                                    $b = (float) ($d->total_pengeluaran_rekap ?? 0);
                                    $lb = (float) ($d->laba_bersih_rekap ?? 0);
                                    $m = \App\Helpers\FormatAkuntansi::hitungMargin($p, $b);
                                    $countLap = $d->laporan_list ? $d->laporan_list->count() : 0;
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-4 px-4 font-semibold text-slate-800">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full bg-[#19A7CE]"></span>
                                            <span>{{ $d->nama_divisi }}</span>
                                            <span class="text-xs text-slate-400 font-mono">({{ $d->kode_divisi }})</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-center text-xs text-slate-600">
                                        <span class="font-medium">{{ \Carbon\Carbon::parse($periode.'-01')->isoFormat('MMMM Y') }}</span>
                                        @if ($mingguKe)
                                            <span class="inline-block ml-1 px-2 py-0.5 rounded-md bg-blue-50 text-[#19A7CE] font-semibold">Minggu {{ $mingguKe }}</span>
                                        @else
                                            <span class="inline-block ml-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 font-medium">Akumulasi Bulan</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-right font-medium text-emerald-600">
                                        Rp {{ number_format($p, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-4 text-right font-medium text-rose-600">
                                        Rp {{ number_format($b, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-4 text-right font-bold {{ $lb >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ \App\Helpers\FormatAkuntansi::formatRupiah($lb) }}
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $m > 0 ? 'bg-emerald-50 text-emerald-700' : ($m < 0 ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-600') }}">
                                            {{ \App\Helpers\FormatAkuntansi::formatMargin($m) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium {{ $countLap > 0 ? 'bg-blue-50 text-[#19A7CE]' : 'bg-slate-100 text-slate-400' }}">
                                            {{ $countLap }} Laporan
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-400">
                                        Tidak ada data divisi untuk filter yang dipilih.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pedoman Akuntansi Alert --}}
            <div class="bg-amber-50/70 border border-amber-200/80 rounded-2xl p-4 text-xs text-amber-900 leading-relaxed flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <span class="font-bold">Pedoman Format Akuntansi:</span>
                    Nilai defisit/rugi ditampilkan dengan format kurung akuntansi <code class="font-semibold text-rose-700">(Rp ...)</code> tanpa tanda minus. Persentase margin laba dihitung <code class="font-semibold">(Laba Bersih &divide; Pendapatan) &times; 100%</code>. Apabila salah satu nilai pendapatan atau biaya bernilai 0, maka persentase margin secara otomatis diset ke <code class="font-semibold">0,0%</code>.
                </div>
            </div>

        </main>
    </div>

</body>

</html>
