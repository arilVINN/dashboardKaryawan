@php
    $isCompact = $compact ?? false;
    $cellPadding = $isCompact ? 'px-4 py-3' : 'px-6 py-4';



    // Data dummy tugas
    $dummyTugas = [
        [
            'judul' => 'Slicing UI Dashboard Staff',
            'tenggat' => '30/09/2026',
            'status' => 'Sudah di-Acc',
            'status_class' => 'bg-[#CCF4DB] text-emerald-700',
            'link' => url('/tugas/detail/1'),
        ],
        [
            'judul' => 'Integrasi API Autentikasi',
            'tenggat' => '02/10/2026',
            'status' => 'Berjalan',
            'status_class' => 'bg-[#DFE4EA] text-black-700',
            'link' => url('/tugas/detail/2'),
        ],
        [
            'judul' => 'Perbaikan Responsif Mobile',
            'tenggat' => '05/10/2026',
            'status' => 'Baru',
            'status_class' => 'bg-[#C0E7FF] text-blue-700',

            'link' => url('/tugas/detail/3'),
        ],
        [
            'judul' => 'Testing Flow Notifikasi & Pesan',
            'tenggat' => '08/10/2026',
            'status' => 'Menunggu Acc',
            'status_class' => 'bg-[#FFF7ED] text-[#C2410C]',
            'link' => url('/tugas/detail/4'),
        ],
        [
            'judul' => 'Testing Flow',
            'tenggat' => '08/10/2026',
            'status' => 'Telat',
            'status_class' => 'bg-red-100 text-red-700',
            'link' => url('/tugas/detail/4'),
        ],
    ];
@endphp

<div class="flex flex-col gap-3">
    <h2 class="text-xl font-bold text-slate-800">Table Tugas</h2>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="{{ $isCompact ? 'overflow-hidden' : 'overflow-x-auto' }}">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-medium whitespace-nowrap">Judul Tugas</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap">Tenggat Waktu</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($dummyTugas as $tugas)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="{{ $cellPadding }} font-medium text-slate-800 whitespace-nowrap">
                                {{ $tugas['judul'] }}
                            </td>
                            <td class="{{ $cellPadding }} whitespace-nowrap text-slate-600">
                                {{ $tugas['tenggat'] }}
                            </td>
                            <td class="{{ $cellPadding }} whitespace-nowrap">
                                <span
                                    class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $tugas['status_class'] }}">
                                    {{ $tugas['status'] }}
                                </span>
                            </td>

                            <td class="{{ $cellPadding }} whitespace-nowrap text-center">
                                <a href="{{ $tugas['link'] }}"
                                    class="text-[#0097B2] hover:text-[#008199] font-medium hover:underline">
                                    Lihat
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
