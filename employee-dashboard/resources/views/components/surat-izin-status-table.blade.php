@props([
    'suratIzinStatus',
    'title' => 'Status Surat Izin',
])

<section aria-labelledby="leave-request-status-heading"
    class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 px-5 py-4">
        <h2 id="leave-request-status-heading" class="text-base font-semibold text-slate-800">{{ $title }}</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-[#EAF4F8] text-xs text-[#334155]">
                <tr>
                    <th class="px-5 py-3 font-medium">Jenis &amp; Judul</th>
                    <th class="px-5 py-3 font-medium">Tanggal Izin</th>
                    <th class="px-5 py-3 font-medium">Keterangan</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Diajukan</th>
                    <th class="px-5 py-3 font-medium">Lampiran</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($suratIzinStatus as $surat)
                    <tr>
                        <td class="px-5 py-4">
                            <div class="font-medium text-slate-800">{{ $surat->judul_surat }}</div>
                            <div class="mt-0.5 flex flex-wrap items-center gap-1.5 text-xs text-slate-500">
                                <span>{{ $surat->kategori_pengajuan ?? 'Izin / Cuti' }} · {{ $surat->jenis_izin ?? '-' }}@if ($surat->jam_pengajuan) · {{ $surat->jam_pengajuan_label }}@endif</span>
                                @if ($surat->keperluan)
                                    <span class="rounded-full border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-[10px] font-medium text-slate-600">
                                        {{ $surat->keperluan }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 text-xs">
                            {{ $surat->tanggal_mulai?->format('d M Y') ?? $surat->tanggal_izin?->format('d M Y') ?? '-' }}
                            @if ($surat->tanggal_selesai)
                                <span class="mx-1 text-slate-400">s/d</span>{{ $surat->tanggal_selesai->format('d M Y') }}
                            @endif
                        </td>
                        <td class="max-w-xs px-5 py-4">{{ $surat->keterangan ?: '-' }}</td>
                        <td class="whitespace-nowrap px-5 py-4">
                            <x-status-badge :status="$surat->status" :label="$surat->tahap_label" />
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 text-xs">
                            {{ ($surat->tanggal_pengajuan ?? $surat->created_at)?->format('d M Y') ?? '-' }}
                        </td>
                        <td class="whitespace-nowrap px-5 py-4">
                            @if ($surat->status === 'Disetujui')
                                <a href="{{ route('suratIzin.pdf', ['suratIzin' => $surat, 'preview' => 1]) }}"
                                   target="_blank" rel="noopener"
                                   title="Buka lampiran PDF"
                                   class="inline-flex items-center gap-1.5 rounded-md border border-[#D5DEE3] bg-white px-2.5 py-1.5 text-[11px] font-medium text-[#146C94] transition hover:border-[#146C94] hover:bg-[#EAF6FA]">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                    PDF
                                </a>
                            @else
                                <span class="text-[11px] italic text-slate-400">
                                    {{ $surat->status === 'Ditolak' ? 'Tidak tersedia' : 'Setelah disetujui' }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-slate-400">Belum ada surat izin.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
