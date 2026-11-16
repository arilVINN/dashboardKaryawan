<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permintaan & Verifikasi Dana - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#F4F7F9] flex h-screen overflow-hidden">

    @include('component_kadiv.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')

        <main class="flex-1 overflow-y-auto bg-[#F4F7F9] p-4 pb-10 sm:p-6 lg:p-8">
            @include('component.breadcrumbs', [
                'rootText' => 'Dashboard',
                'rootUrl' => url('/kadiv/dashboard'),
                'parentText' => 'Keuangan',
                'parentUrl' => route('kadiv.keuangan'),
                'currentPage' => 'Permintaan & Verifikasi',
                'inline' => true,
            ])


            {{-- Header --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                <div>
                    <x-page-heading title="Permintaan & Verifikasi" icon="document" />
                    <p class="mt-1 text-[13px] text-[#4B5563]">Kelola permintaan dana divisi dan verifikasi laporan laba/rugi mingguan.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-[#FEF0C3] text-[#B45309]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#D97706] animate-pulse"></span>
                        {{ $jumlahMenungguPermintaan }} Permintaan Menunggu
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-[#E3F1F7] text-[#146C94] border border-[#7DD3C8]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#146C94] animate-pulse"></span>
                        {{ $jumlahMenungguLaporan }} Laporan Belum Diverifikasi
                    </span>
                </div>
            </div>

            {{-- Flash Alerts --}}
            @if (session('success'))
                <div class="mb-4 p-4 rounded-xl bg-[#F0FDF4] border border-[#BBF7D0] text-[#166534] text-[13px] flex items-center gap-2 shadow-sm">
                    <svg class="w-5 h-5 text-[#047857] shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-4 rounded-xl bg-[#FEF2F2] border border-[#FECACA] text-[#991B1B] text-[13px] flex items-center gap-2 shadow-sm">
                    <svg class="w-5 h-5 text-[#B91C1C] shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- Tabs + Filter + Table --}}
            <div class="bg-white rounded-xl border border-[#E2E8EC] shadow-sm overflow-hidden mb-6">

                {{-- Filter --}}
                <div class="p-5 border-b border-[#E2E8EC]">
                    <form id="filterForm" method="GET" action="{{ route('kadiv.keuangan.manajemenDana') }}" class="flex flex-wrap items-end gap-4">
                        <input type="hidden" name="tab" value="{{ $tab }}">

                        @if ($tab === 'permintaan')
                            <div class="w-full sm:w-56">
                                <label class="block text-[12px] font-medium text-[#4B5563] mb-1">Status Pengajuan</label>
                                <select name="status" onchange="this.form.submit()"
                                    class="h-9 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] font-medium text-[#172B36] focus:outline-none focus:ring-2 focus:ring-[#146C94] cursor-pointer transition">
                                    <option value="">Semua Status</option>
                                    <option value="menunggu" {{ ($status ?? '') === 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                                    <option value="disetujui" {{ ($status ?? '') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                                    <option value="dicairkan" {{ ($status ?? '') === 'dicairkan' ? 'selected' : '' }}>Dicairkan</option>
                                    <option value="ditolak" {{ ($status ?? '') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                                </select>
                            </div>
                        @else
                            <div class="w-full sm:w-56">
                                <label class="block text-[12px] font-medium text-[#4B5563] mb-1">Periode Bulan</label>
                                <input type="month" name="periode" value="{{ $periode ?? now()->format('Y-m') }}"
                                    onchange="this.form.submit()"
                                    class="h-9 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] font-medium text-[#172B36] focus:outline-none focus:ring-2 focus:ring-[#146C94] cursor-pointer transition">
                            </div>
                            <div class="w-full sm:w-56">
                                <label class="block text-[12px] font-medium text-[#4B5563] mb-1">Status Verifikasi</label>
                                <select name="status" onchange="this.form.submit()"
                                    class="h-9 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] font-medium text-[#172B36] focus:outline-none focus:ring-2 focus:ring-[#146C94] cursor-pointer transition">
                                    <option value="">Semua Status</option>
                                    <option value="menunggu" {{ ($status ?? '') === 'menunggu' ? 'selected' : '' }}>Menunggu Review</option>
                                    <option value="disetujui" {{ ($status ?? '') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                                    <option value="perlu_revisi" {{ ($status ?? '') === 'perlu_revisi' ? 'selected' : '' }}>Perlu Revisi</option>
                                </select>
                            </div>
                        @endif

                        <div class="w-full sm:w-56">
                            <label class="block text-[12px] font-medium text-[#4B5563] mb-1">Divisi</label>
                            <select name="divisi_id" onchange="this.form.submit()"
                                class="h-9 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] font-medium text-[#172B36] focus:outline-none focus:ring-2 focus:ring-[#146C94] cursor-pointer transition">
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
                                class="inline-flex items-center gap-1.5 px-3 py-2 bg-[#F5FAFC] hover:bg-[#EAF4F8] text-[#4B5563] text-xs font-medium rounded-xl transition h-[38px]">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Reset
                            </a>
                        @endif
                    </form>
                </div>

                {{-- Tab Navigation --}}
                <div class="flex border-b border-[#E2E8EC] bg-[#F5FAFC]">
                    <a href="{{ route('kadiv.keuangan.manajemenDana', array_merge(request()->except('tab'), ['tab' => 'permintaan'])) }}"
                        class="flex items-center gap-2 px-5 py-3 text-[13px] font-semibold transition border-b-2 {{ $tab === 'permintaan' ? 'border-[#146C94] text-[#146C94] bg-white' : 'border-transparent text-[#4B5563] hover:text-[#172B36]' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Permintaan Dana
                        @if ($jumlahMenungguPermintaan > 0)
                            <span class="ml-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FEF0C3] text-[#B45309]">{{ $jumlahMenungguPermintaan }}</span>
                        @endif
                    </a>
                    <a href="{{ route('kadiv.keuangan.manajemenDana', array_merge(request()->except('tab'), ['tab' => 'laporan'])) }}"
                        class="flex items-center gap-2 px-5 py-3 text-[13px] font-semibold transition border-b-2 {{ $tab === 'laporan' ? 'border-[#146C94] text-[#146C94] bg-white' : 'border-transparent text-[#4B5563] hover:text-[#172B36]' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        Verifikasi Laporan L/R
                        @if ($jumlahMenungguLaporan > 0)
                            <span class="ml-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#E3F1F7] text-[#146C94] border border-[#7DD3C8]">{{ $jumlahMenungguLaporan }}</span>
                        @endif
                    </a>
                </div>

                {{-- TABLE: Permintaan Dana --}}
                @if ($tab === 'permintaan')
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-[13px] border-collapse">
                            <thead>
                                <tr class="border-b border-[#E2E8EC] text-[12px] font-medium text-[#4B5563] bg-[#EAF4F8]">
                                    <th class="py-3.5 px-4">No. Pengajuan</th>
                                    <th class="py-3.5 px-4">Divisi & Pengaju</th>
                                    <th class="py-3.5 px-4">Judul & Keperluan</th>
                                    <th class="py-3.5 px-4 text-right">Nominal</th>
                                    <th class="py-3.5 px-4 text-center">Status</th>
                                    <th class="py-3.5 px-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#EEF2F5]">
                                @forelse ($permintaan as $p)
                                    <tr class="hover:bg-[#F5FAFC] transition">
                                        <td class="py-3 px-4 font-mono text-xs font-bold text-[#172B36]">
                                            {{ $p->nomor_pengajuan }}
                                            <p class="text-[11px] text-[#4B5563] font-normal mt-0.5">{{ $p->created_at->isoFormat('DD MMM Y HH:mm') }}</p>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="font-semibold text-[#172B36]">{{ $p->divisi?->nama_divisi }}</div>
                                            <div class="text-xs text-[#4B5563]">{{ $p->diajukanOleh?->karyawan?->nama ?? $p->diajukanOleh?->username }}</div>
                                        </td>
                                        <td class="py-3 px-4 max-w-xs">
                                            <div class="font-medium text-[#172B36] truncate">{{ $p->judul_pengajuan }}</div>
                                            <div class="text-xs text-[#4B5563] truncate mt-0.5">{{ $p->keperluan }}</div>
                                        </td>
                                        <td class="py-3 px-4 text-right font-bold text-[#172B36]">Rp {{ number_format($p->nominal_diajukan, 0, ',', '.') }}</td>
                                        <td class="py-3 px-4 text-center">
                                            @if ($p->status === 'menunggu')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-[#FEF0C3] text-[#B45309] border border-[#F5C85C]">Menunggu</span>
                                            @elseif ($p->status === 'disetujui')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-[#D5F3F0] text-[#0D9488] border border-[#7DD3C8]">Disetujui</span>
                                            @elseif ($p->status === 'dicairkan')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-[#E2E8EC] text-[#4B5563] border border-[#D5DEE3]">Dicairkan</span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-[#FDE0E0] text-[#B91C1C] border border-[#F5A3A3]">Ditolak</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <a href="{{ route('kadiv.keuangan.permintaan.show', $p->id_kpd) }}"
                                                class="text-[12px] font-medium text-[#146C94] transition hover:text-[#0f5878] hover:underline">Detail</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-10 text-center text-[#4B5563]">Tidak ada permintaan dana ditemukan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($permintaan->hasPages())
                        <div class="px-6 py-4 border-t border-[#E2E8EC]">{{ $permintaan->withQueryString()->links() }}</div>
                    @endif

                {{-- TABLE: Verifikasi Laporan L/R --}}
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-[13px] border-collapse">
                            <thead>
                                <tr class="border-b border-[#E2E8EC] text-[12px] font-medium text-[#4B5563] bg-[#EAF4F8]">
                                    <th class="py-3.5 px-4">Divisi & Pengirim</th>
                                    <th scope="col" class="px-4 py-3 text-center text-[12px] font-medium">Periode &amp; minggu</th>
                                    <th class="py-3.5 px-4 text-right">Pendapatan</th>
                                    <th class="py-3.5 px-4 text-right">Pengeluaran</th>
                                    <th class="py-3.5 px-4 text-right">Laba / (Rugi)</th>
                                    <th class="py-3.5 px-4 text-center">Margin %</th>
                                    <th class="py-3.5 px-4 text-center">Status</th>
                                    <th class="py-3.5 px-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#EEF2F5]">
                                @forelse ($laporan as $lap)
                                    <tr class="hover:bg-[#F5FAFC] transition">
                                        <td class="py-3 px-4 font-semibold text-[#172B36]">
                                            <div class="flex items-center gap-2">
                                                <span class="w-2.5 h-2.5 rounded-full bg-[#146C94]"></span>
                                                <span>{{ $lap->divisi?->nama_divisi }}</span>
                                            </div>
                                            <div class="text-xs text-[#4B5563] mt-0.5">{{ $lap->pengirim?->karyawan?->nama ?? $lap->pengirim?->username }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <x-date-range :start="$lap->tanggal_mulai" :end="$lap->tanggal_selesai"
                                                class="text-[12px] font-medium text-[#172B36]" />
                                            <div class="mt-1">
                                                <span
                                                    class="inline-flex h-[22px] items-center rounded-md border border-[#E2E8F0] bg-[#F1F5F9] px-2 text-[11px] font-medium text-[#475569]">
                                                    Minggu ke-{{ $lap->minggu_ke }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-right font-medium text-[#047857]">Rp {{ number_format($lap->total_pendapatan, 0, ',', '.') }}</td>
                                        <td class="py-3 px-4 text-right font-medium text-[#B91C1C]">Rp {{ number_format($lap->total_pengeluaran, 0, ',', '.') }}</td>
                                        <td class="py-3 px-4 text-right font-bold {{ $lap->laba_bersih >= 0 ? 'text-[#047857]' : 'text-[#B91C1C]' }}">
                                            {{ \App\Helpers\FormatAkuntansi::formatRupiah($lap->laba_bersih) }}
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $lap->margin_persen > 0 ? 'bg-[#D5F3F0] text-[#0D9488]' : ($lap->margin_persen < 0 ? 'bg-[#FDE0E0] text-[#B91C1C]' : 'bg-[#F5FAFC] text-[#4B5563]') }}">
                                                {{ \App\Helpers\FormatAkuntansi::formatMargin($lap->margin_persen) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if ($lap->status_verifikasi === 'disetujui')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-[#D5F3F0] text-[#0D9488]">Disetujui</span>
                                            @elseif ($lap->status_verifikasi === 'perlu_revisi')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-[#FDE0E0] text-[#B91C1C]">Perlu Revisi</span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-[#FEF0C3] text-[#B45309]">Menunggu</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="inline-flex items-center gap-1.5">
                                                @if ($lap->bukti_laporan)
                                                    <a href="{{ asset('storage/' . $lap->bukti_laporan) }}" target="_blank"
                                                        class="px-2 py-1 bg-[#F5FAFC] hover:bg-[#EAF4F8] text-[#172B36] text-xs font-semibold rounded-lg transition">File</a>
                                                @endif
                                                <button type="button"
                                                    onclick="openVerifModal('{{ $lap->id_klr }}', '{{ $lap->divisi?->nama_divisi }}', '{{ $lap->minggu_ke }}', '{{ $lap->status_verifikasi }}', '{{ addslashes($lap->catatan_verifikasi ?? '') }}')"
                                                    class="px-2.5 py-1 bg-[#0097B2] hover:bg-[#008199] text-white text-xs font-semibold rounded-lg shadow-sm transition">Verifikasi</button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-10 text-center text-[#4B5563]">Belum ada laporan laba rugi yang masuk.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($laporan->hasPages())
                        <div class="px-6 py-4 border-t border-[#E2E8EC]">{{ $laporan->withQueryString()->links() }}</div>
                    @endif
                @endif
            </div>

        </main>
    </div>

    {{-- MODAL APPROVE --}}
    <div id="modal-approve" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl border border-[#E2E8EC]">
            <h3 class="text-base font-bold text-[#172B36] mb-1">Setujui Pengajuan Dana</h3>
            <p id="approve-desc" class="text-xs text-[#4B5563] mb-4"></p>
            <form id="form-approve" method="POST" action="" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-[#172B36]  mb-1">Catatan Finance (Opsional)</label>
                    <textarea id="catatan_approve" name="catatan_finance" rows="3" placeholder="Misal: Disetujui, harap sertakan nota setelah belanja..."
                        class="w-full text-sm px-3 py-2 bg-[#F4F7F9] border border-[#E2E8EC] rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-[#E2E8EC]">
                    <button type="button" onclick="closeModal('modal-approve')" class="px-4 py-2 text-xs font-semibold text-[#4B5563] bg-[#F5FAFC] hover:bg-[#EAF4F8] rounded-xl transition">Batal</button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#047857] hover:bg-[#065F46] rounded-xl shadow-sm transition">Konfirmasi Setujui</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL REJECT --}}
    <div id="modal-reject" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl border border-[#E2E8EC]">
            <h3 class="text-base font-bold text-[#172B36] mb-1">Tolak Pengajuan Dana</h3>
            <p id="reject-desc" class="text-xs text-[#4B5563] mb-4"></p>
            <form id="form-reject" method="POST" action="" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-[#172B36]  mb-1">Alasan Penolakan (Wajib)</label>
                    <textarea id="catatan_reject" name="catatan_finance" rows="3" required placeholder="Jelaskan alasan penolakan..."
                        class="w-full text-sm px-3 py-2 bg-[#F4F7F9] border border-[#E2E8EC] rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-[#E2E8EC]">
                    <button type="button" onclick="closeModal('modal-reject')" class="px-4 py-2 text-xs font-semibold text-[#4B5563] bg-[#F5FAFC] hover:bg-[#EAF4F8] rounded-xl transition">Batal</button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#B91C1C] hover:bg-[#991B1B] rounded-xl shadow-sm transition">Tolak Pengajuan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL VERIFIKASI LAPORAN --}}
    <div id="modal-verif" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl border border-[#E2E8EC]">
            <h3 class="text-base font-bold text-[#172B36] mb-1">Verifikasi Laporan Divisi</h3>
            <p id="verif-desc" class="text-xs text-[#4B5563] mb-4"></p>
            <form id="form-verif" method="POST" action="" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-[#172B36]  mb-1">Status Keputusan</label>
                    <select id="status_verifikasi" name="status_verifikasi" required
                        class="w-full text-sm px-3 py-2 bg-[#F4F7F9] border border-[#E2E8EC] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                        <option value="disetujui">Setujui & Verifikasi Laporan</option>
                        <option value="perlu_revisi">Kembalikan (Perlu Revisi)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#172B36]  mb-1">Catatan / Arahan Revisi</label>
                    <textarea id="catatan_verifikasi" name="catatan_verifikasi" rows="3" placeholder="Berikan arahan perbaikan jika meminta revisi..."
                        class="w-full text-sm px-3 py-2 bg-[#F4F7F9] border border-[#E2E8EC] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#146C94]"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-[#E2E8EC]">
                    <button type="button" onclick="closeModal('modal-verif')" class="px-4 py-2 text-xs font-semibold text-[#4B5563] bg-[#F5FAFC] hover:bg-[#EAF4F8] rounded-xl transition">Batal</button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#0097B2] hover:bg-[#008199] rounded-xl shadow-sm transition">Simpan Verifikasi</button>
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
