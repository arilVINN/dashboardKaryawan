<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Divisi - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">
    @include('component_hrd.sidebar')
    

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')
        <main class="flex-1 overflow-y-auto p-8 pt-6">
            @include('component.breadcrumbs', ['parentText' => 'Dashboard', 'parentUrl' => url('/hrd/dashboard'), 'currentPage' => 'Manajemen Divisi', 'inline' => true])
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <x-page-heading title="Manajemen Divisi" icon="division" />
                <a href="{{ route('hrd.jabatan.index') }}"
                   class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] font-medium text-[#146C94] hover:bg-[#F4F7F9]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2-11H7a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2Z" />
                    </svg>
                    Kelola jabatan
                </a>
            </div>
            <livewire:hrd.divisi-table manage :show-hierarchy="false" />
            @include('component_hrd.divisiModal')

        </main>

            
    </div>
</body>

</html>
