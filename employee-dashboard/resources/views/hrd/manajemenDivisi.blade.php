<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Divisi - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">
    @include('component_hrd.sidebar')
    

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')
        @include('component.breadcrumbs', ['parentText' => 'Dashboard', 'parentUrl' => url('/hrd/dashboard'), 'currentPage' => 'Manajemen Divisi'])
        <main class="flex-1 overflow-y-auto p-8 pt-6">
            <h1 class="text-2xl font-bold text-slate-800 mb-6 flex items-center gap-2.5"><span class="p-2 rounded-lg bg-violet-100 text-violet-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M4 18h16M6 18v-7m4 7v-7m4 7v-7m4 7v-7M2 10l10-6 10 6"></path></svg></span><span>Manajemen Divisi</span></h1>
            <livewire:hrd.divisi-table manage />
            @include('component_hrd.divisiModal')

        </main>

            
    </div>
</body>

</html>
