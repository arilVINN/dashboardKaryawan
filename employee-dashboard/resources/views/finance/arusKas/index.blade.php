<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arus Kas - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">

    @include('component_kadiv.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')

        <main class="flex-1 overflow-y-auto p-8 pb-10">

            {{-- Header --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Arus Kas</h1>
                    <p class="text-sm text-slate-500 mt-1">Riwayat kas masuk (pemasukan) dan kas keluar (pengeluaran) PT Silindo.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="openModal('modal-tambah-pemasukan')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                        </svg>
                        <span>Catat Kas Masuk</span>
                    </button>
                    <button type="button" onclick="openModal('modal-tambah-pengeluaran')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                        </svg>
                        <span>Catat Kas Keluar</span>
                    </button>
                </div>
            </div>

            {{-- Flash Alerts --}}
            @if (session('success'))
                <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-2 shadow-sm">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-2 shadow-sm">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
                    <p class="text-xs font-semibold text-slate-400 uppercase">Total Kas Masuk ({{ $periode }})</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
                    <p class="text-xs font-semibold text-slate-400 uppercase">Total Kas Keluar ({{ $periode }})</p>
                    <p class="text-2xl font-bold text-rose-600 mt-1">Rp {{ number_format($totalKeluar, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
                    <p class="text-xs font-semibold text-slate-400 uppercase">Saldo Bersih</p>
                    @php $saldo = $totalMasuk - $totalKeluar; @endphp
                    <p class="text-2xl font-bold mt-1 {{ $saldo >= 0 ? 'text-[#044564]' : 'text-rose-600' }}">
                        {{ $saldo >= 0 ? '' : '- ' }}Rp {{ number_format(abs($saldo), 0, ',', '.') }}
                    </p>
                </div>
            </div>

            {{-- Filter + Tabs --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm mb-6 overflow-hidden">
                {{-- Filter --}}
                <div class="p-5 border-b border-slate-100">
                    <form id="filterForm" method="GET" action="{{ route('kadiv.keuangan.arusKas') }}" class="flex flex-wrap items-end gap-4">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <div class="w-full sm:w-56">
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Periode Bulan</label>
                            <input type="month" name="periode" value="{{ $periode }}"
                                onchange="this.form.submit()"
                                class="w-full text-sm font-medium text-slate-700 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE] cursor-pointer transition">
                        </div>
                        @if ($tab === 'masuk')
                            <div class="w-full sm:w-56">
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Kategori</label>
                                <select name="kategori" onchange="this.form.submit()"
                                    class="w-full text-sm font-medium text-slate-700 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE] cursor-pointer transition">
                                    <option value="">Semua Kategori</option>
                                    @foreach ($kategoriList as $kat)
                                        <option value="{{ $kat }}" {{ ($kategori ?? '') === $kat ? 'selected' : '' }}>{{ $kat }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <div class="w-full sm:w-56">
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Divisi</label>
                                <select name="divisi_id_divisi" onchange="this.form.submit()"
                                    class="w-full text-sm font-medium text-slate-700 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE] cursor-pointer transition">
                                    <option value="">Semua Divisi</option>
                                    @foreach ($divisiList as $div)
                                        <option value="{{ $div->id_divisi }}" {{ ($divisiId ?? '') === $div->id_divisi ? 'selected' : '' }}>{{ $div->nama_divisi }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        @if (request()->has('kategori') || request()->has('divisi_id_divisi'))
                            <a href="{{ route('kadiv.keuangan.arusKas', ['tab' => $tab, 'periode' => $periode]) }}"
                                class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-medium rounded-xl transition h-[38px]">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Reset
                            </a>
                        @endif
                    </form>
                </div>

                {{-- Tab Navigation --}}
                <div class="flex border-b border-slate-100 bg-slate-50/60">
                    <a href="{{ route('kadiv.keuangan.arusKas', array_merge(request()->except('tab'), ['tab' => 'masuk'])) }}"
                        class="flex items-center gap-2 px-6 py-3.5 text-sm font-semibold transition border-b-2 {{ $tab === 'masuk' ? 'border-emerald-500 text-emerald-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                        </svg>
                        Kas Masuk
                        <span class="ml-1 px-2 py-0.5 rounded-full text-[11px] font-bold {{ $tab === 'masuk' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-500' }}">
                            {{ $pemasukan->total() }}
                        </span>
                    </a>
                    <a href="{{ route('kadiv.keuangan.arusKas', array_merge(request()->except('tab'), ['tab' => 'keluar'])) }}"
                        class="flex items-center gap-2 px-6 py-3.5 text-sm font-semibold transition border-b-2 {{ $tab === 'keluar' ? 'border-rose-500 text-rose-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                        </svg>
                        Kas Keluar
                        <span class="ml-1 px-2 py-0.5 rounded-full text-[11px] font-bold {{ $tab === 'keluar' ? 'bg-rose-100 text-rose-700' : 'bg-slate-200 text-slate-500' }}">
                            {{ $pengeluaran->total() }}
                        </span>
                    </a>
                </div>

                {{-- Table Kas Masuk --}}
                @if ($tab === 'masuk')
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-50/75">
                                    <th class="py-3.5 px-4">Kode & Tanggal</th>
                                    <th class="py-3.5 px-4">Kategori</th>
                                    <th class="py-3.5 px-4">Divisi Terkait</th>
                                    <th class="py-3.5 px-4">Keterangan</th>
                                    <th class="py-3.5 px-4 text-right">Nominal (Rp)</th>
                                    <th class="py-3.5 px-4 text-center">Bukti</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($pemasukan as $item)
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="py-4 px-4">
                                            <div class="font-mono text-xs font-bold text-slate-800">{{ $item->kode_transaksi }}</div>
                                            <div class="text-[11px] text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($item->tanggal_masuk)->isoFormat('DD MMM Y') }}</div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                                                {{ $item->kategori_pemasukan }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 text-xs font-medium text-slate-700">{{ $item->divisi?->nama_divisi ?? 'Umum / Pusat' }}</td>
                                        <td class="py-4 px-4 text-xs text-slate-600 max-w-xs truncate">{{ $item->keterangan ?? '-' }}</td>
                                        <td class="py-4 px-4 text-right font-bold text-emerald-600">+ Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                        <td class="py-4 px-4 text-center">
                                            @if ($item->bukti_dokumen)
                                                <a href="{{ asset('storage/' . $item->bukti_dokumen) }}" target="_blank"
                                                    class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium rounded-lg transition inline-flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    Lihat
                                                </a>
                                            @else
                                                <span class="text-xs text-slate-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-10 text-center text-slate-400">Belum ada data kas masuk untuk periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($pemasukan->hasPages())
                        <div class="px-6 py-4 border-t border-slate-100">{{ $pemasukan->withQueryString()->links() }}</div>
                    @endif

                {{-- Table Kas Keluar --}}
                @else
                    {{-- Antrean Pencairan --}}
                    @if ($permintaanSiapCair->count() > 0)
                        <div class="p-5 border-b border-slate-100 bg-blue-50/40">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Antrean Pencairan Dana (Sudah Di-ACC)</h3>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">{{ $permintaanSiapCair->count() }} Siap Dicairkan</span>
                            </div>
                            <div class="divide-y divide-slate-100">
                                @foreach ($permintaanSiapCair as $kpd)
                                    <div class="py-3 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-mono text-xs font-bold text-[#044564]">{{ $kpd->nomor_pengajuan }}</span>
                                                <span class="text-xs text-slate-400">&bull;</span>
                                                <span class="text-xs font-semibold text-slate-700">{{ $kpd->divisi?->nama_divisi }}</span>
                                            </div>
                                            <p class="text-sm font-medium text-slate-800 mt-0.5">{{ $kpd->judul_pengajuan }}</p>
                                            <p class="text-xs text-slate-400">Nominal: <strong class="text-slate-700">Rp {{ number_format($kpd->nominal_diajukan, 0, ',', '.') }}</strong></p>
                                        </div>
                                        <button type="button"
                                            onclick="openCairkanModal('{{ $kpd->id_kpd }}', '{{ $kpd->nomor_pengajuan }}', '{{ $kpd->nominal_diajukan }}', '{{ addslashes($kpd->judul_pengajuan) }}')"
                                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition shrink-0">
                                            Cairkan Dana
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-50/75">
                                    <th class="py-3.5 px-4">Kode & Tanggal Cair</th>
                                    <th class="py-3.5 px-4">Divisi Penerima</th>
                                    <th class="py-3.5 px-4">Keperluan / Keterangan</th>
                                    <th class="py-3.5 px-4">Metode</th>
                                    <th class="py-3.5 px-4 text-right">Nominal Keluar</th>
                                    <th class="py-3.5 px-4 text-center">Bukti</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($pengeluaran as $out)
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="py-4 px-4">
                                            <div class="font-mono text-xs font-bold text-slate-800">{{ $out->kode_pengeluaran }}</div>
                                            <div class="text-[11px] text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($out->tanggal_cair)->isoFormat('DD MMM Y') }}</div>
                                        </td>
                                        <td class="py-4 px-4 font-semibold text-slate-800">{{ $out->divisi?->nama_divisi }}</td>
                                        <td class="py-4 px-4 text-xs text-slate-600 max-w-xs">
                                            <div class="truncate">{{ $out->keterangan ?? '-' }}</div>
                                            @if ($out->permintaanDana)
                                                <span class="inline-block mt-0.5 text-[10px] text-blue-600 font-mono">Ref: {{ $out->permintaanDana->nomor_pengajuan }}</span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700">{{ $out->metode_pembayaran }}</span>
                                        </td>
                                        <td class="py-4 px-4 text-right font-bold text-rose-600">- Rp {{ number_format($out->nominal, 0, ',', '.') }}</td>
                                        <td class="py-4 px-4 text-center">
                                            @if ($out->bukti_pencairan)
                                                <a href="{{ asset('storage/' . $out->bukti_pencairan) }}" target="_blank"
                                                    class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium rounded-lg transition inline-flex items-center gap-1">
                                                    Bukti
                                                </a>
                                            @else
                                                <span class="text-xs text-slate-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-10 text-center text-slate-400">Belum ada data kas keluar untuk periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($pengeluaran->hasPages())
                        <div class="px-6 py-4 border-t border-slate-100">{{ $pengeluaran->withQueryString()->links() }}</div>
                    @endif
                @endif
            </div>

        </main>
    </div>

    {{-- MODAL TAMBAH PEMASUKAN --}}
    <div id="modal-tambah-pemasukan" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-800">Catat Kas Masuk Baru</h3>
                <button type="button" onclick="closeModal('modal-tambah-pemasukan')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>
            <form method="POST" action="{{ route('kadiv.keuangan.pemasukan.store') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tanggal Masuk</label>
                        <input type="date" name="tanggal_masuk" value="{{ now()->toDateString() }}" required
                            class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Kategori Pemasukan</label>
                        <select name="kategori_pemasukan" required
                            class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($kategoriList as $kat)
                                <option value="{{ $kat }}">{{ $kat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nominal (Rp)</label>
                        <input type="number" name="nominal" min="1" step="1" placeholder="Contoh: 5000000" required
                            class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Divisi Terkait (Opsional)</label>
                        <select name="divisi_id_divisi"
                            class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                            <option value="">-- Umum / Tanpa Divisi --</option>
                            @foreach ($divisiList as $div)
                                <option value="{{ $div->id_divisi }}">{{ $div->nama_divisi }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Keterangan / Sumber Dana</label>
                    <textarea name="keterangan" rows="2" placeholder="Penjelasan penerimaan dana kas..."
                        class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Upload Bukti (PDF/JPG/PNG, Max 5MB)</label>
                    <input type="file" name="bukti_dokumen" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeModal('modal-tambah-pemasukan')"
                        class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition">Simpan Kas Masuk</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL CAIRKAN DANA --}}
    <div id="modal-cairkan" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-800">Cairkan Permintaan Dana</h3>
                <button type="button" onclick="closeModal('modal-cairkan')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>
            <form id="form-cairkan" method="POST" action="" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs">
                    <p class="text-slate-500">Nomor Pengajuan: <span id="cairkan-no" class="font-mono font-bold text-slate-800"></span></p>
                    <p class="text-slate-500 mt-1">Keperluan: <span id="cairkan-judul" class="font-semibold text-slate-800"></span></p>
                    <p class="text-slate-500 mt-1">Nominal: <span id="cairkan-nominal" class="font-bold text-emerald-600 text-sm"></span></p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tanggal Cair</label>
                        <input type="date" name="tanggal_cair" value="{{ now()->toDateString() }}" required
                            class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Metode Pembayaran</label>
                        <select name="metode_pembayaran" required
                            class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="Transfer Bank">Transfer Bank</option>
                            <option value="Kas Kecil">Kas Kecil</option>
                            <option value="Talangan Direktur">Talangan Direktur</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Upload Bukti (Opsional)</label>
                    <input type="file" name="bukti_pencairan" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Catatan Pencairan (Opsional)</label>
                    <textarea name="keterangan" rows="2" class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeModal('modal-cairkan')"
                        class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition">Konfirmasi Cairkan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL TAMBAH PENGELUARAN LANGSUNG --}}
    <div id="modal-tambah-pengeluaran" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-800">Catat Kas Keluar Langsung</h3>
                <button type="button" onclick="closeModal('modal-tambah-pengeluaran')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>
            <form method="POST" action="{{ route('kadiv.keuangan.pengeluaran.store') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Divisi Terkait</label>
                        <select name="divisi_id_divisi" required
                            class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                            <option value="">-- Pilih Divisi --</option>
                            @foreach ($divisiList as $div)
                                <option value="{{ $div->id_divisi }}">{{ $div->nama_divisi }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nominal (Rp)</label>
                        <input type="number" name="nominal" min="1" step="1" placeholder="Contoh: 250000" required
                            class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tanggal Keluar</label>
                        <input type="date" name="tanggal_cair" value="{{ now()->toDateString() }}" required
                            class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Metode Pembayaran</label>
                        <select name="metode_pembayaran" required
                            class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                            <option value="Kas Kecil">Kas Kecil</option>
                            <option value="Transfer Bank">Transfer Bank</option>
                            <option value="Talangan Direktur">Talangan Direktur</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Keperluan / Keterangan</label>
                    <textarea name="keterangan" rows="2" placeholder="Biaya air galon, listrik, konsumsi..."
                        class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Upload Bukti (PDF/JPG/PNG)</label>
                    <input type="file" name="bukti_pencairan" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeModal('modal-tambah-pengeluaran')"
                        class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-sm transition">Simpan Pengeluaran</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
        function openCairkanModal(idKpd, noReq, nominal, judul) {
            document.getElementById('form-cairkan').action = '/kadiv/keuangan/pengeluaran/cairkan/' + idKpd;
            document.getElementById('cairkan-no').textContent = noReq;
            document.getElementById('cairkan-judul').textContent = judul;
            document.getElementById('cairkan-nominal').textContent = 'Rp ' + parseInt(nominal).toLocaleString('id-ID');
            openModal('modal-cairkan');
        }
    </script>

</body>
</html>
