@php
    $isCompact = $compact ?? false;
    $cellPadding = $isCompact ? 'px-4 py-3' : 'px-6 py-4';

    // Data dummy pesan
    $dummyPesan = [
        [
            'id' => 1,
            'judul' => 'Revisi Layout',
            'pengirim' => 'aril',
            'tanggal' => '28 Sept 2026',
        ],
        [
            'id' => 2,
            'judul' => 'Integrasi API',
            'pengirim' => 'jeremy',
            'tanggal' => '27 Sept 2026',
        ],
        [
            'id' => 3,
            'judul' => 'Update Asset Logo',
            'pengirim' => 'fitri',
            'tanggal' => '26 Sept 2026',
        ],
    ];
@endphp

<div class="flex flex-col gap-3 w-full">
    <h2 class="text-xl font-bold text-slate-800">Table Pesan</h2>
    
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
        <div class="{{ $isCompact ? 'overflow-hidden' : 'overflow-x-auto' }}">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Pengirim</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Judul</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Tanggal</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($dummyPesan as $pesan)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="{{ $cellPadding }} font-semibold text-slate-800 whitespace-nowrap">
                                {{ $pesan['pengirim'] }}
                            </td>
                            <td class="{{ $cellPadding }} max-w-[160px] truncate text-slate-500">
                                {{ $pesan['judul'] }}
                            </td>
                            <td class="{{ $cellPadding }} whitespace-nowrap text-slate-500">
                                {{ $pesan['tanggal'] }}
                            </td>
                            <td class="{{ $cellPadding }} whitespace-nowrap text-center">
                                <a href="{{ url('/pesan/detail/' . $pesan['id']) }}" 
                                   class="text-[#0097B2] hover:text-[#008199] font-medium hover:underline">
                                    Baca
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>