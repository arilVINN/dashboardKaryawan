@php
    $role = $role ?? strtolower(auth()->user()?->role?->nama_role ?? 'hrd');
    $canManage = $canManage ?? in_array($role, ['hrd', 'direktur', 'direksi', 'superadmin'], true);
    $sidebar = $sidebar ?? match ($role) {
        'kadiv' => 'component_kadiv.sidebar',
        'staff' => 'component.sidebar',
        'direktur', 'direksi', 'superadmin' => 'component_direktur.sidebar',
        default => 'component_hrd.sidebar',
    };
    $dashboardUrl = $dashboardUrl ?? match ($role) {
        'kadiv' => route('kadiv.dashboard'),
        'staff' => url('/'),
        'direktur', 'direksi', 'superadmin' => route('direktur.dashboard'),
        default => route('hrd.dashboard'),
    };
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Struktur Organisasi - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ======= Clean Modern Profile Card Styles ======= */
        .org-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.06);
            border: 1.5px solid #cbd5e1;
            padding: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .org-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(44, 74, 110, 0.12);
            border-color: #94a3b8;
        }

        /* Card Khusus Magang & Probation (Garis Putus-Putus) */
        .org-card.is-dashed {
            border: 2px dashed #170bf5 !important;
            background: #fffdf7 !important;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.08);
        }

        .org-card.is-dashed:hover {
            border-color: #170bf5 !important;
            box-shadow: 0 8px 24px rgba(217, 119, 6, 0.2) !important;
        }

        .org-card-photo {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            object-fit: cover;
            background: #e2e8f0;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #475569;
            font-size: 16px;
        }

        /* Connector Lines */
        .v-line {
            width: 1.5px;
            background: #475569;
        }

        .v-line.is-dashed {
            background: transparent !important;
            border-left: 2px dashed #170bf5 !important;
            width: 2px;
        }

        .h-line {
            height: 1.5px;
            background: #475569;
        }

        /* Division Header Badge */
        .org-division-header {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
            border: 1.5px solid #cbd5e1;
            border-top: 3px solid #02527a;
            padding: 8px 6px;
            text-align: center;
            font-weight: 700;
            font-size: 11px;
            letter-spacing: 0.04em;
            color: #0f172a;
            text-transform: uppercase;
            min-height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            transition: all 0.2s ease;
            position: relative;
        }

        .org-division-header:hover {
            border-color: #0284c7;
            box-shadow: 0 6px 16px rgba(2, 132, 199, 0.12);
        }

        /* Search highlighting */
        .org-card.highlight-search {
            border-color: #170bf5 !important;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.3), 0 8px 24px rgba(245, 158, 11, 0.2) !important;
            transform: scale(1.02);
        }

        .org-card.dimmed {
            opacity: 0.35;
            filter: grayscale(80%);
        }

        .canvas-drag-wrapper {
            user-select: none;
            cursor: grab;
            transform-origin: top center;
            transition: transform 0.15s ease-out;
        }

        .canvas-drag-wrapper:active {
            cursor: grabbing;
        }
    </style>
</head>

