<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arus Kas - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#F4F7F9] flex h-screen overflow-hidden">

    @include('component_kadiv.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')

        <main class="flex-1 overflow-y-auto bg-[#F4F7F9] p-4 pb-10 sm:p-6 lg:p-8">
            @include('component.breadcrumbs', [
                'rootText' => 'Dashboard',
                'rootUrl' => url('/kadiv/dashboard'),
                'parentText' => 'Keuangan',
                'parentUrl' => route('kadiv.keuangan'),
                'currentPage' => 'Arus Kas',
                'inline' => true,
            ])

            <livewire:finance.arus-kas />
        </main>
    </div>

</body>

</html>
