<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">
    @include('component.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        
        @include('component.topbar')

        <main class="flex-1 overflow-y-auto p-8">
            <h1 class="text-2xl font-bold text-gray-800">Status Tugas Staff</h1>
            @include('component.statusbar')

            <h1 class="text-2xl font-bold text-gray-800 pt-5">Notifikasi</h1>
            @include('component.notifikasi')

            <div class="grid grid-cols-1 lg:grid-cols-1 gap-6 items-start mt-6">
                @include('component.tableTugas',['compact' => true])
                @include('component.tablePesan',['compact' => true])
            </div>
        </main>

    </div>

</body>

</html>