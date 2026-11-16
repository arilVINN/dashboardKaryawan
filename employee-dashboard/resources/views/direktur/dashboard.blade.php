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

        <main class="flex-1 overflow-y-auto p-6 md:p-8 space-y-6">

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
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

                {{-- Card Keuangan --}}
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Saldo Kas Bersih</span>
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                            Rp
                        </div>
                    </div>
                    <p class="text-xl md:text-2xl font-black text-slate-900 mt-3">
                        {{ \App\Helpers\FormatAkuntansi::formatRupiah($saldoKas) }}
                    </p>
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                        <span class="text-emerald-600 font-medium">+{{ \App\Helpers\FormatAkuntansi::formatRupiah($pemasukanBulanIni) }} bln ini</span>
                        <a href="{{ route('kadiv.keuangan') }}" class="text-[#19A7CE] hover:underline font-semibold">Keuangan &rarr;</a>
                    </div>
                </div>

                {{-- Card SDM & Karyawan --}}
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Karyawan Aktif</span>
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mt-3">
                        <p class="text-2xl font-black text-slate-900">{{ $totalKaryawan }}</p>
                        <span class="text-xs text-slate-500 font-medium">orang di {{ $totalDivisi }} divisi</span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                        <span>Personalia & Organisasi</span>
                        <a href="{{ url('/hrd/daftarKaryawan') }}" class="text-[#19A7CE] hover:underline font-semibold">Kelola SDM &rarr;</a>
                    </div>
                </div>

                {{-- Card Tugas & Operasional --}}
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Operasional Tugas</span>
                        <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mt-3">
                        <p class="text-2xl font-black text-slate-900">{{ $tugasBerjalan }}</p>
                        <span class="text-xs text-slate-500 font-medium">berjalan / {{ $tugasSelesai }} selesai</span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                        <span class="{{ $tugasRevisi > 0 ? 'text-amber-600 font-semibold' : '' }}">
                            {{ $tugasRevisi }} revisi &middot; {{ $tugasMenunggu }} menunggu ACC
                        </span>
                        <a href="{{ url('/kadiv/tugas') }}" class="text-[#19A7CE] hover:underline font-semibold">Tugas Divisi &rarr;</a>
                    </div>
                </div>

                {{-- Card Approval Pending --}}
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Menunggu Approval</span>
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mt-3">
                        <p class="text-2xl font-black text-amber-600">{{ $permintaanDanaPending + $suratIzinPending + $bookingPending }}</p>
                        <span class="text-xs text-slate-500 font-medium">pengajuan tertunda</span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                        <span>{{ $permintaanDanaPending }} Dana, {{ $suratIzinPending }} Izin, {{ $bookingPending }} Booking</span>
                        <span class="font-bold text-amber-600">Perlu Tindakan</span>
                    </div>
                </div>

            </div>

            {{-- 3. Dua Kolom: Permintaan Approval & Tugas Berjalan --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

                {{-- Kolom Kiri: Tindakan Persetujuan Eksekutif --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Persetujuan Menunggu (Approval)</h2>
                            <p class="text-xs text-slate-500">Permintaan dana, cuti/izin, dan fasilitas yang membutuhkan persetujuan.</p>
                        </div>
                    </div>

                    {{-- Permintaan Dana --}}
                    <div>
                        <div class="flex items-center justify-between mb-2.5">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Permintaan Dana Divisi (KPD)
                            </h3>
                            <a href="{{ route('kadiv.keuangan.manajemenDana') }}" class="text-xs text-[#19A7CE] hover:underline font-medium">Lihat Semua &rarr;</a>
                        </div>
                        @if ($permintaanDanaList->isEmpty())
                            <p class="text-xs text-slate-400 italic py-2">Tidak ada permohonan dana yang menunggu persetujuan.</p>
                        @else
                            <div class="space-y-2">
                                @foreach ($permintaanDanaList as $item)
                                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 hover:bg-slate-100/70 transition">
                                        <div class="min-w-0 pr-3">
                                            <p class="text-xs font-bold text-slate-900 truncate">{{ $item->judul_pengajuan }}</p>
                                            <p class="text-[11px] text-slate-500">
                                                Divisi: <span class="font-medium text-slate-700">{{ $item->divisi->nama_divisi ?? '-' }}</span> &bull; 
                                                Nominal: <span class="font-bold text-emerald-600">{{ \App\Helpers\FormatAkuntansi::formatRupiah($item->nominal_diajukan) }}</span>
                                            </p>
                                        </div>
                                        <a href="{{ route('kadiv.keuangan.permintaan.show', $item->id_kpd) }}" class="shrink-0 px-3 py-1.5 bg-[#044564] text-white text-xs font-medium rounded-lg hover:bg-[#0b5d84] transition">
                                            Review
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Surat Izin Karyawan --}}
                    <div class="pt-3 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-2.5">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                Surat Izin & Cuti Karyawan
                            </h3>
                            <a href="{{ route('hrd.suratIzin') }}" class="text-xs text-[#19A7CE] hover:underline font-medium">Lihat Semua &rarr;</a>
                        </div>
                        @if ($suratIzinList->isEmpty())
                            <p class="text-xs text-slate-400 italic py-2">Tidak ada pengajuan izin yang menunggu persetujuan.</p>
                        @else
                            <div class="space-y-2">
                                @foreach ($suratIzinList as $izin)
                                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 hover:bg-slate-100/70 transition">
                                        <div class="min-w-0 pr-3">
                                            <p class="text-xs font-bold text-slate-900 truncate">{{ $izin->karyawan->nama ?? 'Karyawan' }} - <span class="text-blue-600 font-semibold">{{ $izin->jenis_izin ?? 'Izin' }}</span></p>
                                            <p class="text-[11px] text-slate-500 truncate">
                                                {{ $izin->judul_surat ?? $izin->keterangan ?? 'Permohonan izin' }}
                                            </p>
                                        </div>
                                        <a href="{{ route('hrd.suratIzin') }}" class="shrink-0 px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition">
                                            Tinjau
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Booking Ruangan / Kendaraan --}}
                    <div class="pt-3 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-2.5">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                Peminjaman Fasilitas & Kendaraan
                            </h3>
                            <a href="{{ url('/hrd/suratBooking') }}" class="text-xs text-[#19A7CE] hover:underline font-medium">Lihat Semua &rarr;</a>
                        </div>
                        @if ($bookingList->isEmpty())
                            <p class="text-xs text-slate-400 italic py-2">Tidak ada permohonan booking fasilitas yang menunggu.</p>
                        @else
                            <div class="space-y-2">
                                @foreach ($bookingList as $book)
                                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 hover:bg-slate-100/70 transition">
                                        <div class="min-w-0 pr-3">
                                            <p class="text-xs font-bold text-slate-900 truncate">{{ $book->pemohon?->karyawan?->nama ?? $book->pemohon?->username }} - <span class="text-amber-700 font-medium">{{ ucfirst($book->kategori) }}: {{ $book->jenis }}</span></p>
                                            <p class="text-[11px] text-slate-500">
                                                Tanggal: {{ $book->tanggal?->format('d/m/Y') }} &bull; {{ $book->jam_mulai }} - {{ $book->jam_selesai }}
                                            </p>
                                        </div>
                                        <a href="{{ url('/hrd/suratBooking') }}" class="shrink-0 px-3 py-1.5 bg-amber-600 text-white text-xs font-medium rounded-lg hover:bg-amber-700 transition">
                                            Proses
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                </div>

                {{-- Kolom Kanan: Status Operasional & Monitoring Tugas --}}
                <div class="space-y-6">

                    {{-- Daftar Divisi & Headcount --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                            <h2 class="text-base font-bold text-slate-900">Struktur Divisi & Kekuatan Tim</h2>
                            <a href="{{ url('/hrd/manajemenDivisi') }}" class="text-xs text-[#19A7CE] hover:underline font-semibold">Semua Divisi &rarr;</a>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach ($divisiList as $div)
                                <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50 hover:bg-white hover:border-slate-300 transition">
                                    <div class="flex items-center justify-between">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#044564]/10 text-[#044564]">
                                            {{ $div->kode_divisi }}
                                        </span>
                                        <span class="text-xs font-bold text-slate-700">{{ $div->jumlah_karyawan }} Anggota</span>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-800 mt-2 truncate">{{ $div->nama_divisi }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Tugas Terkini Lintas Divisi --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                            <div>
                                <h2 class="text-base font-bold text-slate-900">Monitoring Tugas Terkini</h2>
                                <p class="text-xs text-slate-500">Aktivitas penugasan dan status pengerjaan seluruh divisi.</p>
                            </div>
                            <a href="{{ url('/kadiv/tugas') }}" class="text-xs text-[#19A7CE] hover:underline font-semibold">Manajemen Tugas &rarr;</a>
                        </div>

                        @if ($tugasTerbaru->isEmpty())
                            <p class="text-xs text-slate-400 italic py-4 text-center">Belum ada tugas yang tercatat di sistem.</p>
                        @else
                            <div class="divide-y divide-slate-100">
                                @foreach ($tugasTerbaru as $t)
                                    <div class="py-3 flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-slate-800 truncate">{{ $t->judul_tugas }}</p>
                                            <p class="text-[11px] text-slate-500">
                                                Penerima: <span class="font-medium text-slate-700">{{ $t->karyawan?->nama ?? '-' }}</span> ({{ $t->karyawan?->divisi?->nama_divisi ?? '-' }})
                                            </p>
                                        </div>
                                        <div class="shrink-0 flex items-center gap-2">
                                            <x-status-badge :status="$t->status_efektif" />
                                            <a href="{{ url('/kadiv/detailTugas/' . $t->id_tugas) }}" class="text-xs text-slate-400 hover:text-[#044564]">
                                                &rarr;
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                </div>

            </div>

        </main>

    </div>

</body>

</html>
