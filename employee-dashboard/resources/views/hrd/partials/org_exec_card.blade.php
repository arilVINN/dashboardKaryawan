@php
    $cardId = $node->node_key ?? ('exec_' . $node->id);
    $fotoUrl = $node->foto ?? '';
    $cardWidth = $width ?? '290px';
@endphp
<div class="org-card group {{ !empty($node->garis_putus) ? 'is-dashed' : '' }}" style="width: {{ $cardWidth }};"
    id="org-card-{{ $cardId }}"
    data-node-key="{{ $cardId }}"
    data-id="{{ $node->id }}"
    data-source-type="{{ $node->source_type ?? 'struktur' }}"
    data-employee='{!! json_encode([
        "id"           => $node->id,
        "node_key"     => $cardId,
        "source_type"  => $node->source_type ?? "struktur",
        "nama"         => $node->nama,
        "jabatan"      => $node->jabatan,
        "divisi_id"    => $node->divisi_id ?? "",
        "divisi"       => $node->divisi ?? "-",
        "status_kerja" => $node->status_kerja ?? "tetap",
        "garis_putus"  => (bool)($node->garis_putus ?? false),
        "email"        => $node->email ?? "-",
        "no_telepon"   => $node->no_telepon ?? "-",
        "keterangan"   => $node->keterangan ?? "-",
        "foto"         => $fotoUrl,
        "atasan_id"    => $node->atasan_id ?? "",
    ], JSON_HEX_APOS | JSON_HEX_QUOT) !!}'
    onclick="openProfileModal(this)">

    @if ($fotoUrl)
        <img src="{{ $fotoUrl }}" alt="{{ $node->nama }}" class="org-card-photo object-cover">
    @else
        <div class="org-card-photo">{{ mb_strtoupper(mb_substr($node->nama, 0, 1)) }}</div>
    @endif

    <div class="min-w-0 flex-1">
        <h4 class="text-xs font-bold text-slate-900 truncate">{{ $node->nama }}</h4>
        <p class="text-[11px] text-slate-500 font-medium truncate mt-0.5">{{ $node->jabatan }}</p>
    </div>

    @if ($canManage)
        <div class="action-btn-group absolute right-2 top-2 hidden group-hover:flex items-center gap-1 z-30"
            onclick="event.stopPropagation(); event.stopImmediatePropagation();">
            <button type="button" onclick="openEditNodeModal(event, '{{ $cardId }}')" title="Edit Posisi"
                class="p-1.5 rounded-lg bg-white shadow-sm border border-slate-200 text-slate-600 hover:text-blue-600 hover:border-blue-400 hover:bg-blue-50 active:scale-95 transition cursor-pointer">
                <svg class="w-3.5 h-3.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
            </button>
        </div>
    @endif
</div>
