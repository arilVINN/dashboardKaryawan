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
        @include('component.breadcrumbs', ['parentText' => 'Manajemen Divisi', 'parentUrl' => url('/hrd/manajemenDivisi'), 'currentPage' => 'Detail Divisi'])

        <div class="flex flex-col gap-3 w-full pt-9 ml-8">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">{{ $divisi->nama_divisi }}</h2>
                    <p class="text-sm text-slate-500">Kode divisi: {{ $divisi->kode_divisi }}</p>
                </div>
            </div>
        </div>

        <main class="flex overflow-y-auto p-8 pt-2 items-center gap-5 drop-shadow-2sm">
            <div class="w-20 h-20 rounded-full bg-cyan-100 text-cyan-700 flex items-center justify-center font-bold text-2xl shrink-0">
                {{ $ketuaDivisi ? strtoupper(substr($ketuaDivisi->nama, 0, 1)) : '?' }}
            </div>
            <div class="items-center">
                <h3 class="font-medium">{{ $ketuaDivisi->nama ?? 'Belum ditentukan' }}</h3>
                <h4 class="font-light">Ketua divisi</h4>
            </div>

        </main>

        @include('component_hrd.tabelAnggotaDivisi')


    </div>

</body>

</html>
