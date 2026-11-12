<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">
    
    @include('component_hrd.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        
        @include('component.topbar')

        <main class="flex-1 overflow-y-auto p-8 pt-6 pb-6">
            @include('component_hrd.statusbar')
            @include('components.carousel')

            <div class="mt-6 grid grid-cols-1 items-start gap-6 min-[900px]:grid-cols-[1.5fr_1fr]">
                @include('component_hrd.tabelDivisi')
                @include('component_hrd.notifikasi', ['pesan' => $pesan])
            </div>

            @include('component_hrd.divisiModal')
        </main>
        
    </div>

</body>

</html>