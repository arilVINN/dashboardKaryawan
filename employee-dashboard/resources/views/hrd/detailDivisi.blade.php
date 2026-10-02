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

        <div class="flex flex-col gap-3 w-full pt-9 ml-8">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold text-slate-800">Divisi Komunikasi dan IT</h2>
            </div>
        </div>

        <main class="flex overflow-y-auto p-8 pt-2 items-center gap-5 drop-shadow-2sm">
            <div
                class="w-20 h-20 rounded-full bg-white stroke-blue-500 text-white flex items-center justify-center font-bold text-sm shrink-0">
                <img src="/gambar/silindo.png" alt="">
            </div>
            <div class=" items-center">
                <h3 class="font-medium">Panji doe <br></h3>
                <h4 class="font-light"> Ketua divisi </h4>
            </div>

        </main>

        @include('component_hrd.tabelAnggotaDivisi')


    </div>

</body>

</html>
