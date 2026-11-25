<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pengajuan Dana - PT Silindo</title>
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
                'parentText' => 'Pengajuan Dana',
                'parentUrl' => route('kadiv.pengajuanDana'),
                'currentPage' => 'Riwayat Pengajuan Dana',
                'inline' => true,
            ])

            {{-- Header --}}
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <x-page-heading title="Riwayat Pengajuan Dana" icon="archive" />
                    <p class="mt-1 text-[13px] text-[#4B5563]">
                        Daftar seluruh permohonan dana divisi Anda dan status persetujuan dari Finance.
                    </p>
                </div>

                <a href="{{ route('kadiv.pengajuanDana') }}"
                    class="inline-flex h-10 shrink-0 items-center gap-2 rounded-lg bg-[#0097B2] px-4 text-[13px] font-medium text-white shadow-sm transition hover:bg-[#008199] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] focus-visible:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14"></path>
                    </svg>
                    <span>Buat pengajuan baru</span>
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

            {{-- Table --}}
            <div class="mt-5 overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1000px] border-collapse text-left">
                        <thead class="bg-[#EAF4F8] text-[#334155]">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-[12px] font-medium">No. pengajuan</th>
                                <th scope="col" class="px-4 py-3 text-[12px] font-medium">Tanggal</th>
                                <th scope="col" class="px-4 py-3 text-[12px] font-medium">Judul &amp; keperluan</th>
                                <th scope="col" class="px-4 py-3 text-right text-[12px] font-medium">Nominal</th>
                                <th scope="col" class="px-4 py-3 text-center text-[12px] font-medium">Status</th>
                                <th scope="col" class="px-4 py-3 text-[12px] font-medium">Catatan finance</th>
                                <th scope="col" class="px-4 py-3 text-center text-[12px] font-medium">Lampiran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#EEF2F5]">
                            @forelse ($riwayat as $item)
                                <tr class="transition hover:bg-[#F5FAFC]">
                                    <td class="whitespace-nowrap px-4 py-3 font-mono text-[12px] font-semibold text-[#172B36]">
                                        {{ $item->nomor_pengajuan }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-[12px] text-[#4B5563]">
                                        {{ $item->created_at->locale('id')->isoFormat('D MMM Y') }}
                                    </td>
                                    <td class="max-w-xs px-4 py-3">
                                        <div class="truncate text-[13px] font-medium text-[#172B36]">
                                            {{ $item->judul_pengajuan }}
                                        </div>
                                        <div class="mt-0.5 truncate text-[12px] text-[#4B5563]">
                                            {{ $item->keperluan }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-[13px] font-medium tabular-nums text-[#172B36]">
                                        {{ \App\Helpers\FormatAkuntansi::formatRupiah($item->nominal_diajukan) }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        @if ($item->status === 'menunggu')
                                            <span
                                                class="inline-flex items-center rounded-md border border-[#F5C85C] bg-[#FEF0C3] px-2 py-0.5 text-[12px] font-medium text-[#B45309]">
                                                Menunggu
                                            </span>
                                        @elseif ($item->status === 'disetujui')
                                            <span
                                                class="inline-flex items-center rounded-md border border-[#7DD3C8] bg-[#D5F3F0] px-2 py-0.5 text-[12px] font-medium text-[#0D9488]">
                                                Disetujui
                                            </span>
                                        @elseif ($item->status === 'dicairkan')
                                            <span
                                                class="inline-flex items-center rounded-md border border-[#D5DEE3] bg-[#E2E8EC] px-2 py-0.5 text-[12px] font-medium text-[#4B5563]">
                                                Dicairkan
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center rounded-md border border-[#F5A3A3] bg-[#FDE0E0] px-2 py-0.5 text-[12px] font-medium text-[#B91C1C]">
                                                Ditolak
                                            </span>
                                        @endif
                                    </td>
                                    <td class="max-w-xs px-4 py-3 text-[12px] text-[#4B5563]">
                                        {{ $item->catatan_finance ?: '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        @if ($item->lampiran_proposal)
                                            <a href="{{ \App\Support\FileStorage::url($item->lampiran_proposal) }}" target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-[#D5DEE3] bg-white px-2.5 text-[12px] font-medium text-[#172B36] transition hover:bg-[#F5FAFC] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"
                                                    viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13">
                                                    </path>
                                                </svg>
                                                Lihat
                                            </a>
                                        @else
                                            <span class="text-[12px] text-[#B45309]">Belum ada</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center text-[12px] text-[#4B5563]">
                                        Belum ada pengajuan dana untuk divisi Anda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col gap-2 border-t border-[#E2E8EC] px-4 py-3 text-[12px] text-[#4B5563] sm:flex-row sm:items-center sm:justify-between">
                    <span>
                        @if ($riwayat->total() > 0)
                            Menampilkan {{ $riwayat->firstItem() }}-{{ $riwayat->lastItem() }} dari {{ $riwayat->total() }} pengajuan
                        @else
                            Belum ada pengajuan
                        @endif
                    </span>
                    @if ($riwayat->hasPages())
                        <div>{{ $riwayat->links() }}</div>
                    @endif
                </div>
            </div>
        </main>
    </div>

</body>

</html>
