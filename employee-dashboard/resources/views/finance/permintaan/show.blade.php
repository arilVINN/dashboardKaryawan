<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengajuan {{ $permintaan->nomor_pengajuan }} - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">

    @include('component_kadiv.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')

        <main class="flex-1 overflow-y-auto p-8 pb-10">

            {{-- Back Link & Breadcrumb --}}
            <div class="flex items-center gap-2 mb-4 text-xs font-medium text-slate-500">
                <a href="{{ route('kadiv.keuangan.permintaan') }}" class="hover:text-[#19A7CE] transition flex items-center gap-1">
                    &larr; Kembali ke Daftar Permintaan
                </a>
                <span>&bull;</span>
                <span class="text-slate-800 font-mono">{{ $permintaan->nomor_pengajuan }}</span>
            </div>

            {{-- Title Bar --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">{{ $permintaan->judul_pengajuan }}</h1>
                    <p class="text-sm text-slate-500 mt-1">Diajukan oleh {{ $permintaan->diajukanOleh?->karyawan?->nama ?? $permintaan->diajukanOleh?->username }} &bull; Divisi {{ $permintaan->divisi?->nama_divisi }}</p>
                </div>
                <div>
                    @if ($permintaan->status === 'menunggu')
                        <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            Menunggu Persetujuan
                        </span>
                    @elseif ($permintaan->status === 'disetujui')
                        <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                            Disetujui (Siap Dicairkan)
                        </span>
                    @elseif ($permintaan->status === 'dicairkan')
                        <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Sudah Dicairkan
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                            Ditolak
                        </span>
                    @endif
                </div>
            </div>

            {{-- Flash Alert --}}
            @if (session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-2 shadow-sm">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Kolom Kiri: Detail Pengajuan & Proposal --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100">
                            Rincian Pengajuan Dana
                        </h3>

                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt class="text-xs text-slate-400 font-medium">Nomor Dokumen</dt>
                                <dd class="font-mono font-bold text-slate-800 mt-0.5">{{ $permintaan->nomor_pengajuan }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-400 font-medium">Tanggal Pengajuan</dt>
                                <dd class="font-medium text-slate-800 mt-0.5">{{ $permintaan->created_at->isoFormat('dddd, DD MMMM Y HH:mm') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-400 font-medium">Divisi Pemohon</dt>
                                <dd class="font-medium text-slate-800 mt-0.5">{{ $permintaan->divisi?->nama_divisi }} ({{ $permintaan->divisi?->kode_divisi }})</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-400 font-medium">Nominal Anggaran Diajukan</dt>
                                <dd class="text-lg font-bold text-[#044564] mt-0.5">Rp {{ number_format($permintaan->nominal_diajukan, 0, ',', '.') }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-xs text-slate-400 font-medium">Keperluan / Keterangan</dt>
                                <dd class="text-slate-700 leading-relaxed mt-1 p-3 bg-slate-50 rounded-xl border border-slate-100">
                                    {{ $permintaan->keperluan }}
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-xs text-slate-400 font-medium mb-1">Lampiran Dokumen / Proposal / RAB</dt>
                                <dd>
                                    @if ($permintaan->lampiran_proposal)
                                        <a href="{{ asset('storage/' . $permintaan->lampiran_proposal) }}" target="_blank"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span>Lihat / Unduh Dokumen Lampiran</span>
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Tidak ada lampiran dokumen diunggah.</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Catatan Finance Bila Ada --}}
                    @if ($permintaan->catatan_finance || $permintaan->disetujuiOleh)
                        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-3 pb-2 border-b border-slate-100">
                                Riwayat Keputusan Finance
                            </h3>
                            <div class="text-xs space-y-2">
                                @if ($permintaan->disetujuiOleh)
                                    <p class="text-slate-600">Diproses oleh: <span class="font-bold text-slate-800">{{ $permintaan->disetujuiOleh?->karyawan?->nama ?? $permintaan->disetujuiOleh?->username }}</span></p>
                                    <p class="text-slate-600">Tanggal: <span class="font-medium text-slate-800">{{ $permintaan->tanggal_disetujui ? \Carbon\Carbon::parse($permintaan->tanggal_disetujui)->isoFormat('DD MMMM Y') : '-' }}</span></p>
                                @endif
                                @if ($permintaan->catatan_finance)
                                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-slate-700">
                                        <span class="font-bold block mb-0.5">Catatan:</span>
                                        {{ $permintaan->catatan_finance }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Kolom Kanan: Analisis Pagu Anggaran Divisi --}}
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100">
                            Analisis Pagu Divisi
                        </h3>

                        <div class="space-y-3.5 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Pagu Bulan Ini:</span>
                                <span class="font-bold text-slate-800">Rp {{ number_format($totalAlokasi, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Sudah Dicairkan:</span>
                                <span class="font-bold text-rose-600">Rp {{ number_format($totalTerpakai, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                                <span class="text-slate-700 font-semibold">Sisa Pagu Tersedia:</span>
                                <span class="font-bold {{ $sisaPagu >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ \App\Helpers\FormatAkuntansi::formatRupiah($sisaPagu) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                                <span class="text-slate-700 font-semibold">Estimasi Jika Disetujui:</span>
                                @php
                                    $sisaSetelah = $sisaPagu - $permintaan->nominal_diajukan;
                                @endphp
                                <span class="font-bold {{ $sisaSetelah >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                                    {{ \App\Helpers\FormatAkuntansi::formatRupiah($sisaSetelah) }}
                                </span>
                            </div>
                        </div>

                        @if ($sisaSetelah < 0)
                            <div class="mt-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-[11px] leading-relaxed">
                                <span class="font-bold">Peringatan:</span> Nominal pengajuan ini melebihi sisa pagu anggaran divisi yang tersedia sebesar <strong>{{ \App\Helpers\FormatAkuntansi::formatRupiah(abs($sisaSetelah)) }}</strong>.
                            </div>
                        @endif
                    </div>

                    {{-- Action Card --}}
                    @if ($permintaan->isMenunggu())
                        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-3">
                            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Tindakan Finance</h3>

                            <form method="POST" action="{{ route('kadiv.keuangan.permintaan.approve', $permintaan->id_kpd) }}" class="space-y-3">
                                @csrf
                                <textarea name="catatan_finance" rows="2" placeholder="Catatan persetujuan (opsional)..."
                                    class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                                <button type="submit"
                                    class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                                    Setujui Pengajuan Ini (ACC)
                                </button>
                            </form>

                            <div class="pt-2 border-t border-slate-100">
                                <form method="POST" action="{{ route('kadiv.keuangan.permintaan.reject', $permintaan->id_kpd) }}" class="space-y-3">
                                    @csrf
                                    <textarea name="catatan_finance" rows="2" required placeholder="Alasan penolakan..."
                                        class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                                    <button type="submit"
                                        class="w-full py-2 px-4 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-xl transition">
                                        Tolak Pengajuan
                                    </button>
                                </form>
                            </div>
                        </div>
                    @elseif ($permintaan->isDisetujui())
                        <div class="bg-blue-50/70 rounded-2xl p-6 border border-blue-200/80 space-y-3">
                            <h4 class="text-xs font-bold text-blue-900 uppercase tracking-wider">Siap Dicairkan</h4>
                            <p class="text-xs text-blue-700 leading-relaxed">Pengajuan ini sudah di-ACC dan menunggu pencairan dana ke pihak pemohon.</p>
                            <a href="{{ route('kadiv.keuangan.pengeluaran') }}"
                                class="inline-flex items-center justify-center w-full py-2.5 px-4 bg-[#044564] hover:bg-[#03334a] text-white text-xs font-bold rounded-xl shadow-sm transition">
                                Buka Menu Kas Keluar untuk Mencairkan &rarr;
                            </a>
                        </div>
                    @endif
                </div>

            </div>

        </main>
    </div>

</body>

</html>
