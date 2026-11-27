@php
    $activeTab = request('tab') === 'approvals' ? 'approvals' : 'mine';
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Izin - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">
    @include('component_kadiv.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')

        <main class="flex-1 overflow-y-auto p-8 pt-6 pb-6">
            @include('component.breadcrumbs', [
                'parentText' => 'Dashboard',
                'parentUrl' => route('kadiv.dashboard'),
                'currentPage' => 'Surat Izin',
                'inline' => true,
            ])

            @if (session('success'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <x-page-heading title="Surat Izin" icon="mail" />

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" data-modal-open="modalKadivSignature"
                        class="inline-flex h-10 shrink-0 items-center gap-1.5 rounded-lg border border-[#D5DEE3] bg-white px-4 text-sm font-medium text-[#172B36] transition hover:bg-[#F1F5F9]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                        </svg>
                        Tanda Tangan
                    </button>

                    <button type="button" data-modal-open="modalSuratIzin"
                        class="inline-flex h-10 shrink-0 items-center gap-1.5 rounded-lg bg-[#146C94] px-4 text-sm font-medium text-white transition hover:bg-[#0f5878]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Ajukan Perizinan
                    </button>
                </div>
            </div>

            @if (! $signatureAvailable)
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
                    <p class="text-[12px] text-amber-800">
                        Unggah tanda tangan PNG terlebih dahulu. Tombol <strong>ACC</strong> akan aktif setelah tanda tangan tersedia.
                    </p>
                    <button type="button" data-modal-open="modalKadivSignature"
                        class="inline-flex h-8 shrink-0 items-center rounded-lg border border-amber-300 bg-white px-3 text-[12px] font-semibold text-amber-800 transition hover:bg-amber-100">
                        Unggah Tanda Tangan
                    </button>
                </div>
            @endif

            {{-- Tabs --}}
            <div class="mb-4 border-b border-slate-200">
                <nav class="-mb-px flex gap-6" aria-label="Surat izin">
                    <button type="button" data-sz-tab="mine"
                        class="border-b-2 px-1 pb-3 text-[13px] {{ $activeTab === 'mine' ? 'border-[#146C94] font-semibold text-[#146C94]' : 'border-transparent font-medium text-[#4B5563]' }}">
                        Pengajuan Saya
                    </button>
                    <button type="button" data-sz-tab="approvals"
                        class="border-b-2 px-1 pb-3 text-[13px] {{ $activeTab === 'approvals' ? 'border-[#146C94] font-semibold text-[#146C94]' : 'border-transparent font-medium text-[#4B5563]' }}">
                        Persetujuan Tim
                        <span class="ml-1 inline-flex min-w-5 items-center justify-center rounded-full bg-[#EAF6FA] px-1.5 py-0.5 text-[10px] font-semibold text-[#146C94]">
                            {{ $pendingApprovals }}
                        </span>
                    </button>
                </nav>
            </div>

            {{-- Pengajuan Saya --}}
            <div data-sz-panel="mine" class="{{ $activeTab === 'mine' ? '' : 'hidden' }}">
                <x-surat-izin-status-table :surat-izin-status="$suratIzinStatus" title="Status Surat Izin Saya" />
            </div>

            {{-- Persetujuan Tim --}}
            <div data-sz-panel="approvals" class="{{ $activeTab === 'approvals' ? '' : 'hidden' }}">
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-5 py-4">
                        <h2 class="text-base font-semibold text-slate-800">Persetujuan &amp; Riwayat Surat Izin Tim</h2>
                        <p class="mt-0.5 text-[12px] text-slate-500">
                            Pengajuan dari anggota divisi Anda, termasuk yang sudah diproses.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-600">
                            <thead class="bg-[#EAF4F8] text-xs text-[#334155]">
                                <tr>
                                    <th class="px-5 py-3 font-medium">Karyawan</th>
                                    <th class="px-5 py-3 font-medium">Jenis &amp; Judul</th>
                                    <th class="px-5 py-3 font-medium text-center">Tanggal Izin</th>
                                    <th class="px-5 py-3 font-medium">Keterangan</th>
                                    <th class="px-5 py-3 font-medium">File</th>
                                    <th class="px-5 py-3 font-medium">Status</th>
                                    <th class="px-5 py-3 font-medium text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse ($suratIzin as $surat)
                                    <tr class="hover:bg-[#F5FAFC]">
                                        <td class="px-5 py-4 font-medium text-slate-800 whitespace-nowrap">
                                            {{ $surat->karyawan?->nama ?? 'Karyawan tidak tersedia' }}
                                            <div class="mt-0.5 text-[11px] font-normal text-slate-500">
                                                {{ $surat->karyawan?->divisi?->nama_divisi ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="font-semibold text-slate-800">{{ $surat->judul_surat }}</div>
                                            <div class="mt-0.5 text-xs font-medium text-[#19A7CE]">
                                                {{ $surat->jenis_izin ?? 'Izin / Cuti' }}@if ($surat->jam_pengajuan) · {{ $surat->jam_pengajuan_label }}@endif
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 text-center text-xs whitespace-nowrap">
                                            {{ $surat->tanggal_mulai?->format('d M Y') ?? '-' }}
                                            @if ($surat->tanggal_mulai && $surat->tanggal_selesai && ! $surat->tanggal_selesai->equalTo($surat->tanggal_mulai))
                                                <span class="mx-1 text-slate-400">s/d</span>{{ $surat->tanggal_selesai->format('d M Y') }}
                                            @endif
                                        </td>
                                        <td class="max-w-xs px-5 py-4 text-xs">{{ $surat->keterangan ?: '-' }}</td>
                                        <td class="px-5 py-4 text-xs whitespace-nowrap">
                                            @if ($surat->file)
                                                <a href="{{ \App\Support\FileStorage::url($surat->file) }}"
                                                   target="_blank" rel="noopener"
                                                   class="font-medium text-[#0097B2] hover:underline">Lihat file</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            @php
                                                $statusClass = match ($surat->status) {
                                                    'Disetujui' => 'border-emerald-500 bg-emerald-100 text-emerald-700',
                                                    'Ditolak' => 'border-red-500 bg-red-100 text-red-700',
                                                    default => 'border-amber-500 bg-amber-100 text-amber-700',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $statusClass }}">
                                                {{ $surat->tahap_label }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 text-center whitespace-nowrap">
                                            <div class="inline-flex flex-wrap items-center justify-center gap-2">
                                                @if ($surat->status === 'Pending' && $surat->tahap === 'kadiv')
                                                    @if ($signatureAvailable)
                                                        <form method="POST" action="{{ route('kadiv.suratIzin.acc', $surat) }}">
                                                            @csrf
                                                            <button type="submit"
                                                                    class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                                                ACC
                                                            </button>
                                                        </form>
                                                    @else
                                                        <button type="button" disabled
                                                                title="Unggah tanda tangan terlebih dahulu"
                                                                class="cursor-not-allowed rounded-md bg-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-500">
                                                            ACC
                                                        </button>
                                                    @endif

                                                    <form method="POST" action="{{ route('kadiv.suratIzin.tolak', $surat) }}">
                                                        @csrf
                                                        <button type="submit"
                                                                class="rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-red-700">
                                                            Tolak
                                                        </button>
                                                    </form>
                                                @elseif ($surat->status === 'Disetujui')
                                                    <a href="{{ route('suratIzin.pdf', ['suratIzin' => $surat, 'preview' => 1]) }}"
                                                       target="_blank" rel="noopener"
                                                       class="inline-flex items-center gap-1.5 rounded-md border border-[#D5DEE3] bg-white px-3 py-1.5 text-xs font-semibold text-[#146C94] transition hover:border-[#146C94] hover:bg-[#EAF6FA]">
                                                        PDF
                                                    </a>
                                                @elseif ($surat->status === 'Pending')
                                                    <span class="text-amber-600 text-xs italic">Menunggu HRD</span>
                                                @else
                                                    <span class="text-slate-400 text-xs italic">Sudah diproses</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-5 py-10 text-center text-slate-400 italic">
                                            Belum ada pengajuan dari tim.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-slate-200 px-6 py-4">
                        {{ $suratIzin->appends(['tab' => 'approvals'])->links('vendor.pagination.custom-links') }}
                    </div>
                </div>
            </div>

            <x-signature-modal
                id="modalKadivSignature"
                title="Tanda Tangan Kepala Divisi"
                description="Unggah tanda tangan PNG yang akan dicetak pada surat izin yang Anda setujui."
                :action="route('kadiv.signature.upload')"
                :preview-url="route('kadiv.signature.show')"
                :employee="$kadiv"
                :has-signature="$signatureAvailable"
                submit-label="Simpan Tanda Tangan"
            />
            @include('components.modalSuratIzin', ['routeSurat' => 'staff.suratIzin.store'])
        </main>
    </div>

<script>
(function () {
    const tabs = document.querySelectorAll('[data-sz-tab]');
    const panels = document.querySelectorAll('[data-sz-panel]');

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            const target = tab.dataset.szTab;

            tabs.forEach(function (item) {
                const active = item === tab;
                item.classList.toggle('border-[#146C94]', active);
                item.classList.toggle('border-transparent', !active);
                item.classList.toggle('font-semibold', active);
                item.classList.toggle('font-medium', !active);
                item.classList.toggle('text-[#146C94]', active);
                item.classList.toggle('text-[#4B5563]', !active);
            });

            panels.forEach(function (panel) {
                panel.classList.toggle('hidden', panel.dataset.szPanel !== target);
            });

            const url = new URL(window.location.href);
            if (target === 'approvals') {
                url.searchParams.set('tab', 'approvals');
            } else {
                url.searchParams.delete('tab');
            }
            window.history.replaceState(null, '', url);
        });
    });
})();
</script>
</body>

</html>