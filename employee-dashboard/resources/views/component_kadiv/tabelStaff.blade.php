@php
    $isCompact = $compact ?? false;
    $cellPadding = $isCompact ? 'px-4 py-3' : 'px-6 py-4';

    // Data dummy dipindahkan ke array agar mudah di-looping
    $dummyStaff = [
        [
            'id' => 1,
            'nama' => 'Samuel Sigalingging',
            'tugas' => '3 Tugas',
            'status' => 'Aktif',
        ],
        [
            'id' => 2,
            'nama' => 'Andi Pratama',
            'tugas' => '2 Tugas',
            'status' => 'Aktif',
        ],
        [
            'id' => 3,
            'nama' => 'Rina Maharani',
            'tugas' => '4 Tugas',
            'status' => 'Aktif',
        ],
        [
            'id' => 4,
            'nama' => 'Dimas Saputra',
            'tugas' => '1 Tugas',
            'status' => 'Aktif',
        ],
    ];
@endphp

<div class="flex flex-col gap-3 w-full mt-2">
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold text-slate-800">Manajemen Staff</h2>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
        <div class="{{ $isCompact ? 'overflow-hidden' : 'overflow-x-auto' }}">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Nama Staff</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">Tugas</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">Keterangan</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($dummyStaff as $staff)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="{{ $cellPadding }} font-semibold text-slate-800 whitespace-nowrap">
                                {{ $staff['nama'] }}
                            </td>

                            <td class="{{ $cellPadding }} text-slate-700 text-center">
                                {{ $staff['tugas'] }}
                            </td>

                            <td class="{{ $cellPadding }} whitespace-nowrap text-center">
                                @if ($staff['status'] == 'Aktif')
                                    <span
                                        class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">
                                        {{ $staff['status'] }}
                                    </span>
                                @else
                                    <span
                                        class="px-2.5 py-1 bg-slate-100 text-slate-500 rounded-full text-xs font-bold">
                                        {{ $staff['status'] }}
                                    </span>
                                @endif
                            </td>

                            {{-- Kolom Aksi --}}
                            <td class="{{ $cellPadding }} whitespace-nowrap text-center text-xs">
                                <a href="{{ route('kadiv.manajemenStaff.show', $staff['id']) }}"
                                    class="text-[#0c88a9] hover:text-[#06627b] hover:underline transition font-medium">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
