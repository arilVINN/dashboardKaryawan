<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusat Data & Arsip - PT Silindo</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">

    @include('component.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        @include('component.topbar')
        @include('component.breadcrumbs', [
            'parentText' => 'Dashboard',
            'parentUrl' => url('/'),
            'currentPage' => 'Pusat Data & Arsip',
        ])

        <main class="flex-1 overflow-y-auto p-8 pt-4 pb-12">
            <livewire:tower-database-table />
        </main>

    </div>

</body>

</html>
