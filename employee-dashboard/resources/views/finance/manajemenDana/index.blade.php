<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permintaan & Verifikasi Dana - PT Silindo</title>
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
                    <h1 class="text-2xl font-bold text-slate-800">Permintaan & Verifikasi</h1>
                    <p class="text-sm text-slate-500 mt-1">Kelola permintaan dana divisi dan verifikasi laporan laba/rugi mingguan.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        {{ $jumlahMenungguPermintaan }} Permintaan Menunggu
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                        {{ $jumlahMenungguLaporan }} Laporan Belum Diverifikasi
                    </span>
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

            {{-- Tabs + Filter + Table --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">

                {{-- Filter --}}
                <div class="p-5 border-b border-slate-100">
                    <form id="filterForm" method="GET" action="{{ route('kadiv.keuangan.manajemenDana') }}" class="flex flex-wrap items-end gap-4">
                        <input type="hidden" name="tab" value="{{ $tab }}">

                        @if ($tab === 'permintaan')
                            <div class="w-full sm:w-56">
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Status Pengajuan</label>
                                <select name="status" onchange="this.form.submit()"
                                    class="w-full text-sm font-medium text-slate-700 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE] cursor-pointer transition">
                                    <option value="">Semua Status</option>
                                    <option value="menunggu" {{ ($status ?? '') === 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                                    <option value="disetujui" {{ ($status ?? '') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                                    <option value="dicairkan" {{ ($status ?? '') === 'dicairkan' ? 'selected' : '' }}>Dicairkan</option>
                                    <option value="ditolak" {{ ($status ?? '') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                                </select>
                            </div>
                        @else
                            <div class="w-full sm:w-56">
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Periode Bulan</label>
                                <input type="month" name="periode" value="{{ $periode ?? now()->format('Y-m') }}"
                                    onchange="this.form.submit()"
                                    class="w-full text-sm font-medium text-slate-700 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE] cursor-pointer transition">
                            </div>
                            <div class="w-full sm:w-56">
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Status Verifikasi</label>
                                <select name="status" onchange="this.form.submit()"
                                    class="w-full text-sm font-medium text-slate-700 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE] cursor-pointer transition">
                                    <option value="">Semua Status</option>
                                    <option value="menunggu" {{ ($status ?? '') === 'menunggu' ? 'selected' : '' }}>Menunggu Review</option>
                                    <option value="disetujui" {{ ($status ?? '') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                                    <option value="perlu_revisi" {{ ($status ?? '') === 'perlu_revisi' ? 'selected' : '' }}>Perlu Revisi</option>
                                </select>
                            </div>
                        @endif

                        <div class="w-full sm:w-56">
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Divisi</label>
                            <select name="divisi_id" onchange="this.form.submit()"
                                class="w-full text-sm font-medium text-slate-700 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE] cursor-pointer transition">
                                <option value="">Semua Divisi</option>
                                @foreach ($divisiList as $div)
                                    <option value="{{ $div->id_divisi }}" {{ ($divisiId ?? '') === $div->id_divisi ? 'selected' : '' }}>
                                        {{ $div->nama_divisi }} ({{ $div->kode_divisi }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @if (request()->hasAny(['status', 'divisi_id', 'periode']))
                            <a href="{{ route('kadiv.keuangan.manajemenDana', ['tab' => $tab]) }}"
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
                    <a href="{{ route('kadiv.keuangan.manajemenDana', array_merge(request()->except('tab'), ['tab' => 'permintaan'])) }}"
                        class="flex items-center gap-2 px-6 py-3.5 text-sm font-semibold transition border-b-2 {{ $tab === 'permintaan' ? 'border-[#044564] text-[#044564] bg-white' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Permintaan Dana
                        @if ($jumlahMenungguPermintaan > 0)
                            <span class="ml-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-700">{{ $jumlahMenungguPermintaan }}</span>
                        @endif
                    </a>
                    <a href="{{ route('kadiv.keuangan.manajemenDana', array_merge(request()->except('tab'), ['tab' => 'laporan'])) }}"
                        class="flex items-center gap-2 px-6 py-3.5 text-sm font-semibold transition border-b-2 {{ $tab === 'laporan' ? 'border-[#044564] text-[#044564] bg-white' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        Verifikasi Laporan L/R
                        @if ($jumlahMenungguLaporan > 0)
                            <span class="ml-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-700">{{ $jumlahMenungguLaporan }}</span>
                        @endif
                    </a>
                </div>

                {{-- TABLE: Permintaan Dana --}}
                @if ($tab === 'permintaan')
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-50/75">
                                    <th class="py-3.5 px-4">No. Pengajuan</th>
                                    <th class="py-3.5 px-4">Divisi & Pengaju</th>
                                    <th class="py-3.5 px-4">Judul & Keperluan</th>
                                    <th class="py-3.5 px-4 text-right">Nominal</th>
                                    <th class="py-3.5 px-4 text-center">Status</th>
                                    <th class="py-3.5 px-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($permintaan as $p)
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="py-4 px-4 font-mono text-xs font-bold text-slate-700">
                                            {{ $p->nomor_pengajuan }}
                                            <p class="text-[11px] text-slate-400 font-normal mt-0.5">{{ $p->created_at->isoFormat('DD MMM Y HH:mm') }}</p>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="font-semibold text-slate-800">{{ $p->divisi?->nama_divisi }}</div>
                                            <div class="text-xs text-slate-400">{{ $p->diajukanOleh?->karyawan?->nama ?? $p->diajukanOleh?->username }}</div>
                                        </td>
                                        <td class="py-4 px-4 max-w-xs">
                                            <div class="font-medium text-slate-800 truncate">{{ $p->judul_pengajuan }}</div>
                                            <div class="text-xs text-slate-500 truncate mt-0.5">{{ $p->keperluan }}</div>
                                        </td>
                                        <td class="py-4 px-4 text-right font-bold text-slate-800">Rp {{ number_format($p->nominal_diajukan, 0, ',', '.') }}</td>
                                        <td class="py-4 px-4 text-center">
                                            @if ($p->status === 'menunggu')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">Menunggu</span>
                                            @elseif ($p->status === 'disetujui')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">Disetujui</span>
                                            @elseif ($p->status === 'dicairkan')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Dicairkan</span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">Ditolak</span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 text-center">
                                            <div class="inline-flex items-center gap-1.5">
                                                <a href="{{ route('kadiv.keuangan.permintaan.show', $p->id_kpd) }}"
                                                    class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">Detail</a>
                                                @if ($p->isMenunggu())
                                                    <button type="button"
                                                        onclick="openApproveModal('{{ $p->id_kpd }}', '{{ $p->nomor_pengajuan }}', '{{ $p->nominal_diajukan }}')"
                                                        class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">ACC</button>
                                                    <button type="button"
                                                        onclick="openRejectModal('{{ $p->id_kpd }}', '{{ $p->nomor_pengajuan }}')"
                                                        class="px-2.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">Tolak</button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-10 text-center text-slate-400">Tidak ada permintaan dana ditemukan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($permintaan->hasPages())
                        <div class="px-6 py-4 border-t border-slate-100">{{ $permintaan->withQueryString()->links() }}</div>
                    @endif

                {{-- TABLE: Verifikasi Laporan L/R --}}
                @else
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
                                    <th class="py-3.5 px-4 text-center">Aksi</th>
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
                                            <div class="text-xs text-slate-400 mt-0.5">{{ $lap->pengirim?->karyawan?->nama ?? $lap->pengirim?->username }}</div>
                                        </td>
                                        <td class="py-4 px-4 text-center text-xs text-slate-600">
                                            <span class="font-medium">{{ \Carbon\Carbon::parse($lap->periode.'-01')->isoFormat('MMM Y') }}</span>
                                            <span class="inline-block ml-1 px-2 py-0.5 rounded-md bg-blue-50 text-[#19A7CE] font-bold">M-{{ $lap->minggu_ke }}</span>
                                            <div class="text-[10px] text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($lap->tanggal_mulai)->format('d/m') }} - {{ \Carbon\Carbon::parse($lap->tanggal_selesai)->format('d/m/Y') }}</div>
                                        </td>
                                        <td class="py-4 px-4 text-right font-medium text-emerald-600">Rp {{ number_format($lap->total_pendapatan, 0, ',', '.') }}</td>
                                        <td class="py-4 px-4 text-right font-medium text-rose-600">Rp {{ number_format($lap->total_pengeluaran, 0, ',', '.') }}</td>
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
                                                        class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">File</a>
                                                @endif
                                                <button type="button"
                                                    onclick="openVerifModal('{{ $lap->id_klr }}', '{{ $lap->divisi?->nama_divisi }}', '{{ $lap->minggu_ke }}', '{{ $lap->status_verifikasi }}', '{{ addslashes($lap->catatan_verifikasi ?? '') }}')"
                                                    class="px-2.5 py-1 bg-[#044564] hover:bg-[#03334a] text-white text-xs font-semibold rounded-lg shadow-sm transition">Verifikasi</button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-10 text-center text-slate-400">Belum ada laporan laba rugi yang masuk.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($laporan->hasPages())
                        <div class="px-6 py-4 border-t border-slate-100">{{ $laporan->withQueryString()->links() }}</div>
                    @endif
                @endif
            </div>

        </main>
    </div>

    {{-- MODAL APPROVE --}}
    <div id="modal-approve" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-100">
            <h3 class="text-base font-bold text-slate-800 mb-1">Setujui Pengajuan Dana</h3>
            <p id="approve-desc" class="text-xs text-slate-500 mb-4"></p>
            <form id="form-approve" method="POST" action="" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Catatan Finance (Opsional)</label>
                    <textarea id="catatan_approve" name="catatan_finance" rows="3" placeholder="Misal: Disetujui, harap sertakan nota setelah belanja..."
                        class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeModal('modal-approve')" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">Batal</button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition">Konfirmasi Setujui</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL REJECT --}}
    <div id="modal-reject" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-100">
            <h3 class="text-base font-bold text-slate-800 mb-1">Tolak Pengajuan Dana</h3>
            <p id="reject-desc" class="text-xs text-slate-500 mb-4"></p>
            <form id="form-reject" method="POST" action="" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Alasan Penolakan (Wajib)</label>
                    <textarea id="catatan_reject" name="catatan_finance" rows="3" required placeholder="Jelaskan alasan penolakan..."
                        class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeModal('modal-reject')" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">Batal</button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-sm transition">Tolak Pengajuan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL VERIFIKASI LAPORAN --}}
    <div id="modal-verif" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-100">
            <h3 class="text-base font-bold text-slate-800 mb-1">Verifikasi Laporan Divisi</h3>
            <p id="verif-desc" class="text-xs text-slate-500 mb-4"></p>
            <form id="form-verif" method="POST" action="" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Status Keputusan</label>
                    <select id="status_verifikasi" name="status_verifikasi" required
                        class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                        <option value="disetujui">Setujui & Verifikasi Laporan</option>
                        <option value="perlu_revisi">Kembalikan (Perlu Revisi)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Catatan / Arahan Revisi</label>
                    <textarea id="catatan_verifikasi" name="catatan_verifikasi" rows="3" placeholder="Berikan arahan perbaikan jika meminta revisi..."
                        class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeModal('modal-verif')" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">Batal</button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#044564] hover:bg-[#03334a] rounded-xl shadow-sm transition">Simpan Verifikasi</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

        function openApproveModal(idKpd, noReq, nominal) {
            document.getElementById('form-approve').action = '/kadiv/keuangan/permintaan/' + idKpd + '/approve';
            document.getElementById('approve-desc').textContent = 'Anda akan menyetujui pengajuan ' + noReq + ' sebesar Rp ' + parseInt(nominal).toLocaleString('id-ID') + '.';
            openModal('modal-approve');
        }
        function openRejectModal(idKpd, noReq) {
            document.getElementById('form-reject').action = '/kadiv/keuangan/permintaan/' + idKpd + '/reject';
            document.getElementById('reject-desc').textContent = 'Pengajuan ' + noReq + ' akan ditolak dan dikembalikan ke divisi terkait.';
            openModal('modal-reject');
        }
        function openVerifModal(idKlr, divisiNama, mingguKe, currentStatus, currentCatatan) {
            document.getElementById('form-verif').action = '/kadiv/keuangan/laporan/' + idKlr + '/verifikasi';
            document.getElementById('verif-desc').textContent = divisiNama + ' — Minggu ke-' + mingguKe;
            document.getElementById('status_verifikasi').value = (currentStatus === 'perlu_revisi' || currentStatus === 'disetujui') ? currentStatus : 'disetujui';
            document.getElementById('catatan_verifikasi').value = currentCatatan || '';
            openModal('modal-verif');
        }
    </script>

</body>
</html>
