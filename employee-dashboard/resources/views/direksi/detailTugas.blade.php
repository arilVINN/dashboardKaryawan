<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Tugas - PT Silindo</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white flex h-screen overflow-hidden">

    @include('component_direksi.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')
        @include('component.breadcrumbs', ['parentText' => 'Monitor Tugas', 'parentUrl' => url('/direksi/tugas'), 'currentPage' => 'Detail Tugas'])

        <main class="flex-1 overflow-y-auto p-8 pt-6">
            <div class="max-w-[1134px]">

                @php
                    $status = $task->is_terlambat && in_array($task->status, [\App\Models\Tugas::STATUS_MENUNGGU_ACC, \App\Models\Tugas::STATUS_SUDAH_ACC], true)
                        ? $task->status . ' · Terlambat'
                        : $task->status_efektif;
                    $badge = match ($task->status_efektif) {
                        'berjalan' => 'bg-emerald-100 text-emerald-700',
                        'menunggu di-acc' => 'bg-amber-100 text-amber-700',
                        'sudah di-acc' => 'bg-[#CCF4DB] text-emerald-700',
                        \App\Models\Tugas::STATUS_TERLAMBAT => 'bg-red-100 text-red-700',
                        default => 'bg-[#C0E7FF] text-blue-700',
                    };
                    $fileUrl = fn (?string $path) => $path ? '/storage/' . ltrim($path, '/') : null;
                @endphp

                <div class="flex items-center justify-between gap-4">
                    <h1 class="text-[28px] leading-[36px] font-bold text-black">Tugas {{ $task->id_tugas }}</h1>
                </div>

                <h2 class="mt-6 text-[28px] leading-[36px] font-bold text-black">{{ $task->judul_tugas }}</h2>

                <div class="mt-2 flex flex-wrap items-center gap-3 text-sm">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $badge }}">{{ $status }}</span>
                    <span class="text-slate-500">Divisi: <span class="font-semibold text-slate-700">{{ $task->karyawan?->divisi?->nama_divisi ?? '-' }}</span></span>
                    <span class="text-slate-500">Staff: <span class="font-semibold text-slate-700">{{ $task->karyawan?->nama ?? '-' }}</span></span>
                </div>

                <p class="text-[16px] leading-[24px] font-bold text-black mt-4">
                    tenggat : {{ $task->deadline ? \Illuminate\Support\Carbon::parse($task->deadline)->format('d M Y, H.i') : '-' }}
                </p>

                <p class="mt-8 text-[16px] leading-[24px] text-black">{{ $task->deskripsi }}</p>

                @if ($task->file_pendukung || $task->link_pendukung)
                    <div class="mt-5 flex flex-wrap gap-3">
                        @if ($task->file_pendukung)
                            <a href="{{ $fileUrl($task->file_pendukung) }}" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-300 rounded-xl shadow-sm hover:shadow-md transition text-sm font-semibold text-slate-800">
                                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                File Pendukung
                            </a>
                        @endif
                        @if ($task->link_pendukung)
                            <a href="{{ $task->link_pendukung }}" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-300 rounded-xl shadow-sm hover:shadow-md transition text-sm font-semibold text-slate-800">
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                Link Referensi
                            </a>
                        @endif
                    </div>
                @endif

                <div class="mt-10 border-t border-gray-400"></div>

                <section class="pt-6">
                    <h2 class="text-[24px] leading-[32px] font-bold text-black">Pengumpulan Tugas</h2>

                    @if ($latestSubmission)
                        <p class="text-[16px] leading-[24px] font-bold text-black">
                            status : {{ $latestSubmission->status_review ?? 'belum dikumpulkan' }}
                        </p>
                        <p class="mt-3 text-[16px] leading-[24px] text-black">{{ $latestSubmission->catatan_karyawan ?: '-' }}</p>

                        @if ($latestSubmission->file_hasil || $latestSubmission->link_submit)
                            <div class="mt-4 flex flex-wrap gap-3">
                                @if ($latestSubmission->file_hasil)
                                    <a href="{{ $fileUrl($latestSubmission->file_hasil) }}" target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-300 rounded-xl shadow-sm hover:shadow-md transition text-sm font-semibold text-slate-800">
                                        File Hasil
                                    </a>
                                @endif
                                @if ($latestSubmission->link_submit)
                                    <a href="{{ $latestSubmission->link_submit }}" target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-300 rounded-xl shadow-sm hover:shadow-md transition text-sm font-semibold text-slate-800">
                                        Link Pengumpulan
                                    </a>
                                @endif
                            </div>
                        @endif
                    @else
                        <p class="mt-2 text-[16px] leading-[24px] text-slate-500">Belum ada pengumpulan.</p>
                    @endif
                </section>

                @php
                    $revisions = $submissions->where('status_review', 'revisi')->filter(fn ($item) => filled($item->catatan_revisi));
                @endphp

                @if ($revisions->isNotEmpty())
                    <div class="mt-8 border-t border-gray-400 pt-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-[18px] leading-[26px] font-bold text-black">Riwayat Revisi</h3>
                            <span class="text-sm text-[#565E74]">{{ $revisions->count() }} kali revisi</span>
                        </div>
                        <div class="space-y-6">
                            @foreach ($revisions as $revision)
                                <div>
                                    <h2 class="text-[24px] leading-[32px] font-bold text-black">Catatan Revisi</h2>
                                    <p class="text-[14px] leading-[20px] text-[#565E74]">
                                        {{ $revision->created_at ? $revision->created_at->format('d M Y, H.i') : '-' }}
                                    </p>
                                    <p class="text-[16px] leading-[24px] font-bold text-black mt-1">
                                        tenggat : {{ $revision->deadline_revisi ? \Illuminate\Support\Carbon::parse($revision->deadline_revisi)->format('d M Y, H.i') : '-' }}
                                    </p>
                                    <p class="text-[16px] leading-[24px] text-black mt-3">{{ $revision->catatan_revisi }}</p>
                                    @if ($revision->file_revisi)
                                        <a href="{{ $fileUrl($revision->file_revisi) }}" target="_blank" rel="noopener noreferrer"
                                            class="inline-flex items-center gap-2 mt-3 px-4 py-2.5 bg-white border border-slate-300 rounded-xl shadow-sm hover:shadow-md transition text-sm font-semibold text-slate-800">
                                            File Revisi
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </main>
    </div>

</body>

</html>
