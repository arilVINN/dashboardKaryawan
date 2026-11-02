<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Project - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">
    @include('component_kadiv.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')
        @include('component.breadcrumbs', [
            'parentText' => 'Daftar Tugas',
            'parentUrl' => url('/kadiv/tugas'),
            'currentPage' => 'Detail Project',
        ])

        <main class="flex-1 overflow-y-auto p-8 pt-6 pb-16">
            @livewire('kadiv.project-detail', ['id' => $id])
        </main>
    </div>
</body>

</html>
