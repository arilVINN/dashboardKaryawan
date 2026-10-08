@php
    $indicator = fn (string $col) => $sort === $col ? ($dir === 'asc' ? '▲' : '▼') : '↕';
    $cellPadding = 'px-6 py-4';
@endphp

<div class="flex flex-col gap-3 w-full mt-2">
    <div class="flex flex-wrap items-end gap-4 bg-white border border-slate-200 rounded-xl shadow-sm p-5">
        <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
            <label for="karyawanSearch" class="text-xs font-bold text-slate-500">Cari Nama</label>
            <input id="karyawanSearch" type="text" wire:model.live.debounce.300ms="search" placeholder="Nama karyawan..."
                class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
        </div>
        <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
            <label for="karyawanDivisi" class="text-xs font-bold text-slate-500">Divisi</label>
            <select id="karyawanDivisi" wire:model.live="divisi"
                class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                <option value="">Semua</option>
                @foreach ($daftarDivisi as $d)
                    <option value="{{ $d->id_divisi }}">{{ $d->nama_divisi }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
            <label for="karyawanJabatan" class="text-xs font-bold text-slate-500">Jabatan</label>
            <select id="karyawanJabatan" wire:model.live="jabatan"
                class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                <option value="">Semua</option>
                @foreach ($daftarJabatan as $j)
                    <option value="{{ $j }}">{{ $j }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
            <label for="karyawanStatus" class="text-xs font-bold text-slate-500">Status Akun</label>
            <select id="karyawanStatus" wire:model.live="status"
                class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                <option value="">Semua</option>
                <option value="aktif">Aktif</option>
                <option value="belum">Belum ada akun</option>
            </select>
        </div>
        <div class="flex shrink-0 gap-2 sm:ml-auto">
            <button type="button" wire:click="resetFilters"
                class="h-10 inline-flex items-center px-4 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">Reset</button>
        </div>
    </div>

    @if (!empty($chips))
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-bold text-slate-500 uppercase">Filter aktif:</span>
            @foreach ($chips as $chip)
                <span data-filter-chip="{{ $chip['name'] }}"
                    class="inline-flex items-center gap-1.5 pl-2.5 pr-1.5 py-1 rounded-full bg-cyan-50 text-cyan-700 text-xs font-semibold border border-cyan-100">
                    {{ $chip['label'] }}
                    <button type="button" wire:click="clearFilter('{{ $chip['name'] === 'q' ? 'search' : $chip['name'] }}')"
                        data-remove-filter="{{ $chip['name'] }}" title="Hapus filter"
                        class="inline-flex items-center justify-center w-4 h-4 rounded-full text-cyan-500 hover:bg-cyan-200 hover:text-cyan-900">&times;</button>
                </span>
            @endforeach
        </div>
    @endif

    <div class="flex justify-between items-center mb-2">
        <h2 class="text-xl font-bold text-slate-800">Daftar Karyawan</h2>
        <button type="button" data-staff-create onclick="bukaModalKaryawan()"
            class="text-sm px-4 py-2 bg-[#0097B2] text-white rounded-lg font-medium hover:bg-[#008199] transition">
            Tambah
        </button>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">
                            <button type="button" wire:click="sortBy('id')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                ID Karyawan <span class="{{ $sort === 'id' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('id') }}</span>
                            </button>
                        </th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">
                            <button type="button" wire:click="sortBy('nama')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Nama Karyawan <span class="{{ $sort === 'nama' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('nama') }}</span>
                            </button>
                        </th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">
                            <button type="button" wire:click="sortBy('divisi')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Divisi <span class="{{ $sort === 'divisi' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('divisi') }}</span>
                            </button>
                        </th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">
                            <button type="button" wire:click="sortBy('jabatan')" class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Jabatan <span class="{{ $sort === 'jabatan' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('jabatan') }}</span>
                            </button>
                        </th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Status</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($rows as $k)
                        <tr wire:key="karyawan-{{ $k->id_karyawan }}" class="hover:bg-slate-50 transition">
                            <td class="{{ $cellPadding }} font-semibold text-slate-800 whitespace-nowrap">
                                {{ $k->id_karyawan }}
                            </td>

                            <td class="{{ $cellPadding }} text-slate-900 font-medium whitespace-nowrap">
                                {{ $k->nama }}
                            </td>

                            <td class="{{ $cellPadding }} text-slate-500 whitespace-nowrap">
                                {{ $k->divisi->nama_divisi ?? '-' }}
                            </td>

                            <td class="{{ $cellPadding }} text-slate-500 whitespace-nowrap">
                                {{ $k->jabatan }}
                            </td>

                            <td class="{{ $cellPadding }} whitespace-nowrap">
                                @if ($k->user)
                                    <span
                                        class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">Aktif</span>
                                @else
                                    <span
                                        class="px-2.5 py-1 bg-slate-100 text-slate-500 rounded-full text-xs font-bold">Belum
                                        ada akun</span>
                                @endif
                            </td>

                            <td class="{{ $cellPadding }} whitespace-nowrap text-center text-xs">
                                <div class="flex items-center justify-center gap-3">
                                    <a href="{{ url('/hrd/detailKaryawan/' . $k->id_karyawan) }}"
                                        class="text-[#0097B2] hover:text-[#008199] font-medium hover:underline">
                                        Detail
                                    </a>
                                    <button type="button" data-staff-edit
                                        data-id="{{ $k->id_karyawan }}"
                                        data-nama="{{ $k->nama }}"
                                        data-gender="{{ $k->jenis_kelamin }}"
                                        data-lahir="{{ $k->tanggal_lahir }}"
                                        data-email="{{ $k->email }}"
                                        data-telepon="{{ $k->no_telepon }}"
                                        data-jabatan="{{ $k->jabatan }}"
                                        data-divisi="{{ $k->divisi_id_divisi }}"
                                        data-username="{{ $k->user->username ?? '' }}"
                                        onclick="bukaEditKaryawan(this)"
                                        class="text-amber-600 hover:text-amber-800 font-medium hover:underline">
                                        Edit
                                    </button>
                                    <button type="button" data-staff-delete
                                        data-id="{{ $k->id_karyawan }}"
                                        data-nama="{{ $k->nama }}"
                                        onclick="hapusKaryawan(this)"
                                        class="text-red-600 hover:text-red-800 font-medium hover:underline">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                                @if ($hasActiveFilters)
                                    <span>Tidak ada data yang cocok dengan filter.</span>
                                    <button type="button" wire:click="resetFilters"
                                        class="mt-1 inline-block text-sm text-[#0097B2] hover:underline">Reset filter</button>
                                @else
                                    Belum ada karyawan.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $rows->links() }}</div>
</div>
