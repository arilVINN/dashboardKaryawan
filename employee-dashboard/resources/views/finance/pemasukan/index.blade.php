<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kas Masuk / Pemasukan - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">

    @include('component_kadiv.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')

        <main class="flex-1 overflow-y-auto p-8 pb-10">

            {{-- Header & Button Tambah --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Kas Masuk (Pemasukan)</h1>
                    <p class="text-sm text-slate-500 mt-1">Pencatatan seluruh penerimaan kas, modal, piutang, dan pendapatan operasional PT Silindo.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="openModal('modal-tambah-pemasukan')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Catat Kas Masuk Baru</span>
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

            @if (isset($errors) && $errors->any())
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-sm">
                    <p class="font-bold mb-1">Gagal Menyimpan Data:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Filter & Summary Card --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm md:col-span-2">
                    <form id="filterForm" method="GET" action="{{ route('kadiv.keuangan.pemasukan') }}" class="flex flex-col sm:flex-row items-end gap-3">
                        <div class="flex-1 w-full">
                            <label for="periode" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Periode Bulan</label>
                            <input type="month" id="periode" name="periode" value="{{ $periode }}"
                                onchange="this.form.submit()"
                                class="w-full text-sm font-medium text-slate-700 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE] cursor-pointer transition">
                        </div>
                        <div class="flex-1 w-full">
                            <label for="kategori" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Kategori</label>
                            <select id="kategori" name="kategori"
                                onchange="this.form.submit()"
                                class="w-full text-sm font-medium text-slate-700 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE] cursor-pointer transition">
                                <option value="">Semua Kategori</option>
                                @foreach ($kategoriList as $kat)
                                    <option value="{{ $kat }}" {{ $kategori === $kat ? 'selected' : '' }}>{{ $kat }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if (request()->has('periode') || request()->has('kategori'))
                            <div>
                                <a href="{{ route('kadiv.keuangan.pemasukan') }}"
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
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Kas Masuk ({{ $periode }})</span>
                    <h3 class="text-2xl font-bold text-emerald-600 mt-2">Rp {{ number_format($totalPeriode, 0, ',', '.') }}</h3>
                </div>
            </div>

            {{-- Table Kas Masuk --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-8">
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
                                    <td class="py-4 px-4 text-xs font-medium text-slate-700">
                                        {{ $item->divisi?->nama_divisi ?? 'Umum / Pusat' }}
                                    </td>
                                    <td class="py-4 px-4 text-xs text-slate-600 max-w-xs truncate">
                                        {{ $item->keterangan ?? '-' }}
                                    </td>
                                    <td class="py-4 px-4 text-right font-bold text-emerald-600">
                                        + Rp {{ number_format($item->nominal, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        @if ($item->bukti_dokumen)
                                            <a href="{{ asset('storage/' . $item->bukti_dokumen) }}" target="_blank"
                                                class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium rounded-lg transition inline-flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                <span>Lihat</span>
                                            </a>
                                        @else
                                            <span class="text-xs text-slate-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400">
                                        Belum ada data kas masuk untuk filter yang dipilih.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($pemasukan->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100">
                        {{ $pemasukan->withQueryString()->links() }}
                    </div>
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
                        <label for="tanggal_masuk" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tanggal Masuk</label>
                        <input type="date" id="tanggal_masuk" name="tanggal_masuk" value="{{ now()->toDateString() }}" required
                            class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                    </div>

                    <div>
                        <label for="kategori_pemasukan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Kategori Pemasukan</label>
                        <select id="kategori_pemasukan" name="kategori_pemasukan" required
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
                        <label for="nominal" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nominal (Rp)</label>
                        <input type="number" id="nominal" name="nominal" min="1" step="1" placeholder="Contoh: 5000000" required
                            class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                    </div>

                    <div>
                        <label for="divisi_id_divisi" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Divisi Terkait (Opsional)</label>
                        <select id="divisi_id_divisi" name="divisi_id_divisi"
                            class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                            <option value="">-- Umum / Tanpa Divisi --</option>
                            @foreach ($divisiList as $div)
                                <option value="{{ $div->id_divisi }}">{{ $div->nama_divisi }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label for="keterangan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Keterangan / Sumber Dana</label>
                    <textarea id="keterangan" name="keterangan" rows="2" placeholder="Penjelasan penerimaan dana kas..."
                        class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]"></textarea>
                </div>

                <div>
                    <label for="bukti_dokumen" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Upload Bukti Transaksi (PDF/JPG/PNG, Max 5MB)</label>
                    <input type="file" id="bukti_dokumen" name="bukti_dokumen" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeModal('modal-tambah-pemasukan')"
                        class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition">
                        Simpan Kas Masuk
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
    </script>

</body>

</html>
