<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Staff - PT SILINDO</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#F3F4F6] flex h-screen overflow-hidden">

    @include('component_kadiv.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        @include('component_kadiv.topbar')

        <main class="flex-1 overflow-y-auto">
            <div class="px-9 pt-7 pb-16">

                @php
                    $staff = $staff ?? ['id' => 1, 'nama' => 'Agung', 'jabatan' => 'Writer', 'status' => 'Aktif'];

                    $baris = $tugasStaff ?? [
                        ['id' => 1, 'tugas' => 'Menulis poster', 'tenggat' => '08 Oct 2026', 'approval' => 'Pending'],
                    ];

                    $warna = [
                        'Pending'   => 'bg-[#FEF3C7] text-[#B45309]',
                        'Disetujui' => 'bg-[#DCFCE7] text-[#15803D]',
                        'Revisi'    => 'bg-[#FEE2E2] text-[#B91C1C]',
                    ];
                @endphp

                @include('component.breadcrumbs')

                <div class="mt-4 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-full bg-[#19A7CE] text-white text-xl font-bold flex items-center justify-center">
                            {{ strtoupper(substr($staff['nama'], 0, 1)) }}
                        </div>
                        <div>
                            <h1 class="text-[28px] leading-[36px] font-bold text-black">{{ $staff['nama'] }}</h1>
                            <p class="text-sm text-[#565E74]">
                                {{ $staff['jabatan'] }} ·
                                <span class="text-[#15803D] font-semibold">{{ $staff['status'] }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <h2 class="mt-8 text-xl font-bold text-[#0F172A]">Tugas {{ $staff['nama'] }}</h2>

                <div class="mt-4 bg-white rounded-xl border border-[#E2E8F0] shadow-sm overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-[#F1F5F9] text-[#565E74] uppercase">
                            <tr>
                                <th class="px-6 py-4 text-left font-normal">Tugas</th>
                                <th class="px-6 py-4 text-center font-normal">Tenggat</th>
                                <th class="px-6 py-4 text-center font-normal">Approval</th>
                                <th class="px-6 py-4 text-center font-normal">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-[#E2E8F0]">
                            @forelse ($baris as $b)
                                <tr class="hover:bg-[#F8FAFC] transition">
                                    <td class="px-6 py-4 font-semibold text-[#0F172A]">{{ $b['tugas'] }}</td>
                                    <td class="px-6 py-4 text-center text-[#565E74]">{{ $b['tenggat'] }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $warna[$b['approval']] ?? 'bg-slate-100 text-slate-600' }}">
                                            {{ $b['approval'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ url('/kadiv/detailTugas/' . $b['id']) }}"
                                           class="text-[#0e9dc3] hover:text-[#0c88a9] hover:underline transition font-medium">
                                            Lihat
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-[#94A3B8]">
                                        Staff ini belum punya tugas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </main>
    </div>

</body>
</html>