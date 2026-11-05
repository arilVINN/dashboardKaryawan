@php
    $divisis = collect($divisis ?? []);
@endphp

<div class="flex flex-col gap-2">
    <div class="flex items-center justify-between px-1">
        <h2 class="text-lg font-semibold text-[#172B36]">Manajemen divisi</h2>
        <a href="{{ route('hrd.manajemenDivisi') }}" class="text-xs text-[#146C94] hover:underline">
            Lihat semua &rsaquo;
        </a>
    </div>

    <div class="overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[640px] table-fixed text-left text-[13px] text-[#475569]">
            <thead class="bg-[#EAF4F8]">
                <tr class="h-8">
                    <th class="w-[36%] px-4 text-left text-xs font-medium text-[#334155]">Divisi</th>
                    <th class="w-[24%] px-4 text-left text-xs font-medium text-[#334155]">Tugas selesai</th>
                    <th class="w-[16%] px-4 text-left text-xs font-medium text-[#334155]">Status</th>
                    <th class="w-[24%] px-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#EEF2F5]">
                @forelse ($divisis as $divisi)
                    @php
                        $total = (int) ($divisi->tugas_total_count ?? 0);
                        $selesai = (int) ($divisi->tugas_selesai_count ?? 0);
                        $percentage = $total > 0 ? (int) round(($selesai / $total) * 100) : 0;
                        $detailUrl = url('/hrd/detailDivisi/' . $divisi->id_divisi);
                    @endphp
                    <tr class="h-[54px] cursor-pointer hover:bg-[#F5FAFC] focus:bg-[#F5FAFC] focus:outline-none"
                        tabindex="0" role="link"
                        onclick="window.location.href='{{ $detailUrl }}'"
                        onkeydown="if (event.target.closest('button, a')) return; if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location.href='{{ $detailUrl }}'; }">
                        <td class="px-4 py-2">
                            <div class="min-w-0">
                                <p class="truncate text-[13px] font-medium text-[#172B36]">{{ $divisi->nama_divisi }}</p>
                                <p class="mt-0.5 truncate font-mono text-[11px] text-[#4B5563]">{{ $divisi->kode_divisi }}</p>
                            </div>
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex items-baseline gap-1">
                                <span class="text-[13px] font-medium text-[#172B36]">{{ $selesai }}/{{ $total }}</span>
                                <span class="text-[11px] text-[#4B5563]">tugas</span>
                            </div>
                            <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-[#E2E8EC]"
                                role="progressbar"
                                aria-label="{{ $selesai }} dari {{ $total }} tugas selesai"
                                aria-valuemin="0"
                                aria-valuemax="{{ $total }}"
                                aria-valuenow="{{ $selesai }}">
                                <div class="h-full rounded-full bg-[#0D9488]" style="width: {{ $percentage }}%"></div>
                            </div>
                        </td>
                        <td class="px-4 py-2">
                            <x-status-badge :status="$divisi->status_aktif ?? 'Aktif'" />
                        </td>
                        <td class="px-3 py-2">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ $detailUrl }}" onclick="event.stopPropagation()"
                                    class="inline-flex items-center justify-center rounded px-1 py-1 text-[11px] font-semibold text-[#146C94] transition hover:text-[#0B4A66] hover:underline focus:outline-none focus:ring-2 focus:ring-[#146C94]/30">
                                    Detail
                                </a>
                                <button type="button" onclick="event.stopPropagation(); bukaModalEditDivisi(this)"
                                    data-id="{{ $divisi->id_divisi }}"
                                    data-kode="{{ $divisi->kode_divisi }}"
                                    data-nama="{{ $divisi->nama_divisi }}"
                                    class="text-xs font-medium text-amber-600 hover:text-amber-800 hover:underline">
                                    Edit
                                </button>
                                <button type="button" onclick="event.stopPropagation(); hapusDivisi(this)"
                                    data-id="{{ $divisi->id_divisi }}"
                                    data-nama="{{ $divisi->nama_divisi }}"
                                    class="text-xs font-medium text-red-600 hover:text-red-800 hover:underline">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-[13px] text-[#4B5563]">
                            Belum ada data divisi.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

        <div class="border-t border-[#EEF2F5] px-4 py-3 text-xs text-[#4B5563]">
            Menampilkan {{ $divisis->count() }} dari {{ $divisis->count() }} divisi
        </div>
    </div>
</div>
