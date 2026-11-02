<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">

    @include('component_kadiv.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        @include('component.topbar')

        <div class="px-8">
            @include('component_kadiv.statusbar')
        </div>
        <main class="flex-1 overflow-y-auto p-8">
            <h4 class="text-2xl font-bold text-slate-800 pt-2">
                Manajemen Staff
            </h4>
            @include('component_kadiv.tabelstaff')

            @include('component_kadiv.tabelpdant')
        </main>
    </div>

</body>

</html>