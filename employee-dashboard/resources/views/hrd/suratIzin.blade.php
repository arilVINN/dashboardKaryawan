<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Izin Masuk - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">
    @include('component_hrd.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')
        @include('component.breadcrumbs', [
            'parentText' => 'Pesan',
            'parentUrl' => route('hrd.pesan'),
            'currentPage' => 'Surat Izin Masuk',
        ])

        <main class="flex-1 overflow-y-auto p-8 pt-6">
            <h1 class="text-2xl font-bold text-slate-800 mb-6">Surat Izin Masuk</h1>

            @if (session('success'))
                <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                            <tr>
                                <th class="px-6 py-4 font-medium">Karyawan</th>
                                <th class="px-6 py-4 font-medium">Jenis & Judul</th>
                                <th class="px-6 py-4 font-medium text-center">Tanggal Mulai s/d Selesai</th>
                                <th class="px-6 py-4 font-medium">Keterangan</th>
                                <th class="px-6 py-4 font-medium">File</th>
                                <th class="px-6 py-4 font-medium">Status</th>
                                <th class="px-6 py-4 font-medium text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($suratIzin as $surat)
                                <tr>
                                    <td class="px-6 py-4 font-medium text-slate-800 whitespace-nowrap">
                                        {{ $surat->karyawan?->nama ?? 'Karyawan tidak tersedia' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-slate-800">{{ $surat->judul_surat }}</div>
                                        <div class="text-xs text-[#19A7CE] font-medium">{{ $surat->jenis_izin ?? 'Izin / Cuti' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-xs">
                                        {{ $surat->tanggal_mulai ? \Carbon\Carbon::parse($surat->tanggal_mulai)->format('d M Y') : '-' }}
                                        <span class="text-slate-400 mx-1">s/d</span>
                                        {{ $surat->tanggal_selesai ? \Carbon\Carbon::parse($surat->tanggal_selesai)->format('d M Y') : '-' }}
                                    </td>
                                    <td class="px-6 py-4">{{ $surat->keterangan ?: '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($surat->file)
                                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($surat->file) }}"
                                                target="_blank" rel="noopener"
                                                class="text-[#0097B2] hover:underline font-medium">Lihat file</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $surat->status === 'Disetujui' ? 'bg-green-100 text-green-700' : ($surat->status === 'Ditolak' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                            {{ $surat->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if ($surat->status === 'Pending')
                                            <div class="inline-flex items-center gap-2">
                                                <form method="POST" action="{{ route('hrd.suratIzin.acc', $surat) }}">
                                                    @csrf
                                                    <button type="submit"
                                                        class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 transition">
                                                        ACC
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('hrd.suratIzin.tolak', $surat) }}">
                                                    @csrf
                                                    <button type="submit"
                                                        class="rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700 transition">
                                                        Tolak
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-slate-400 text-xs italic">Sudah diproses</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-10 text-center text-slate-400 italic">
                                        Belum ada surat izin masuk.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 px-6 py-4">
                    {{ $suratIzin->links() }}
                </div>
            </div>
        </main>
    </div>
</body>

</html>