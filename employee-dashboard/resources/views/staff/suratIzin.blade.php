<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Izin - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">
    @include('component.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')

        <main class="flex-1 overflow-y-auto p-8 pt-6 pb-6">
            @include('component.breadcrumbs', [
                'parentText' => 'Dashboard',
                'parentUrl' => url('/'),
                'currentPage' => 'Surat Izin',
                'inline' => true,
            ])

            @if (session('success'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <x-page-heading title="Surat Izin" icon="mail" />

                <button type="button" data-modal-open="modalSuratIzin"
                    class="inline-flex h-10 shrink-0 items-center gap-1.5 rounded-lg bg-[#146C94] px-4 text-sm font-medium text-white transition hover:bg-[#0f5878]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Ajukan Perizinan
                </button>
            </div>

            <p class="mb-4 max-w-2xl text-[13px] text-[#4B5563]">
                Ajukan izin, cuti, atau keperluan dinas Anda. Pengajuan akan diteruskan ke Kepala Divisi
                untuk disetujui sebelum diproses HRD.
            </p>

            <x-surat-izin-status-table :surat-izin-status="$suratIzinStatus" title="Status Surat Izin Saya" />

            @include('components.modalSuratIzin', ['routeSurat' => 'staff.suratIzin.store'])
        </main>
    </div>
</body>

</html>
