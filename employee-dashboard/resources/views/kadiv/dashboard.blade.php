<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kepala Divisi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">

    @include('component_kadiv.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        @include('component_kadiv.topbar')
        <main class="flex-1 overflow-y-auto p-8">
            <h1 class="text-2xl font-bold text-gray-800">Status Tugas Divisi</h1>
            @include('component_kadiv.statusbar')

            <h1 class="text-2xl font-bold text-gray-800 pt-8">
                Manajemen Staff
            </h1>
            @include('component_kadiv.tabelstaff')

            @include('component_kadiv.tabelpdant')
        </main>
    </div>

</body>

</html>