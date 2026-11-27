<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Izin Masuk - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">
    @include('component_hrd.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')

        <main class="flex-1 overflow-y-auto p-8 pt-6">
            @include('component.breadcrumbs', [
                'parentText' => 'Pesan',
                'parentUrl' => route('hrd.pesan'),
                'currentPage' => 'Surat Izin Masuk',
                'inline' => true,
            ])
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <x-page-heading title="Surat Izin Masuk" icon="mail" />

                @if ($canManageSignature)
                    <button type="button" data-modal-open="modalHrdSignature"
                        class="inline-flex h-10 shrink-0 items-center gap-1.5 rounded-lg border border-[#D5DEE3] bg-white px-4 text-sm font-medium text-[#172B36] transition hover:bg-[#F1F5F9]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                        </svg>
                        Tanda Tangan
                    </button>
                @endif
            </div>

            @if (session('success'))
                <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if ($canManageSignature && ! $signatureAvailable)
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
                    <p class="text-[12px] text-amber-800">
                        Tanda tangan HR belum diunggah. Surat izin tetap dapat disetujui, tetapi kolom HR pada PDF akan kosong.
                    </p>
                    <button type="button" data-modal-open="modalHrdSignature"
                        class="inline-flex h-8 shrink-0 items-center rounded-lg border border-amber-300 bg-white px-3 text-[12px] font-semibold text-amber-800 transition hover:bg-amber-100">
                        Unggah Tanda Tangan
                    </button>
                </div>
            @endif

            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-[#EAF4F8] text-[#334155] text-xs">
                            <tr>
                                <th class="px-6 py-4 font-medium">Karyawan</th>
                                <th class="px-6 py-4 font-medium">Jenis & Judul</th>
                                <th class="px-6 py-4 font-medium text-center">Tanggal Mulai s/d Selesai</th>
                                <th class="px-6 py-4 font-medium">Keterangan</th>
                                <th class="px-6 py-4 font-medium">File</th>
                                <th class="px-6 py-4 font-medium">Status / Tahap</th>
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
                                        <div class="mt-0.5 flex flex-wrap items-center gap-1.5 text-xs">
                                            <span class="font-medium text-[#19A7CE]">{{ $surat->kategori_pengajuan ?? 'Izin / Cuti' }}</span>
                                            <span class="text-slate-300">·</span>
                                            <span class="text-slate-500">{{ $surat->jenis_izin ?? '-' }}@if ($surat->jam_pengajuan) · {{ $surat->jam_pengajuan_label }}@endif</span>
                                            @if ($surat->keperluan)
                                                <span class="rounded-full border border-[#E2E8EC] bg-[#F8FAFC] px-1.5 py-0.5 text-[10px] font-medium text-[#4B5563]">
                                                    {{ $surat->keperluan }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-xs">
                                        {{ $surat->tanggal_mulai ? \Carbon\Carbon::parse($surat->tanggal_mulai)->format('d M Y') : '-' }}
                                        <span class="text-slate-400 mx-1">s/d</span>
                                        {{ $surat->tanggal_selesai ? \Carbon\Carbon::parse($surat->tanggal_selesai)->format('d M Y') : '-' }}
                                        @if ($surat->tanggal_pengajuan)
                                            <div class="mt-1 text-[10px] text-slate-400">
                                                Diajukan {{ \Carbon\Carbon::parse($surat->tanggal_pengajuan)->format('d M Y') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">{{ $surat->keterangan ?: '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($surat->file)
                                            <a href="{{ \App\Support\FileStorage::url($surat->file) }}"
                                                target="_blank" rel="noopener"
                                                class="text-[#0097B2] hover:underline font-medium">Lihat file</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-block px-2.5 py-0.5 rounded-full border text-xs font-semibold {{ $surat->status === 'Disetujui' ? 'bg-emerald-100 text-emerald-700 border-emerald-500' : ($surat->status === 'Ditolak' ? 'bg-red-100 text-red-700 border-red-500' : 'bg-amber-100 text-amber-700 border-amber-500') }}">
                                            {{ $surat->tahap_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="inline-flex flex-wrap items-center justify-center gap-2">
                                            @if ($surat->status === 'Disetujui')
                                                <a href="{{ route('suratIzin.pdf', ['suratIzin' => $surat, 'preview' => 1]) }}"
                                                   target="_blank" rel="noopener"
                                                   class="inline-flex items-center gap-1.5 rounded-md border border-[#D5DEE3] bg-white px-3 py-1.5 text-xs font-semibold text-[#146C94] transition hover:border-[#146C94] hover:bg-[#EAF6FA]">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                                                    </svg>
                                                    Cetak PDF
                                                </a>
                                            @endif

                                            @if ($surat->status === 'Pending' && $surat->tahap === 'hrd')
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
                                            @elseif ($surat->status === 'Pending')
                                                <span class="text-amber-600 text-xs italic">Menunggu Kadiv</span>
                                            @else
                                                <span class="text-slate-400 text-xs italic">Sudah diproses</span>
                                            @endif
                                        </div>
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
                    {{ $suratIzin->links('vendor.pagination.custom-links') }}
                </div>
            </div>

            @if ($canManageSignature)
                <x-signature-modal
                    id="modalHrdSignature"
                    title="Tanda Tangan HR"
                    description="Unggah tanda tangan PNG yang akan dicetak pada surat izin yang Anda setujui sebagai HR."
                    :action="route('hrd.signature.upload')"
                    :preview-url="route('hrd.signature.show')"
                    :employee="$hrdEmployee"
                    :has-signature="$signatureAvailable"
                    submit-label="Simpan Tanda Tangan"
                />
            @endif
        </main>
    </div>
</body>

</html>