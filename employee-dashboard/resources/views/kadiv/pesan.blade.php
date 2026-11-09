<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pesan - PT Silindo</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">

    @include('component_kadiv.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        {{-- TOPBAR --}}
        @include('component.topbar')

        {{-- MAIN CONTENT --}}
        <main class="flex-1 overflow-y-auto p-8 pt-6 pb-6">
            @include('component.breadcrumbs', ['parentText' => 'Dashboard', 'parentUrl' => url('/kadiv/dashboard'), 'currentPage' => 'Daftar Pesan', 'inline' => true])

            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <x-page-heading title="Daftar Pesan" icon="message" />

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" data-modal-open="modalKirimPesan"
                        class="inline-flex h-10 shrink-0 items-center gap-1.5 rounded-lg bg-[#0097B2] px-4 text-sm font-medium text-white transition hover:bg-[#008199]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        Buat Pesan
                    </button>

                    <button type="button" data-modal-open="modalSuratIzin"
                        class="inline-flex h-10 shrink-0 items-center gap-1.5 rounded-lg bg-[#146C94] px-4 text-sm font-medium text-white transition hover:bg-[#0f5878]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12h6m-6 4h6M9 8h1m5 12H7a2 2 0 01-2-2V6a2 2 0 012-2h6l5 5v11a2 2 0 01-2 2z" />
                        </svg>
                        Kirim Surat Izin
                    </button>
                </div>
            </div>

            <livewire:kadiv.pesan-table />

        </main>

    </div>
</body>
</html>