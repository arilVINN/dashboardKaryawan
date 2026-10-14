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
            <livewire:staff.pesan-table />
        </main>

    </div>

</body>

</html>
