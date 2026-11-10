<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alokasi Anggaran Divisi - PT Silindo</title>
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
                    <h1 class="text-2xl font-bold text-slate-800">Alokasi Pagu Anggaran</h1>
                    <p class="text-sm text-slate-500 mt-1">Tetapkan dan kelola pagu anggaran operasional bulanan untuk masing-masing divisi.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="openModal('modal-tambah-alokasi')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#044564] hover:bg-[#03334a] text-white text-xs font-semibold rounded-xl shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Tetapkan Alokasi Baru</span>
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

            {{-- Filter & Total Summary --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm md:col-span-2 flex items-center justify-between">
                    <form method="GET" action="{{ route('kadiv.keuangan.alokasi') }}" class="flex items-center gap-3 w-full">
                        <div class="flex-1">
                            <label for="periode" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Pilih Periode Bulan</label>
                            <input type="month" id="periode" name="periode" value="{{ $periode }}"
                                class="w-full text-sm font-medium text-slate-700 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]"
                                onchange="this.form.submit()">
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Pagu Periode {{ $periode }}</span>
                    <h3 class="text-2xl font-bold text-[#044564] mt-2">Rp {{ number_format($totalAlokasi, 0, ',', '.') }}</h3>
                </div>
            </div>

            {{-- Tabel Alokasi --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-8">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-50/75">
                                <th class="py-3.5 px-4">Divisi</th>
                                <th class="py-3.5 px-4 text-center">Periode</th>
                                <th class="py-3.5 px-4 text-right">Nominal Alokasi (Pagu)</th>
                                <th class="py-3.5 px-4">Keterangan</th>
                                <th class="py-3.5 px-4">Ditetapkan Oleh</th>
                                <th class="py-3.5 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($alokasi as $item)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-4 px-4 font-semibold text-slate-800">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full bg-[#19A7CE]"></span>
                                            <span>{{ $item->divisi?->nama_divisi }}</span>
                                            <span class="text-xs text-slate-400 font-mono">({{ $item->divisi?->kode_divisi }})</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-center text-xs font-medium text-slate-600">
                                        {{ \Carbon\Carbon::parse($item->periode.'-01')->isoFormat('MMMM Y') }}
                                    </td>
                                    <td class="py-4 px-4 text-right font-bold text-slate-800">
                                        Rp {{ number_format($item->nominal_alokasi, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-4 text-slate-600 text-xs max-w-xs truncate">
                                        {{ $item->keterangan ?? '-' }}
                                    </td>
                                    <td class="py-4 px-4 text-xs text-slate-500">
                                        {{ $item->createdBy?->karyawan?->nama ?? $item->createdBy?->username ?? 'Finance' }}
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <button type="button"
                                            onclick="openEditModal('{{ $item->id_kad }}', '{{ $item->divisi?->nama_divisi }}', '{{ $item->nominal_alokasi }}', '{{ addslashes($item->keterangan ?? '') }}')"
                                            class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-semibold rounded-lg transition inline-flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            <span>Edit</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400">
                                        Belum ada pagu anggaran yang ditetapkan untuk periode {{ $periode }}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($alokasi->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100">
                        {{ $alokasi->withQueryString()->links() }}
                    </div>
                @endif
            </div>

        </main>
    </div>

    {{-- MODAL TAMBAH ALOKASI --}}
    <div id="modal-tambah-alokasi" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-800">Tetapkan Alokasi Anggaran Baru</h3>
                <button type="button" onclick="closeModal('modal-tambah-alokasi')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('kadiv.keuangan.alokasi.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label for="divisi_id_divisi" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Pilih Divisi</label>
                    <select id="divisi_id_divisi" name="divisi_id_divisi" required
                        class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                        <option value="">-- Pilih Divisi --</option>
                        @foreach ($divisiList as $div)
                            <option value="{{ $div->id_divisi }}">{{ $div->nama_divisi }} ({{ $div->kode_divisi }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="modal_periode" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Periode Bulan</label>
                    <input type="month" id="modal_periode" name="periode" value="{{ $periode }}" required
                        class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                </div>

                <div>
                    <label for="nominal_alokasi" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nominal Pagu (Rp)</label>
                    <input type="number" id="nominal_alokasi" name="nominal_alokasi" min="1" step="1" placeholder="Contoh: 15000000" required
                        class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                </div>

                <div>
                    <label for="keterangan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Keterangan / Catatan (Opsional)</label>
                    <textarea id="keterangan" name="keterangan" rows="3" placeholder="Catatan peruntukan pagu anggaran operasional..."
                        class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeModal('modal-tambah-alokasi')"
                        class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-xs font-semibold text-white bg-[#044564] hover:bg-[#03334a] rounded-xl shadow-sm transition">
                        Simpan Alokasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT ALOKASI --}}
    <div id="modal-edit-alokasi" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-800">Edit Pagu Anggaran Divisi</h3>
                <button type="button" onclick="closeModal('modal-edit-alokasi')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form id="form-edit-alokasi" method="POST" action="" class="mt-4 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Divisi</label>
                    <input type="text" id="edit_divisi_nama" readonly
                        class="w-full text-sm px-3 py-2 bg-slate-100 text-slate-500 border border-slate-200 rounded-xl cursor-not-allowed">
                </div>

                <div>
                    <label for="edit_nominal_alokasi" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nominal Pagu Baru (Rp)</label>
                    <input type="number" id="edit_nominal_alokasi" name="nominal_alokasi" min="1" step="1" required
                        class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                </div>

                <div>
                    <label for="edit_keterangan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Keterangan / Catatan Perubahan</label>
                    <textarea id="edit_keterangan" name="keterangan" rows="3"
                        class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeModal('modal-edit-alokasi')"
                        class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-sm transition">
                        Perbarui Pagu
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

        function openEditModal(idKad, namaDivisi, nominal, keterangan) {
            const form = document.getElementById('form-edit-alokasi');
            form.action = '/kadiv/keuangan/alokasi/' + idKad;
            document.getElementById('edit_divisi_nama').value = namaDivisi;
            document.getElementById('edit_nominal_alokasi').value = Math.round(nominal);
            document.getElementById('edit_keterangan').value = keterangan || '';
            openModal('modal-edit-alokasi');
        }
    </script>

</body>

</html>
