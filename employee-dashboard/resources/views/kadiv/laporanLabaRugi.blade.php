<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Laba / Rugi Mingguan - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">

    @include('component_kadiv.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')

        <main class="flex-1 overflow-y-auto p-8 pb-10">

            {{-- Header & Button Kirim Laporan --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Laporan Laba / Rugi Mingguan</h1>
                    <p class="text-sm text-slate-500 mt-1">Kirim dan pantau status laporan laba rugi mingguan divisi Anda ke tim Finance.</p>
                </div>
                <div>
                    <button type="button" onclick="openModal('modal-kirim-laporan')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#044564] hover:bg-[#03334a] text-white text-xs font-semibold rounded-xl shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Kirim Laporan Minggu Baru</span>
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

            @if (isset($errors) && $errors->any())
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-sm">
                    <p class="font-bold mb-1">Gagal Mengirim Laporan:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Table Riwayat Laporan Divisi --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-8">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-50/75">
                                <th class="py-3.5 px-4">Periode & Minggu</th>
                                <th class="py-3.5 px-4">Rentang Tanggal</th>
                                <th class="py-3.5 px-4 text-right">Pendapatan</th>
                                <th class="py-3.5 px-4 text-right">Biaya / Beban</th>
                                <th class="py-3.5 px-4 text-right">Laba / (Rugi)</th>
                                <th class="py-3.5 px-4 text-center">Margin %</th>
                                <th class="py-3.5 px-4 text-center">Status Verifikasi</th>
                                <th class="py-3.5 px-4">Catatan Finance</th>
                                <th class="py-3.5 px-4 text-center">Lampiran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($riwayat as $item)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-4 px-4 font-semibold text-slate-800 whitespace-nowrap">
                                        <div class="flex items-center gap-1.5">
                                            <span>{{ \Carbon\Carbon::parse($item->periode.'-01')->isoFormat('MMMM Y') }}</span>
                                            <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-blue-50 text-[#19A7CE]">M-{{ $item->minggu_ke }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-xs text-slate-500 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d/m/Y') }} &ndash; {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/Y') }}
                                    </td>
                                    <td class="py-4 px-4 text-right font-medium text-emerald-600 whitespace-nowrap">
                                        Rp {{ number_format($item->total_pendapatan, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-4 text-right font-medium text-rose-600 whitespace-nowrap">
                                        Rp {{ number_format($item->total_pengeluaran, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-4 text-right font-bold whitespace-nowrap {{ $item->laba_bersih >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ \App\Helpers\FormatAkuntansi::formatRupiah($item->laba_bersih) }}
                                    </td>
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $item->margin_persen > 0 ? 'bg-emerald-50 text-emerald-700' : ($item->margin_persen < 0 ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-600') }}">
                                            {{ \App\Helpers\FormatAkuntansi::formatMargin($item->margin_persen) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        @if ($item->status_verifikasi === 'disetujui')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-emerald-100 text-emerald-700">Terverifikasi</span>
                                        @elseif ($item->status_verifikasi === 'perlu_revisi')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-rose-100 text-rose-700">Perlu Revisi</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-amber-100 text-amber-700">Menunggu</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-xs text-slate-600 max-w-xs">
                                        @if ($item->catatan_verifikasi)
                                            <div class="p-2 bg-slate-50 rounded-lg border border-slate-100">
                                                {{ $item->catatan_verifikasi }}
                                            </div>
                                        @else
                                            <span class="text-slate-400 italic">-</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        @if ($item->bukti_laporan)
                                            <a href="{{ asset('storage/' . $item->bukti_laporan) }}" target="_blank"
                                                class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium rounded-lg transition inline-flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <span>File</span>
                                            </a>
                                        @else
                                            <span class="text-xs text-slate-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-8 text-center text-slate-400">
                                        Divisi Anda belum pernah mengirimkan laporan mingguan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($riwayat->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100">
                        {{ $riwayat->links() }}
                    </div>
                @endif
            </div>

        </main>
    </div>

    {{-- MODAL KIRIM LAPORAN --}}
    <div id="modal-kirim-laporan" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-800">Kirim Laporan Laba/Rugi Mingguan</h3>
                <button type="button" onclick="closeModal('modal-kirim-laporan')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('kadiv.laporanLabaRugi.store') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="periode" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Periode Bulan</label>
                        <input type="month" id="periode" name="periode" value="{{ now()->format('Y-m') }}" required
                            class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                    </div>

                    <div>
                        <label for="minggu_ke" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Minggu Ke</label>
                        <select id="minggu_ke" name="minggu_ke" required
                            class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                            <option value="1">Minggu Ke-1</option>
                            <option value="2">Minggu Ke-2</option>
                            <option value="3">Minggu Ke-3</option>
                            <option value="4">Minggu Ke-4</option>
                            <option value="5">Minggu Ke-5</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="tanggal_mulai" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tanggal Mulai</label>
                        <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="{{ now()->startOfWeek()->toDateString() }}" required
                            class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                    </div>

                    <div>
                        <label for="tanggal_selesai" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tanggal Selesai</label>
                        <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="{{ now()->endOfWeek()->toDateString() }}" required
                            class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="total_pendapatan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Total Pendapatan (Rp)</label>
                        <input type="number" id="total_pendapatan" name="total_pendapatan" min="0" step="1" value="0" required
                            class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                    </div>

                    <div>
                        <label for="total_pengeluaran" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Total Pengeluaran / Beban (Rp)</label>
                        <input type="number" id="total_pengeluaran" name="total_pengeluaran" min="0" step="1" value="0" required
                            class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                    </div>
                </div>

                <div>
                    <label for="bukti_laporan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Upload File Laporan / Rekap (PDF/Excel, Wajib)</label>
                    <input type="file" id="bukti_laporan" name="bukti_laporan" accept=".pdf,.xlsx,.xls,.jpg,.jpeg,.png" required
                        class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                </div>

                <div>
                    <label for="catatan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Catatan Tambahan (Opsional)</label>
                    <textarea id="catatan" name="catatan" rows="2" placeholder="Catatan operasional minggu ini..."
                        class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeModal('modal-kirim-laporan')"
                        class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-xs font-semibold text-white bg-[#044564] hover:bg-[#03334a] rounded-xl shadow-sm transition">
                        Kirim Laporan
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
