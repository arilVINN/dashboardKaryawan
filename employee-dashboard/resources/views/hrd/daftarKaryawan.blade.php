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

                <div class="flex flex-col">
                    <label for="sort" class="text-xs font-bold text-slate-500 mb-1">Urutkan</label>
                    <select id="sort" name="sort"
                        class="px-3 py-2 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                        <option value="nama" @selected(request('sort', 'nama') === 'nama')>Nama</option>
                        <option value="id" @selected(request('sort') === 'id')>ID Karyawan</option>
                        <option value="divisi" @selected(request('sort') === 'divisi')>Divisi</option>
                        <option value="jabatan" @selected(request('sort') === 'jabatan')>Jabatan</option>
                    </select>
                </div>

                <div class="flex flex-col">
                    <label for="dir" class="text-xs font-bold text-slate-500 mb-1">Arah</label>
                    <select id="dir" name="dir"
                        class="px-3 py-2 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                        <option value="asc" @selected(request('dir', 'asc') === 'asc')>Naik (A-Z)</option>
                        <option value="desc" @selected(request('dir') === 'desc')>Turun (Z-A)</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="px-4 py-2 bg-[#004A65] text-white text-sm font-medium rounded-md hover:bg-[#003347] transition">Terapkan</button>
                    <a href="{{ url('/hrd/daftarKaryawan') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">Reset</a>
                </div>
            </form>

            @include('component_hrd.tabelKaryawan', ['karyawan' => $karyawan])
            <div class="px-1 mt-2">{{ $karyawan->links() }}</div>

        </main>
        

            
    </div>
</body>

</html>
