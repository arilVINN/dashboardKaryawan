<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pengajuan Dana - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">

    @include('component_kadiv.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')

        <main class="flex-1 overflow-y-auto p-8 pb-10">

            {{-- Header & Button Pengajuan Baru --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Riwayat Pengajuan Dana</h1>
                    <p class="text-sm text-slate-500 mt-1">Daftar seluruh permohonan dana divisi Anda dan status persetujuan dari Finance.</p>
                </div>
                <div>
                    <a href="{{ route('kadiv.pengajuanDana') }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#044564] hover:bg-[#03334a] text-white text-xs font-semibold rounded-xl shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Buat Pengajuan Baru</span>
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

            {{-- Table Riwayat --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-8">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-50/75">
                                <th class="py-3.5 px-4">No. Pengajuan</th>
                                <th class="py-3.5 px-4">Tanggal</th>
                                <th class="py-3.5 px-4">Judul & Keperluan</th>
                                <th class="py-3.5 px-4 text-right">Nominal Diajukan</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-4">Catatan Finance</th>
                                <th class="py-3.5 px-4 text-center">Lampiran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($riwayat as $item)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-4 px-4 font-mono text-xs font-bold text-slate-700">
                                        {{ $item->nomor_pengajuan }}
                                    </td>
                                    <td class="py-4 px-4 text-xs text-slate-500 whitespace-nowrap">
                                        {{ $item->created_at->isoFormat('DD MMM Y') }}
                                    </td>
                                    <td class="py-4 px-4 max-w-xs">
                                        <div class="font-semibold text-slate-800">{{ $item->judul_pengajuan }}</div>
                                        <div class="text-xs text-slate-500 mt-0.5 truncate">{{ $item->keperluan }}</div>
                                    </td>
                                    <td class="py-4 px-4 text-right font-bold text-slate-800 whitespace-nowrap">
                                        Rp {{ number_format($item->nominal_diajukan, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        @if ($item->status === 'menunggu')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                Menunggu
                                            </span>
                                        @elseif ($item->status === 'disetujui')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                Disetujui
                                            </span>
                                        @elseif ($item->status === 'dicairkan')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Dicairkan
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                Ditolak
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-xs text-slate-600 max-w-xs">
                                        @if ($item->catatan_finance)
                                            <div class="p-2 bg-slate-50 rounded-lg border border-slate-100">
                                                {{ $item->catatan_finance }}
                                            </div>
                                        @else
                                            <span class="text-slate-400 italic">-</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        @if ($item->lampiran_proposal)
                                            <a href="{{ asset('storage/' . $item->lampiran_proposal) }}" target="_blank"
                                                class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium rounded-lg transition inline-flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                <span>File</span>
                                            </a>
                                        @else
                                            <span class="text-xs text-slate-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-400">
                                        Divisi Anda belum pernah membuat pengajuan dana.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($riwayat->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100">
                        {{ $riwayat->links() }}
                    </div>
                @endif
            </div>

        </main>
    </div>

</body>

</html>
