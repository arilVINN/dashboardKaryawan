<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kas Keluar & Pencairan Dana - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">

    @include('component_kadiv.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')

        <main class="flex-1 overflow-y-auto p-8 pb-10">

            {{-- Header & Button Tambah Langsung --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Kas Keluar & Pencairan Dana</h1>
                    <p class="text-sm text-slate-500 mt-1">Eksekusi pencairan anggaran yang telah di-ACC serta pencatatan pengeluaran operasional perusahaan.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="openModal('modal-tambah-pengeluaran')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Catat Pengeluaran Langsung</span>
                    </button>
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

            @if (session('error'))
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-2 shadow-sm">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- ANTREAN PENCAIRAN (Disetujui, belum dicairkan) --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 mb-8">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500 animate-pulse"></span>
                        <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Antrean Pencairan Dana (Sudah Di-ACC)</h2>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700">
                        {{ $permintaanSiapCair->count() }} Pengajuan Siap Dicairkan
                    </span>
                </div>

                @if ($permintaanSiapCair->isEmpty())
                    <p class="py-4 text-xs text-center text-slate-400">Tidak ada pengajuan dana yang menunggu pencairan saat ini.</p>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($permintaanSiapCair as $kpd)
                            <div class="py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-xs font-bold text-[#044564]">{{ $kpd->nomor_pengajuan }}</span>
                                        <span class="text-xs text-slate-400">&bull;</span>
                                        <span class="text-xs font-semibold text-slate-700">{{ $kpd->divisi?->nama_divisi }}</span>
                                    </div>
                                    <p class="text-sm font-medium text-slate-800 mt-0.5">{{ $kpd->judul_pengajuan }}</p>
                                    <p class="text-xs text-slate-400">Diajukan: {{ $kpd->diajukanOleh?->karyawan?->nama ?? $kpd->diajukanOleh?->username }} &bull; Nominal: <strong class="text-slate-700">Rp {{ number_format($kpd->nominal_diajukan, 0, ',', '.') }}</strong></p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                        onclick="openCairkanModal('{{ $kpd->id_kpd }}', '{{ $kpd->nomor_pengajuan }}', '{{ $kpd->nominal_diajukan }}', '{{ addslashes($kpd->judul_pengajuan) }}')"
                                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <span>Cairkan Dana Sekarang</span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Filter & Summary Total --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm md:col-span-2">
                    <form id="filterForm" method="GET" action="{{ route('kadiv.keuangan.pengeluaran') }}" class="flex flex-col sm:flex-row items-end gap-3">
                        <div class="flex-1 w-full">
                            <label for="periode" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Periode Bulan</label>
                            <input type="month" id="periode" name="periode" value="{{ $periode }}"
                                onchange="this.form.submit()"
                                class="w-full text-sm font-medium text-slate-700 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE] cursor-pointer transition">
                        </div>
                        <div class="flex-1 w-full">
                            <label for="divisi_id_divisi" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Divisi</label>
                            <select id="divisi_id_divisi" name="divisi_id_divisi"
                                onchange="this.form.submit()"
                                class="w-full text-sm font-medium text-slate-700 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE] cursor-pointer transition">
                                <option value="">Semua Divisi</option>
                                @foreach ($divisiList as $div)
                                    <option value="{{ $div->id_divisi }}" {{ $divisiId === $div->id_divisi ? 'selected' : '' }}>
                                        {{ $div->nama_divisi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @if (request()->has('periode') || request()->has('divisi_id_divisi'))
                            <div>
                                <a href="{{ route('kadiv.keuangan.pengeluaran') }}"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-medium rounded-xl transition h-[38px] whitespace-nowrap">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span>Reset</span>
                                </a>
                            </div>
                        @endif
                    </form>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Kas Keluar ({{ $periode }})</span>
                    <h3 class="text-2xl font-bold text-rose-600 mt-2">Rp {{ number_format($totalPeriode, 0, ',', '.') }}</h3>
                </div>
            </div>

            {{-- Table Riwayat Kas Keluar --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-8">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-50/75">
                                <th class="py-3.5 px-4">Kode & Tanggal Cair</th>
                                <th class="py-3.5 px-4">Divisi Penerima</th>
                                <th class="py-3.5 px-4">Keperluan / Keterangan</th>
                                <th class="py-3.5 px-4">Metode Bayar</th>
                                <th class="py-3.5 px-4 text-right">Nominal Keluar</th>
                                <th class="py-3.5 px-4 text-center">Bukti Bayar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($pengeluaran as $out)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-4 px-4">
                                        <div class="font-mono text-xs font-bold text-slate-800">{{ $out->kode_pengeluaran }}</div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($out->tanggal_cair)->isoFormat('DD MMM Y') }}</div>
                                    </td>
                                    <td class="py-4 px-4 font-semibold text-slate-800">
                                        {{ $out->divisi?->nama_divisi }}
                                    </td>
                                    <td class="py-4 px-4 text-xs text-slate-600 max-w-xs">
                                        <div class="truncate">{{ $out->keterangan ?? '-' }}</div>
                                        @if ($out->permintaanDana)
                                            <span class="inline-block mt-0.5 text-[10px] text-blue-600 font-mono">Ref: {{ $out->permintaanDana->nomor_pengajuan }}</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                                            {{ $out->metode_pembayaran }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-right font-bold text-rose-600">
                                        - Rp {{ number_format($out->nominal, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        @if ($out->bukti_pencairan)
                                            <a href="{{ asset('storage/' . $out->bukti_pencairan) }}" target="_blank"
                                                class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium rounded-lg transition inline-flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                <span>Bukti</span>
                                            </a>
                                        @else
                                            <span class="text-xs text-slate-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400">
                                        Belum ada data pengeluaran pada periode {{ $periode }}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($pengeluaran->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100">
                        {{ $pengeluaran->withQueryString()->links() }}
                    </div>
                @endif
            </div>

        </main>
    </div>

    {{-- MODAL CAIRKAN PERMINTAAN DANA --}}
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
                    <p class="text-slate-500 mt-1">Nominal Dicairkan: <span id="cairkan-nominal" class="font-bold text-emerald-600 text-sm"></span></p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="cairkan_tanggal" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tanggal Cair</label>
                        <input type="date" id="cairkan_tanggal" name="tanggal_cair" value="{{ now()->toDateString() }}" required
                            class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label for="cairkan_metode" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Metode Pembayaran</label>
                        <select id="cairkan_metode" name="metode_pembayaran" required
                            class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="Transfer Bank">Transfer Bank</option>
                            <option value="Kas Kecil">Kas Kecil</option>
                            <option value="Talangan Direktur">Talangan Direktur</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="cairkan_bukti" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Upload Bukti Transfer / Nota (Opsional)</label>
                    <input type="file" id="cairkan_bukti" name="bukti_pencairan" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                </div>

                <div>
                    <label for="cairkan_keterangan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Catatan Pencairan (Opsional)</label>
                    <textarea id="cairkan_keterangan" name="keterangan" rows="2" placeholder="Catatan no resi / ref transfer..."
                        class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeModal('modal-cairkan')"
                        class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition">
                        Konfirmasi Cairkan
                    </button>
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
                        <label for="direct_divisi" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Divisi Terkait</label>
                        <select id="direct_divisi" name="divisi_id_divisi" required
                            class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                            <option value="">-- Pilih Divisi --</option>
                            @foreach ($divisiList as $div)
                                <option value="{{ $div->id_divisi }}">{{ $div->nama_divisi }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="direct_nominal" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nominal (Rp)</label>
                        <input type="number" id="direct_nominal" name="nominal" min="1" step="1" placeholder="Contoh: 250000" required
                            class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="direct_tanggal" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tanggal Keluar</label>
                        <input type="date" id="direct_tanggal" name="tanggal_cair" value="{{ now()->toDateString() }}" required
                            class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                    </div>

                    <div>
                        <label for="direct_metode" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Metode Pembayaran</label>
                        <select id="direct_metode" name="metode_pembayaran" required
                            class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                            <option value="Kas Kecil">Kas Kecil</option>
                            <option value="Transfer Bank">Transfer Bank</option>
                            <option value="Talangan Direktur">Talangan Direktur</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="direct_keterangan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Keperluan / Keterangan</label>
                    <textarea id="direct_keterangan" name="keterangan" rows="2" placeholder="Biaya air galon, listrik, konsumsi..."
                        class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]"></textarea>
                </div>

                <div>
                    <label for="direct_bukti" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Upload Bukti Transaksi (PDF/JPG/PNG)</label>
                    <input type="file" id="direct_bukti" name="bukti_pencairan" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeModal('modal-tambah-pengeluaran')"
                        class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-sm transition">
                        Simpan Pengeluaran
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        function openCairkanModal(idKpd, noReq, nominal, judul) {
            const form = document.getElementById('form-cairkan');
            form.action = '/kadiv/keuangan/pengeluaran/cairkan/' + idKpd;
            document.getElementById('cairkan-no').textContent = noReq;
            document.getElementById('cairkan-judul').textContent = judul;
            document.getElementById('cairkan-nominal').textContent = 'Rp ' + parseInt(nominal).toLocaleString('id-ID');
            openModal('modal-cairkan');
        }
    </script>

</body>

</html>
