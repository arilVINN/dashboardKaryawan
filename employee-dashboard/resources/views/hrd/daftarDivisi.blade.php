<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Karyawan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">
    @include('component_hrd.sidebar')


    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')
        @include('component.breadcrumbs', ['parentText' => 'Dashboard', 'parentUrl' => url('/hrd/dashboard'), 'currentPage' => 'Daftar Divisi'])
        <main class="flex-1 overflow-y-auto p-8 pt-6">
            <form method="GET" action="{{ url('/hrd/daftarDivisi') }}"
                class="flex flex-wrap items-end gap-4 bg-white border border-slate-200 rounded-xl shadow-sm p-5 mb-6">
                <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
                    <label for="q" class="text-xs font-bold text-slate-500 mb-1">Cari Kode/Nama</label>
                    <input type="text" id="q" name="q" value="{{ request('q') }}" placeholder="Kode atau nama divisi..."
                        class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                </div>

                <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
                    <label for="status" class="text-xs font-bold text-slate-500 mb-1">Status</label>
                    <select id="status" name="status"
                        class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                        <option value="">Semua</option>
                        <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                        <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
                    </select>
                </div>

                @if (request()->filled('sort'))
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                @endif
                @if (request()->filled('dir'))
                    <input type="hidden" name="dir" value="{{ request('dir') }}">
                @endif

                <div class="flex shrink-0 gap-2 sm:ml-auto">
                    <button type="submit"
                        class="h-10 inline-flex items-center px-4 bg-[#004A65] text-white text-sm font-medium rounded-md hover:bg-[#003347] transition">Terapkan</button>
                    <a href="{{ url('/hrd/daftarDivisi') }}"
                        class="h-10 inline-flex items-center px-4 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">Reset</a>
                </div>
            </form>

            @php
                $activeFilters = [];
                if (request()->filled('q')) {
                    $q = request()->query();
                    unset($q['q'], $q['page']);
                    $activeFilters[] = ['name' => 'q', 'label' => 'Cari: "' . request('q') . '"', 'url' => url()->current() . '?' . http_build_query($q)];
                }
                if (request()->filled('status')) {
                    $q = request()->query();
                    unset($q['status'], $q['page']);
                    $activeFilters[] = ['name' => 'status', 'label' => 'Status: ' . ucfirst(request('status')), 'url' => url()->current() . '?' . http_build_query($q)];
                }
            @endphp

            @if (!empty($activeFilters))
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    <span class="text-xs font-bold text-slate-500 uppercase">Filter aktif:</span>
                    @foreach ($activeFilters as $chip)
                        <x-filter-chip :name="$chip['name']" :label="$chip['label']" :remove-url="$chip['url']" />
                    @endforeach
                </div>
            @endif

            @include('component_hrd.tabelDaftarDivisi', ['divisis' => $divisis, 'compact' => true])
            <div class="px-1 mt-2">{{ $divisis->links() }}</div>
        </main>



    </div>
</body>

</html>
