<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pesan - PT Silindo</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white flex h-screen overflow-hidden">

    {{-- SIDEBAR --}}
    @include('component_kadiv.sidebar')
    
    {{-- AREA KANAN --}}
    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        {{-- TOPBAR --}}
        @include('component_kadiv.topbar')
        @include('component.breadcrumbs', ['parentText' => 'Dashboard', 'parentUrl' => url('/kadiv/dashboard'), 'currentPage' => 'Daftar Pesan'])

        {{-- MAIN CONTENT --}}
        <main class="flex-1 overflow-y-auto p-8 pt-6 pb-16">
            <h1 class="text-2xl font-bold text-slate-800 mb-6">Daftar Pesan</h1>

            <livewire:kadiv.pesan-table />

        </main>

    </div>
</body>
</html>