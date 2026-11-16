<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Tugas - PT Silindo</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">


    @include('component.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        @include('component.topbar')

        <main class="flex-1 overflow-y-auto p-8 pt-6">
            @include('component.breadcrumbs', ['parentText' => 'Dashboard', 'parentUrl' => url('/'), 'currentPage' => 'Daftar Tugas', 'inline' => true])
            <div class="flex items-center justify-between mb-6">
                <div>
                    <x-page-heading title="Daftar Tugas" icon="tasks" />
                    
                </div>
            </div>

            <div class="w-full">
                @include('component.tableTugas')
                @include('component.tabelProject')
            </div>
        </main>

    </div>

</body>

</html>
