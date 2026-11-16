<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Dana Operasional - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">

    @include('component_kadiv.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')

        <main class="flex-1 overflow-y-auto p-8 pb-10">

            {{-- Header & Riwayat Link --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Pengajuan Dana Operasional Divisi</h1>
                    <p class="text-sm text-slate-500 mt-1">Ajukan permohonan dana operasional atau kegiatan proyek ke bagian Finance.</p>
                </div>
                <div>
                    <a href="{{ route('kadiv.pengajuanDana.riwayat') }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 shadow-sm transition">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Lihat Riwayat Pengajuan</span>
                    </a>
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
                    <p class="font-bold mb-1">Gagal Mengirim Pengajuan:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Status Pagu Divisi Bulan Ini --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pagu Divisi Bulan Ini ({{ $periode }})</span>
                    <h3 class="text-xl font-bold text-slate-800 mt-2">Rp {{ number_format($totalAlokasi, 0, ',', '.') }}</h3>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Dana yang Sudah Dicairkan</span>
                    <h3 class="text-xl font-bold text-rose-600 mt-2">Rp {{ number_format($totalTerpakai, 0, ',', '.') }}</h3>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Sisa Pagu Anggaran Tersedia</span>
                    <h3 class="text-xl font-bold {{ $sisaPagu >= 0 ? 'text-emerald-600' : 'text-rose-600' }} mt-2">
                        {{ \App\Helpers\FormatAkuntansi::formatRupiah($sisaPagu) }}
                    </h3>
                </div>
            </div>

            {{-- Form Pengajuan Dana --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 max-w-3xl">
                <div class="pb-4 mb-5 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-800">Form Permohonan Dana Baru</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Lengkapi formulir di bawah ini dengan jelas beserta estimasi biaya dan proposal (jika ada).</p>
                </div>

                <form method="POST" action="{{ route('kadiv.pengajuanDana.store') }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <div>
                        <label for="judul_pengajuan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Judul Pengajuan / Kebutuhan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="judul_pengajuan" name="judul_pengajuan" value="{{ old('judul_pengajuan') }}"
                            placeholder="Contoh: Pengadaan Kabel Jaringan & Router Switch" required
                            class="w-full text-sm px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                    </div>

                    <div>
                        <label for="nominal_diajukan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nominal Dana yang Diajukan (Rp) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" id="nominal_diajukan" name="nominal_diajukan" value="{{ old('nominal_diajukan') }}"
                            min="1" step="1" placeholder="Contoh: 1500000" required
                            class="w-full text-sm px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">
                    </div>

                    <div>
                        <label for="keperluan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Uraian / Keperluan Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <textarea id="keperluan" name="keperluan" rows="4" required
                            placeholder="Jelaskan secara rinci rincian biaya atau tujuan pengeluaran..."
                            class="w-full text-sm px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#19A7CE]">{{ old('keperluan') }}</textarea>
                    </div>

                    <div>
                        <label for="lampiran_proposal" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Lampiran Proposal / RAB / Dokumen Pendukung (Opsional)
                        </label>
                        <input type="file" id="lampiran_proposal" name="lampiran_proposal" accept=".pdf,.xlsx,.xls,.doc,.docx"
                            class="w-full text-xs text-slate-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                        <p class="text-[11px] text-slate-400 mt-1">Format didukung: PDF, Excel (.xlsx, .xls), Word (.doc, .docx). Maksimal 5MB.</p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="reset" class="px-5 py-2.5 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                            Reset Form
                        </button>
                        <button type="submit" class="px-6 py-2.5 text-xs font-bold text-white bg-[#044564] hover:bg-[#03334a] rounded-xl shadow-sm transition">
                            Kirim Pengajuan ke Finance
                        </button>
                    </div>
                </form>
            </div>

        </main>
    </div>

</body>

</html>
