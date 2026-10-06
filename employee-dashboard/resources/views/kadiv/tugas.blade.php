

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Tugas</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white flex h-screen overflow-hidden">

    @include('component_kadiv.sidebar')
    
    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component_kadiv.topbar')

        <main class="flex-1 overflow-y-auto p-6 lg:p-8 pb-16">


            @include('component_kadiv.tabelTugas')

        </main>

    </div>


</body>
</html>