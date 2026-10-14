<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pesan - PT Silindo</title>
    
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">

    @include('component.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        
        @include('component.topbar')
        @include('component.breadcrumbs', ['parentText' => 'Dashboard', 'parentUrl' => url('/'), 'currentPage' => 'Daftar Pesan'])

        <main class="flex-1 overflow-y-auto p-8 pt-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2.5"><span class="p-2 rounded-lg bg-green-100 text-green-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"></path></svg></span><span>Daftar Pesan</span></h1>
                    <p class="text-sm text-gray-500 mt-1">Kelola dan pantau progres seluruh Pesan staff</p>
                </div>
            </div>

            <div class="w-full">
                @include('component.tablePesan')
            </div>
        </main>

    </div>

</body>

</html>