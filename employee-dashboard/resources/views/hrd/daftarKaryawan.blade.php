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
        @include('component.breadcrumbs')
        <main class="flex-1 overflow-y-auto p-8 pt-6">
            <form method="GET" action="{{ url('/hrd/daftarKaryawan') }}"
                class="flex flex-wrap items-end gap-3 bg-white border border-slate-200 rounded-xl shadow-sm p-4 mb-4">
                <div class="flex flex-col">
                    <label for="q" class="text-xs font-bold text-slate-500 mb-1">Cari Nama</label>
                    <input type="text" id="q" name="q" value="{{ request('q') }}" placeholder="Nama karyawan..."
                        class="px-3 py-2 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                </div>

                <div class="flex flex-col">
                    <label for="divisi" class="text-xs font-bold text-slate-500 mb-1">Divisi</label>
                    <select id="divisi" name="divisi"
                        class="px-3 py-2 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                        <option value="">Semua</option>
                        @foreach ($daftarDivisi as $d)
                            <option value="{{ $d->id_divisi }}" @selected(request('divisi') === $d->id_divisi)>{{ $d->nama_divisi }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col">
                    <label for="jabatan" class="text-xs font-bold text-slate-500 mb-1">Jabatan</label>
                    <select id="jabatan" name="jabatan"
                        class="px-3 py-2 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                        <option value="">Semua</option>
                        @foreach ($daftarJabatan as $j)
                            <option value="{{ $j }}" @selected(request('jabatan') === $j)>{{ $j }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col">
                    <label for="status" class="text-xs font-bold text-slate-500 mb-1">Status Akun</label>
                    <select id="status" name="status"
                        class="px-3 py-2 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                        <option value="">Semua</option>
                        <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                        <option value="belum" @selected(request('status') === 'belum')>Belum ada akun</option>
                    </select>
                </div>

                @if (request()->filled('sort'))
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                @endif
                @if (request()->filled('dir'))
                    <input type="hidden" name="dir" value="{{ request('dir') }}">
                @endif

                <div class="flex gap-2">
                    <button type="submit"
                        class="px-4 py-2 bg-[#004A65] text-white text-sm font-medium rounded-md hover:bg-[#003347] transition">Terapkan</button>
                    <a href="{{ url('/hrd/daftarKaryawan') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">Reset</a>
                </div>
            </form>

            @php
                $activeFilters = [];
                if (request()->filled('divisi')) {
                    $namaDivisi = optional($daftarDivisi->firstWhere('id_divisi', request('divisi')))->nama_divisi ?? request('divisi');
                    $q = request()->query();
                    unset($q['divisi'], $q['page']);
                    $activeFilters[] = ['name' => 'divisi', 'label' => 'Divisi: ' . $namaDivisi, 'url' => url()->current() . '?' . http_build_query($q)];
                }
                if (request()->filled('jabatan')) {
                    $q = request()->query();
                    unset($q['jabatan'], $q['page']);
                    $activeFilters[] = ['name' => 'jabatan', 'label' => 'Jabatan: ' . request('jabatan'), 'url' => url()->current() . '?' . http_build_query($q)];
                }
                if (request()->filled('status')) {
                    $q = request()->query();
                    unset($q['status'], $q['page']);
                    $activeFilters[] = ['name' => 'status', 'label' => 'Status Akun: ' . (request('status') === 'aktif' ? 'Aktif' : 'Belum ada akun'), 'url' => url()->current() . '?' . http_build_query($q)];
                }
                if (request()->filled('q')) {
                    $q = request()->query();
                    unset($q['q'], $q['page']);
                    $activeFilters[] = ['name' => 'q', 'label' => 'Cari: "' . request('q') . '"', 'url' => url()->current() . '?' . http_build_query($q)];
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

            @include('component_hrd.tabelKaryawan', ['karyawan' => $karyawan])
            <div class="px-1 mt-2">{{ $karyawan->links() }}</div>

        </main>
        

            
    </div>
</body>

</html>
