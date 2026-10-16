<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Staff - PT Silindo</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white flex h-screen overflow-hidden">

    {{-- SIDEBAR --}}
    @include('component_kadiv.sidebar')

    {{-- AREA KANAN --}}
    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        {{-- TOPBAR --}}
        @include('component.topbar')
        @include('component.breadcrumbs', [
            'parentText' => 'Dashboard',
            'parentUrl' => url('/kadiv/dashboard'),
            'currentPage' => 'Manajemen Staff',
        ])

        {{-- MAIN CONTENT --}}
        <main class="flex-1 overflow-y-auto p-8 pt-6">
            <h1 class="text-2xl font-bold text-slate-800 mb-6 flex items-center gap-2.5">
                <span class="p-2 rounded-lg bg-teal-100 text-teal-600"><svg class="w-6 h-6" fill="none"
                        stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z">
                        </path>
                    </svg>
                </span>
                <span>Manajemen Staff</span>
            </h1>

            <livewire:kadiv.staff-table />

        </main>

    </div>

</body>

</html>
