<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pesan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">
    @include('component_hrd.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')
        <main class="flex-1 overflow-auto p-8 pt6 items-center">
            @include('component_hrd.pesanbar')
            <livewire:hrd.pesan-table />
            @include('component_hrd.pesanModal')

        </main>


    </div>
</body>

</html>
