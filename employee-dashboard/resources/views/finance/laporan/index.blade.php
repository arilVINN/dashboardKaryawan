<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Laporan Laba/Rugi - PT Silindo</title>
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
                    <h1 class="text-2xl font-bold text-slate-800">Verifikasi Laporan Laba / Rugi Divisi</h1>
                    <p class="text-sm text-slate-500 mt-1">Review dan verifikasi laporan mingguan yang dikirim oleh kepala divisi.</p>
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

            {{-- Filter Panel (Auto-refresh on change) --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm mb-6">
                <form id="filterForm" method="GET" action="{{ route('kadiv.keuangan.laporan') }}" class="flex flex-col sm:flex-row items-end gap-4">
                    <div class="w-full sm:w-64">
                        <label for="periode" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Periode Bulan</label>
                        <input type="month" id="periode" name="periode" value="{{ $periode }}"
                            onchange="this.form.submit()"
                            class="w-full text-sm font-medium text-slate-700 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE] cursor-pointer transition">
                    </div>

                    <div class="w-full sm:w-64">
                        <label for="divisi_id_divisi" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Divisi</label>
                        <select id="divisi_id_divisi" name="divisi_id_divisi"
                            onchange="this.form.submit()"
                            class="w-full text-sm font-medium text-slate-700 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE] cursor-pointer transition">
                            <option value="">Semua Divisi</option>
                            @foreach ($divisiList as $div)
                                <option value="{{ $div->id_divisi }}" {{ $divisiId === $div->id_divisi ? 'selected' : '' }}>
                                    {{ $div->nama_divisi }} ({{ $div->kode_divisi }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full sm:w-64">
                        <label for="status" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Status Verifikasi</label>
                        <select id="status" name="status"
                            onchange="this.form.submit()"
                            class="w-full text-sm font-medium text-slate-700 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE] cursor-pointer transition">
                            <option value="">Semua Status</option>
                            <option value="menunggu" {{ $status === 'menunggu' ? 'selected' : '' }}>Menunggu Review</option>
                            <option value="disetujui" {{ $status === 'disetujui' ? 'selected' : '' }}>Disetujui / Terverifikasi</option>
                            <option value="perlu_revisi" {{ $status === 'perlu_revisi' ? 'selected' : '' }}>Perlu Revisi</option>
                        </select>
                    </div>

                    @if (request()->has('periode') || request()->has('divisi_id_divisi') || request()->has('status'))
                        <div>
                            <a href="{{ route('kadiv.keuangan.laporan') }}"
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

            {{-- Table Laporan --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-8">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-50/75">
                                <th class="py-3.5 px-4">Divisi & Pengirim</th>
                                <th class="py-3.5 px-4 text-center">Periode & Minggu</th>
                                <th class="py-3.5 px-4 text-right">Pendapatan</th>
                                <th class="py-3.5 px-4 text-right">Pengeluaran</th>
                                <th class="py-3.5 px-4 text-right">Laba / (Rugi)</th>
                                <th class="py-3.5 px-4 text-center">Margin %</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-4 text-center">Bukti & Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($laporan as $lap)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-4 px-4 font-semibold text-slate-800">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full bg-[#19A7CE]"></span>
                                            <span>{{ $lap->divisi?->nama_divisi }}</span>
                                        </div>
                                        <div class="text-xs text-slate-400 mt-0.5">Dikirim oleh: {{ $lap->pengirim?->karyawan?->nama ?? $lap->pengirim?->username }}</div>
                                    </td>
                                    <td class="py-4 px-4 text-center text-xs text-slate-600">
                                        <span class="font-medium">{{ \Carbon\Carbon::parse($lap->periode.'-01')->isoFormat('MMM Y') }}</span>
                                        <span class="inline-block ml-1 px-2 py-0.5 rounded-md bg-blue-50 text-[#19A7CE] font-bold">M-{{ $lap->minggu_ke }}</span>
                                        <div class="text-[10px] text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($lap->tanggal_mulai)->format('d/m') }} - {{ \Carbon\Carbon::parse($lap->tanggal_selesai)->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="py-4 px-4 text-right font-medium text-emerald-600">
                                        Rp {{ number_format($lap->total_pendapatan, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-4 text-right font-medium text-rose-600">
                                        Rp {{ number_format($lap->total_pengeluaran, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-4 text-right font-bold {{ $lap->laba_bersih >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ \App\Helpers\FormatAkuntansi::formatRupiah($lap->laba_bersih) }}
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $lap->margin_persen > 0 ? 'bg-emerald-50 text-emerald-700' : ($lap->margin_persen < 0 ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-600') }}">
                                            {{ \App\Helpers\FormatAkuntansi::formatMargin($lap->margin_persen) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        @if ($lap->status_verifikasi === 'disetujui')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-emerald-100 text-emerald-700">Disetujui</span>
                                        @elseif ($lap->status_verifikasi === 'perlu_revisi')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-rose-100 text-rose-700">Perlu Revisi</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-amber-100 text-amber-700">Menunggu</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <div class="inline-flex items-center gap-1.5">
                                            @if ($lap->bukti_laporan)
                                                <a href="{{ asset('storage/' . $lap->bukti_laporan) }}" target="_blank"
                                                    class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition"
                                                    title="Unduh File Laporan">
                                                    File
                                                </a>
                                            @endif

                                            <button type="button"
                                                onclick="openVerifModal('{{ $lap->id_klr }}', '{{ $lap->divisi?->nama_divisi }}', '{{ $lap->minggu_ke }}', '{{ $lap->status_verifikasi }}', '{{ addslashes($lap->catatan_verifikasi ?? '') }}')"
                                                class="px-2.5 py-1 bg-[#044564] hover:bg-[#03334a] text-white text-xs font-semibold rounded-lg shadow-sm transition">
                                                Verifikasi
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-slate-400">
                                        Belum ada laporan laba rugi divisi yang masuk.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($laporan->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100">
                        {{ $laporan->withQueryString()->links() }}
                    </div>
                @endif
            </div>

        </main>
    </div>

    {{-- MODAL VERIFIKASI LAPORAN --}}
    <div id="modal-verif" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-100">
            <h3 class="text-base font-bold text-slate-800 mb-1">Verifikasi Laporan Divisi</h3>
            <p id="verif-desc" class="text-xs text-slate-500 mb-4"></p>

            <form id="form-verif" method="POST" action="" class="space-y-4">
                @csrf
                <div>
                    <label for="status_verifikasi" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Status Keputusan</label>
                    <select id="status_verifikasi" name="status_verifikasi" required
                        class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                        <option value="disetujui">Setujui & Verifikasi Laporan</option>
                        <option value="perlu_revisi">Kembalikan (Perlu Revisi)</option>
                    </select>
                </div>

                <div>
                    <label for="catatan_verifikasi" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Catatan / Catatan Revisi</label>
                    <textarea id="catatan_verifikasi" name="catatan_verifikasi" rows="3" placeholder="Berikan arahan perbaikan jika meminta revisi..."
                        class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeModal('modal-verif')"
                        class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-xs font-semibold text-white bg-[#044564] hover:bg-[#03334a] rounded-xl shadow-sm transition">
                        Simpan Verifikasi
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

        function openVerifModal(idKlr, divisiNama, mingguKe, currentStatus, currentCatatan) {
            const form = document.getElementById('form-verif');
            form.action = '/kadiv/keuangan/laporan/' + idKlr + '/verifikasi';
            document.getElementById('verif-desc').textContent = divisiNama + ' - Minggu ke-' + mingguKe;
            if (currentStatus === 'perlu_revisi' || currentStatus === 'disetujui') {
                document.getElementById('status_verifikasi').value = currentStatus;
            } else {
                document.getElementById('status_verifikasi').value = 'disetujui';
            }
            document.getElementById('catatan_verifikasi').value = currentCatatan || '';
            openModal('modal-verif');
        }
    </script>

</body>

</html>
