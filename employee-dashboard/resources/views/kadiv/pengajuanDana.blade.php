<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Dana Operasional - PT Silindo</title>
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
                'currentPage' => 'Pengajuan Dana',
                'inline' => true,
            ])

            @php
                $periodeLabel = \Carbon\Carbon::createFromFormat('Y-m', $periode)->locale('id')->isoFormat('MMMM Y');
            @endphp

            {{-- Header --}}
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <x-page-heading title="Pengajuan Dana" icon="document" />
                    <p class="mt-1 text-[13px] text-[#4B5563]">
                        Ajukan permohonan dana operasional atau kegiatan proyek ke bagian Finance.
                    </p>
                    <p class="mt-1 text-[12px] text-[#4B5563]">Periode {{ $periodeLabel }}</p>
                </div>

                <a href="{{ route('kadiv.pengajuanDana.riwayat') }}"
                    class="inline-flex h-10 shrink-0 items-center gap-2 rounded-lg border border-[#D5DEE3] bg-white px-4 text-[13px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] focus-visible:ring-offset-2">
                    <svg class="h-4 w-4 text-[#4B5563]" fill="none" stroke="currentColor" stroke-width="1.8"
                        viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path>
                    </svg>
                    <span>Lihat riwayat</span>
                </a>
            </div>

            {{-- Flash --}}
            @if (session('success'))
                <div
                    class="mt-4 flex items-start gap-2.5 rounded-lg border border-[#BBF7D0] bg-[#F0FDF4] px-3.5 py-2.5 text-[13px] text-[#166534]">
                    <svg class="mt-0.5 h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.707-9.293a1 1 0 0 0-1.414-1.414L9 10.586 7.707 9.293a1 1 0 0 0-1.414 1.414l2 2a1 1 0 0 0 1.414 0l4-4Z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (isset($errors) && $errors->any())
                <div class="mt-4 rounded-lg border border-[#FECACA] bg-[#FEF2F2] px-3.5 py-2.5 text-[13px] text-[#991B1B]">
                    <p class="font-medium">Gagal mengirim pengajuan:</p>
                    <ul class="mt-1 list-inside list-disc space-y-0.5 text-[12px]">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Pagu cards --}}
            <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="flex min-h-[92px] flex-col justify-center rounded-xl border border-[#E2E8EC] bg-white p-3.5 shadow-sm">
                    <p class="text-[13px] font-medium text-[#4B5563]">Pagu divisi bulan ini</p>
                    <p class="mt-1 text-[24px] font-semibold tabular-nums text-[#172B36]">
                        {{ \App\Helpers\FormatAkuntansi::formatRupiah($totalAlokasi) }}
                    </p>
                </div>
                <div class="flex min-h-[92px] flex-col justify-center rounded-xl border border-[#E2E8EC] bg-white p-3.5 shadow-sm">
                    <p class="text-[13px] font-medium text-[#4B5563]">Dana sudah dicairkan</p>
                    <p class="mt-1 text-[24px] font-semibold tabular-nums text-[#172B36]">
                        {{ \App\Helpers\FormatAkuntansi::formatRupiah($totalTerpakai) }}
                    </p>
                </div>
                <div class="flex min-h-[92px] flex-col justify-center rounded-xl border border-[#E2E8EC] bg-white p-3.5 shadow-sm">
                    <p class="text-[13px] font-medium text-[#4B5563]">Sisa pagu tersedia</p>
                    <p class="mt-1 text-[24px] font-semibold tabular-nums {{ $sisaPagu < 0 ? 'text-[#B91C1C]' : 'text-[#172B36]' }}">
                        {{ \App\Helpers\FormatAkuntansi::formatRupiah($sisaPagu) }}
                    </p>
                </div>
            </div>

            {{-- Form --}}
            <div class="mt-5 max-w-3xl rounded-xl border border-[#E2E8EC] bg-white p-5 shadow-sm">
                <div class="border-b border-[#E2E8EC] pb-4">
                    <h2 class="text-[16px] font-semibold text-[#172B36]">Form permohonan dana baru</h2>
                    <p class="mt-1 text-[13px] text-[#4B5563]">
                        Lengkapi formulir dengan jelas beserta estimasi biaya dan proposal jika ada.
                    </p>
                </div>

                <form method="POST" action="{{ route('kadiv.pengajuanDana.store') }}" enctype="multipart/form-data"
                    class="mt-5 space-y-5">
                    @csrf

                    <div>
                        <label for="judul_pengajuan" class="mb-1 block text-[12px] font-medium text-[#4B5563]">
                            Judul pengajuan / kebutuhan <span class="text-[#B91C1C]">*</span>
                        </label>
                        <input type="text" id="judul_pengajuan" name="judul_pengajuan"
                            value="{{ old('judul_pengajuan') }}" placeholder="Contoh: Pengadaan kabel jaringan & router"
                            required
                            class="h-10 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] text-[#172B36] placeholder:text-[#4B5563] focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                    </div>

                    <div>
                        <label for="nominal_diajukan" class="mb-1 block text-[12px] font-medium text-[#4B5563]">
                            Nominal dana yang diajukan (Rp) <span class="text-[#B91C1C]">*</span>
                        </label>
                        <input type="number" id="nominal_diajukan" name="nominal_diajukan"
                            value="{{ old('nominal_diajukan') }}" min="1" step="1" placeholder="Contoh: 1500000"
                            required
                            class="h-10 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] text-[#172B36] placeholder:text-[#4B5563] focus:outline-none focus:ring-2 focus:ring-[#146C94]">
                    </div>

                    <div>
                        <label for="keperluan" class="mb-1 block text-[12px] font-medium text-[#4B5563]">
                            Uraian / keperluan lengkap <span class="text-[#B91C1C]">*</span>
                        </label>
                        <textarea id="keperluan" name="keperluan" rows="4" required
                            placeholder="Jelaskan rincian biaya atau tujuan pengeluaran..."
                            class="w-full rounded-lg border border-[#D5DEE3] bg-white px-3 py-2 text-[13px] text-[#172B36] placeholder:text-[#4B5563] focus:outline-none focus:ring-2 focus:ring-[#146C94]">{{ old('keperluan') }}</textarea>
                    </div>

                    <div>
                        <label for="lampiran_proposal" class="mb-1 block text-[12px] font-medium text-[#4B5563]">
                            Lampiran proposal / RAB / dokumen pendukung (opsional)
                        </label>
                        <input type="file" id="lampiran_proposal" name="lampiran_proposal"
                            accept=".pdf,.xlsx,.xls,.doc,.docx"
                            class="w-full cursor-pointer text-[12px] text-[#4B5563] file:mr-3 file:rounded-lg file:border-0 file:bg-[#F5FAFC] file:px-4 file:py-2 file:text-[12px] file:font-medium file:text-[#172B36] hover:file:bg-[#EAF4F8]">
                        <p class="mt-1 text-[12px] text-[#4B5563]">
                            Format didukung: PDF, Excel (.xlsx, .xls), Word (.doc, .docx). Maksimal 5MB.
                        </p>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-[#E2E8EC] pt-4">
                        <button type="reset"
                            class="inline-flex h-10 items-center rounded-lg border border-[#D5DEE3] bg-white px-4 text-[13px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] focus-visible:ring-offset-2">
                            Reset
                        </button>
                        <button type="submit"
                            class="inline-flex h-10 items-center rounded-lg bg-[#0097B2] px-4 text-[13px] font-medium text-white shadow-sm transition hover:bg-[#008199] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] focus-visible:ring-offset-2">
                            Kirim pengajuan
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

</body>

</html>
