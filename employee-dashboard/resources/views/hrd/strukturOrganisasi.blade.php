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
            border: 2px dashed #f59e0b !important;
            background: #fffdf7 !important;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.08);
        }

        .org-card.is-dashed:hover {
            border-color: #d97706 !important;
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
            flex-shrink: 0;
        }

        .v-line.is-dashed {
            background: transparent !important;
            border-left: 2px dashed #f59e0b !important;
            width: 2px;
        }

        /* Division Header Badge */
        .org-division-header {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
            border: 1.5px solid #cbd5e1;
            border-top: 3px solid #02527a;
            padding: 8px 10px;
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

        /* ============================================================== */
        /* AUTOMATIC CSS TREE CONNECTORS (100% Presisi tanpa Hardcode px) */
        /* ============================================================== */
        .org-tree-row {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            position: relative;
            width: max-content;
            margin: 0 auto;
        }

        .org-tree-col {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 8px 0 8px;
            width: 184px;
            flex-shrink: 0;
        }

        /* Garis vertikal turun ke header kolom */
        .org-tree-col::after {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 1.5px;
            height: 20px;
            background: #475569;
        }

        /* Garis horizontal antar kolom */
        .org-tree-col::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1.5px;
            background: #475569;
        }

        .org-tree-col:first-child::before {
            left: 50%;
        }

        .org-tree-col:last-child::before {
            right: 50%;
            left: 0;
        }

        .org-tree-col:only-child::before {
            display: none;
        }

        /* Sayap Kiri (di bawah Direktur Utama -> nyambung ke garis tengah kolom 280px) */
        .wing-left {
            display: flex;
            justify-content: flex-end;
            align-items: flex-start;
            padding-right: 16px;
            position: relative;
        }

        .wing-left .org-tree-col {
            width: 168px;
            padding: 20px 6px 0 6px;
        }

        .wing-left .org-tree-col:last-child::before {
            left: 0;
            right: -156px;
            display: block;
        }

        .wing-left .org-tree-col:only-child::before {
            left: 50%;
            right: -156px;
            display: block;
        }

        /* Sayap Kanan (di bawah Direktur Operasional -> nyambung ke garis tengah kolom 280px) */
        .wing-right {
            display: flex;
            justify-content: flex-start;
            align-items: flex-start;
            padding-left: 16px;
            position: relative;
        }

        .wing-right .org-tree-col {
            width: 176px;
            padding: 20px 6px 0 6px;
        }

        .wing-right .org-tree-col:first-child::before {
            left: -156px;
            right: 0;
            display: block;
        }

        .wing-right .org-tree-col:only-child::before {
            left: -156px;
            right: 50%;
            display: block;
        }

        /* Search highlighting */
        .org-card.highlight-search {
            border-color: #f59e0b !important;
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
                            <div>
                                <h1 class="text-xl md:text-2xl font-bold text-[#172B36]">Struktur Organisasi PT Silindo</h1>
                                <p class="text-xs md:text-sm text-slate-500 mt-0.5">
                                    Terintegrasi otomatis dengan data <span class="font-semibold text-slate-700">Divisi</span> &amp; <span class="font-semibold text-slate-700">Karyawan</span>.
                                </p>
                            </div>
                        </div>
                    </div>

                    @if ($canManage)
                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            @if ($role === 'hrd')
                                <a href="{{ route('hrd.manajemenDivisi') }}"
                                    class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 text-xs font-semibold rounded-xl shadow-xs transition flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                    </svg>
                                    <span>Kelola Divisi</span>
                                </a>
                                <a href="{{ route('hrd.daftarKaryawan') }}"
                                    class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 text-xs font-semibold rounded-xl shadow-xs transition flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                    </svg>
                                    <span>Kelola Karyawan</span>
                                </a>
                            @endif

                            <button type="button" onclick="openLayoutModal()"
                                class="px-4 py-2 bg-[#0284c7] hover:bg-sky-700 text-white text-xs md:text-sm font-semibold rounded-xl shadow-sm transition-all flex items-center gap-2 cursor-pointer active:scale-95">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                                </svg>
                                <span>Atur Posisi Divisi</span>
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
                                class="p-1 rounded-lg hover:bg-white text-slate-600 hover:text-slate-900 transition cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" /></svg>
                            </button>
                            <button type="button" onclick="zoomReset()" title="Reset Zoom (100%)"
                                class="px-2 py-0.5 text-[11px] font-bold text-slate-600 hover:text-slate-900 rounded-lg hover:bg-white transition cursor-pointer" id="zoomLevelText">
                                100%
                            </button>
                            <button type="button" onclick="zoomOut()" title="Perkecil (-)"
                                class="p-1 rounded-lg hover:bg-white text-slate-600 hover:text-slate-900 transition cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kanvas Bagan Struktur Organisasi --}}
            <section aria-label="Bagan struktur organisasi" class="flex-1 overflow-auto bg-slate-100/70 border border-slate-200 rounded-2xl relative p-6">
                <div class="canvas-drag-wrapper min-w-max pb-16 mx-auto" id="canvasWrapper">
                    <div class="flex flex-col items-center mx-auto" id="orgChartCanvas">

                        {{-- ============================================================ --}}
                        {{-- LEVEL 1: PENGAWAS PERUSAHAAN                                 --}}
                        {{-- ============================================================ --}}
                        @if ($pengawas)
                            <div class="flex justify-center">
                                <div class="org-card group {{ $pengawas->garis_putus ? 'is-dashed' : '' }}" style="width: 290px;"
                                    id="org-card-{{ $pengawas->id }}"
                                    data-id="{{ $pengawas->id }}"
                                    data-employee='{{ e(json_encode([
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
                                    ])) }}'
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
                            <div class="v-line mx-auto {{ !empty($dirUtama->garis_putus) ? 'is-dashed' : '' }}" style="height: 24px;"></div>
                        @endif

                        {{-- ============================================================ --}}
                        {{-- LEVEL 2: DIREKTUR UTAMA                                      --}}
                        {{-- ============================================================ --}}
                        @if ($dirUtama)
                            <div class="flex justify-center">
                                <div class="org-card group {{ $dirUtama->garis_putus ? 'is-dashed' : '' }}" style="width: 310px;"
                                    id="org-card-{{ $dirUtama->id }}"
                                    data-id="{{ $dirUtama->id }}"
                                    data-employee='{{ e(json_encode([
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
                                    ])) }}'
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
                            <div class="v-line mx-auto" style="height: 28px;"></div>
                        @endif

                        {{-- ============================================================ --}}
                        {{-- LEVEL 3: SAYAP KIRI (Di bawah Dirut) | TENGAH 280px (Dir Ops) | SAYAP KANAN (Di bawah Dir Ops) --}}
                        {{-- Garis bercabang otomatis mengikuti jumlah divisi di masing-masing sayap --}}
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
                                        data-employee='{{ e(json_encode([
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
                                        ])) }}'
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
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- KOLOM TENGAH (280px): GARIS VERTIKAL + KARTU DIREKTUR OPERASIONAL --}}
                            <div class="relative flex flex-col items-center" style="min-height: 220px;">
                                <div class="v-line" style="position: absolute; top: 0; bottom: 0; left: 50%; transform: translateX(-50%); z-index: 1;"></div>
                                @if ($dirOps)
                                    <div class="relative z-10 mx-auto" style="margin-top: 44px;">
                                        @include('hrd.partials.org_exec_card', ['node' => $dirOps, 'canManage' => $canManage, 'width' => '260px'])
                                    </div>
                                @endif
                            </div>

                            {{-- SAYAP KANAN: Divisi di bawah Direktur Operasional --}}
                            <div class="flex justify-start items-start" style="padding-top: {{ $dirOps ? '132px' : '24px' }};">
                                @if ($diropsDivisions->isNotEmpty())
                                    <div class="wing-right">
                                        @foreach ($diropsDivisions as $divBox)
                                            <div class="org-tree-col dept-column" data-dept="{{ $divBox->nama_divisi }}">
                                                <div class="org-division-header group">
                                                    <span class="truncate">{{ $divBox->nama_divisi }}</span>
                                                    @if ($canManage)
                                                        <button type="button"
                                                            onclick="openLayoutModalFor('{{ $divBox->id_divisi }}', '{{ $divBox->posisi_bagan }}', {{ $divBox->urutan }})"
                                                            title="Atur Posisi Divisi {{ $divBox->nama_divisi }}"
                                                            class="absolute right-1 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 p-1 text-sky-600 hover:bg-sky-50 rounded transition cursor-pointer">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" /></svg>
                                                        </button>
                                                    @endif
                                                </div>

                                                @if ($divBox->members->isNotEmpty())
                                                    <div class="v-line {{ $divBox->members->first()?->garis_putus ? 'is-dashed' : '' }}" style="height: 12px;"></div>
                                                    <div class="flex flex-col items-center w-full">
                                                        @foreach ($divBox->members as $index => $member)
                                                            @if ($index > 0)
                                                                <div class="v-line {{ $member->garis_putus ? 'is-dashed' : '' }}" style="height: 10px;"></div>
                                                            @endif
                                                            @include('hrd.partials.org_card_item', ['member' => $member, 'canManage' => $canManage])
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="v-line" style="height: 12px;"></div>
                                                    <div class="w-full p-2.5 text-center border border-dashed border-slate-300 rounded-xl bg-white/70">
                                                        <p class="text-[10px] text-slate-400">Belum ada anggota</p>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                        </div>

                        {{-- ============================================================ --}}
                        {{-- LEVEL 4: DEPARTEMEN OPERASIONAL (Garis Otomatis CSS Tree)    --}}
                        {{-- ============================================================ --}}
                        @if ($operasionalDivisions->isNotEmpty())
                            <div class="v-line mx-auto" style="height: 28px;"></div>

                            <div class="org-tree-row" id="deptGrid">
                                @foreach ($operasionalDivisions as $divBox)
                                    <div class="org-tree-col dept-column" data-dept="{{ $divBox->nama_divisi }}">
                                        {{-- HEADER DIVISI --}}
                                        <div class="org-division-header group">
                                            <span class="truncate">{{ $divBox->nama_divisi }}</span>
                                            @if ($canManage)
                                                <button type="button"
                                                    onclick="openLayoutModalFor('{{ $divBox->id_divisi }}', '{{ $divBox->posisi_bagan }}', {{ $divBox->urutan }})"
                                                    title="Atur Posisi Divisi {{ $divBox->nama_divisi }}"
                                                    class="absolute right-1 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 p-1 text-sky-600 hover:bg-sky-50 rounded transition cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" /></svg>
                                                </button>
                                            @endif
                                        </div>

                                        {{-- STAF-STAF DI BAWAH DIVISI TERSEBUT (Otomatis dari Karyawan) --}}
                                        @if ($divBox->members->isNotEmpty())
                                            <div class="v-line {{ $divBox->members->first()?->garis_putus ? 'is-dashed' : '' }}" style="height: 12px;"></div>
                                            <div class="flex flex-col items-center w-full">
                                                @foreach ($divBox->members as $index => $member)
                                                    @if ($index > 0)
                                                        <div class="v-line {{ $member->garis_putus ? 'is-dashed' : '' }}" style="height: 10px;"></div>
                                                    @endif
                                                    @include('hrd.partials.org_card_item', ['member' => $member, 'canManage' => $canManage])
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="v-line" style="height: 12px;"></div>
                                            <div class="p-3 text-center border border-dashed border-slate-300 rounded-xl bg-white/70 w-full">
                                                <p class="text-[10px] text-slate-400">Belum ada karyawan</p>
                                                @if ($canManage && $role === 'hrd')
                                                    <a href="{{ route('hrd.daftarKaryawan') }}"
                                                        class="mt-1 inline-block text-[10px] font-bold text-sky-600 hover:underline">
                                                        + Kelola Karyawan
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                    </div>
                </div>
            </section>
        </main>
    </div>

    {{-- ============================================================== --}}
    {{-- MODAL DETAIL PROFIL KARYAWAN                                   --}}
    {{-- ============================================================== --}}
    <div id="employee-profile-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm"
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
                        <dt class="text-slate-500">Keterangan</dt>
                        <dd id="employee-profile-desc" class="break-words text-right text-xs text-slate-700"></dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    @if ($canManage)
        {{-- ============================================================== --}}
        {{-- MODAL ATUR POSISI DIVISI DI BAGAN (MUDAH & INSTAN)             --}}
        {{-- ============================================================== --}}
        <div id="modalLayoutDivisi" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden p-4">
            <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden border border-slate-100 flex flex-col max-h-[90vh]">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <span class="p-2 bg-sky-100 text-sky-700 rounded-xl">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Atur Posisi Divisi di Bagan</h3>
                            <p class="text-[11px] text-slate-400">Tentukan divisi berada di bawah Direktur Utama, Dir. Operasional, atau Baris Operasional.</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeModal('modalLayoutDivisi')" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="formLayoutDivisi" onsubmit="submitDivisiLayout(event)" class="p-6 space-y-4 overflow-y-auto flex-1">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Pilih Divisi <span class="text-rose-500">*</span>
                        </label>
                        <select name="id_divisi" id="layout_id_divisi" onchange="onLayoutDivisiChange(this)" required
                            class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 bg-white">
                            @foreach ($daftarDivisiObj as $d)
                                <option value="{{ $d->id_divisi }}"
                                    data-posisi="{{ $d->posisi_bagan ?: 'operasional' }}"
                                    data-urutan="{{ $d->urutan }}">
                                    {{ $d->nama_divisi }} ({{ $d->kode_divisi }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">
                            Ditaruh Di Bawah Siapa? <span class="text-rose-500">*</span>
                        </label>
                        <div class="space-y-2.5">
                            <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 hover:border-sky-400 hover:bg-sky-50/40 cursor-pointer transition">
                                <input type="radio" name="posisi_bagan" id="posisi_dirut_pendukung" value="dirut_pendukung" class="mt-0.5 text-sky-600 focus:ring-sky-500">
                                <div>
                                    <p class="text-xs font-bold text-slate-800">Di Bawah Direktur Utama (Baris 3 Kiri)</p>
                                    <p class="text-[11px] text-slate-500">Muncul otomatis dengan garis cabang di sebelah Legal, Personal Asst, atau Writer.</p>
                                </div>
                            </label>

                            <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 hover:border-sky-400 hover:bg-sky-50/40 cursor-pointer transition">
                                <input type="radio" name="posisi_bagan" id="posisi_dirops_pendukung" value="dirops_pendukung" class="mt-0.5 text-sky-600 focus:ring-sky-500">
                                <div>
                                    <p class="text-xs font-bold text-slate-800">Di Bawah Direktur Operasional (Baris 3 Kanan)</p>
                                    <p class="text-[11px] text-slate-500">Muncul otomatis dengan garis cabang di sebelah Finance &amp; Accounting dan HR.</p>
                                </div>
                            </label>

                            <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 hover:border-sky-400 hover:bg-sky-50/40 cursor-pointer transition">
                                <input type="radio" name="posisi_bagan" id="posisi_operasional" value="operasional" class="mt-0.5 text-sky-600 focus:ring-sky-500">
                                <div>
                                    <p class="text-xs font-bold text-slate-800">Departemen Operasional (Baris 4 Bawah)</p>
                                    <p class="text-[11px] text-slate-500">Tampil sejajar pada jajaran divisi operasional utama dengan garis otomatis di atasnya.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Urutan Tampil (Kiri ke Kanan)</label>
                        <input type="number" name="urutan" id="layout_urutan" min="1" value="1"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button" onclick="closeModal('modalLayoutDivisi')"
                            class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-semibold rounded-xl transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" id="btnSubmitLayout"
                            class="px-5 py-2 bg-[#0284c7] hover:bg-sky-700 text-white text-xs font-semibold rounded-xl transition shadow cursor-pointer">
                            Simpan Posisi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- MODAL EDIT KARYAWAN / POSISI (SINKRON KE KARYAWANS)            --}}
        {{-- ============================================================== --}}
        <div id="modalEditNode" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden p-4">
            <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden border border-slate-100 flex flex-col max-h-[90vh]">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="p-1.5 bg-sky-100 text-sky-700 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Edit Data &amp; Posisi Anggota</h3>
                            <p class="text-[11px] text-slate-400" id="editSyncHint">Perubahan langsung tersinkron ke data Karyawan.</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeModal('modalEditNode')" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="formEditNode" onsubmit="submitEditNode(event)" enctype="multipart/form-data" class="p-6 space-y-4 overflow-y-auto flex-1">
                    @csrf
                    <input type="hidden" id="edit_node_id">
                    <input type="hidden" id="edit_source_type" value="karyawan">

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

                    {{-- Divisi (Untuk Karyawan pilih id_divisi; Untuk node struktur fallback isi teks) --}}
                    <div id="editDivisiKaryawanWrap">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Divisi <span class="text-rose-500">*</span>
                        </label>
                        <select name="divisi_id_divisi" id="edit_divisi_id"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 bg-white">
                            @foreach ($daftarDivisiObj as $d)
                                <option value="{{ $d->id_divisi }}">{{ $d->nama_divisi }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="editDivisiStrukturWrap" class="hidden">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Divisi</label>
                        <input type="text" name="divisi" id="edit_divisi_text"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>

                    {{-- Status Kerja (Magang & Probation otomatis garis putus-putus) --}}
                    <div class="p-3 bg-amber-50/60 border border-amber-200 rounded-xl space-y-2">
                        <label class="block text-xs font-bold text-amber-900 uppercase">Status Kepegawaian</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                            <label class="flex items-center gap-1.5 p-2 rounded-lg bg-white border border-amber-200 cursor-pointer">
                                <input type="radio" name="status_karyawan" id="edit_status_tetap" value="tetap" class="text-amber-600">
                                <span>Tetap</span>
                            </label>
                            <label class="flex items-center gap-1.5 p-2 rounded-lg bg-white border border-amber-200 cursor-pointer">
                                <input type="radio" name="status_karyawan" id="edit_status_kontrak" value="kontrak" class="text-amber-600">
                                <span>Kontrak</span>
                            </label>
                            <label class="flex items-center gap-1.5 p-2 rounded-lg bg-white border border-amber-200 cursor-pointer">
                                <input type="radio" name="status_karyawan" id="edit_status_probation" value="probation" class="text-amber-600">
                                <span class="text-purple-700 font-semibold">Probation</span>
                            </label>
                            <label class="flex items-center gap-1.5 p-2 rounded-lg bg-white border border-amber-200 cursor-pointer">
                                <input type="radio" name="status_karyawan" id="edit_status_magang" value="magang" class="text-amber-600">
                                <span class="text-amber-700 font-semibold">Magang</span>
                            </label>
                        </div>
                        <p class="text-[11px] text-amber-700">* Magang &amp; Probation otomatis tampil dengan garis &amp; border putus-putus (┄┄).</p>
                    </div>

                    {{-- Atasan Langsung --}}
                    <div id="editAtasanWrap">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Atasan Langsung</label>
                        <select name="atasan_id" id="edit_atasan_id"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500 bg-white">
                            <option value="">-- Tidak Ada / Pimpinan Utama --</option>
                            @foreach ($daftarKaryawan as $k)
                                <option value="{{ $k->id_karyawan }}">{{ $k->nama }} ({{ $k->jabatanLabel() }} - {{ $k->divisi?->nama_divisi ?? '-' }})</option>
                            @endforeach
                        </select>
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
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ganti Foto (Opsional · Maks. 3 MB)</label>
                        <input type="file" name="foto" accept="image/jpeg,image/png,image/webp"
                            class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 cursor-pointer">
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button" onclick="closeModal('modalEditNode')"
                            class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-semibold rounded-xl transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" id="btnSubmitEdit"
                            class="px-5 py-2 bg-[#0284c7] hover:bg-sky-700 text-white text-xs font-semibold rounded-xl transition shadow cursor-pointer">
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
        // PROFIL ANGGOTA
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

        document.addEventListener('DOMContentLoaded', function() {
            ['employee-profile-modal', 'modalLayoutDivisi', 'modalEditNode'].forEach((modalId) => {
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
                    closeModal('modalLayoutDivisi');
                    closeModal('modalEditNode');
                }
            });
        });

        // =========================================================================
        // MODAL ATUR POSISI DIVISI DI BAGAN
        // =========================================================================
        function openLayoutModal() {
            closeProfileModal();
            closeModal('modalEditNode');
            const select = document.getElementById('layout_id_divisi');
            if (select) onLayoutDivisiChange(select);
            openModal('modalLayoutDivisi');
        }

        function openLayoutModalFor(idDivisi, posisiBagan, urutan) {
            closeProfileModal();
            closeModal('modalEditNode');
            const select = document.getElementById('layout_id_divisi');
            if (select) {
                select.value = idDivisi;
            }
            const radio = document.getElementById('posisi_' + (posisiBagan || 'operasional'));
            if (radio) radio.checked = true;
            const urutanEl = document.getElementById('layout_urutan');
            if (urutanEl) urutanEl.value = urutan || 1;
            openModal('modalLayoutDivisi');
        }

        function onLayoutDivisiChange(selectEl) {
            const opt = selectEl.options[selectEl.selectedIndex];
            if (!opt) return;
            const posisi = opt.dataset.posisi || 'operasional';
            const urutan = opt.dataset.urutan || 1;
            const radio = document.getElementById('posisi_' + posisi);
            if (radio) radio.checked = true;
            const urutanEl = document.getElementById('layout_urutan');
            if (urutanEl) urutanEl.value = urutan;
        }

        async function submitDivisiLayout(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmitLayout');
            btn.disabled = true;
            btn.textContent = 'Menyimpan...';

            const form = document.getElementById('formLayoutDivisi');
            const formData = new FormData(form);

            try {
                const res = await fetch('{{ route("strukturOrganisasi.divisiLayout") }}', {
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
                    alert('Gagal menyimpan posisi: ' + (data.message || 'Terjadi kesalahan'));
                    btn.disabled = false;
                    btn.textContent = 'Simpan Posisi';
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan.');
                btn.disabled = false;
                btn.textContent = 'Simpan Posisi';
            }
        }

        // =========================================================================
        // MODAL EDIT ANGGOTA / KARYAWAN
        // =========================================================================
        function openEditNodeModal(e, nodeKey) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
                if (e.stopImmediatePropagation) e.stopImmediatePropagation();
            }

            closeProfileModal();
            closeModal('modalLayoutDivisi');

            const card = document.getElementById('org-card-' + nodeKey)
                || (e && e.target ? e.target.closest('.org-card') : null);
            if (!card) return;

            let emp;
            try {
                emp = JSON.parse(card.dataset.employee);
            } catch (err) {
                console.error('Gagal membaca data kartu:', err);
                return;
            }

            const sourceType = emp.source_type || 'karyawan';
            document.getElementById('edit_node_id').value = emp.id;
            document.getElementById('edit_source_type').value = sourceType;
            document.getElementById('edit_nama').value = emp.nama || '';
            document.getElementById('edit_jabatan').value = emp.jabatan || '';
            document.getElementById('edit_email').value = (emp.email && emp.email !== '-') ? emp.email : '';
            document.getElementById('edit_no_telepon').value = (emp.no_telepon && emp.no_telepon !== '-') ? emp.no_telepon : '';

            const status = emp.status_kerja || 'tetap';
            const radio = document.getElementById('edit_status_' + status);
            if (radio) radio.checked = true;

            const divKarWrap = document.getElementById('editDivisiKaryawanWrap');
            const divStrWrap = document.getElementById('editDivisiStrukturWrap');
            const atasanWrap = document.getElementById('editAtasanWrap');
            const syncHint = document.getElementById('editSyncHint');

            if (sourceType === 'karyawan') {
                divKarWrap.classList.remove('hidden');
                divStrWrap.classList.add('hidden');
                atasanWrap.classList.remove('hidden');
                syncHint.textContent = 'Perubahan langsung tersinkron ke data Karyawan.';

                const divSelect = document.getElementById('edit_divisi_id');
                if (divSelect && emp.divisi_id) divSelect.value = emp.divisi_id;

                const atasanSelect = document.getElementById('edit_atasan_id');
                if (atasanSelect) atasanSelect.value = emp.atasan_id || '';
            } else {
                divKarWrap.classList.add('hidden');
                divStrWrap.classList.remove('hidden');
                atasanWrap.classList.add('hidden');
                syncHint.textContent = 'Edit posisi pimpinan pada bagan struktur.';
                document.getElementById('edit_divisi_text').value = emp.divisi || '';
            }

            openModal('modalEditNode');
        }

        async function submitEditNode(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmitEdit');
            btn.disabled = true;
            btn.textContent = 'Menyimpan...';

            const form = document.getElementById('formEditNode');
            const id = document.getElementById('edit_node_id').value;
            const sourceType = document.getElementById('edit_source_type').value;
            const formData = new FormData(form);

            const url = sourceType === 'karyawan'
                ? `/struktur-organisasi/karyawan/${id}`
                : `/struktur-organisasi/${id}`;

            if (sourceType !== 'karyawan') {
                const statusVal = form.querySelector('input[name="status_karyawan"]:checked')?.value || 'tetap';
                formData.append('status_kerja', statusVal);
            }

            try {
                const res = await fetch(url, {
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
                cards.forEach(c => c.classList.remove('highlight-search', 'dimmed'));
                return;
            }

            cards.forEach(c => {
                let emp;
                try { emp = JSON.parse(c.dataset.employee); } catch (e) { return; }

                const match = (emp.nama && emp.nama.toLowerCase().includes(term))
                    || (emp.jabatan && emp.jabatan.toLowerCase().includes(term))
                    || (emp.divisi && emp.divisi.toLowerCase().includes(term));

                c.classList.toggle('highlight-search', Boolean(match));
                c.classList.toggle('dimmed', !match);
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
                try { emp = JSON.parse(c.dataset.employee); } catch (e) { return; }

                const match = emp.divisi && emp.divisi.toLowerCase() === filter;
                c.classList.toggle('highlight-search', Boolean(match));
                c.classList.toggle('dimmed', !match);
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
