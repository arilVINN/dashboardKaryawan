<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Tugas - PT Silindo</title>

    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">


    @include('component.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        @include('component.topbar')
        @include('component.breadcrumbs', ['parentText' => 'Dashboard', 'parentUrl' => url('/'), 'currentPage' => 'Daftar Tugas'])

        <main class="flex-1 overflow-y-auto p-8 pt-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2.5"><span class="p-2 rounded-lg bg-blue-100 text-blue-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9 2 2 4-4"></path></svg></span><span>Daftar Tugas</span></h1>
                </div>
            </div>

            <div class="w-full">
                @include('component.tableTugas')
            </div>
        </main>

    </div>

</body>

</html>