<body class="flex h-screen overflow-hidden bg-slate-50 text-slate-800">
    @include($sidebar)

    <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
        @include('component.topbar')

        <main class="flex-1 flex flex-col min-h-0 overflow-hidden p-6 md:p-8">
            <div class="w-full mb-3 shrink-0">
                @include('component.breadcrumbs', [
                    'parentText' => 'Dashboard',
                    'parentUrl' => $dashboardUrl,
                    'currentPage' => 'Struktur Organisasi',
                    'inline' => true,
                ])

                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mt-2">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="p-2 bg-sky-100 text-sky-700 rounded-xl">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                </svg>
                            </span>
                            <h1 class="text-xl md:text-2xl font-bold text-[#172B36]">Struktur Organisasi PT Silindo</h1>
                        </div>
                        <p class="text-xs md:text-sm text-slate-500 mt-1">
                            Bagan hierarki struktural PT Sinergi Ilmiah Indonesia.
                        </p>
                    </div>

                    @if ($canManage)
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" onclick="openAddNodeModal()"
                                class="px-4 py-2.5 bg-[#0284c7] hover:bg-sky-700 text-white text-xs md:text-sm font-semibold rounded-xl shadow-sm transition-all flex items-center gap-2 cursor-pointer active:scale-95">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                <span>Tambah Karyawan / Posisi</span>
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Toolbar Filter, Pencarian & Legend --}}
                <div class="mt-3 bg-white p-3 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-3">
                        {{-- Search Input --}}
                        <div class="relative w-52 sm:w-60">
                            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" id="searchInput" oninput="handleSearch(this.value)"
                                placeholder="Cari nama, jabatan, divisi..."
                                class="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-sky-500 text-slate-700">
                        </div>

                        {{-- Filter Divisi --}}
                        <div class="flex items-center gap-1.5 text-xs text-slate-600">
                            <label for="filterDivisiSelect" class="font-semibold text-slate-500">Divisi:</label>
                            <select id="filterDivisiSelect" onchange="handleFilterDivisi(this.value)"
                                class="rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500 bg-white">
                                <option value="all">Semua Divisi</option>
                                @foreach ($daftarDivisi as $divName)
                                    <option value="{{ $divName }}">{{ $divName }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Legenda Garis & Kontrol Zoom --}}
                    <div class="flex flex-wrap items-center gap-4 text-xs">
                        <div class="flex items-center gap-3 px-3 py-1 rounded-xl bg-slate-50 border border-slate-200">
                            <div class="flex items-center gap-1.5">
                                <div class="w-5 h-0.5 bg-[#475569]"></div>
                                <span class="text-[11px] font-medium text-slate-600">Tetap / Kontrak</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <div class="w-5 border-t-2 border-dashed border-amber-500"></div>
                                <span class="text-[11px] font-medium text-amber-700">Magang / Probation (┄┄)</span>
                            </div>
                        </div>

                        {{-- Zoom Buttons --}}
                        <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl">
                            <button type="button" onclick="zoomIn()" title="Perbesar (+)"
                                class="p-1 rounded-lg hover:bg-white text-slate-600 hover:text-slate-900 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" /></svg>
                            </button>
                            <button type="button" onclick="zoomReset()" title="Reset Zoom (100%)"
                                class="px-2 py-0.5 text-[11px] font-bold text-slate-600 hover:text-slate-900 rounded-lg hover:bg-white transition" id="zoomLevelText">
                                100%
                            </button>
                            <button type="button" onclick="zoomOut()" title="Perkecil (-)"
                                class="p-1 rounded-lg hover:bg-white text-slate-600 hover:text-slate-900 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kanvas Bagan Struktur Organisasi --}}
            <section aria-label="Bagan struktur organisasi" class="flex-1 overflow-auto bg-slate-100/70 border border-slate-200 rounded-2xl relative p-6">
                <div class="canvas-drag-wrapper min-w-[1250px] pb-16" id="canvasWrapper">
                    <div style="width: 1200px; margin: 0 auto; position: relative;" id="orgChartCanvas">

                        {{-- ============================================================ --}}
                        {{-- LEVEL 1: PENGAWAS PERUSAHAAN                                 --}}
                        {{-- ============================================================ --}}
                        @if ($pengawas)
                            <div class="flex justify-center">
                                <div class="org-card group {{ $pengawas->garis_putus ? 'is-dashed' : '' }}" style="width: 290px;"
                                    id="org-card-{{ $pengawas->id }}"
                                    data-id="{{ $pengawas->id }}"
                                    data-employee='{!! json_encode([
                                        "id"           => $pengawas->id,
                                        "nama"         => $pengawas->nama,
                                        "jabatan"      => $pengawas->jabatan,
                                        "divisi"       => $pengawas->divisi,
                                        "status_kerja" => $pengawas->status_kerja ?? "tetap",
                                        "garis_putus"  => (bool)$pengawas->garis_putus,
                                        "email"        => $pengawas->email ?? "-",
                                        "no_telepon"   => $pengawas->no_telepon ?? "-",
                                        "keterangan"   => $pengawas->keterangan ?? "-",
                                        "foto"         => $pengawas->foto ? asset("storage/" . $pengawas->foto) : ($pengawas->karyawan?->foto ? asset("storage/" . $pengawas->karyawan->foto) : ""),
                                        "parent_id"    => $pengawas->parent_id,
                                        "urutan"       => $pengawas->urutan,
                                    ]) !!}'
                                    onclick="openProfileModal(this)">
                                    
                                    {{-- Photo / Avatar --}}
                                    @if ($pengawas->foto || $pengawas->karyawan?->foto)
                                        <img src="{{ asset('storage/' . ($pengawas->foto ?? $pengawas->karyawan?->foto)) }}"
                                            alt="{{ $pengawas->nama }}" class="org-card-photo object-cover">
                                    @else
                                        <div class="org-card-photo">{{ mb_strtoupper(mb_substr($pengawas->nama, 0, 1)) }}</div>
                                    @endif

                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-xs font-bold text-slate-900 truncate">{{ $pengawas->nama }}</h4>
                                        <p class="text-[11px] text-slate-500 font-medium truncate mt-0.5">{{ $pengawas->jabatan }}</p>
                                    </div>

                                    @if ($canManage)
                                        <div class="action-btn-group absolute right-2 top-2 hidden group-hover:flex items-center gap-1 z-30" onclick="event.stopPropagation(); event.stopImmediatePropagation();">
                                            <button type="button" onclick="openEditNodeModal(event, {{ $pengawas->id }})" title="Edit Posisi"
                                                class="p-1.5 rounded-lg bg-white shadow-sm border border-slate-200 text-slate-600 hover:text-blue-600 hover:border-blue-400 hover:bg-blue-50 active:scale-95 transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Garis L1 -> L2 --}}
                            <div class="v-line mx-auto {{ $dirUtama?->garis_putus ? 'is-dashed' : '' }}" style="height: 24px;"></div>
                        @endif

                        {{-- ============================================================ --}}
                        {{-- LEVEL 2: DIREKTUR UTAMA                                      --}}
                        {{-- ============================================================ --}}
                        @if ($dirUtama)
                            <div class="flex justify-center">
                                <div class="org-card group {{ $dirUtama->garis_putus ? 'is-dashed' : '' }}" style="width: 310px;"
                                    id="org-card-{{ $dirUtama->id }}"
                                    data-id="{{ $dirUtama->id }}"
                                    data-employee='{!! json_encode([
                                        "id"           => $dirUtama->id,
                                        "nama"         => $dirUtama->nama,
                                        "jabatan"      => $dirUtama->jabatan,
                                        "divisi"       => $dirUtama->divisi,
                                        "status_kerja" => $dirUtama->status_kerja ?? "tetap",
                                        "garis_putus"  => (bool)$dirUtama->garis_putus,
                                        "email"        => $dirUtama->email ?? "-",
                                        "no_telepon"   => $dirUtama->no_telepon ?? "-",
                                        "keterangan"   => $dirUtama->keterangan ?? "-",
                                        "foto"         => $dirUtama->foto ? asset("storage/" . $dirUtama->foto) : ($dirUtama->karyawan?->foto ? asset("storage/" . $dirUtama->karyawan->foto) : ""),
                                        "parent_id"    => $dirUtama->parent_id,
                                        "urutan"       => $dirUtama->urutan,
                                    ]) !!}'
                                    onclick="openProfileModal(this)">
                                    
                                    @if ($dirUtama->foto || $dirUtama->karyawan?->foto)
                                        <img src="{{ asset('storage/' . ($dirUtama->foto ?? $dirUtama->karyawan?->foto)) }}"
                                            alt="{{ $dirUtama->nama }}" class="org-card-photo object-cover">
                                    @else
                                        <div class="org-card-photo">{{ mb_strtoupper(mb_substr($dirUtama->nama, 0, 1)) }}</div>
                                    @endif

                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-xs font-bold text-slate-900 truncate">{{ $dirUtama->nama }}</h4>
                                        <p class="text-[11px] text-slate-500 font-medium truncate mt-0.5">{{ $dirUtama->jabatan }}</p>
                                    </div>

                                    @if ($canManage)
                                        <div class="action-btn-group absolute right-2 top-2 hidden group-hover:flex items-center gap-1 z-30" onclick="event.stopPropagation(); event.stopImmediatePropagation();">
                                            <button type="button" onclick="openEditNodeModal(event, {{ $dirUtama->id }})" title="Edit Posisi"
                                                class="p-1.5 rounded-lg bg-white shadow-sm border border-slate-200 text-slate-600 hover:text-blue-600 hover:border-blue-400 hover:bg-blue-50 active:scale-95 transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- ============================================================ --}}
                        {{-- KANVAS KONEKTOR UTAMA & DIREKTUR OPERASIONAL                 --}}
                        {{-- ============================================================ --}}
                        <div style="position: relative; width: 1200px; height: 250px;">
                            {{-- Main vertical line continues from director down to department branch --}}
                            <div class="v-line" style="position: absolute; left: 600px; top: 0; height: 250px;"></div>

                            {{-- Kotak Direktur Operasional (Menimpa garis tengah) --}}
                            @if ($dirOps)
                                <div style="position: absolute; left: 470px; top: 44px; width: 260px; z-index: 2;">
                                    <div class="org-card group {{ $dirOps->garis_putus ? 'is-dashed' : '' }}"
                                        id="org-card-{{ $dirOps->id }}"
                                        data-id="{{ $dirOps->id }}"
                                        data-employee='{!! json_encode([
                                            "id"           => $dirOps->id,
                                            "nama"         => $dirOps->nama,
                                            "jabatan"      => $dirOps->jabatan,
                                            "divisi"       => $dirOps->divisi,
                                            "status_kerja" => $dirOps->status_kerja ?? "tetap",
                                            "garis_putus"  => (bool)$dirOps->garis_putus,
                                            "email"        => $dirOps->email ?? "-",
                                            "no_telepon"   => $dirOps->no_telepon ?? "-",
                                            "keterangan"   => $dirOps->keterangan ?? "-",
                                            "foto"         => $dirOps->foto ? asset("storage/" . $dirOps->foto) : ($dirOps->karyawan?->foto ? asset("storage/" . $dirOps->karyawan->foto) : ""),
                                            "parent_id"    => $dirOps->parent_id,
                                            "urutan"       => $dirOps->urutan,
                                        ]) !!}'
                                        onclick="openProfileModal(this)">
                                        
                                        @if ($dirOps->foto || $dirOps->karyawan?->foto)
                                            <img src="{{ asset('storage/' . ($dirOps->foto ?? $dirOps->karyawan?->foto)) }}"
                                                alt="{{ $dirOps->nama }}" class="org-card-photo object-cover" style="width: 44px; height: 44px;">
                                        @else
                                            <div class="org-card-photo" style="width: 44px; height: 44px; font-size: 15px;">{{ mb_strtoupper(mb_substr($dirOps->nama, 0, 1)) }}</div>
                                        @endif

                                        <div class="min-w-0 flex-1">
                                            <h4 class="text-xs font-bold text-slate-900 truncate">{{ $dirOps->nama }}</h4>
                                            <p class="text-[11px] text-slate-500 font-medium truncate mt-0.5">{{ $dirOps->jabatan }}</p>
                                        </div>

                                        @if ($canManage)
                                            <div class="action-btn-group absolute right-2 top-2 hidden group-hover:flex items-center gap-1 z-30" onclick="event.stopPropagation(); event.stopImmediatePropagation();">
                                                <button type="button" onclick="openEditNodeModal(event, {{ $dirOps->id }})" title="Edit Posisi"
                                                    class="p-1.5 rounded-lg bg-white shadow-sm border border-slate-200 text-slate-600 hover:text-blue-600 hover:border-blue-400 hover:bg-blue-50 active:scale-95 transition cursor-pointer">
                                                    <svg class="w-3.5 h-3.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            {{-- Director branches: Legal, PA, Writer --}}
                            <div class="h-line" style="position: absolute; left: 100px; width: 500px; top: 24px;"></div>
                            <div class="v-line" style="position: absolute; left: 100px; top: 24px; height: 192px;"></div>
                            <div class="v-line" style="position: absolute; left: 260px; top: 24px; height: 192px;"></div>
                            <div class="v-line" style="position: absolute; left: 420px; top: 24px; height: 192px;"></div>

                            {{-- Operations branches: Finance & Accounting and HR & GA --}}
                            <div class="h-line" style="position: absolute; left: 600px; width: 440px; top: 130px;"></div>
                            <div class="v-line" style="position: absolute; left: 820px; top: 130px; height: 86px;"></div>
                            <div class="v-line" style="position: absolute; left: 1040px; top: 130px; height: 86px;"></div>
                        </div>

                        {{-- ============================================================ --}}
                        {{-- LEVEL PENDUKUNG: LEGAL, PA, WRITER, FINANCE, & HR (Sejajar)  --}}
                        {{-- ============================================================ --}}
                        <div id="supportLevel" style="position: relative; width: 1200px; min-height: 190px; margin-top: -34px;">
                            <div class="v-line" style="position: absolute; left: 600px; top: 0; height: 100%;"></div>

                            {{-- 1. LEGAL --}}
                            <div class="flex flex-col items-center" style="position: absolute; left: 25px; top: 0; width: 150px;">
                                <div class="org-division-header group" style="min-height: 42px; padding: 6px;">
                                    <span>Legal</span>
                                    @if ($canManage)
                                        <button type="button" onclick="openAddNodeModalWithDivision('Legal & Compliance')" title="Tambah staf ke Legal"
                                            class="absolute right-1 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 p-1 text-sky-600 hover:bg-sky-50 rounded transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                        </button>
                                    @endif
                                </div>
                                <div class="v-line" style="height: 12px;"></div>
                                <div class="flex flex-col items-center w-full">
                                    @foreach ($legalMembers as $index => $member)
                                        @if ($index > 0)
                                            <div class="v-line {{ $member->garis_putus ? 'is-dashed' : '' }}" style="height: 10px;"></div>
                                        @endif
                                        @include('hrd.partials.org_card_item', ['member' => $member, 'canManage' => $canManage])
                                    @endforeach
                                </div>
                            </div>

                            {{-- 2. PERSONAL ASSISTANT --}}
                            <div class="flex flex-col items-center" style="position: absolute; left: 185px; top: 0; width: 150px;">
                                <div class="org-division-header group" style="min-height: 42px; padding: 6px;">
                                    <span>Personal Asst.</span>
                                    @if ($canManage)
                                        <button type="button" onclick="openAddNodeModalWithDivision('Direksi')" title="Tambah staf ke Personal Assistant"
                                            class="absolute right-1 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 p-1 text-sky-600 hover:bg-sky-50 rounded transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                        </button>
                                    @endif
                                </div>
                                <div class="v-line" style="height: 12px;"></div>
                                <div class="flex flex-col items-center w-full">
                                    @foreach ($paMembers as $index => $member)
                                        @if ($index > 0)
                                            <div class="v-line {{ $member->garis_putus ? 'is-dashed' : '' }}" style="height: 10px;"></div>
                                        @endif
                                        @include('hrd.partials.org_card_item', ['member' => $member, 'canManage' => $canManage])
                                    @endforeach
                                </div>
                            </div>

                            {{-- 3. WRITER --}}
                            <div class="flex flex-col items-center" style="position: absolute; left: 345px; top: 0; width: 150px;">
                                <div class="org-division-header group" style="min-height: 42px; padding: 6px;">
                                    <span>Writer</span>
                                    @if ($canManage)
                                        <button type="button" onclick="openAddNodeModalWithDivision('Writer')" title="Tambah staf ke Writer"
                                            class="absolute right-1 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 p-1 text-sky-600 hover:bg-sky-50 rounded transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                        </button>
                                    @endif
                                </div>
                                <div class="v-line" style="height: 12px;"></div>
                                <div class="flex flex-col items-center w-full">
                                    @foreach ($writerMembers as $index => $member)
                                        @if ($index > 0)
                                            <div class="v-line {{ $member->garis_putus ? 'is-dashed' : '' }}" style="height: 10px;"></div>
                                        @endif
                                        @include('hrd.partials.org_card_item', ['member' => $member, 'canManage' => $canManage])
                                    @endforeach
                                </div>
                            </div>

                            {{-- 4. FINANCE & ACCOUNTING --}}
                            <div class="flex flex-col items-center" style="position: absolute; left: 735px; top: 0; width: 170px;">
                                <div class="org-division-header group" style="min-height: 42px; padding: 6px;">
                                    <span>Finance</span>
                                    @if ($canManage)
                                        <button type="button" onclick="openAddNodeModalWithDivision('Finance & Accounting')" title="Tambah staf ke Finance"
                                            class="absolute right-1 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 p-1 text-sky-600 hover:bg-sky-50 rounded transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                        </button>
                                    @endif
                                </div>
                                <div class="v-line" style="height: 12px;"></div>
                                <div class="flex flex-col items-center w-full">
                                    @foreach ($financeMembers as $index => $member)
                                        @if ($index > 0)
                                            <div class="v-line {{ $member->garis_putus ? 'is-dashed' : '' }}" style="height: 10px;"></div>
                                        @endif
                                        @include('hrd.partials.org_card_item', ['member' => $member, 'canManage' => $canManage])
                                    @endforeach
                                </div>
                            </div>

                            {{-- 5. HR & GA --}}
                            <div class="flex flex-col items-center" style="position: absolute; left: 955px; top: 0; width: 170px;">
                                <div class="org-division-header group" style="min-height: 42px; padding: 6px;">
                                    <span>HR & GA</span>
                                    @if ($canManage)
                                        <button type="button" onclick="openAddNodeModalWithDivision('HR & GA')" title="Tambah staf ke HR & GA"
                                            class="absolute right-1 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 p-1 text-sky-600 hover:bg-sky-50 rounded transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                        </button>
                                    @endif
                                </div>
                                <div class="v-line" style="height: 12px;"></div>
                                <div class="flex flex-col items-center w-full">
                                    @foreach ($hrMembers as $index => $member)
                                        @if ($index > 0)
                                            <div class="v-line {{ $member->garis_putus ? 'is-dashed' : '' }}" style="height: 10px;"></div>
                                        @endif
                                        @include('hrd.partials.org_card_item', ['member' => $member, 'canManage' => $canManage])
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- ============================================================ --}}
                        {{-- GARIS T LEVEL 4: DEPARTEMEN OPERASIONAL (Otomatis & Presisi) --}}
                        {{-- ============================================================ --}}
                        @php
                            $deptCount = count($deptGroups);
                            $colW = 180;
                            $gap = 16;
                            $totalDeptWidth = ($deptCount * $colW) + (($deptCount - 1) * $gap);
                            $canvasWidth = max(1200, $totalDeptWidth);
                            $deptOffsetLeft = max(0, ($canvasWidth - $totalDeptWidth) / 2);

                            $deptCenters = [];
                            for ($i = 0; $i < $deptCount; $i++) {
                                $deptCenters[] = round($deptOffsetLeft + ($i * ($colW + $gap)) + ($colW / 2));
                            }
                            $hLeft = $deptCenters[0] ?? 110;
                            $hWidth = (end($deptCenters) ?? 1090) - $hLeft;
                        @endphp
                        <div style="position: relative; width: {{ $canvasWidth }}px; height: 32px; margin-top: 15px;">
                            {{-- Garis horizontal yang menghubungkan seluruh departemen --}}
                            <div class="h-line" style="position: absolute; left: {{ $hLeft }}px; width: {{ $hWidth }}px; top: 0;"></div>
                            {{-- Garis vertikal turun tepat ke atas masing-masing header departemen --}}
                            @foreach ($deptCenters as $colX)
                                <div class="v-line" style="position: absolute; left: {{ $colX }}px; top: 0; height: 32px;"></div>
                            @endforeach
                        </div>

                        {{-- ============================================================ --}}
                        {{-- LEVEL 4: KOTAK DEPARTEMEN DENGAN JUDUL DIVISI DI ATASNYA    --}}
                        {{-- (Setiap divisi otomatis memiliki header di atas dan staf di bawahnya) --}}
                        {{-- ============================================================ --}}
                        <div id="deptGrid" style="display: grid; grid-template-columns: repeat({{ $deptCount }}, 180px); justify-content: center; gap: 16px; width: {{ $canvasWidth }}px;">
                            @foreach ($deptGroups as $deptName => $members)
                                <div class="flex flex-col items-center w-full dept-column" data-dept="{{ $deptName }}">
                                    {{-- HEADER DIVISI --}}
                                    <div class="org-division-header group">
                                        <span>{{ $deptName }}</span>
                                        @if ($canManage)
                                            <button type="button" onclick="openAddNodeModalWithDivision('{{ addslashes($deptName) }}')" title="Tambah staf ke divisi {{ $deptName }}"
                                                class="absolute right-1 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 p-1 text-sky-600 hover:bg-sky-50 rounded transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                            </button>
                                        @endif
                                    </div>

                                    <div class="v-line" style="height: 12px;"></div>

                                    {{-- STAF-STAF DI BAWAH DIVISI TERSEBUT --}}
                                    <div class="flex flex-col items-center w-full">
                                        @forelse ($members as $index => $member)
                                            @if ($index > 0)
                                                <div class="v-line {{ $member->garis_putus ? 'is-dashed' : '' }}" style="height: 10px;"></div>
                                            @endif
                                            @include('hrd.partials.org_card_item', ['member' => $member, 'canManage' => $canManage])
                                        @empty
                                            <div class="p-3 text-center border border-dashed border-slate-200 rounded-xl bg-white/60 w-full">
                                                <p class="text-[10px] text-slate-400">Belum ada anggota</p>
                                                @if ($canManage)
                                                    <button type="button" onclick="openAddNodeModalWithDivision('{{ addslashes($deptName) }}')"
                                                        class="mt-1 text-[10px] font-bold text-sky-600 hover:underline">
                                                        + Tambah Staf
                                                    </button>
                                                @endif
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>
            </section>
        </main>
    </div>

    {{-- ============================================================== --}}
    {{-- MODAL DETAIL PROFIL KARYAWAN (TAMPILAN ASLI SEPERTI AWAL)       --}}
    {{-- ============================================================== --}}
    <div id="employee-profile-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-[rgba(15,40,55,.45)] p-4"
        role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="employee-profile-title">

        <div class="relative w-full max-w-2xl overflow-hidden rounded-3xl bg-white shadow-2xl">
            <button type="button" onclick="closeProfileModal()" aria-label="Tutup profil"
                class="absolute right-4 top-4 z-10 flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 cursor-pointer">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 6 12 12M18 6 6 18" />
                </svg>
            </button>

            <div class="px-7 pb-5 pt-7 sm:px-9">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-sky-700">Struktur Organisasi PT Silindo</p>
                <h3 id="employee-profile-title" class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Profil Anggota</h3>
            </div>

            <div class="grid gap-6 px-7 pb-8 sm:px-9 md:grid-cols-[176px_minmax(0,1fr)]">
                <div class="mx-auto h-44 w-40 overflow-hidden rounded-2xl bg-slate-100 ring-1 ring-slate-200 md:mx-0 flex items-center justify-center">
                    <img id="employee-profile-photo" alt="" class="hidden h-full w-full object-cover">
                    <div id="employee-profile-initial"
                        class="flex h-full w-full items-center justify-center bg-gradient-to-br from-sky-100 to-indigo-100 text-5xl font-bold text-sky-800">
                        U
                    </div>
                </div>

                <dl class="divide-y divide-slate-100 text-sm">
                    <div class="grid grid-cols-[minmax(100px,0.8fr)_minmax(0,1.2fr)] items-center gap-4 py-2.5">
                        <dt class="text-slate-500">Nama Lengkap</dt>
                        <dd id="employee-profile-name" class="break-words text-right font-bold text-slate-900"></dd>
                    </div>
                    <div class="grid grid-cols-[minmax(100px,0.8fr)_minmax(0,1.2fr)] items-center gap-4 py-2.5">
                        <dt class="text-slate-500">Jabatan / Posisi</dt>
                        <dd id="employee-profile-position" class="break-words text-right font-bold text-slate-900"></dd>
                    </div>
                    <div class="grid grid-cols-[minmax(100px,0.8fr)_minmax(0,1.2fr)] items-center gap-4 py-2.5">
                        <dt class="text-slate-500">Divisi</dt>
                        <dd id="employee-profile-division" class="break-words text-right font-bold text-slate-900"></dd>
                    </div>
                    <div class="grid grid-cols-[minmax(100px,0.8fr)_minmax(0,1.2fr)] items-center gap-4 py-2.5">
                        <dt class="text-slate-500">Status Kepegawaian</dt>
                        <dd id="employee-profile-status" class="break-words text-right font-bold text-slate-900"></dd>
                    </div>
                    <div class="grid grid-cols-[minmax(100px,0.8fr)_minmax(0,1.2fr)] items-center gap-4 py-2.5">
                        <dt class="text-slate-500">Email</dt>
                        <dd id="employee-profile-email" class="break-words text-right font-bold text-slate-900"></dd>
                    </div>
                    <div class="grid grid-cols-[minmax(100px,0.8fr)_minmax(0,1.2fr)] items-center gap-4 py-2.5">
                        <dt class="text-slate-500">No. Telepon</dt>
                        <dd id="employee-profile-phone" class="break-words text-right font-bold text-slate-900"></dd>
                    </div>
                    <div class="grid grid-cols-[minmax(100px,0.8fr)_minmax(0,1.2fr)] items-center gap-4 py-2.5">
                        <dt class="text-slate-500">Keterangan / Tugas</dt>
                        <dd id="employee-profile-desc" class="break-words text-right text-xs text-slate-700"></dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    @if ($canManage)
        {{-- ============================================================== --}}
        {{-- MODAL TAMBAH ANGGOTA / KARYAWAN (SEDERHANA & CEPAT)             --}}
        {{-- ============================================================== --}}
        <div id="modalAddNode" class="modal-overlay-in fixed inset-0 z-50 flex items-center justify-center bg-[rgba(15,40,55,.45)] hidden p-4">
            <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden border border-slate-100 flex flex-col max-h-[90vh]">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="p-1.5 bg-sky-100 text-sky-700 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Tambah Anggota / Posisi Baru</h3>
                            <p class="text-[11px] text-slate-400">Pilih divisi, anggota otomatis berada di bawah header divisinya.</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeModal('modalAddNode')" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="formAddNode" onsubmit="submitAddNode(event)" enctype="multipart/form-data" class="p-6 space-y-4 overflow-y-auto flex-1">
                    @csrf

                    {{-- Quick Fill dari Karyawan Terdaftar (Opsional) --}}
                    @if ($daftarKaryawan->isNotEmpty())
                        <div class="p-3 bg-sky-50/70 border border-sky-200 rounded-xl">
                            <label class="block text-xs font-bold text-sky-900 mb-1">
                                Ambil Data Karyawan Terdaftar (Opsional)
                            </label>
                            <select id="add_select_karyawan" onchange="onSelectKaryawan('add', this)"
                                class="w-full rounded-xl border border-sky-300 px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500 bg-white">
                                <option value="">-- Ketik / Pilih data karyawan untuk isi otomatis --</option>
                                @foreach ($daftarKaryawan as $k)
                                    <option value="{{ $k->id_karyawan }}"
                                        data-nama="{{ $k->nama }}"
                                        data-jabatan="{{ $k->jabatan }}"
                                        data-divisi="{{ $k->divisi?->nama_divisi }}"
                                        data-email="{{ $k->email }}"
                                        data-foto="{{ $k->foto }}">
                                        {{ $k->nama }} - {{ $k->jabatan ?? 'Karyawan' }} ({{ $k->divisi?->nama_divisi ?? 'Tanpa Divisi' }})
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="id_karyawan" id="add_id_karyawan">
                        </div>
                    @endif

                    {{-- Nama Lengkap --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama" id="add_nama" required placeholder="Contoh: Budi Santoso, S.Kom."
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>

                    {{-- Jabatan --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Jabatan / Posisi <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="jabatan" id="add_jabatan" required placeholder="Contoh: Staff Keuangan / Desain Grafis"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>

                    {{-- Divisi (Tinggal dipanggil, otomatis diatasnya header divisi) --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Divisi / Departemen <span class="text-rose-500">*</span>
                        </label>
                        <select id="add_divisi_select" onchange="handleDivisiSelectChange('add', this.value)"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 bg-white">
                            @foreach ($daftarDivisi as $d)
                                <option value="{{ $d }}">{{ $d }}</option>
                            @endforeach
                            <option value="__NEW__">+ Ketik Divisi Baru Lainnya...</option>
                        </select>
                        <input type="text" name="divisi" id="add_divisi" value="{{ $daftarDivisi->first() ?? 'IT' }}"
                            class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 hidden"
                            placeholder="Ketik nama divisi baru...">
                        <p class="text-[11px] text-slate-400 mt-1">
                            * Anggota otomatis tampil di bagan di bawah header divisi yang dipilih.
                        </p>
                    </div>

                    {{-- Status Kerja & Garis Putus-Putus (Magang / Probation) --}}
                    <div class="p-3 bg-blue-50/60 border border-blue-200 rounded-xl space-y-2">
                        <label class="block text-xs font-bold text-blue-900 uppercase">
                            Status Kerja & Hubungan Struktural
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                            <label class="flex items-center gap-1.5 p-2 rounded-lg bg-white border border-blue-200 cursor-pointer">
                                <input type="radio" name="status_kerja" value="tetap" checked onchange="handleStatusChange('add', this.value)" class="text-blue-600">
                                <span>Tetap</span>
                            </label>
                            <label class="flex items-center gap-1.5 p-2 rounded-lg bg-white border border-blue-200 cursor-pointer">
                                <input type="radio" name="status_kerja" value="probation" onchange="handleStatusChange('add', this.value)" class="text-blue-600">
                                <span class="text-purple-700 font-semibold">Probation</span>
                            </label>
                            <label class="flex items-center gap-1.5 p-2 rounded-lg bg-white border border-blue-200 cursor-pointer">
                                <input type="radio" name="status_kerja" value="magang" onchange="handleStatusChange('add', this.value)" class="text-blue-600">
                                <span class="text-amber-700 font-semibold">Magang</span>
                            </label>
                            <label class="flex items-center gap-1.5 p-2 rounded-lg bg-white border border-blue-200 cursor-pointer">
                                <input type="radio" name="status_kerja" value="kontrak" onchange="handleStatusChange('add', this.value)" class="text-blue-600">
                                <span>Kontrak</span>
                            </label>
                        </div>

                        <label class="flex items-center gap-2 pt-1 cursor-pointer">
                            <input type="checkbox" name="garis_putus" id="add_garis_putus" value="1" class="rounded text-blue-600 focus:ring-blue-500">
                            <span class="text-xs font-bold text-blue-900">
                                Gunakan garis / border putus-putus (┄┄)
                            </span>
                        </label>
                        <p class="text-[11px] text-blue-700">
                            * Magang & Probation otomatis menggunakan border dan garis putus-putus pada bagan.
                        </p>
                    </div>

                    {{-- Dia Dibawah Siapa (Atasan Langsung) --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Dia Dibawah Siapa? (Atasan Langsung)
                            <span class="ml-1 text-[11px] font-normal text-slate-400 normal-case">(Opsional - otomatis ke atasan divisi)</span>
                        </label>
                        <select name="parent_id" id="add_parent_id"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500 bg-white">
                            <option value="">-- [ Otomatis Sesuai Divisi / Direktur Operasional ] --</option>
                            @foreach ($allNodes as $n)
                                <option value="{{ $n->id }}">{{ $n->nama }} - {{ $n->jabatan }} ({{ $n->divisi }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Urutan Tampil --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Urutan Tampil (Sort Order)
                        </label>
                        <input type="number" name="urutan" id="add_urutan" min="1" value="1"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>

                    {{-- Email & No Telepon --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email (Opsional)</label>
                            <input type="email" name="email" id="add_email" placeholder="nama@silindo.co.id"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. Telepon (Opsional)</label>
                            <input type="text" name="no_telepon" id="add_no_telepon" placeholder="08xxxxxxxxxx"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                        </div>
                    </div>

                    {{-- Foto Profil --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Foto Profil (Opsional · Maks. 3 MB)</label>
                        <input type="file" name="foto" accept="image/jpeg,image/png,image/webp"
                            class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 cursor-pointer">
                    </div>

                    {{-- Keterangan / Tugas --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Keterangan / Tanggung Jawab Singkat</label>
                        <textarea name="keterangan" rows="2" placeholder="Tanggung jawab dan uraian tugas pokok posisi ini..."
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button" onclick="closeModal('modalAddNode')"
                            class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-semibold rounded-xl transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" id="btnSubmitAdd"
                            class="px-5 py-2 bg-[#0284c7] hover:bg-sky-700 text-white text-xs font-semibold rounded-xl transition shadow cursor-pointer">
                            Simpan Karyawan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- MODAL EDIT ANGGOTA / KARYAWAN                                  --}}
        {{-- ============================================================== --}}
        <div id="modalEditNode" class="modal-overlay-in fixed inset-0 z-50 flex items-center justify-center bg-[rgba(15,40,55,.45)] hidden p-4">
            <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden border border-slate-100 flex flex-col max-h-[90vh]">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="p-1.5 bg-blue-100 text-blue-700 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                        </span>
                        <h3 class="text-base font-bold text-slate-800">Edit Data Anggota Organisasi</h3>
                    </div>
                    <button type="button" onclick="closeModal('modalEditNode')" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="formEditNode" onsubmit="submitEditNode(event)" enctype="multipart/form-data" class="p-6 space-y-4 overflow-y-auto flex-1">
                    @csrf
                    <input type="hidden" name="node_id" id="edit_node_id">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama" id="edit_nama" required
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Jabatan / Posisi <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="jabatan" id="edit_jabatan" required
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Divisi / Departemen <span class="text-rose-500">*</span>
                        </label>
                        <select id="edit_divisi_select" onchange="handleDivisiSelectChange('edit', this.value)"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 bg-white">
                            @foreach ($daftarDivisi as $d)
                                <option value="{{ $d }}">{{ $d }}</option>
                            @endforeach
                            <option value="__NEW__">+ Ketik Divisi Baru...</option>
                        </select>
                        <input type="text" name="divisi" id="edit_divisi"
                            class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 hidden">
                    </div>

                    {{-- Status Kerja & Garis Putus --}}
                    <div class="p-3 bg-blue-50/60 border border-blue-200 rounded-xl space-y-2">
                        <label class="block text-xs font-bold text-white uppercase">
                            Status Kerja
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                            <label class="flex items-center gap-1.5 p-2 rounded-lg bg-white border border-blue-200 cursor-pointer">
                                <input type="radio" name="status_kerja" id="edit_status_tetap" value="tetap" onchange="handleStatusChange('edit', this.value)" class="text-amber-600">
                                <span>Tetap</span>
                            </label>
                            <label class="flex items-center gap-1.5 p-2 rounded-lg bg-white border border-blue-200 cursor-pointer">
                                <input type="radio" name="status_kerja" id="edit_status_probation" value="probation" onchange="handleStatusChange('edit', this.value)" class="text-amber-600">
                                <span class="text-purple-700 font-semibold">Probation</span>
                            </label>
                            <label class="flex items-center gap-1.5 p-2 rounded-lg bg-white border border-blue-200 cursor-pointer">
                                <input type="radio" name="status_kerja" id="edit_status_magang" value="magang" onchange="handleStatusChange('edit', this.value)" class="text-amber-600">
                                <span class="text-amber-700 font-semibold">Magang</span>
                            </label>
                            <label class="flex items-center gap-1.5 p-2 rounded-lg bg-white border border-blue-200 cursor-pointer">
                                <input type="radio" name="status_kerja" id="edit_status_kontrak" value="kontrak" onchange="handleStatusChange('edit', this.value)" class="text-amber-600">
                                <span>Kontrak</span>
                            </label>
                        </div>

                        <label class="flex items-center gap-2 pt-1 cursor-pointer">
                            <input type="checkbox" name="garis_putus" id="edit_garis_putus" value="1" class="rounded text-amber-600 focus:ring-amber-500">
                            <span class="text-xs font-bold text-blue-900">
                                Gunakan garis / border putus-putus (┄┄)
                            </span>
                        </label>
                    </div>

                    {{-- Pindah Atasan --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Dia Dibawah Siapa? (Atasan Langsung)
                        </label>
                        <select name="parent_id" id="edit_parent_id"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500 bg-white">
                            <option value="">-- [ Tanpa Atasan / Pimpinan Tertinggi ] --</option>
                            @foreach ($allNodes as $n)
                                <option value="{{ $n->id }}">{{ $n->nama }} - {{ $n->jabatan }} ({{ $n->divisi }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Urutan Tampil (Sort Order)</label>
                        <input type="number" name="urutan" id="edit_urutan" min="1"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email</label>
                            <input type="email" name="email" id="edit_email"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. Telepon</label>
                            <input type="text" name="no_telepon" id="edit_no_telepon"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ganti Foto Profil</label>
                        <input type="file" name="foto" accept="image/jpeg,image/png,image/webp"
                            class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 cursor-pointer">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Keterangan / Tanggung Jawab</label>
                        <textarea name="keterangan" id="edit_keterangan" rows="2"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button" onclick="closeModal('modalEditNode')"
                            class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-semibold rounded-xl transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" id="btnSubmitEdit"
                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl transition shadow cursor-pointer">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <script>
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        let currentZoom = 1;

        // Modal Helpers
        function openModal(modalId) {
            const m = document.getElementById(modalId);
            if (!m) return;
            m.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeModal(modalId) {
            const m = document.getElementById(modalId);
            if (!m) return;
            m.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // =========================================================================
        // DETAIL PROFIL KARYAWAN (POPUP ASLI)
        // =========================================================================
        function openProfileModal(card) {
            let employee;
            try {
                employee = JSON.parse(card.dataset.employee);
            } catch (error) {
                console.error('Gagal membaca data profil:', error);
                return;
            }

            const modal = document.getElementById('employee-profile-modal');
            const photo = document.getElementById('employee-profile-photo');
            const initial = document.getElementById('employee-profile-initial');

            document.getElementById('employee-profile-name').textContent = employee.nama || '-';
            document.getElementById('employee-profile-position').textContent = employee.jabatan || '-';
            document.getElementById('employee-profile-division').textContent = employee.divisi || '-';
            
            const statusBadge = (employee.status_kerja || 'tetap').toUpperCase();
            document.getElementById('employee-profile-status').textContent = statusBadge + (employee.garis_putus ? ' (Garis Putus-Putus)' : '');
            
            document.getElementById('employee-profile-email').textContent = employee.email || '-';
            document.getElementById('employee-profile-phone').textContent = employee.no_telepon || '-';
            document.getElementById('employee-profile-desc').textContent = employee.keterangan || '-';

            initial.textContent = (employee.nama || 'U').trim().charAt(0).toUpperCase();

            if (employee.foto) {
                photo.onerror = () => {
                    photo.classList.add('hidden');
                    initial.classList.remove('hidden');
                };
                photo.src = employee.foto;
                photo.alt = 'Foto ' + employee.nama;
                photo.classList.remove('hidden');
                initial.classList.add('hidden');
            } else {
                photo.removeAttribute('src');
                photo.classList.add('hidden');
                initial.classList.remove('hidden');
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeProfileModal() {
            const modal = document.getElementById('employee-profile-modal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }

        // Binding click untuk tutup modal via backdrop & keyboard
        document.addEventListener('DOMContentLoaded', function() {
            // Close modal jika klik di area backdrop hitam luar
            ['employee-profile-modal', 'modalAddNode', 'modalEditNode'].forEach((modalId) => {
                const modalEl = document.getElementById(modalId);
                if (modalEl) {
                    modalEl.addEventListener('click', (e) => {
                        if (e.target === modalEl) {
                            if (modalId === 'employee-profile-modal') closeProfileModal();
                            else closeModal(modalId);
                        }
                    });
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeProfileModal();
                    closeModal('modalAddNode');
                    closeModal('modalEditNode');
                }
            });
        });

        // =========================================================================
        // MODAL ADD KARYAWAN & DIVISI AUTOFILL
        // =========================================================================
        function openAddNodeModal() {
            closeProfileModal();
            closeModal('modalEditNode');

            const form = document.getElementById('formAddNode');
            if (form) form.reset();
            const selectDiv = document.getElementById('add_divisi_select');
            const inputDiv = document.getElementById('add_divisi');
            if (selectDiv && inputDiv) {
                inputDiv.value = selectDiv.value;
                inputDiv.classList.add('hidden');
            }
            const garisCheck = document.getElementById('add_garis_putus');
            if (garisCheck) garisCheck.checked = false;

            openModal('modalAddNode');
        }

        function openAddNodeModalWithDivision(divName) {
            openAddNodeModal();
            const selectDiv = document.getElementById('add_divisi_select');
            const inputDiv = document.getElementById('add_divisi');
            if (selectDiv && inputDiv) {
                let found = false;
                for (let i = 0; i < selectDiv.options.length; i++) {
                    if (selectDiv.options[i].value.toLowerCase() === divName.toLowerCase()) {
                        selectDiv.selectedIndex = i;
                        inputDiv.value = selectDiv.options[i].value;
                        found = true;
                        break;
                    }
                }
                if (!found) {
                    selectDiv.value = '__NEW__';
                    inputDiv.classList.remove('hidden');
                    inputDiv.value = divName;
                }
            }
        }

        function handleDivisiSelectChange(formType, val) {
            const input = document.getElementById(formType + '_divisi');
            if (val === '__NEW__') {
                input.classList.remove('hidden');
                input.value = '';
                input.focus();
            } else {
                input.classList.add('hidden');
                input.value = val;
            }
        }

        function onSelectKaryawan(formType, selectEl) {
            const opt = selectEl.options[selectEl.selectedIndex];
            if (!opt || !opt.value) return;

            document.getElementById(formType + '_id_karyawan').value = opt.value;
            if (opt.dataset.nama) document.getElementById(formType + '_nama').value = opt.dataset.nama;
            if (opt.dataset.jabatan) document.getElementById(formType + '_jabatan').value = opt.dataset.jabatan;
            if (opt.dataset.email) document.getElementById(formType + '_email').value = opt.dataset.email;

            if (opt.dataset.divisi) {
                const selectDiv = document.getElementById(formType + '_divisi_select');
                const inputDiv = document.getElementById(formType + '_divisi');
                let found = false;
                for (let i = 0; i < selectDiv.options.length; i++) {
                    if (selectDiv.options[i].value.toLowerCase() === opt.dataset.divisi.toLowerCase()) {
                        selectDiv.selectedIndex = i;
                        inputDiv.value = selectDiv.options[i].value;
                        inputDiv.classList.add('hidden');
                        found = true;
                        break;
                    }
                }
                if (!found) {
                    selectDiv.value = '__NEW__';
                    inputDiv.classList.remove('hidden');
                    inputDiv.value = opt.dataset.divisi;
                }
            }
        }

        function handleStatusChange(formType, status) {
            const checkbox = document.getElementById(formType + '_garis_putus');
            if (checkbox) {
                if (status === 'magang' || status === 'probation') {
                    checkbox.checked = true;
                } else {
                    checkbox.checked = false;
                }
            }
        }

        async function submitAddNode(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmitAdd');
            btn.disabled = true;
            btn.textContent = 'Menyimpan...';

            const form = document.getElementById('formAddNode');
            const formData = new FormData(form);

            try {
                const res = await fetch('{{ route("strukturOrganisasi.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    window.location.reload();
                } else {
                    alert('Gagal menyimpan: ' + (data.message || 'Terjadi kesalahan'));
                    btn.disabled = false;
                    btn.textContent = 'Simpan Karyawan';
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan.');
                btn.disabled = false;
                btn.textContent = 'Simpan Karyawan';
            }
        }

        // =========================================================================
        // MODAL EDIT KARYAWAN (TIDAK AKAN CRASH / BENTROK)
        // =========================================================================
        function openEditNodeModal(e, id) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
                if (e.stopImmediatePropagation) e.stopImmediatePropagation();
            }

            // Tutup modal lain jika ada
            closeProfileModal();
            closeModal('modalAddNode');

            // Temukan kartu elemen langsung via ID atau event target
            const card = document.getElementById('org-card-' + id)
                || (e && e.target ? e.target.closest('.org-card') : null)
                || document.querySelector(`[data-id="${id}"]`);

            if (!card) {
                console.error('Kartu anggota dengan ID ' + id + ' tidak ditemukan.');
                return;
            }

            let employee;
            try {
                employee = JSON.parse(card.dataset.employee);
            } catch (err) {
                console.error('Gagal membaca data JSON kartu:', err);
                return;
            }

            document.getElementById('edit_node_id').value = employee.id;
            document.getElementById('edit_nama').value = employee.nama || '';
            document.getElementById('edit_jabatan').value = employee.jabatan || '';
            document.getElementById('edit_urutan').value = employee.urutan || 1;
            document.getElementById('edit_email').value = (employee.email && employee.email !== '-') ? employee.email : '';
            document.getElementById('edit_no_telepon').value = (employee.no_telepon && employee.no_telepon !== '-') ? employee.no_telepon : '';
            document.getElementById('edit_keterangan').value = (employee.keterangan && employee.keterangan !== '-') ? employee.keterangan : '';

            // Status kerja radio
            const status = employee.status_kerja || 'tetap';
            const radio = document.getElementById('edit_status_' + status);
            if (radio) radio.checked = true;

            // Garis putus
            const garisCheck = document.getElementById('edit_garis_putus');
            if (garisCheck) garisCheck.checked = Boolean(employee.garis_putus);

            // Divisi
            const selectDiv = document.getElementById('edit_divisi_select');
            const inputDiv = document.getElementById('edit_divisi');
            if (selectDiv && inputDiv) {
                let found = false;
                for (let i = 0; i < selectDiv.options.length; i++) {
                    if (selectDiv.options[i].value.toLowerCase() === (employee.divisi || '').toLowerCase()) {
                        selectDiv.selectedIndex = i;
                        inputDiv.value = selectDiv.options[i].value;
                        inputDiv.classList.add('hidden');
                        found = true;
                        break;
                    }
                }
                if (!found) {
                    selectDiv.value = '__NEW__';
                    inputDiv.classList.remove('hidden');
                    inputDiv.value = employee.divisi || '';
                }
            }

            // Atasan langsung
            const parentSelect = document.getElementById('edit_parent_id');
            if (parentSelect) parentSelect.value = employee.parent_id || '';

            openModal('modalEditNode');
        }

        async function submitEditNode(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmitEdit');
            btn.disabled = true;
            btn.textContent = 'Menyimpan...';

            const form = document.getElementById('formEditNode');
            const id = document.getElementById('edit_node_id').value;
            const formData = new FormData(form);

            try {
                const res = await fetch(`/struktur-organisasi/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'X-HTTP-Method-Override': 'PUT',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    window.location.reload();
                } else {
                    alert('Gagal memperbarui: ' + (data.message || 'Terjadi kesalahan'));
                    btn.disabled = false;
                    btn.textContent = 'Simpan Perubahan';
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan.');
                btn.disabled = false;
                btn.textContent = 'Simpan Perubahan';
            }
        }

        // =========================================================================
        // PENCARIAN & FILTER DIVISI REALTIME
        // =========================================================================
        function handleSearch(keyword) {
            const term = (keyword || '').trim().toLowerCase();
            const cards = document.querySelectorAll('[data-employee]');

            if (!term) {
                cards.forEach(c => {
                    c.classList.remove('highlight-search', 'dimmed');
                });
                return;
            }

            cards.forEach(c => {
                let emp;
                try {
                    emp = JSON.parse(c.dataset.employee);
                } catch (e) { return; }

                const match = (emp.nama && emp.nama.toLowerCase().includes(term))
                    || (emp.jabatan && emp.jabatan.toLowerCase().includes(term))
                    || (emp.divisi && emp.divisi.toLowerCase().includes(term));

                if (match) {
                    c.classList.add('highlight-search');
                    c.classList.remove('dimmed');
                } else {
                    c.classList.remove('highlight-search');
                    c.classList.add('dimmed');
                }
            });
        }

        function handleFilterDivisi(selectedDivisi) {
            const filter = (selectedDivisi || '').trim().toLowerCase();
            const cards = document.querySelectorAll('[data-employee]');

            if (!filter || filter === 'all') {
                cards.forEach(c => c.classList.remove('dimmed', 'highlight-search'));
                return;
            }

            cards.forEach(c => {
                let emp;
                try {
                    emp = JSON.parse(c.dataset.employee);
                } catch (e) { return; }

                const match = emp.divisi && emp.divisi.toLowerCase() === filter;
                if (match) {
                    c.classList.remove('dimmed');
                    c.classList.add('highlight-search');
                } else {
                    c.classList.remove('highlight-search');
                    c.classList.add('dimmed');
                }
            });
        }

        // =========================================================================
        // ZOOM KANVAS
        // =========================================================================
        function updateZoom() {
            const wrapper = document.getElementById('canvasWrapper');
            if (!wrapper) return;
            wrapper.style.transform = `scale(${currentZoom})`;
            document.getElementById('zoomLevelText').textContent = Math.round(currentZoom * 100) + '%';
        }

        function zoomIn() {
            if (currentZoom < 1.6) {
                currentZoom += 0.1;
                updateZoom();
            }
        }

        function zoomOut() {
            if (currentZoom > 0.5) {
                currentZoom -= 0.1;
                updateZoom();
            }
        }

        function zoomReset() {
            currentZoom = 1;
            updateZoom();
        }
    </script>
</body>

</html>
