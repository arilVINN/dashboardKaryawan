<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pesan Staff</title>
    
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">

    @include('component.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        
        @include('component.topbar')

        <main class="flex-1 overflow-y-auto p-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Daftar Semua Pesan</h1>
                    <p class="text-sm text-gray-500 mt-1">Kelola dan pantau progres seluruh Pesan staff</p>
                </div>
            </div>

            <div class="w-full">
                <livewire:staff.pesan-table />
            </div>
        </main>

    </div>

</body>

</html>