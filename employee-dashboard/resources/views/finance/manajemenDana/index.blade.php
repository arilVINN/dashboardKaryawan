<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permintaan & Verifikasi Dana - PT Silindo</title>
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
                'parentText' => 'Keuangan',
                'parentUrl' => route('kadiv.keuangan'),
                'currentPage' => 'Permintaan & Verifikasi',
                'inline' => true,
            ])

            {{-- Header --}}
            <div class="mb-6">
                <x-page-heading title="Permintaan & Verifikasi" icon="document" />
                <p class="mt-1 text-[13px] text-[#4B5563]">Kelola permintaan dana divisi dan verifikasi laporan laba/rugi mingguan.</p>
            </div>

            {{-- Flash Alerts --}}
            @if (session('success'))
                <div class="mb-4 p-4 rounded-xl bg-[#F0FDF4] border border-[#BBF7D0] text-[#166534] text-[13px] flex items-center gap-2 shadow-sm">
                    <svg class="w-5 h-5 text-[#047857] shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-4 rounded-xl bg-[#FEF2F2] border border-[#FECACA] text-[#991B1B] text-[13px] flex items-center gap-2 shadow-sm">
                    <svg class="w-5 h-5 text-[#B91C1C] shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <livewire:finance.manajemen-dana />

        </main>
    </div>

</body>
</html>
