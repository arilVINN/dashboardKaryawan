<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Divisi - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">

    @include('component_hrd.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        @include('component.topbar')
        @include('component.breadcrumbs', ['parentText' => 'Manajemen Divisi', 'parentUrl' => url('/hrd/manajemenDivisi'), 'currentPage' => $divisi->nama_divisi])

        <main class="flex-1 space-y-5 overflow-y-auto px-8 pb-8 pt-2">
            <div>
                <h2 class="text-[20px] font-semibold text-[#172B36]">{{ $divisi->nama_divisi }}</h2>
                <p class="mt-1 text-[13px] text-[#4B5563]">Kode divisi: {{ $divisi->kode_divisi }}</p>
            </div>

            <section class="flex items-center gap-4 rounded-xl border border-[#E2E8EC] bg-white p-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-full bg-[#E3F1F7] text-[18px] font-semibold text-[#0B4A66]">
                    @if ($ketuaDivisi?->foto)
                        <img src="{{ route('hrd.karyawan.foto', $ketuaDivisi->id_karyawan) }}"
                             alt="Foto profil {{ $ketuaDivisi->nama }}"
                             class="h-full w-full rounded-full object-cover">
                    @else
                        {{ $ketuaDivisi ? mb_strtoupper(mb_substr($ketuaDivisi->nama, 0, 1)) : '?' }}
                    @endif
                </div>
                <div>
                    <p class="text-[14px] font-semibold text-[#172B36]">{{ $ketuaDivisi->nama ?? 'Belum ditentukan' }}</p>
                    <p class="mt-0.5 text-[12px] text-[#4B5563]">Ketua divisi</p>
                </div>
            </section>

            @include('component_hrd.tabelAnggotaDivisi', ['bisaTambahAnggota' => $bisaTambahAnggota])
        </main>

        @include('component_hrd.karyawanModals', [
            'daftarDivisi' => $daftarDivisi,
            'divisiTerkunci' => $divisi->id_divisi,
            'judulTambah' => 'Tambah Karyawan ke '.$divisi->nama_divisi,
        ])


    </div>

</body>

</html>
