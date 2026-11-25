<div class="org-card group w-full {{ $member->garis_putus ? 'is-dashed' : '' }}" style="padding: 8px;"
    id="org-card-{{ $member->id }}"
    data-id="{{ $member->id }}"
    data-employee='{{ e(json_encode([
        "id"           => $member->id,
        "nama"         => $member->nama,
        "jabatan"      => $member->jabatan,
        "divisi"       => $member->divisi,
        "status_kerja" => $member->status_kerja ?? "tetap",
        "garis_putus"  => (bool)$member->garis_putus,
        "email"        => $member->email ?? "-",
        "no_telepon"   => $member->no_telepon ?? "-",
        "keterangan"   => $member->keterangan ?? "-",
        "foto"         => $member->foto ? asset("storage/" . $member->foto) : ($member->karyawan?->foto ? asset("storage/" . $member->karyawan->foto) : ""),
        "parent_id"    => $member->parent_id,
        "urutan"       => $member->urutan,
    ])) }}'
    onclick="openProfileModal(this)">

    @if ($member->foto || $member->karyawan?->foto)
        <img src="{{ asset('storage/' . ($member->foto ?? $member->karyawan?->foto)) }}"
            alt="{{ $member->nama }}" class="org-card-photo object-cover" style="width: 38px; height: 38px;">
    @else
        <div class="org-card-photo" style="width: 38px; height: 38px; font-size: 13px;">
            {{ mb_strtoupper(mb_substr($member->nama, 0, 1)) }}
        </div>
    @endif

    <div class="min-w-0 flex-1">
        <h4 class="text-[11px] font-bold text-slate-900 leading-tight truncate">{{ $member->nama }}</h4>
        <div class="flex items-center gap-1.5 mt-0.5">
            <p class="text-[9.5px] text-slate-500 font-medium truncate">{{ $member->jabatan }}</p>
            @if ($member->status_kerja === 'magang')
                <span class="px-1 py-0.5 text-[8px] font-bold rounded bg-amber-100 text-amber-800 shrink-0">Magang</span>
            @elseif ($member->status_kerja === 'probation')
                <span class="px-1 py-0.5 text-[8px] font-bold rounded bg-purple-100 text-purple-800 shrink-0">Probation</span>
            @endif
        </div>
    </div>

    @if ($canManage)
        <div class="action-btn-group absolute right-1.5 top-1.5 hidden group-hover:flex items-center gap-1 z-30"
            onclick="event.stopPropagation(); event.stopImmediatePropagation();">
            <button type="button" onclick="openEditNodeModal(event, {{ $member->id }})" title="Edit Posisi"
                class="p-1.5 rounded-lg bg-white shadow-sm border border-slate-200 text-slate-600 hover:text-blue-600 hover:border-blue-400 hover:bg-blue-50 active:scale-95 transition cursor-pointer">
                <svg class="w-3.5 h-3.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
            </button>
        </div>
    @endif
</div>
