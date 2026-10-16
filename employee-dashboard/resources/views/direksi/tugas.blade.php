<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Tugas - PT Silindo</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white flex h-screen overflow-hidden">

    @include('component_direksi.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')
        @include('component.breadcrumbs', ['parentText' => 'Dashboard', 'parentUrl' => url('/direksi/dashboard'), 'currentPage' => 'Monitor Tugas'])

        <main class="flex-1 overflow-y-auto p-8 pt-6">
            <livewire:direksi.tugas-table />
        </main>
    </div>

</body>

</html>
