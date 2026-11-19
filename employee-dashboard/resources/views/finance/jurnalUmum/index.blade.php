<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jurnal Umum - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">

    @include('component_kadiv.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')

        <main class="flex-1 overflow-y-auto bg-[#F4F7F9] p-4 sm:p-6 lg:p-8">
            @include('component.breadcrumbs', [
                'parentText' => 'Dashboard',
                'parentUrl' => url('/kadiv/dashboard'),
                'currentPage' => 'Jurnal Umum',
                'inline' => true,
            ])

            @livewire('finance.jurnal-umum-table')
        </main>
    </div>

</body>

</html>
