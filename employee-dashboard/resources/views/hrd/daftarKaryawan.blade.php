<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Karyawan - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">
    @include('component_hrd.sidebar')


    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')
        @include('component.breadcrumbs', ['parentText' => 'Dashboard', 'parentUrl' => url('/hrd/dashboard'), 'currentPage' => 'Manajemen Karyawan'])
        <main class="flex-1 overflow-y-auto p-8 pt-6">
            <h1 class="text-2xl font-bold text-slate-800 mb-6 flex items-center gap-2.5"><span class="p-2 rounded-lg bg-cyan-100 text-cyan-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0ZM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766Z"></path></svg></span><span>Manajemen Karyawan</span></h1>
            <livewire:hrd.karyawan-table />
            @include('component_hrd.karyawanModals', ['daftarDivisi' => $daftarDivisi])

        </main>



    </div>
</body>

</html>
