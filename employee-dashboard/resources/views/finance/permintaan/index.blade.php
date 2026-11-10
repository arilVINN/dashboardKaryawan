<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permintaan Dana Masuk - PT Silindo</title>
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
                    <h1 class="text-2xl font-bold text-slate-800">Permintaan Dana Masuk</h1>
                    <p class="text-sm text-slate-500 mt-1">Review dan berikan persetujuan pengajuan anggaran operasional dari kepala divisi.</p>
                </div>
                @if ($jumlahMenunggu > 0)
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-xs font-bold">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        <span>{{ $jumlahMenunggu }} Pengajuan Menunggu Review</span>
                    </div>
                @endif
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

            {{-- Filter Status Tabs & Form (Auto-refresh on change) --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm mb-6">
                <form id="filterForm" method="GET" action="{{ route('kadiv.keuangan.permintaan') }}" class="flex flex-col sm:flex-row items-end gap-4">
                    <div class="w-full sm:w-64">
                        <label for="status" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Status Pengajuan</label>
                        <select id="status" name="status"
                            onchange="this.form.submit()"
                            class="w-full text-sm font-medium text-slate-700 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE] cursor-pointer transition">
                            <option value="">Semua Status</option>
                            <option value="menunggu" {{ $status === 'menunggu' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                            <option value="disetujui" {{ $status === 'disetujui' ? 'selected' : '' }}>Disetujui (Siap Dicairkan)</option>
                            <option value="dicairkan" {{ $status === 'dicairkan' ? 'selected' : '' }}>Sudah Dicairkan</option>
                            <option value="ditolak" {{ $status === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>

                    <div class="w-full sm:w-64">
                        <label for="divisi_id" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Filter Divisi</label>
                        <select id="divisi_id" name="divisi_id"
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

                    @if (request()->has('status') || request()->has('divisi_id'))
                        <div>
                            <a href="{{ route('kadiv.keuangan.permintaan') }}"
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

            {{-- Table Permintaan --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-8">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-50/75">
                                <th class="py-3.5 px-4">No. Pengajuan</th>
                                <th class="py-3.5 px-4">Divisi & Pengaju</th>
                                <th class="py-3.5 px-4">Judul & Keperluan</th>
                                <th class="py-3.5 px-4 text-right">Nominal Diajukan</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-4 text-center">Aksi & Review</th>
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
                                    <td class="py-4 px-4 text-right font-bold text-slate-800">
                                        Rp {{ number_format($p->nominal_diajukan, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        @if ($p->status === 'menunggu')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                Menunggu
                                            </span>
                                        @elseif ($p->status === 'disetujui')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                Disetujui
                                            </span>
                                        @elseif ($p->status === 'dicairkan')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Dicairkan
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                Ditolak
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <div class="inline-flex items-center gap-1.5">
                                            <a href="{{ route('kadiv.keuangan.permintaan.show', $p->id_kpd) }}"
                                                class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition"
                                                title="Lihat Detail & Dokumen">
                                                Detail
                                            </a>

                                            @if ($p->isMenunggu())
                                                <button type="button"
                                                    onclick="openApproveModal('{{ $p->id_kpd }}', '{{ $p->nomor_pengajuan }}', '{{ $p->nominal_diajukan }}')"
                                                    class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                                                    ACC
                                                </button>
                                                <button type="button"
                                                    onclick="openRejectModal('{{ $p->id_kpd }}', '{{ $p->nomor_pengajuan }}')"
                                                    class="px-2.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                                                    Tolak
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400">
                                        Tidak ada permintaan dana ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($permintaan->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100">
                        {{ $permintaan->withQueryString()->links() }}
                    </div>
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
                    <label for="catatan_approve" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Catatan Finance (Opsional)</label>
                    <textarea id="catatan_approve" name="catatan_finance" rows="3" placeholder="Misal: Disetujui, harap sertakan nota setelah belanja..."
                        class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeModal('modal-approve')"
                        class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition">
                        Konfirmasi Setujui
                    </button>
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
                    <label for="catatan_reject" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Alasan Penolakan (Wajib)</label>
                    <textarea id="catatan_reject" name="catatan_finance" rows="3" required placeholder="Jelaskan alasan penolakan agar divisi dapat merevisi..."
                        class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeModal('modal-reject')"
                        class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-sm transition">
                        Tolak Pengajuan
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

        function openApproveModal(idKpd, noReq, nominal) {
            const form = document.getElementById('form-approve');
            form.action = '/kadiv/keuangan/permintaan/' + idKpd + '/approve';
            document.getElementById('approve-desc').textContent = 'Anda akan menyetujui pengajuan ' + noReq + ' sebesar Rp ' + parseInt(nominal).toLocaleString('id-ID') + '.';
            openModal('modal-approve');
        }

        function openRejectModal(idKpd, noReq) {
            const form = document.getElementById('form-reject');
            form.action = '/kadiv/keuangan/permintaan/' + idKpd + '/reject';
            document.getElementById('reject-desc').textContent = 'Pengajuan ' + noReq + ' akan ditolak dan dikembalikan ke divisi terkait.';
            openModal('modal-reject');
        }
    </script>

</body>

</html>
