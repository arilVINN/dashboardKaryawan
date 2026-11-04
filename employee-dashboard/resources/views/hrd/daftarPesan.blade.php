<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pesan - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">
    @include('component_hrd.sidebar')
    

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')
        <main class="flex-1 overflow-y-auto p-8 pt-6">
            @include('component.breadcrumbs', ['parentText' => 'Dashboard', 'parentUrl' => url('/hrd/dashboard'), 'currentPage' => 'Daftar Pesan', 'inline' => true])
            @include('component_hrd.tabelPesan')
        </main>
        

    </div>
</body>

</html>
