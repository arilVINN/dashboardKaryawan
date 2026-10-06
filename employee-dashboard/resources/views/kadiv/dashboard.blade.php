<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kadiv</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">
    
    @include('component_kadiv.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        
        @include('component_kadiv.topbar')
        {{-- =====================================================
             MAIN CONTENT
        ====================================================== --}}
        <main class="flex-1 overflow-y-auto p-8">
            <h1 class="text-2xl font-bold text-gray-800">Status Tugas Divisi</h1>
            <div class="px-8">
                @include('component_kadiv.statusbar')
            </div>

            {{-- =================================================
                 MANAJEMEN STAFF
            ================================================== --}}
            <h1 class="text-2xl font-bold text-gray-800 pt-8">
                Manajemen Staff
            </h1>
            @include('component_kadiv.tabelstaff')

            {{-- =================================================
                 FILTER + PAGINATION
            ================================================== --}}
            <div class="flex flex-wrap items-center justify-between gap-4 mt-4">

                <button
                    type="button"
                    class="flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-600 hover:bg-slate-50">

                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24">

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M7 12h10M10 18h4">
                        </path>

                    </svg>

                    Filter

                </button>

                <div class="flex items-center gap-2">

                    <button
                        type="button"
                        class="h-7 w-7 rounded border border-slate-300 text-sm">
                        «
                    </button>

                    <button
                        type="button"
                        class="h-7 w-7 rounded bg-[#19A7CE] text-sm text-white">
                        1
                    </button>

                    <button
                        type="button"
                        class="h-7 w-7 rounded border border-slate-300 text-sm">
                        2
                    </button>

                    <button
                        type="button"
                        class="h-7 w-7 rounded border border-slate-300 text-sm">
                        3
                    </button>

                    <button
                        type="button"
                        class="h-7 w-7 rounded border border-slate-300 text-sm">
                        4
                    </button>

                    <button
                        type="button"
                        class="h-7 w-7 rounded border border-slate-300 text-sm">
                        » 
                    </button>

                </div>

            </div>

            @include('component_kadiv.tabelpdant')
        </main>

        </main>
        
    </div>

</body>

</html>