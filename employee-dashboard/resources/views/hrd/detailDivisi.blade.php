<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Divisi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">

    @include('component_hrd.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        @include('component.topbar')
        @include('component.breadcrumbs')

        <div class="flex flex-col gap-3 w-full mt-2 ml-8">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold text-slate-800">Divisi Komunikasi dan IT</h2>
            </div>
        </div>

        <main class="flex overflow-y-auto p-8 pt-6 items-center gap-5">
            <!-- Konten  -->
            <div
                class="w-20 h-20 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold text-sm shrink-0">
                P
            </div>
            <div class="font-medium items-center">
                <h3>Panji doe <br>Ketua divisi</h3>
            </div>

        </main>

        <div class="flex justify-end pr-4 sm:pr-6 lg:pr-8 gap-3">
            <button type="button" id="btn-toggle-balas"
                class="px-6 py-2.5 bg-[#0097B2] hover:bg-[#008199] text-white text-sm font-semibold rounded-lg shadow-sm transition">
                Tambah
            </button>
        </div>
        @include('component_hrd.tabelAnggotaDivisi')


    </div>

</body>

</html>
