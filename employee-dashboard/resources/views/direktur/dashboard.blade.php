<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executive Dashboard - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 flex h-screen overflow-hidden text-slate-800">

    @include('component_direktur.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        @include('component.topbar')

        <main class="flex-1 overflow-y-auto bg-[#F4F7F9] p-4 sm:p-6 lg:p-8 space-y-6">

            {{-- 1. Executive Welcome Banner --}}
            <div class="bg-gradient-to-r from-[#044564] via-[#0b5d84] to-[#19A7CE] rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-white/15 text-amber-300 text-xs font-semibold uppercase tracking-wider mb-2">
                            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                            Executive Command Center
                        </div>
                        <h1 class="text-2xl md:text-3xl font-bold tracking-tight">Selamat Datang, {{ auth()->user()?->karyawan?->nama ?? 'Direktur' }}</h1>
                        <p class="text-slate-200 text-sm mt-1">Pengawasan terpadu seluruh divisi, keuangan, personalia, dan operasional PT Sinergi Ilmiah Indonesia.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="bg-white/10 backdrop-blur-md rounded-xl px-4 py-3 border border-white/20 text-right">
                            <p class="text-[11px] text-slate-200 uppercase font-medium">Hari ini</p>
                            <p class="text-base font-bold text-white">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
                        </div>
                    </div>
                </div>
                {{-- Decorative background shape --}}
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>
            </div>

            {{-- 2. KPI Cards (4 Pilar Utama) --}}
            <div class="grid grid-cols-1 gap-4 min-[560px]:grid-cols-2 min-[900px]:grid-cols-4">

                {{-- Card Keuangan --}}
                <div class="flex min-h-[132px] flex-col justify-between rounded-xl border border-[#E2E8EC] bg-white p-3.5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-[13px] font-medium text-[#4B5563]">Saldo Kas Bersih</p>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#E3F1F7] text-[#146C94]">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/>
                            </svg>
                        </span>
                    </div>
                    <div>
                        <p class="mt-2 text-[24px] font-semibold leading-tight tabular-nums text-[#172B36]">
                            {{ \App\Helpers\FormatAkuntansi::formatRupiah($saldoKas) }}
                        </p>
                        <div class="mt-1 flex flex-wrap items-center justify-between gap-x-2 gap-y-1">
                            <span class="text-[12px] font-medium text-[#059669]">+{{ \App\Helpers\FormatAkuntansi::formatRupiah($pemasukanBulanIni) }} bln ini</span>
                            <a href="{{ route('kadiv.keuangan') }}" class="text-[12px] font-medium text-[#146C94] hover:underline">
                                Keuangan <span aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Card SDM & Karyawan --}}
                <div class="flex min-h-[132px] flex-col justify-between rounded-xl border border-[#E2E8EC] bg-white p-3.5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-[13px] font-medium text-[#4B5563]">Karyawan Aktif</p>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#E3F1F7] text-[#146C94]">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
                            </svg>
                        </span>
                    </div>
                    <div>
                        <p class="mt-2 text-[24px] font-semibold leading-tight tabular-nums text-[#172B36]">{{ $totalKaryawan }}</p>
                        <div class="mt-1 flex flex-wrap items-center justify-between gap-x-2 gap-y-1">
                            <p class="text-[12px] text-[#4B5563]">Karyawan di {{ $totalDivisi }} divisi</p>
                            <a href="{{ url('/hrd/daftarKaryawan') }}" class="text-[12px] font-medium text-[#146C94] hover:underline">
                                Kelola SDM <span aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Card Tugas & Operasional --}}
                <div class="flex min-h-[132px] flex-col justify-between rounded-xl border border-[#E2E8EC] bg-white p-3.5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-[13px] font-medium text-[#4B5563]">Operasional Tugas</p>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#E3F1F7] text-[#146C94]">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2"/>
                            </svg>
                        </span>
                    </div>
                    <div>
                        <p class="mt-2 text-[24px] font-semibold leading-tight tabular-nums text-[#172B36]">{{ $tugasBerjalan }}</p>
                        <p class="mt-1 text-[12px] {{ $tugasRevisi > 0 ? 'font-medium text-[#B45309]' : 'text-[#4B5563]' }}">
                            {{ $tugasRevisi }} revisi &middot; {{ $tugasMenunggu }} menunggu ACC
                        </p>
                        <div class="mt-1 flex items-center justify-between gap-2">
                            <span class="text-[12px] text-[#4B5563]">{{ $tugasSelesai }} selesai</span>
                            <a href="{{ url('/kadiv/tugas') }}" class="text-[12px] font-medium text-[#146C94] hover:underline">
                                Tugas <span aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Card Approval Pending --}}
                @php $totalApproval = $permintaanDanaPending + $suratIzinPending + $bookingPending; @endphp
                <div @class([
                    'flex min-h-[132px] flex-col justify-between rounded-xl border p-3.5 shadow-sm',
                    'border-[#F5C85C] bg-[#FFFBEB]' => $totalApproval > 0,
                    'border-[#E2E8EC] bg-white' => $totalApproval === 0,
                ])>
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-[13px] font-medium text-[#4B5563]">Menunggu Approval</p>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#FEF0C3] text-[#B45309]">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="12" cy="12" r="8.25"></circle>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5V12l3 1.5"></path>
                            </svg>
                        </span>
                    </div>
                    <div>
                        <p class="mt-2 text-[24px] font-semibold leading-tight tabular-nums text-[#172B36]">{{ $totalApproval }}</p>
                        <div class="mt-1 flex flex-wrap items-center justify-between gap-x-2 gap-y-1">
                            <span class="text-[12px] text-[#4B5563]">{{ $permintaanDanaPending }} dana &middot; {{ $suratIzinPending }} izin &middot; {{ $bookingPending }} booking</span>
                            @if ($totalApproval > 0)
                                <span class="text-[12px] font-medium text-[#B45309]">Perlu tindakan</span>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

            {{-- 3. Dua Kolom: Persetujuan & Monitoring di kiri, Struktur Divisi di kanan --}}
            <div class="grid grid-cols-1 items-start gap-6 min-[900px]:grid-cols-[1.5fr_1fr]">

                {{-- Kolom Kiri: Persetujuan & Monitoring Tugas --}}
                <div class="space-y-6">
                    <div class="flex flex-col gap-2">
                        <div class="px-1">
                            <h2 class="text-lg font-semibold text-[#172B36]">Persetujuan Menunggu</h2>
                        </div>

                        <div class="overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">
                            {{-- Permintaan Dana --}}
                            <div class="px-4 py-4 sm:px-5">
                                <div class="flex items-center justify-between gap-3">
                                    <h3 class="flex items-center gap-1.5 text-[13px] font-medium text-[#172B36]">
                                        <span class="h-2 w-2 rounded-full bg-[#059669]"></span>
                                        Permintaan Dana Divisi (KPD)
                                    </h3>
                                    <a href="{{ route('kadiv.keuangan.manajemenDana') }}" class="shrink-0 text-xs text-[#146C94] hover:underline">Lihat semua &rsaquo;</a>
                                </div>
                                @if ($permintaanDanaList->isEmpty())
                                    <p class="py-3 text-[13px] text-[#4B5563]">Tidak ada permohonan dana yang menunggu persetujuan.</p>
                                @else
                                    <div class="mt-2 divide-y divide-[#EEF2F5]">
                                        @foreach ($permintaanDanaList as $item)
                                            <div class="flex items-center justify-between gap-3 py-2.5">
                                                <div class="min-w-0">
                                                    <p class="truncate text-[13px] font-medium text-[#172B36]">{{ $item->judul_pengajuan }}</p>
                                                    <p class="mt-0.5 text-[12px] text-[#4B5563]">
                                                        {{ $item->divisi->nama_divisi ?? '-' }} &middot;
                                                        <span class="font-medium text-[#059669]">{{ \App\Helpers\FormatAkuntansi::formatRupiah($item->nominal_diajukan) }}</span>
                                                    </p>
                                                </div>
                                                <a href="{{ route('kadiv.keuangan.permintaan.show', $item->id_kpd) }}" class="shrink-0 text-[12px] font-medium text-[#146C94] hover:underline">
                                                    Review
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Surat Izin Karyawan --}}
                            <div class="border-t border-[#E2E8EC] px-4 py-4 sm:px-5">
                                <div class="flex items-center justify-between gap-3">
                                    <h3 class="flex items-center gap-1.5 text-[13px] font-medium text-[#172B36]">
                                        <span class="h-2 w-2 rounded-full bg-[#2563EB]"></span>
                                        Surat Izin &amp; Cuti Karyawan
                                    </h3>
                                    <a href="{{ route('hrd.suratIzin') }}" class="shrink-0 text-xs text-[#146C94] hover:underline">Lihat semua &rsaquo;</a>
                                </div>
                                @if ($suratIzinList->isEmpty())
                                    <p class="py-3 text-[13px] text-[#4B5563]">Tidak ada pengajuan izin yang menunggu persetujuan.</p>
                                @else
                                    <div class="mt-2 divide-y divide-[#EEF2F5]">
                                        @foreach ($suratIzinList as $izin)
                                            <div class="flex items-center justify-between gap-3 py-2.5">
                                                <div class="min-w-0">
                                                    <p class="truncate text-[13px] font-medium text-[#172B36]">
                                                        {{ $izin->karyawan->nama ?? 'Karyawan' }} &middot;
                                                        <span class="text-[#2563EB]">{{ $izin->jenis_izin ?? 'Izin' }}</span>
                                                    </p>
                                                    <p class="mt-0.5 truncate text-[12px] text-[#4B5563]">
                                                        {{ $izin->judul_surat ?? $izin->keterangan ?? 'Permohonan izin' }}
                                                    </p>
                                                </div>
                                                <a href="{{ route('hrd.suratIzin') }}" class="shrink-0 text-[12px] font-medium text-[#146C94] hover:underline">
                                                    Tinjau
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Booking Ruangan / Kendaraan --}}
                            <div class="border-t border-[#E2E8EC] px-4 py-4 sm:px-5">
                                <div class="flex items-center justify-between gap-3">
                                    <h3 class="flex items-center gap-1.5 text-[13px] font-medium text-[#172B36]">
                                        <span class="h-2 w-2 rounded-full bg-[#D97706]"></span>
                                        Peminjaman Fasilitas &amp; Kendaraan
                                    </h3>
                                    <a href="{{ url('/hrd/suratBooking') }}" class="shrink-0 text-xs text-[#146C94] hover:underline">Lihat semua &rsaquo;</a>
                                </div>
                                @if ($bookingList->isEmpty())
                                    <p class="py-3 text-[13px] text-[#4B5563]">Tidak ada permohonan booking fasilitas yang menunggu.</p>
                                @else
                                    <div class="mt-2 divide-y divide-[#EEF2F5]">
                                        @foreach ($bookingList as $book)
                                            <div class="flex items-center justify-between gap-3 py-2.5">
                                                <div class="min-w-0">
                                                    <p class="truncate text-[13px] font-medium text-[#172B36]">
                                                        {{ $book->pemohon?->karyawan?->nama ?? $book->pemohon?->username }} &middot;
                                                        <span class="text-[#B45309]">{{ ucfirst($book->kategori) }}: {{ $book->jenis }}</span>
                                                    </p>
                                                    <p class="mt-0.5 text-[12px] text-[#4B5563]">
                                                        {{ $book->tanggal?->format('d/m/Y') }} &middot; {{ $book->jam_mulai }} - {{ $book->jam_selesai }}
                                                    </p>
                                                </div>
                                                <a href="{{ url('/hrd/suratBooking') }}" class="shrink-0 text-[12px] font-medium text-[#146C94] hover:underline">
                                                    Proses
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Monitoring Tugas Terkini --}}
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between gap-2 px-1">
                            <h2 class="text-lg font-semibold text-[#172B36]">Monitoring Tugas Terkini</h2>
                            <a href="{{ url('/kadiv/tugas') }}" class="shrink-0 text-xs text-[#146C94] hover:underline">Lihat semua &rsaquo;</a>
                        </div>

                        @if ($tugasTerbaru->isEmpty())
                            <div class="overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">
                                <p class="px-4 py-6 text-center text-[13px] text-[#4B5563] sm:px-5">Belum ada tugas yang tercatat di sistem.</p>
                            </div>
                        @else
                            <div class="overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">
                                <div class="divide-y divide-[#EEF2F5]">
                                    @foreach ($tugasTerbaru as $t)
                                        <div class="flex items-center justify-between gap-3 px-4 py-3 transition hover:bg-[#F5FAFC] sm:px-5">
                                            <div class="min-w-0">
                                                <p class="truncate text-[13px] font-medium text-[#172B36]">{{ $t->judul_tugas }}</p>
                                                <p class="mt-0.5 text-[12px] text-[#4B5563]">
                                                    {{ $t->karyawan?->nama ?? '-' }} &middot; {{ $t->karyawan?->divisi?->nama_divisi ?? '-' }}
                                                </p>
                                            </div>
                                            <div class="flex shrink-0 items-center gap-3">
                                                <x-status-badge :status="$t->status_efektif" />
                                                <a href="{{ url('/kadiv/detailTugas/' . $t->id_tugas) }}" class="text-[12px] font-medium text-[#146C94] hover:underline">
                                                    Detail
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Kolom Kanan: Struktur Divisi --}}
                <div class="space-y-6">

                    {{-- Daftar Divisi & Headcount --}}
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between gap-2 px-1">
                            <h2 class="text-lg font-semibold text-[#172B36]">Struktur Divisi &amp; Kekuatan Tim</h2>
                            <a href="{{ url('/hrd/manajemenDivisi') }}" class="shrink-0 text-xs text-[#146C94] hover:underline">Lihat semua &rsaquo;</a>
                        </div>
                        <div class="overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">
                            <div class="grid grid-cols-1 gap-2.5 px-4 py-4 sm:grid-cols-2 sm:px-5">
                                @foreach ($divisiList as $div)
                                    <div class="rounded-lg border border-[#E2E8EC] bg-white p-3 transition hover:border-[#CBD5E1] hover:bg-[#F5FAFC]">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="rounded bg-[#E3F1F7] px-2 py-0.5 text-[11px] font-medium text-[#146C94]">
                                                {{ $div->kode_divisi }}
                                            </span>
                                            <span class="text-[12px] text-[#4B5563]">{{ $div->jumlah_karyawan }} anggota</span>
                                        </div>
                                        <p class="mt-2 truncate text-[13px] font-medium text-[#172B36]">{{ $div->nama_divisi }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Notifikasi Terbaru --}}
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between gap-2 px-1">
                            <h2 class="text-lg font-semibold text-[#172B36]">Notifikasi Terbaru</h2>
                            <a href="{{ route('direktur.notifikasi') }}" class="shrink-0 text-xs text-[#146C94] hover:underline">Lihat semua &rsaquo;</a>
                        </div>
                        <div class="overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">
                            @if ($notifikasiTerbaru->isEmpty())
                                <p class="px-4 py-6 text-center text-[13px] text-[#4B5563] sm:px-5">Belum ada notifikasi.</p>
                            @else
                                <div class="divide-y divide-[#EEF2F5]">
                                    @foreach ($notifikasiTerbaru as $notifikasi)
                                        <div class="px-4 py-3 sm:px-5">
                                            <p class="text-[13px] font-medium text-[#172B36]">{{ $notifikasi->judul_notifikasi }}</p>
                                            <p class="mt-0.5 text-[12px] leading-[1.45] text-[#4B5563]">{{ $notifikasi->displayMessage() }}</p>
                                            <p class="mt-1 text-[11px] text-[#94A3B8]">
                                                {{ $notifikasi->created_at?->locale('id')->translatedFormat('j M Y, H:i') ?? $notifikasi->tanggal_notifikasi }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                </div>

            </div>

        </main>

    </div>

</body>

</html>
