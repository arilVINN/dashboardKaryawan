<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Staff</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white flex h-screen overflow-hidden">

    {{-- SIDEBAR --}}
    @include('component_kadiv.sidebar')
    
    {{-- AREA KANAN --}}
    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        {{-- TOPBAR --}}
        @include('component_kadiv.topbar')

        {{-- MAIN CONTENT --}}
        <main class="flex-1 overflow-y-auto p-6 lg:p-8 pb-16">
            <h1 class="text-2xl font-bold text-gray-800">
                Manajemen Staff
            </h1>

            @include('component_kadiv.tabelstaff')

        </main>

    </div>

</body>
</html>