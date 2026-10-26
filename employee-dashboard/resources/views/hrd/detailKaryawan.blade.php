<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Karyawan - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">
    @include('component_hrd.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')
        @include('component.breadcrumbs', ['parentText' => 'Karyawan', 'parentUrl' => url('/hrd/daftarKaryawan'), 'currentPage' => 'Detail Karyawan'])

        <main class="flex-1 overflow-y-auto p-8 pt-6 bg-slate-50 space-y-6">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 flex flex-col lg:flex-row lg:items-center gap-5">
                <div class="flex items-center gap-5 min-w-0 flex-1">
                    <div class="w-20 h-20 rounded-full bg-[#0097B2] text-white text-3xl font-bold flex items-center justify-center shrink-0">
                        {{ strtoupper(substr($karyawan->nama, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-2xl font-bold text-slate-900 truncate">{{ $karyawan->nama }}</h1>
                        <p class="text-sm text-slate-500">{{ $karyawan->jabatan }} · {{ $karyawan->id_karyawan }}</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <span class="px-2.5 py-1 bg-cyan-100 text-cyan-700 rounded-full text-xs font-bold">
                                {{ $karyawan->divisi->nama_divisi ?? 'Tanpa divisi' }}
                            </span>
                            <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-bold">
                                {{ $karyawan->user?->role?->nama_role ?? 'Belum ada akun' }}
                            </span>
                            <span class="px-2.5 py-1 {{ $karyawan->status_aktif === 'Tidak Aktif' || !$karyawan->aktif ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }} rounded-full text-xs font-bold">
                                {{ $karyawan->status_aktif === 'Tidak Aktif' || !$karyawan->aktif ? 'Tidak Aktif' : 'Aktif' }}
                            </span>
                        </div>
                    </div>
                </div>
                @if ($karyawan->tanggal_keluar)
                    <p class="text-sm text-slate-600">
                        Tanggal keluar:
                        <span class="font-semibold">{{ $karyawan->tanggal_keluar->format('d M Y') }}</span>
                    </p>
                @else
                    <form method="POST" action="{{ route('hrd.karyawan.keluar', $karyawan->id_karyawan) }}"
                        onsubmit="return confirm('Tandai {{ $karyawan->nama }} sebagai karyawan keluar? Akun akan dinonaktifkan dan riwayat tugas tetap tersimpan.');"
                        class="flex flex-col sm:flex-row sm:items-end gap-3">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label for="tanggal_keluar" class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Keluar</label>
                            <input id="tanggal_keluar" name="tanggal_keluar" type="date"
                                value="{{ old('tanggal_keluar', now()->toDateString()) }}"
                                max="{{ now()->toDateString() }}" required
                                class="rounded-md border-slate-300 text-sm focus:border-[#0097B2] focus:ring-[#0097B2]">
                            @error('tanggal_keluar')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-md transition">
                            Tandai Karyawan Keluar
                        </button>
                    </form>
                @endif
            </div>

            @if (session('success'))
                <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800" role="status">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-bold text-slate-800 mb-4">Informasi Pribadi</h2>
                    <dl class="space-y-3 text-sm">
                        @php
                            $info = [
                                'Jenis Kelamin' => $karyawan->jenis_kelamin,
                                'Tanggal Lahir' => $karyawan->tanggal_lahir ? \Carbon\Carbon::parse($karyawan->tanggal_lahir)->format('d M Y') : null,
                                'No. Telepon' => $karyawan->no_telepon,
                                'Email' => $karyawan->email,
                                'Alamat' => $karyawan->alamat,
                                'No. Rekening' => $karyawan->no_rekening,
                                'Tanggal Masuk' => ($karyawan->tanggal_masuk ?? $karyawan->tanggal_rekrut) ? \Carbon\Carbon::parse($karyawan->tanggal_masuk ?? $karyawan->tanggal_rekrut)->format('d M Y') : null,
                                'Tanggal Akhir Kontrak' => $karyawan->tanggal_akhir_kontrak ? \Carbon\Carbon::parse($karyawan->tanggal_akhir_kontrak)->format('d M Y') : 'Karyawan Tetap / Tidak Ada',
                            ];
                        @endphp
                        @foreach ($info as $label => $nilai)
                            <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                                <dt class="text-slate-500">{{ $label }}</dt>
                                <dd class="text-slate-900 font-medium text-right">{{ $nilai ?: '-' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <div class="space-y-6">
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-bold text-slate-800 mb-4">Akun</h2>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between border-b border-slate-100 pb-2">
                                <dt class="text-slate-500">Username</dt>
                                <dd class="text-slate-900 font-medium">{{ $karyawan->user?->username ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500">Login terakhir</dt>
                                <dd class="text-slate-900 font-medium">
                                    {{ $karyawan->user?->last_login_at?->format('d M Y, H.i') ?? 'Belum pernah' }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-5">
                            <p class="text-sm text-slate-500">Total Tugas</p>
                            <p class="text-3xl font-bold text-slate-800">{{ $totalTugas }}</p>
                        </div>
                        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-5">
                            <p class="text-sm text-slate-500">Tugas Selesai</p>
                            <p class="text-3xl font-bold text-green-600">{{ $tugasSelesai }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Dokumen Lampiran Karyawan -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden p-6">
                <div class="flex items-center justify-between pb-4 border-b border-slate-200 mb-5">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Lampiran Dokumen</h2>
                        <p class="text-xs text-slate-500">Berkas dan dokumen data diri karyawan yang telah diunggah.</p>
                    </div>
                    <span class="px-3 py-1 bg-blue-50 text-[#0097B2] rounded-full text-xs font-semibold">
                        {{ $karyawan->lampiran ? $karyawan->lampiran->count() : 0 }} Dokumen
                    </span>
                </div>

                @php
                    $katalogJenis = [
                        'lamaran' => 'Surat Lamaran',
                        'ijazah' => 'Ijazah',
                        'transkrip' => 'Transkrip Nilai',
                        'ktp' => 'Kartu Tanda Penduduk (KTP)',
                        'kk' => 'Kartu Keluarga (KK)',
                        'offering_letter' => 'Offering Letter',
                        'lainnya' => 'Dokumen Lainnya / SK',
                    ];
                    $lampiranByJenis = ($karyawan->lampiran ?? collect())->groupBy('jenis_lampiran');
                @endphp

                @if (!$karyawan->lampiran || $karyawan->lampiran->isEmpty())
                    <div class="text-center py-8 text-slate-400 text-sm">
                        <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                        </svg>
                        Belum ada lampiran dokumen yang diunggah untuk karyawan ini.
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($katalogJenis as $kodeJenis => $namaJenis)
                            @php
                                $items = $lampiranByJenis->get($kodeJenis, collect());
                            @endphp
                            <div class="border border-slate-200 rounded-lg p-4 bg-slate-50/50 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="text-sm font-bold text-slate-700">{{ $namaJenis }}</h3>
                                        <span class="text-xs px-2 py-0.5 rounded-full {{ $items->isNotEmpty() ? 'bg-green-100 text-green-700 font-semibold' : 'bg-slate-200 text-slate-500' }}">
                                            {{ $items->count() }} file
                                        </span>
                                    </div>

                                    @if ($items->isNotEmpty())
                                        <ul class="divide-y divide-slate-200 text-xs text-slate-600">
                                            @foreach ($items as $lampiran)
                                                <li class="py-2 flex items-center justify-between gap-2">
                                                    <div class="min-w-0 flex-1">
                                                        <p class="font-medium text-slate-800 truncate" title="{{ $lampiran->nama_file }}">
                                                            {{ $lampiran->nama_file }}
                                                        </p>
                                                        <p class="text-[10px] text-slate-400">
                                                            {{ $lampiran->created_at ? $lampiran->created_at->format('d M Y, H:i') : '-' }}
                                                        </p>
                                                    </div>
                                                    <a href="{{ route('hrd.lampiran.download', $lampiran->id_lampiran) }}"
                                                        class="shrink-0 px-2.5 py-1 bg-[#0097B2] hover:bg-[#008199] text-white text-xs font-medium rounded transition flex items-center gap-1"
                                                        title="Download File">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                        </svg>
                                                        <span>Unduh</span>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-xs text-slate-400 italic py-1">Tidak ada dokumen</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h2 class="text-lg font-bold text-slate-800">Tugas {{ $karyawan->nama }}</h2>
                </div>
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3 font-medium">Tugas</th>
                            <th class="px-6 py-3 font-medium text-center">Tanggal</th>
                            <th class="px-6 py-3 font-medium text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($karyawan->tugas->take(5) as $t)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-3 font-semibold text-slate-800">{{ $t->judul_tugas }}</td>
                                <td class="px-6 py-3 text-center">{{ $t->tanggal_tugas }}</td>
                                <td class="px-6 py-3 text-center">{{ $t->status }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-10 text-center text-slate-400">Belum ada tugas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
