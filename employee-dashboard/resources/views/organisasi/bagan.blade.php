<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bagan Struktur Organisasi - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* CSS Tree Connectors */
        .org-tree {
            display: flex;
            justify-content: center;
            padding: 24px;
        }

        .org-tree ul {
            padding-top: 24px;
            position: relative;
            transition: all 0.3s;
            display: flex;
            justify-content: center;
            gap: 24px;
        }

        .org-tree li {
            float: left;
            text-align: center;
            list-style-type: none;
            position: relative;
            padding: 24px 8px 0 8px;
            transition: all 0.3s;
        }

        /* Connectors using pseudo elements */
        .org-tree li::before,
        .org-tree li::after {
            content: '';
            position: absolute;
            top: 0;
            right: 50%;
            border-top: 2px solid #cbd5e1;
            width: 50%;
            height: 24px;
        }

        .org-tree li::after {
            right: auto;
            left: 50%;
            border-left: 2px solid #cbd5e1;
        }

        /* Remove connectors for single / first / last nodes */
        .org-tree li:only-child::after,
        .org-tree li:only-child::before {
            display: none;
        }

        .org-tree li:only-child {
            padding-top: 0;
        }

        .org-tree li:first-child::before,
        .org-tree li:last-child::after {
            border: 0 none;
        }

        .org-tree li:last-child::before {
            border-right: 2px solid #cbd5e1;
            border-radius: 0 8px 0 0;
        }

        .org-tree li:first-child::after {
            border-radius: 8px 0 0 0;
        }

        /* Downward connector line from parent card to children */
        .org-tree ul ul::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            border-left: 2px solid #cbd5e1;
            width: 0;
            height: 24px;
            transform: translateX(-50%);
        }

        /* Canvas Pan & Zoom container */
        #org-canvas-viewport {
            cursor: grab;
            user-select: none;
        }

        #org-canvas-viewport.grabbing {
            cursor: grabbing;
        }

        .node-hover-glow:hover {
            transform: translateY(-4px) scale(1.02);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
    </style>
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden text-slate-800">

    {{-- Role-based Sidebar --}}
    @if ($role === 'hrd')
        @include('component_hrd.sidebar')
    @elseif ($role === 'kadiv')
        @include('component_kadiv.sidebar')
    @else
        @include('component.sidebar')
    @endif

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')

        {{-- Top Action & Filter Toolbar --}}
        <header class="bg-white border-b border-slate-200 px-6 py-4 flex flex-wrap items-center justify-between gap-4 shrink-0 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#044564] to-[#19A7CE] text-white flex items-center justify-center shadow-md shadow-sky-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15M8.25 9.75h7.5m-7.5 4.5h7.5" />
                        <rect x="3" y="3" width="18" height="18" rx="3" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Bagan Struktur Organisasi</h1>
                    <p class="text-xs text-slate-500">Visualisasi hierarki jabatan & tim kerja PT Silindo</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                {{-- Filter Divisi --}}
                <div class="flex items-center gap-2">
                    <label for="filter-divisi" class="text-xs font-semibold text-slate-600">Filter:</label>
                    <select id="filter-divisi" onchange="loadBaganData()"
                            class="text-xs font-medium bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-lg px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#19A7CE] cursor-pointer">
                        <option value="all">Seluruh Perusahaan</option>
                        @foreach ($daftarDivisi as $div)
                            <option value="{{ $div->id_divisi }}">{{ $div->nama_divisi }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>

                {{-- Zoom & Pan Controls --}}
                <div class="flex items-center bg-slate-100 p-1 rounded-lg border border-slate-200 gap-1">
                    <button type="button" onclick="zoomIn()" title="Perbesar"
                            class="p-1.5 rounded-md hover:bg-white text-slate-700 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </button>
                    <button type="button" onclick="zoomOut()" title="Perkecil"
                            class="p-1.5 rounded-md hover:bg-white text-slate-700 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" />
                        </svg>
                    </button>
                    <button type="button" onclick="resetZoom()" title="Reset Tampilan"
                            class="px-2 py-1 text-xs font-semibold rounded-md hover:bg-white text-slate-700 transition cursor-pointer">
                        100%
                    </button>
                </div>

                {{-- Legend Tingkatan / Job Level --}}
                <div class="hidden lg:flex items-center gap-3 text-[11px] font-medium text-slate-500 bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-pink-500"></span> Pimpinan</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Kepala Divisi</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Supervisor</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-cyan-600"></span> Staff</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full border border-dashed border-slate-500 bg-slate-300"></span> Kosong</span>
                </div>
            </div>
        </header>

        {{-- Canvas Area --}}
        <main class="flex-1 relative overflow-hidden bg-slate-50">
            <div id="org-canvas-viewport" class="w-full h-full overflow-auto flex items-start justify-center p-8">
                <div id="org-canvas-container" class="transition-transform duration-100 origin-top">
                    {{-- Tree Content rendered by JS --}}
                    <div id="tree-root" class="org-tree">
                        <div class="flex items-center justify-center p-12 text-slate-400">
                            <svg class="animate-spin h-8 w-8 text-[#19A7CE]" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span class="ml-3 text-sm font-medium">Memuat bagan struktur...</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Floating Quick Hint --}}
            <div class="absolute bottom-4 left-6 bg-white/90 backdrop-blur-xs border border-slate-200 rounded-full px-4 py-1.5 text-xs text-slate-500 shadow-sm pointer-events-none">
                💡 Klik salah satu foto/kartu untuk melihat detail atau mengirim pesan langsung.
            </div>
        </main>
    </div>

    {{-- MODAL DETAIL KARYAWAN (Ketika Node Diklik) --}}
    <div id="modalDetailKaryawan" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs opacity-0 pointer-events-none transition-all duration-200">
        <div id="modalDetailBox" class="bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden transform scale-95 transition-all duration-200 border border-slate-100">
            {{-- Modal Header --}}
            <div id="modalHeaderGradient" class="bg-gradient-to-r from-[#044564] to-[#19A7CE] p-6 text-white text-center relative">
                <button type="button" onclick="closeDetailModal()" class="absolute top-4 right-4 text-white/80 hover:text-white p-1 rounded-full hover:bg-white/10 transition cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <div class="flex justify-center mb-3">
                    <div id="modalAvatarRing" class="w-24 h-24 rounded-full p-1 bg-white shadow-lg">
                        <div id="modalAvatarInner" class="w-full h-full rounded-full overflow-hidden bg-slate-100 flex items-center justify-center font-bold text-2xl text-slate-600">
                            ?
                        </div>
                    </div>
                </div>
                <h3 id="modalNama" class="text-lg font-bold tracking-wide leading-tight">Nama Karyawan</h3>
                <p id="modalJabatanPill" class="text-xs font-semibold text-sky-100 mt-1 uppercase tracking-wider">( Jabatan )</p>
            </div>

            {{-- Modal Body (Detail Informasi Tanpa NIP/ID) --}}
            <div class="p-6 space-y-3.5 text-sm">
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-500 font-medium text-xs">Divisi</span>
                    <span id="modalDivisi" class="text-slate-800 font-semibold text-xs">-</span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-500 font-medium text-xs">Status Karyawan</span>
                    <span id="modalStatusBadge" class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-500 font-medium text-xs">Atasan Langsung</span>
                    <span id="modalAtasan" class="text-slate-800 font-semibold text-xs">-</span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-500 font-medium text-xs">Email</span>
                    <span id="modalEmail" class="text-slate-700 font-mono text-xs">-</span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-500 font-medium text-xs">No. Telepon</span>
                    <span id="modalTelepon" class="text-slate-700 font-semibold text-xs">-</span>
                </div>

                {{-- HRD Only Edit Form (Tersembunyi jika non-HRD) --}}
                @if ($isHrd)
                    <div id="hrdEditSection" class="mt-4 pt-3 border-t border-slate-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-[#19A7CE]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                                </svg>
                                Kelola Struktur (HRD)
                            </span>
                            <button type="button" id="btnToggleEditAtasan" onclick="toggleEditAtasanForm()" class="text-xs text-[#19A7CE] hover:underline font-semibold cursor-pointer">
                                Ganti Atasan
                            </button>
                        </div>

                        <form id="formGantiAtasan" class="hidden mt-2 p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                            <input type="hidden" id="editKaryawanId" name="id_karyawan">
                            <label for="selectNewAtasan" class="block text-[11px] font-semibold text-slate-600">Pilih Atasan Baru:</label>
                            <select id="selectNewAtasan" name="atasan_id" class="w-full text-xs bg-white border border-slate-200 rounded-lg p-2 focus:ring-2 focus:ring-[#19A7CE]">
                                <option value="">-- Posisi Pucuk (Tanpa Atasan) --</option>
                                @foreach ($daftarKaryawanAktif as $calonAtasan)
                                    <option value="{{ $calonAtasan->id_karyawan }}">
                                        {{ $calonAtasan->nama }} ({{ $calonAtasan->jabatan }} - {{ $calonAtasan->divisi->nama_divisi ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="flex justify-end gap-2 pt-1">
                                <button type="button" onclick="toggleEditAtasanForm()" class="px-2.5 py-1 text-xs text-slate-600 hover:bg-slate-200 rounded-md transition cursor-pointer">Batal</button>
                                <button type="button" onclick="simpanAtasanBaru()" class="px-3 py-1 text-xs font-semibold bg-[#146C94] hover:bg-[#0f5474] text-white rounded-md transition cursor-pointer">Simpan Posisi</button>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- Action Buttons --}}
                <div class="pt-3 flex items-center gap-2">
                    {{-- Shortcut Kirim Pesan --}}
                    <a id="btnKirimPesanShortcut" href="#"
                       class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-xs text-white bg-gradient-to-r from-[#044564] to-[#19A7CE] hover:brightness-110 shadow-md shadow-sky-500/20 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                        </svg>
                        <span>Kirim Pesan</span>
                    </a>

                    <button type="button" onclick="closeDetailModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL ISI POSISI KOSONG (Khusus HRD) --}}
    @if ($isHrd)
        <div id="modalVacant" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs opacity-0 pointer-events-none transition-all duration-200">
            <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 border border-slate-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                        ?
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Tugaskan Pejabat Baru</h3>
                        <p class="text-xs text-slate-500">Mengisi posisi yang sedang kosong</p>
                    </div>
                </div>
                <input type="hidden" id="vacantTargetId">
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Pilih Karyawan:</label>
                        <select id="selectPejabatBaru" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-lg p-2.5 focus:ring-2 focus:ring-[#19A7CE]">
                            <option value="">-- Pilih Karyawan Aktif --</option>
                            @foreach ($daftarKaryawanAktif as $karyawan)
                                <option value="{{ $karyawan->id_karyawan }}">{{ $karyawan->nama }} ({{ $karyawan->jabatan }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" onclick="closeVacantModal()" class="px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-100 rounded-lg transition cursor-pointer">Batal</button>
                        <button type="button" onclick="simpanPejabatBaru()" class="px-4 py-1.5 text-xs font-semibold bg-[#146C94] hover:bg-[#0f5474] text-white rounded-lg transition cursor-pointer">Tugaskan</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Frontend JavaScript Logic --}}
    <script>
        const USER_ROLE = '{{ $role }}';
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const DATA_API_URL = '{{ $role === "hrd" ? route("hrd.struktur-organisasi.data") : ($role === "kadiv" ? route("kadiv.struktur-organisasi.data") : route("struktur-organisasi.data")) }}';
        const UPDATE_ATASAN_URL = '{{ route("hrd.struktur-organisasi.update-atasan") }}';
        const ASSIGN_VACANT_URL = '{{ route("hrd.struktur-organisasi.assign-vacant") }}';

        // Message base routes
        const PESAN_BASE_URL = '{{ $role === "hrd" ? url("/hrd/pesan") : ($role === "kadiv" ? url("/kadiv/pesan") : url("/pesan")) }}';

        let currentTreeData = [];
        let zoomLevel = 1;

        // --- Zoom & Pan ---
        function zoomIn() {
            if (zoomLevel < 1.8) {
                zoomLevel += 0.15;
                applyZoom();
            }
        }

        function zoomOut() {
            if (zoomLevel > 0.4) {
                zoomLevel -= 0.15;
                applyZoom();
            }
        }

        function resetZoom() {
            zoomLevel = 1;
            applyZoom();
        }

        function applyZoom() {
            const container = document.getElementById('org-canvas-container');
            container.style.transform = `scale(${zoomLevel})`;
        }

        // Mouse Drag to Pan
        const viewport = document.getElementById('org-canvas-viewport');
        let isDown = false;
        let startX, startY, scrollLeft, scrollTop;

        viewport.addEventListener('mousedown', (e) => {
            if (e.target.closest('.node-card')) return; // Ignore drag when clicking cards
            isDown = true;
            viewport.classList.add('grabbing');
            startX = e.pageX - viewport.offsetLeft;
            startY = e.pageY - viewport.offsetTop;
            scrollLeft = viewport.scrollLeft;
            scrollTop = viewport.scrollTop;
        });

        viewport.addEventListener('mouseleave', () => {
            isDown = false;
            viewport.classList.remove('grabbing');
        });

        viewport.addEventListener('mouseup', () => {
            isDown = false;
            viewport.classList.remove('grabbing');
        });

        viewport.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - viewport.offsetLeft;
            const y = e.pageY - viewport.offsetTop;
            const walkX = (x - startX) * 1.5;
            const walkY = (y - startY) * 1.5;
            viewport.scrollLeft = scrollLeft - walkX;
            viewport.scrollTop = scrollTop - walkY;
        });

        // --- Load & Render Bagan ---
        async function loadBaganData() {
            const divisiId = document.getElementById('filter-divisi').value;
            const treeContainer = document.getElementById('tree-root');
            treeContainer.innerHTML = `
                <div class="flex items-center justify-center p-12 text-slate-400">
                    <svg class="animate-spin h-7 w-7 text-[#19A7CE]" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span class="ml-3 text-sm font-medium">Memperbarui bagan...</span>
                </div>
            `;

            try {
                const response = await fetch(`${DATA_API_URL}?divisi_id=${encodeURIComponent(divisiId)}`);
                const result = await response.json();

                if (!result.success || !result.roots || result.roots.length === 0) {
                    treeContainer.innerHTML = `
                        <div class="p-12 text-center text-slate-400">
                            <p class="text-base font-semibold">Tidak ada karyawan aktif pada filter ini</p>
                            <p class="text-xs text-slate-400 mt-1">Pilih filter divisi lain atau kembali ke seluruh perusahaan.</p>
                        </div>
                    `;
                    return;
                }

                currentTreeData = result.roots;
                renderTree(result.roots);
            } catch (err) {
                console.error('Error fetching tree data:', err);
                treeContainer.innerHTML = `
                    <div class="p-8 text-center text-red-500">
                        <p class="text-sm font-bold">Gagal memuat data bagan struktur.</p>
                    </div>
                `;
            }
        }

        function renderTree(roots) {
            const treeContainer = document.getElementById('tree-root');
            let html = '<ul>';
            roots.forEach(root => {
                html += renderNode(root);
            });
            html += '</ul>';
            treeContainer.innerHTML = html;
        }

        function renderNode(node) {
            const nodeDataJson = encodeURIComponent(JSON.stringify(node));
            const isVacant = node.is_vacant;
            const level = node.level || 4;

            // Tier styling sesuai referensi gambar
            let ringColor = 'ring-cyan-500 shadow-cyan-500/20';
            let pillBg = 'bg-gradient-to-r from-cyan-600 to-blue-600';
            let roleColorText = 'text-cyan-100';

            if (level === 1) {
                ringColor = 'ring-pink-500 shadow-pink-500/25';
                pillBg = 'bg-gradient-to-r from-pink-500 to-rose-500';
                roleColorText = 'text-pink-100';
            } else if (level === 2) {
                ringColor = 'ring-amber-500 shadow-amber-500/25';
                pillBg = 'bg-gradient-to-r from-amber-500 to-orange-500';
                roleColorText = 'text-amber-100';
            } else if (level === 3) {
                ringColor = 'ring-emerald-500 shadow-emerald-500/25';
                pillBg = 'bg-gradient-to-r from-emerald-500 to-teal-500';
                roleColorText = 'text-emerald-100';
            }

            if (isVacant) {
                ringColor = 'ring-dashed ring-slate-400 bg-slate-100';
                pillBg = 'bg-gradient-to-r from-slate-600 to-slate-700';
                roleColorText = 'text-slate-300';
            }

            // Avatar photo or initials
            let avatarContent = `<span class="font-bold text-slate-700 text-base">${node.nama ? node.nama.charAt(0).toUpperCase() : '?'}</span>`;
            if (node.foto) {
                avatarContent = `<img src="${node.foto}" alt="${node.nama}" class="w-full h-full object-cover rounded-full" onerror="this.outerHTML='<span class=\\'font-bold text-slate-700 text-base\\'>${node.nama.charAt(0)}</span>'"/>`;
            } else if (isVacant) {
                avatarContent = `<span class="font-bold text-slate-400 text-xl">?</span>`;
            }

            let html = `<li>`;
            html += `
                <div class="node-card node-hover-glow inline-flex flex-col items-center cursor-pointer transition-all duration-200"
                     onclick="handleNodeClick('${nodeDataJson}')">
                    {{-- Avatar Lingkaran dengan Ring Warna --}}
                    <div class="w-16 h-16 rounded-full p-1 bg-white ring-4 ${ringColor} shadow-lg z-10 flex items-center justify-center overflow-hidden">
                        <div class="w-full h-full rounded-full bg-slate-100 flex items-center justify-center overflow-hidden">
                            ${avatarContent}
                        </div>
                    </div>

                    {{-- Pill Label Nama & Jabatan --}}
                    <div class="-mt-3.5 pt-4 px-4 pb-2 rounded-2xl ${pillBg} text-white shadow-md min-w-[140px] max-w-[200px] text-center z-20">
                        <div class="font-bold text-xs truncate leading-snug">${node.nama}</div>
                        <div class="text-[10px] font-medium ${roleColorText} truncate tracking-wide leading-tight">(${node.jabatan})</div>
                    </div>
                </div>
            `;

            // Render children rekursif
            if (node.children && node.children.length > 0) {
                html += `<ul>`;
                node.children.forEach(child => {
                    html += renderNode(child);
                });
                html += `</ul>`;
            }

            html += `</li>`;
            return html;
        }

        // --- Node Click Interaction ---
        function handleNodeClick(encodedJson) {
            const node = JSON.parse(decodeURIComponent(encodedJson));

            if (node.is_vacant) {
                if (USER_ROLE === 'hrd') {
                    openVacantModal(node.id);
                } else {
                    alert('Posisi ini saat ini kosong dan sedang menunggu penunjukan pejabat baru oleh HRD.');
                }
                return;
            }

            openDetailModal(node);
        }

        function openDetailModal(node) {
            document.getElementById('modalNama').textContent = node.nama;
            document.getElementById('modalJabatanPill').textContent = `( ${node.jabatan} )`;
            document.getElementById('modalDivisi').textContent = node.divisi || '-';
            document.getElementById('modalAtasan').textContent = node.atasan_nama || 'Pucuk Pimpinan / Tidak Ada';
            document.getElementById('modalEmail').textContent = node.email || '-';
            document.getElementById('modalTelepon').textContent = node.no_telepon || '-';

            // Avatar modal
            const avatarInner = document.getElementById('modalAvatarInner');
            if (node.foto) {
                avatarInner.innerHTML = `<img src="${node.foto}" alt="${node.nama}" class="w-full h-full object-cover">`;
            } else {
                avatarInner.innerHTML = `<span class="text-3xl font-bold text-slate-600">${node.nama.charAt(0).toUpperCase()}</span>`;
            }

            // Set Shortcut Kirim Pesan Link
            const btnPesan = document.getElementById('btnKirimPesanShortcut');
            btnPesan.href = `${PESAN_BASE_URL}?recipient_id=${encodeURIComponent(node.id)}&recipient_name=${encodeURIComponent(node.nama)}`;

            // HRD Form Setup
            const editIdInput = document.getElementById('editKaryawanId');
            if (editIdInput) {
                editIdInput.value = node.id;
                const selectAtasan = document.getElementById('selectNewAtasan');
                if (selectAtasan) {
                    selectAtasan.value = node.atasan_id || '';
                }
                const form = document.getElementById('formGantiAtasan');
                if (form) form.classList.add('hidden');
            }

            // Show modal
            const modal = document.getElementById('modalDetailKaryawan');
            const box = document.getElementById('modalDetailBox');
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100');
            box.classList.remove('scale-95');
            box.classList.add('scale-100');
        }

        function closeDetailModal() {
            const modal = document.getElementById('modalDetailKaryawan');
            const box = document.getElementById('modalDetailBox');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modal.classList.remove('opacity-100');
            box.classList.remove('scale-100');
            box.classList.add('scale-95');
        }

        function toggleEditAtasanForm() {
            const form = document.getElementById('formGantiAtasan');
            if (form) {
                form.classList.toggle('hidden');
            }
        }

        async function simpanAtasanBaru() {
            const karyawanId = document.getElementById('editKaryawanId').value;
            const atasanId = document.getElementById('selectNewAtasan').value;

            try {
                const response = await fetch(UPDATE_ATASAN_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        id_karyawan: karyawanId,
                        atasan_id: atasanId
                    })
                });

                const res = await response.json();
                if (!response.ok || !res.success) {
                    alert(res.message || 'Gagal mengubah posisi struktur.');
                    return;
                }

                closeDetailModal();
                loadBaganData();
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan koneksi.');
            }
        }

        // --- Vacant Modal Logic (HRD) ---
        function openVacantModal(vacantId) {
            document.getElementById('vacantTargetId').value = vacantId;
            const modal = document.getElementById('modalVacant');
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100');
        }

        function closeVacantModal() {
            const modal = document.getElementById('modalVacant');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modal.classList.remove('opacity-100');
        }

        async function simpanPejabatBaru() {
            const vacantId = document.getElementById('vacantTargetId').value;
            const newKaryawanId = document.getElementById('selectPejabatBaru').value;

            if (!newKaryawanId) {
                alert('Pilih karyawan terlebih dahulu.');
                return;
            }

            try {
                const response = await fetch(ASSIGN_VACANT_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        vacant_id: vacantId,
                        new_karyawan_id: newKaryawanId
                    })
                });

                const res = await response.json();
                if (!response.ok || !res.success) {
                    alert(res.message || 'Gagal menugaskan pejabat baru.');
                    return;
                }

                closeVacantModal();
                loadBaganData();
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan koneksi.');
            }
        }

        // Inisialisasi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', () => {
            loadBaganData();
        });
    </script>
</body>

</html>
