<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ringkasan Keuangan - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">

    @include('component_kadiv.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')

        <main class="flex-1 overflow-y-auto p-8 pb-10">

            {{-- Header & Filter Periode --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Ringkasan Keuangan</h1>
                    <p class="text-sm text-slate-500 mt-1">Monitoring arus kas, pagu anggaran divisi, dan status keuangan PT Silindo.</p>
                </div>
                <form method="GET" action="{{ route('kadiv.keuangan') }}" class="flex items-center gap-2 bg-white px-4 py-2 rounded-xl border border-slate-200 shadow-sm">
                    <label for="periode" class="text-xs font-semibold text-slate-600">Periode:</label>
                    <input type="month" id="periode" name="periode" value="{{ $periode }}"
                        class="text-sm font-medium text-slate-700 focus:outline-none border-none bg-transparent cursor-pointer"
                        onchange="this.form.submit()">
                </form>
            </div>

            {{-- Flash Alert Messages --}}
            @if (session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-2 shadow-sm">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- KPI Summary Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
                {{-- Kas Masuk --}}
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Kas Masuk</span>
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-emerald-600">Rp {{ number_format($totalKasMasuk, 0, ',', '.') }}</h3>
                        <p class="text-[11px] text-slate-400 mt-1">Periode {{ \Carbon\Carbon::parse($periode.'-01')->isoFormat('MMMM Y') }}</p>
                    </div>
                </div>

                {{-- Kas Keluar --}}
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Kas Keluar</span>
                        <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-rose-600">Rp {{ number_format($totalKasKeluar, 0, ',', '.') }}</h3>
                        <p class="text-[11px] text-slate-400 mt-1">Periode {{ \Carbon\Carbon::parse($periode.'-01')->isoFormat('MMMM Y') }}</p>
                    </div>
                </div>

                {{-- Sisa Saldo Kas Bersih --}}
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Saldo Kas Bersih</span>
                        <div class="w-10 h-10 rounded-xl {{ $sisaKas >= 0 ? 'bg-blue-50 text-[#19A7CE]' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold {{ $sisaKas >= 0 ? 'text-slate-800' : 'text-rose-600' }}">
                            {{ \App\Helpers\FormatAkuntansi::formatRupiah($sisaKas) }}
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-1">Selisih arus kas bulan ini</p>
                    </div>
                </div>

                {{-- Permintaan Menunggu --}}
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pengajuan Menunggu</span>
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-baseline gap-2">
                            <h3 class="text-xl font-bold text-amber-600">{{ $permintaanMenunggu }}</h3>
                            <span class="text-xs text-slate-500">Permintaan</span>
                        </div>
                        <a href="{{ route('kadiv.keuangan.permintaan', ['status' => 'menunggu']) }}" class="text-[11px] text-[#19A7CE] font-semibold hover:underline inline-flex items-center gap-1 mt-1">
                            Review Pengajuan &rarr;
                        </a>
                    </div>
                </div>
            </div>

            {{-- Rekap Pagu & Anggaran Per Divisi --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 mb-8">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Status Pagu Anggaran Operasional Divisi</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Monitoring batas anggaran dan penyerapan dana operasional periode {{ $periode }}.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('kadiv.keuangan.alokasi') }}" class="px-3 py-1.5 bg-[#044564] hover:bg-[#03334a] text-white text-xs font-semibold rounded-lg shadow-sm transition">
                            + Atur Pagu
                        </a>
                        <a href="{{ route('kadiv.keuangan.rekapDivisi') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
                            Lihat Rekap L/R &rarr;
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-50/75">
                                <th class="py-3 px-4">Nama Divisi</th>
                                <th class="py-3 px-4 text-center">Kode</th>
                                <th class="py-3 px-4 text-right">Pagu Ditetapkan</th>
                                <th class="py-3 px-4 text-right">Dana Dicairkan</th>
                                <th class="py-3 px-4 text-right">Sisa Pagu</th>
                                <th class="py-3 px-4 text-center">Persentase Penyerapan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($rekapDivisi as $d)
                                @php
                                    $alokasi = (float) ($d->total_alokasi ?? 0);
                                    $terpakai = (float) ($d->total_terpakai ?? 0);
                                    $sisa = (float) ($d->sisa_pagu ?? 0);
                                    $persen = $alokasi > 0 ? min(100, round(($terpakai / $alokasi) * 100, 1)) : 0;
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-3.5 px-4 font-semibold text-slate-800">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full bg-[#19A7CE]"></span>
                                            <span>{{ $d->nama_divisi }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-mono text-xs text-slate-500">{{ $d->kode_divisi }}</td>
                                    <td class="py-3.5 px-4 text-right text-slate-700 font-medium">Rp {{ number_format($alokasi, 0, ',', '.') }}</td>
                                    <td class="py-3.5 px-4 text-right text-rose-600 font-medium">Rp {{ number_format($terpakai, 0, ',', '.') }}</td>
                                    <td class="py-3.5 px-4 text-right font-bold {{ $sisa < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                        {{ \App\Helpers\FormatAkuntansi::formatRupiah($sisa) }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="inline-flex items-center gap-2">
                                            <div class="w-24 bg-slate-100 rounded-full h-2 overflow-hidden">
                                                <div class="h-full rounded-full {{ $persen >= 90 ? 'bg-rose-500' : ($persen >= 60 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ $persen }}%"></div>
                                            </div>
                                            <span class="text-xs font-semibold text-slate-600 font-mono">{{ $persen }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-slate-400">Belum ada data divisi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Quick Shortcuts --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <a href="{{ route('kadiv.keuangan.pemasukan') }}"
                    class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:border-[#19A7CE] transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-105 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-800">Catat Kas Masuk</h4>
                            <p class="text-xs text-slate-400 mt-0.5">Input pendapatan atau modal baru</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('kadiv.keuangan.pengeluaran') }}"
                    class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:border-[#19A7CE] transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center group-hover:scale-105 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-800">Pencairan Dana</h4>
                            <p class="text-xs text-slate-400 mt-0.5">Eksekusi pengeluaran operasional</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('kadiv.keuangan.laporan') }}"
                    class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:border-[#19A7CE] transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#19A7CE] flex items-center justify-center group-hover:scale-105 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-800">Verifikasi Laporan L/R</h4>
                            <p class="text-xs text-slate-400 mt-0.5">Review laporan laba rugi mingguan</p>
                        </div>
                    </div>
                </a>
            </div>

        </main>
    </div>

</body>

</html>
