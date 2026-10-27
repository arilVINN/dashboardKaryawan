<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kalender Kegiatan Perusahaan - PT Silindo</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- FullCalendar CSS & JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/id.global.min.js"></script>

    <style>
        /* Custom styling for FullCalendar to match Tailwind dashboard */
        :root {
            --fc-border-color: #CBD5E1;
        }
        .fc {
            font-family: inherit;
            border: none !important;
        }
        /* Toolbar navigasi di luar tabel */
        .fc .fc-toolbar {
            border: none !important;
            margin-bottom: 1.25rem !important;
        }
        /* Tabel FullCalendar kotak tegas 90 derajat & selalu 100% fit container (7 kolom sama rata) */
        .fc-scrollgrid,
        .fc-theme-standard .fc-scrollgrid {
            border: 1px solid #CBD5E1 !important;
            border-radius: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            table-layout: fixed !important;
        }
        .fc-scrollgrid-sync-table,
        .fc-col-header,
        .fc-daygrid-body {
            width: 100% !important;
        }
        .fc-col-header-cell,
        .fc-daygrid-day {
            width: 14.2857% !important;
            max-width: 14.2857% !important;
        }
        .fc-scrollgrid-section-header,
        .fc-scrollgrid-section-header > th,
        .fc-scrollgrid-section-header > td,
        .fc-scroller-harness {
            padding: 0 !important;
            margin: 0 !important;
        }
        /* Semua sel dan garis menggunakan #CBD5E1 dan selalu rata ATAS */
        .fc-theme-standard th,
        .fc-theme-standard td,
        .fc-scrollgrid-sync-table td,
        .fc-daygrid-day {
            border-color: #CBD5E1 !important;
            vertical-align: top !important;
        }
        .fc-daygrid-day-frame {
            vertical-align: top !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: flex-start !important;
            min-height: 90px !important;
        }
        .fc-daygrid-day-top {
            display: flex !important;
            flex-direction: row-reverse !important;
            justify-content: flex-start !important;
            align-items: center !important;
            padding: 4px 6px 2px !important;
        }
        .fc-daygrid-day-number {
            color: #334155 !important;
            font-weight: 600 !important;
            font-size: 0.8125rem !important;
            padding: 2px 4px !important;
            text-decoration: none !important;
            line-height: 1 !important;
        }
        .fc-day-other .fc-daygrid-day-number {
            color: #94A3B8 !important;
            opacity: 0.6;
        }
        .fc-daygrid-day-events {
            margin-top: 2px !important;
            padding: 0 2px !important;
        }
        /* Header hari menyambung sempurna ke garis atas dan bawah */
        .fc-col-header-cell {
            background-color: #F8FAFC !important;
            border-color: #CBD5E1 !important;
            border-top: none !important;
            padding: 0 !important;
            text-transform: capitalize;
        }
        .fc-scrollgrid-section-header th,
        .fc-scrollgrid-section-header td {
            border-bottom: 1px solid #CBD5E1 !important;
        }
        .fc-col-header-cell-cushion {
            display: inline-block !important;
            padding: 8px 4px !important;
            color: #334155 !important;
            font-weight: 600 !important;
            font-size: 0.8125rem !important;
            line-height: 1.25rem !important;
            text-decoration: none !important;
        }
        .fc .fc-toolbar-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1E293B;
        }
        /* Hilangkan semua shadow/ring bawaan tombol FullCalendar */
        .fc .fc-button,
        .fc .fc-button-primary,
        .fc .fc-button-primary:hover,
        .fc .fc-button-primary:focus,
        .fc .fc-button-primary:active,
        .fc .fc-button-primary:not(:disabled):active:focus,
        .fc .fc-button-primary:not(:disabled).fc-button-active:focus,
        .fc .fc-button:not(:disabled):active:focus,
        .fc .fc-button:not(:disabled).fc-button-active:focus {
            box-shadow: none !important;
            outline: none !important;
        }
        .fc .fc-button-primary {
            background-color: #004A65;
            border-color: #004A65;
            border-radius: 0.5rem;
            padding: 0.4rem 0.85rem;
            font-size: 0.8125rem;
            font-weight: 500;
            text-transform: capitalize;
            box-shadow: none !important;
            transition: all 0.15s ease-in-out;
        }
        .fc .fc-button-primary:hover:not(:disabled) {
            background-color: #003347;
            border-color: #003347;
            box-shadow: none !important;
        }
        .fc .fc-button-primary:not(:disabled).fc-button-active,
        .fc .fc-button-primary:not(:disabled):active {
            background-color: #002230;
            border-color: #002230;
            box-shadow: none !important;
        }
        .fc-day-today {
            background-color: #F0F9FF !important;
        }
        /* Semua event tampil sebagai FULL KOTAK / blok berwarna */
        .fc-event,
        .fc-daygrid-event,
        .fc-daygrid-block-event {
            display: block !important;
            border-radius: 6px !important;
            padding: 2px 4px !important;
            margin: 2px 2px !important;
            border-width: 1px !important;
            border-style: solid !important;
            cursor: pointer;
            transition: transform 0.1s ease, box-shadow 0.1s ease;
        }
        .fc-event:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.12);
        }
        /* Sembunyikan dot/bullet bawaan FullCalendar */
        .fc-daygrid-event-dot {
            display: none !important;
        }

        /* Popover dan link "+X lainnya" */
        .fc-more-link {
            font-size: 0.72rem !important;
            font-weight: 700 !important;
            color: #004A65 !important;
            background-color: #E0F2FE !important;
            padding: 2px 6px !important;
            border-radius: 4px !important;
            text-decoration: none !important;
            display: inline-block !important;
            margin: 2px 0 0 2px !important;
        }
        .fc-more-link:hover {
            background-color: #BAE6FD !important;
            text-decoration: none !important;
        }
        .fc-popover {
            border: 1px solid #CBD5E1 !important;
            border-radius: 12px !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
            overflow: hidden !important;
            z-index: 100 !important;
        }
        .fc-popover-header {
            background-color: #F8FAFC !important;
            padding: 8px 12px !important;
            border-bottom: 1px solid #E2E8F0 !important;
            font-weight: 700 !important;
            color: #1E293B !important;
            font-size: 0.8125rem !important;
        }
        .fc-popover-body {
            padding: 8px !important;
            max-height: 280px !important;
            overflow-y: auto !important;
        }

        /* ========== LIST VIEW STYLING ========== */
        /* Header tanggal di list view - perhalus warna */
        .fc-list-day-cushion,
        .fc-list-day th {
            background-color: #F1F5F9 !important;
            border-color: #E2E8F0 !important;
        }
        .fc-list-day-text,
        .fc-list-day-side-text {
            color: #475569 !important;
            font-weight: 600 !important;
            font-size: 0.8125rem !important;
            text-decoration: none !important;
        }
        /* Reset border/radius dari rule .fc-event global untuk list view */
        .fc-list-event,
        .fc-list-event td,
        .fc-list-event a,
        .fc-list-event .fc-event-main {
            border-radius: 0 !important;
            border: none !important;
            box-shadow: none !important;
            background-color: transparent !important;
        }
        /* Garis pemisah - samakan semua sisi ke #E2E8F0 */
        .fc-list-table td,
        .fc-list-table th,
        .fc-list-table tr,
        .fc-list-event td,
        .fc-list-day td,
        .fc-list-day th {
            border-color: #E2E8F0 !important;
            border-width: 1px !important;
        }
        .fc-list-event:hover td {
            background-color: #F8FAFC !important;
        }
        /* Sembunyikan dot default di list view */
        .fc-list-event-dot {
            display: none !important;
        }
        /* Kolom waktu di list view */
        .fc-list-event-time {
            color: #64748B !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            white-space: nowrap !important;
        }
        /* Container event di list view - beri ruang penuh */
        .fc-list-event-title {
            width: 100% !important;
        }
    </style>
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">

    @include('component_hrd.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        @include('component.topbar')
        @include('component.breadcrumbs', [
            'parentText' => 'Dashboard',
            'parentUrl' => url('/hrd/dashboard'),
            'currentPage' => 'Kalender',
        ])

        <main class="flex-1 overflow-y-auto p-8 pt-4 pb-12">

            <!-- Page Title -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2.5">
                        <span class="p-2 rounded-lg bg-blue-100 text-[#004A65]">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </span>
                        <span>Kalender Tugas Divisi</span>
                    </h1>
                </div>

                <!-- Legend Status -->
                <div class="flex flex-wrap items-center gap-2 bg-white px-4 py-2.5 rounded-xl border border-slate-200 shadow-sm text-xs">
                    <span class="font-bold text-slate-600 mr-1">Status:</span>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-[#C0E7FF] text-blue-700 font-medium">
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span> Baru
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-700 font-medium">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Berjalan
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-amber-100 text-amber-700 font-medium">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> Menunggu di-acc
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-[#CCF4DB] text-emerald-700 font-medium">
                        <span class="w-2 h-2 rounded-full bg-[#059669]"></span> Sudah di-acc
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-red-100 text-red-700 font-medium">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span> Terlambat
                    </span>
                </div>
            </div>

            <!-- Toolbar Filter Khusus HRD: Divisi + Penerima + Status -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm mb-5 flex flex-wrap items-center gap-3">
                <!-- Filter Divisi -->
                <div class="flex items-center gap-2">
                    <label for="hrdDivisiFilter" class="text-xs font-bold text-slate-600 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span>Divisi:</span>
                    </label>
                    <select id="hrdDivisiFilter" class="text-xs bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 font-medium text-slate-700 outline-none focus:border-[#004A65]">
                        <option value="">Semua Divisi</option>
                        @isset($divisiList)
                            @foreach($divisiList as $divisi)
                                <option value="{{ $divisi->id_divisi }}">{{ $divisi->nama_divisi }}</option>
                            @endforeach
                        @endisset
                    </select>
                </div>

                <!-- Filter Penerima (Staff) -->
                <div class="flex items-center gap-2">
                    <label for="hrdStaffFilter" class="text-xs font-bold text-slate-600 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                        <span>Penerima:</span>
                    </label>
                    <select id="hrdStaffFilter" class="text-xs bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 font-medium text-slate-700 outline-none focus:border-[#004A65]">
                        <option value="">Semua Karyawan</option>
                        @isset($staffList)
                            @foreach($staffList as $staff)
                                <option value="{{ $staff->id_karyawan }}" data-divisi="{{ $staff->divisi_id_divisi }}">
                                    {{ $staff->nama }} ({{ $staff->divisi->nama_divisi ?? 'Tanpa Divisi' }} - {{ $staff->jabatan ?? 'Staff' }})
                                </option>
                            @endforeach
                        @endisset
                    </select>
                </div>

                <!-- Filter Status -->
                <div class="flex items-center gap-2">
                    <label for="hrdStatusFilter" class="text-xs font-bold text-slate-600">Status:</label>
                    <select id="hrdStatusFilter" class="text-xs bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 font-medium text-slate-700 outline-none focus:border-[#004A65]">
                        <option value="">Semua Status</option>
                        <option value="baru">Baru</option>
                        <option value="berjalan">Berjalan</option>
                        <option value="menunggu di-acc">Menunggu di-acc</option>
                        <option value="sudah di-acc">Sudah di-acc</option>
                        <option value="terlambat">Terlambat</option>
                    </select>
                </div>
            </div>

            <!-- Calendar Card -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <!-- Tombol tambah note -->
                <div class="flex items-center justify-end mb-3">
                    <button type="button" onclick="openModalNote()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#004A65] hover:bg-[#003347] text-white text-xs font-bold rounded-lg transition shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Note
                    </button>
                </div>
                <div id="calendar"></div>
            </div>

        </main>

    </div>

    <!-- Modal Detail Event / Tugas Khusus HRD -->
    <div id="modal-event" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4" style="display:none; pointer-events:none;">
        <div class="bg-white rounded-xl shadow-xl border border-slate-200 w-full max-w-lg overflow-hidden transform transition-all duration-200" onclick="event.stopPropagation()">
            <!-- Header Modal -->
            <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span id="modal-badge" class="px-2.5 py-1 rounded-md text-xs font-semibold">Status</span>
                    <span id="modal-divisi-badge" class="text-xs bg-slate-200/80 text-slate-700 font-semibold px-2 py-0.5 rounded border border-slate-300">Divisi</span>
                </div>
                <button type="button" onclick="closeModalEvent()" class="text-slate-400 hover:text-slate-600 transition p-1 rounded-lg hover:bg-slate-200/60">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Body Modal -->
            <div class="p-5 space-y-3.5">
                <!-- Info Alur Penugasan: Dari Kadiv ➔ Kepada Staff -->
                <div class="bg-gradient-to-r from-sky-50 via-slate-50 to-emerald-50 border border-slate-200 rounded-xl p-3.5">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-2">Alur Penugasan Tugas:</span>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5 items-center">
                        <!-- Pemberi Tugas (Kadiv) -->
                        <div class="bg-white/80 p-2.5 rounded-lg border border-sky-200">
                            <span class="text-[10px] font-bold text-sky-800 uppercase tracking-wider block">Dari (Pemberi Tugas):</span>
                            <span id="modal-pengirim-name" class="font-bold text-slate-900 text-sm leading-tight block mt-0.5">Nama Kadiv</span>
                            <span id="modal-pengirim-sub" class="text-[11px] text-slate-500 block mt-0.5">Kepala Divisi</span>
                        </div>

                        <!-- Penerima Tugas (Staff) -->
                        <div class="bg-white/80 p-2.5 rounded-lg border border-emerald-200">
                            <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider block">Kepada (Penerima):</span>
                            <span id="modal-staff-name" class="font-bold text-slate-900 text-sm leading-tight block mt-0.5">Nama Staff</span>
                            <span id="modal-staff-sub" class="text-[11px] text-slate-500 block mt-0.5">Staff • ID</span>
                        </div>
                    </div>
                </div>

                <div>
                    <span class="text-xs text-slate-500 font-medium block mb-0.5">Judul Tugas:</span>
                    <h3 id="modal-title" class="text-base font-bold text-slate-800 leading-snug">Judul Tugas</h3>
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <div class="bg-slate-50 rounded-lg p-2.5 border border-slate-200 space-y-1 text-sm">
                        <span class="text-xs text-slate-500 font-medium block">Tenggat Waktu:</span>
                        <span id="modal-deadline" class="font-semibold text-slate-800 block text-xs">-</span>
                    </div>

                    <div class="bg-slate-50 rounded-lg p-2.5 border border-slate-200 space-y-1 text-sm">
                        <span class="text-xs text-slate-500 font-medium block">Pengumpulan Tugas:</span>
                        <span id="modal-submission-status" class="inline-block px-2 py-0.5 rounded text-xs font-bold">-</span>
                    </div>
                </div>

                <div>
                    <span class="text-xs text-slate-500 font-medium block mb-1">Deskripsi Tugas:</span>
                    <div id="modal-deskripsi" class="text-sm text-slate-600 bg-white border border-slate-200 rounded-lg p-3 max-h-32 overflow-y-auto whitespace-pre-line leading-relaxed">
                        -
                    </div>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModalEvent()"
                        class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-100 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Input Form Tambah Note -->
    <div id="modal-tambah-note" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4" style="display:none; pointer-events:none;">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-md overflow-hidden transform transition-all duration-200" onclick="event.stopPropagation()">
            <!-- Header Modal -->
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-blue-100 text-[#004A65]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </span>
                    <h3 class="text-base font-bold text-slate-800">Tambah Catatan / Note</h3>
                </div>
                <button type="button" onclick="closeModalNote()" class="text-slate-400 hover:text-slate-600 transition p-1 rounded-lg hover:bg-slate-200/60">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Form Body -->
            <form id="form-tambah-note" onsubmit="submitTambahNote(event)" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold tracking-wide text-slate-600 mb-1.5">TANGGAL <span class="text-red-500">*</span></label>
                    <input type="date" id="note-input-tanggal" required
                        class="w-full h-10 rounded-lg border border-slate-300 px-3 text-sm text-slate-800 outline-none focus:border-[#004A65] focus:ring-1 focus:ring-[#004A65]">
                </div>

                <div>
                    <label class="block text-xs font-bold tracking-wide text-slate-600 mb-1.5">JUDUL NOTE <span class="text-red-500">*</span></label>
                    <input type="text" id="note-input-judul" required placeholder="Contoh: Reminder evaluasi mingguan, follow up berkas..."
                        class="w-full h-10 rounded-lg border border-slate-300 px-3 text-sm text-slate-800 outline-none focus:border-[#004A65] focus:ring-1 focus:ring-[#004A65]">
                </div>

                <div>
                    <label class="block text-xs font-bold tracking-wide text-slate-600 mb-1.5">KETERANGAN / DETAIL (OPSIONAL)</label>
                    <textarea id="note-input-detail" rows="3" placeholder="Catatan detail tambahan..."
                        class="w-full resize-none rounded-lg border border-slate-300 p-3 text-sm text-slate-800 outline-none focus:border-[#004A65] focus:ring-1 focus:ring-[#004A65]"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold tracking-wide text-slate-600 mb-1.5">LABEL WARNA</label>
                    <div class="grid grid-cols-6 gap-2" id="note-color-picker">
                        <label class="cursor-pointer">
                            <input type="radio" name="note-warna" value="slate" class="peer sr-only" checked>
                            <div class="h-8 rounded-lg border-2 border-slate-300 bg-slate-100 flex items-center justify-center peer-checked:border-[#004A65] peer-checked:ring-2 peer-checked:ring-[#004A65]/30">
                                <span class="w-3 h-3 rounded-full bg-slate-500"></span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="note-warna" value="blue" class="peer sr-only">
                            <div class="h-8 rounded-lg border-2 border-slate-300 bg-blue-50 flex items-center justify-center peer-checked:border-blue-600 peer-checked:ring-2 peer-checked:ring-blue-400">
                                <span class="w-3 h-3 rounded-full bg-blue-600"></span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="note-warna" value="green" class="peer sr-only">
                            <div class="h-8 rounded-lg border-2 border-slate-300 bg-emerald-50 flex items-center justify-center peer-checked:border-emerald-600 peer-checked:ring-2 peer-checked:ring-emerald-400">
                                <span class="w-3 h-3 rounded-full bg-emerald-600"></span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="note-warna" value="amber" class="peer sr-only">
                            <div class="h-8 rounded-lg border-2 border-slate-300 bg-amber-50 flex items-center justify-center peer-checked:border-amber-600 peer-checked:ring-2 peer-checked:ring-amber-400">
                                <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="note-warna" value="red" class="peer sr-only">
                            <div class="h-8 rounded-lg border-2 border-slate-300 bg-red-50 flex items-center justify-center peer-checked:border-red-600 peer-checked:ring-2 peer-checked:ring-red-400">
                                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="note-warna" value="purple" class="peer sr-only">
                            <div class="h-8 rounded-lg border-2 border-slate-300 bg-purple-50 flex items-center justify-center peer-checked:border-purple-600 peer-checked:ring-2 peer-checked:ring-purple-400">
                                <span class="w-3 h-3 rounded-full bg-purple-500"></span>
                            </div>
                        </label>
                    </div>
                </div>

                <div id="note-error-message" class="text-xs text-red-600 hidden"></div>

                <!-- Footer Modal -->
                <div class="flex items-center justify-between pt-3 border-t border-slate-200">
                    <button type="button" onclick="closeModalNote()"
                        class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 transition">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit-note"
                        class="inline-flex items-center gap-1.5 px-5 py-2 bg-[#004A65] hover:bg-[#003347] text-white text-sm font-bold rounded-lg transition shadow-sm">
                        <span>Simpan Note</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Detail Note -->
    <div id="modal-detail-note" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4" style="display:none; pointer-events:none;">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-md overflow-hidden transform transition-all duration-200" onclick="event.stopPropagation()">
            <!-- Header Modal -->
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-sky-100 text-sky-800">Catatan Pribadi</span>
                    <span id="detail-note-date" class="text-xs text-slate-500 font-medium"></span>
                </div>
                <button type="button" onclick="closeModalDetailNote()" class="text-slate-400 hover:text-slate-600 transition p-1 rounded-lg hover:bg-slate-200/60">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Body Modal -->
            <div class="p-6 space-y-4">
                <div>
                    <h3 id="detail-note-title" class="text-lg font-bold text-slate-800 leading-snug">Judul Note</h3>
                </div>

                <div id="detail-note-body-wrapper">
                    <span class="text-xs text-slate-500 font-medium block mb-1">Keterangan:</span>
                    <div id="detail-note-content" class="text-sm text-slate-600 bg-slate-50 border border-slate-200 rounded-xl p-3 max-h-48 overflow-y-auto whitespace-pre-line leading-relaxed">
                        -
                    </div>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                <button type="button" id="btn-delete-note" onclick="hapusCurrentNote()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus
                </button>
                <button type="button" onclick="closeModalDetailNote()"
                    class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-100 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        let calendarInstance = null;
        let currentSelectedNoteId = null;

        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        }

        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // --- MODAL NOTE PRIBADI ---
        function openModalNote(defaultDate = null) {
            const modal = document.getElementById('modal-tambah-note');
            const dateInput = document.getElementById('note-input-tanggal');
            const judulInput = document.getElementById('note-input-judul');
            const detailInput = document.getElementById('note-input-detail');
            const errorEl = document.getElementById('note-error-message');

            if (errorEl) {
                errorEl.textContent = '';
                errorEl.classList.add('hidden');
            }

            judulInput.value = '';
            detailInput.value = '';

            if (defaultDate) {
                dateInput.value = defaultDate;
            } else {
                const today = new Date();
                const yyyy = today.getFullYear();
                const mm = String(today.getMonth() + 1).padStart(2, '0');
                const dd = String(today.getDate()).padStart(2, '0');
                dateInput.value = `${yyyy}-${mm}-${dd}`;
            }

            modal.style.display = 'flex';
            modal.style.pointerEvents = '';
            setTimeout(() => judulInput.focus(), 100);
        }

        function closeModalNote() {
            const modal = document.getElementById('modal-tambah-note');
            if (modal) {
                modal.style.display = 'none';
                modal.style.pointerEvents = 'none';
            }
        }

        function submitTambahNote(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-submit-note');
            const errorEl = document.getElementById('note-error-message');
            const tanggal = document.getElementById('note-input-tanggal').value;
            const judul = document.getElementById('note-input-judul').value.trim();
            const detail = document.getElementById('note-input-detail').value.trim();
            const warnaRadio = document.querySelector('input[name="note-warna"]:checked');
            const warna = warnaRadio ? warnaRadio.value : 'slate';

            if (!judul) {
                if (errorEl) {
                    errorEl.textContent = 'Judul note wajib diisi.';
                    errorEl.classList.remove('hidden');
                }
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span>Menyimpan...</span>';

            fetch('/hrd/kalender/notes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({ tanggal, judul, detail, warna })
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(data => { throw new Error(data.message || 'Gagal menyimpan note'); });
                }
                return res.json();
            })
            .then(result => {
                closeModalNote();
                if (calendarInstance) {
                    calendarInstance.refetchEvents();
                }
            })
            .catch(err => {
                if (errorEl) {
                    errorEl.textContent = err.message;
                    errorEl.classList.remove('hidden');
                }
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<span>Simpan Note</span>';
            });
        }

        function openModalDetailNote(event) {
            const props = event.extendedProps || {};
            currentSelectedNoteId = props.note_id || null;

            const modal = document.getElementById('modal-detail-note');
            const titleEl = document.getElementById('detail-note-title');
            const dateEl = document.getElementById('detail-note-date');
            const contentEl = document.getElementById('detail-note-content');

            titleEl.textContent = props.judul || event.title;
            dateEl.textContent = props.tanggal_label || '-';
            contentEl.textContent = props.detail ? props.detail : 'Tidak ada keterangan tambahan.';

            modal.style.display = 'flex';
            modal.style.pointerEvents = '';
        }

        function closeModalDetailNote() {
            const modal = document.getElementById('modal-detail-note');
            if (modal) {
                modal.style.display = 'none';
                modal.style.pointerEvents = 'none';
            }
            currentSelectedNoteId = null;
        }

        function hapusCurrentNote() {
            if (!currentSelectedNoteId) return;

            if (!confirm('Apakah Anda yakin ingin menghapus catatan ini?')) {
                return;
            }

            const btn = document.getElementById('btn-delete-note');
            btn.disabled = true;
            btn.textContent = 'Menghapus...';

            fetch('/hrd/kalender/notes/' + currentSelectedNoteId, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('Gagal menghapus note');
                return res.json();
            })
            .then(() => {
                closeModalDetailNote();
                if (calendarInstance) {
                    calendarInstance.refetchEvents();
                }
            })
            .catch(err => {
                alert(err.message);
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Hapus`;
            });
        }

        document.getElementById('modal-tambah-note')?.addEventListener('click', function(e) {
            if (e.target === this) {
                e.stopPropagation();
                closeModalNote();
            }
        });

        document.getElementById('modal-detail-note')?.addEventListener('click', function(e) {
            if (e.target === this) {
                e.stopPropagation();
                closeModalDetailNote();
            }
        });

        function openModalEvent(event) {
            const props = event.extendedProps || {};
            const modal = document.getElementById('modal-event');
            const titleEl = document.getElementById('modal-title');
            const badgeEl = document.getElementById('modal-badge');
            const divisiBadgeEl = document.getElementById('modal-divisi-badge');
            const pengirimNameEl = document.getElementById('modal-pengirim-name');
            const pengirimSubEl = document.getElementById('modal-pengirim-sub');
            const staffNameEl = document.getElementById('modal-staff-name');
            const staffSubEl = document.getElementById('modal-staff-sub');
            const deadlineEl = document.getElementById('modal-deadline');
            const submissionStatusEl = document.getElementById('modal-submission-status');
            const deskripsiEl = document.getElementById('modal-deskripsi');

            titleEl.textContent = props.judul_murni || event.title;
            badgeEl.className = 'px-2.5 py-1 rounded-md text-xs font-semibold ' + (props.badge_class || 'bg-slate-100 text-slate-700');
            badgeEl.textContent = props.status_label || props.status || 'Event';

            divisiBadgeEl.textContent = props.divisi_nama || 'Divisi';

            pengirimNameEl.textContent = props.pengirim_nama || 'Kepala Divisi';
            pengirimSubEl.textContent = props.pengirim_jabatan || 'Kepala Divisi';

            staffNameEl.textContent = props.staff_nama || 'Staff';
            staffSubEl.textContent = (props.staff_jabatan || 'Staff') + (props.staff_id ? ' • ' + props.staff_id : '');

            deadlineEl.textContent = props.deadline_label || '-';

            if (props.has_submission) {
                submissionStatusEl.className = 'inline-block px-2.5 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-700';
                submissionStatusEl.textContent = 'Sudah Dikumpulkan' + (props.submission_date ? ` (${props.submission_date})` : '');
            } else {
                submissionStatusEl.className = 'inline-block px-2.5 py-0.5 rounded text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200';
                submissionStatusEl.textContent = 'Belum Mengumpulkan';
            }

            deskripsiEl.textContent = props.deskripsi || 'Tidak ada deskripsi.';

            modal.style.display = 'flex';
            modal.style.pointerEvents = '';
        }

        function closeModalEvent() {
            const modal = document.getElementById('modal-event');
            if (modal) {
                modal.style.display = 'none';
                modal.style.pointerEvents = 'none';
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModalEvent();
        });

        document.getElementById('modal-event')?.addEventListener('click', function(e) {
            if (e.target === this) {
                e.stopPropagation();
                closeModalEvent();
            }
        });

        function applyHrdFilters() {
            if (calendarInstance) {
                calendarInstance.refetchEvents();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const token = localStorage.getItem('staff_token') || sessionStorage.getItem('staff_token');

            calendarInstance = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                firstDay: 1, // Senin
                height: 'auto',
                dayMaxEvents: 3, // Maksimal 3 event tampil per hari
                moreLinkText: function(n) {
                    return '+' + n + ' lainnya';
                },
                defaultTimedEventDuration: '00:00:01', // Cegah deadline 23:59 nyebrang ke hari berikutnya
                nextDayThreshold: '24:00:00',
                eventDisplay: 'block',
                displayEventTime: true,
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    meridiem: false,
                    hour12: false
                },
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listMonth'
                },
                buttonText: {
                    today: 'Hari Ini',
                    month: 'Bulan',
                    week: 'Minggu',
                    list: 'Daftar'
                },
                events: function(fetchInfo, successCallback, failureCallback) {
                    const headers = { 'Accept': 'application/json' };
                    if (token) {
                        headers['Authorization'] = 'Bearer ' + token;
                    }

                    const divisi = document.getElementById('hrdDivisiFilter')?.value || '';
                    const staff = document.getElementById('hrdStaffFilter')?.value || '';
                    const status = document.getElementById('hrdStatusFilter')?.value || '';

                    const params = new URLSearchParams();
                    if (divisi) params.set('divisi', divisi);
                    if (staff) params.set('staff', staff);
                    if (status) params.set('status', status);

                    const query = params.toString();

                    Promise.all([
                        fetch('/hrd/kalender/events' + (query ? '?' + query : ''), { headers }).then(r => r.ok ? r.json() : { data: [] }),
                        fetch('/hrd/kalender/notes', { headers }).then(r => r.ok ? r.json() : { data: [] })
                    ])
                    .then(([eventsRes, notesRes]) => {
                        const allItems = [...(eventsRes.data || []), ...(notesRes.data || [])];
                        successCallback(allItems);
                    })
                    .catch(err => {
                        console.error('Error load events HRD:', err);
                        failureCallback(err);
                    });
                },
                dateClick: function(info) {
                    openModalNote(info.dateStr);
                },
                /* Tampilan Kustom Event untuk HRD: Menampilkan Divisi, Alur (Dari Kadiv ➔ Ke Staff), dan Judul Tugas */
                eventContent: function(arg) {
                    const props = arg.event.extendedProps || {};
                    const isList = arg.view.type.startsWith('list');
                    const el = document.createElement('div');

                    // Tampilan Khusus Note Pribadi
                    if (props.type === 'note') {
                        if (isList) {
                            el.className = 'flex items-center gap-2 py-0.5 w-full';
                            el.innerHTML = `
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-800 shrink-0">Catatan</span>
                                <span class="font-bold text-[12px] text-slate-800" title="${escapeHtml(arg.event.title)}">${escapeHtml(arg.event.title)}</span>
                            `;
                        } else {
                            el.className = 'w-full px-1.5 py-1 leading-snug flex items-center gap-1.5 overflow-hidden';
                            el.innerHTML = `
                                <svg class="w-3.5 h-3.5 shrink-0 opacity-80" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"/>
                                    <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"/>
                                </svg>
                                <span class="font-bold text-[11px] truncate text-slate-900" title="${escapeHtml(arg.event.title)}">${escapeHtml(arg.event.title)}</span>
                            `;
                        }
                        return { domNodes: [el] };
                    }

                    const divisiNama = props.divisi_nama || 'Divisi';
                    const pengirimNama = props.pengirim_nama || 'Kadiv';
                    const staffName = props.staff_nama || 'Staff';
                    const staffInitial = props.staff_initial || staffName.charAt(0).toUpperCase();
                    const title = props.judul_murni || arg.event.title;
                    const time = arg.timeText;
                    const dotColor = props.dot_color || '#004A65';

                    if (isList) {
                        // List view: badge divisi + alur penugasan (Dari ➔ Kepada) + judul
                        el.className = 'flex items-center gap-2 py-0.5 w-full flex-wrap md:flex-nowrap';
                        el.innerHTML = `
                            <span class="w-5 h-5 rounded-full text-white flex items-center justify-center text-[9px] font-extrabold shrink-0"
                                  style="background-color: ${escapeHtml(dotColor)};">
                                ${escapeHtml(staffInitial)}
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200 shrink-0">
                                ${escapeHtml(divisiNama)}
                            </span>
                            <span class="font-bold text-[11.5px] text-slate-800 shrink-0">${escapeHtml(pengirimNama)}</span>
                            <span class="text-blue-500 font-bold shrink-0">➔</span>
                            <span class="font-bold text-[11.5px] text-[#004A65] shrink-0">${escapeHtml(staffName)}</span>
                            <span class="text-slate-300 shrink-0 hidden md:inline">—</span>
                            <span class="text-[12px] text-slate-700 font-medium" title="${escapeHtml(title)}">${escapeHtml(title)}</span>
                        `;
                    } else {
                        // Month view: compact block dengan tag divisi, alur dari ➔ ke, dan judul
                        el.className = 'w-full px-1.5 py-1 leading-snug flex flex-col gap-0.5 overflow-hidden';
                        el.innerHTML = `
                            <div class="flex items-center justify-between gap-1 overflow-hidden">
                                <span class="inline-flex items-center gap-1 font-bold text-[10px] truncate max-w-[80%] text-slate-800">
                                    <span class="w-3.5 h-3.5 rounded-full text-white flex items-center justify-center text-[8px] font-extrabold shrink-0"
                                          style="background-color: ${escapeHtml(dotColor)};">
                                        ${escapeHtml(staffInitial)}
                                    </span>
                                    <span class="text-[9px] font-bold text-slate-600 bg-white/60 px-1 rounded truncate shrink-0">
                                        ${escapeHtml(divisiNama)}
                                    </span>
                                </span>
                                ${time ? `<span class="text-[9px] font-bold opacity-75 shrink-0">${time}</span>` : ''}
                            </div>
                            <div class="text-[10px] text-slate-700 font-semibold truncate leading-tight">
                                <span class="text-slate-600 font-bold">${escapeHtml(pengirimNama)}</span>
                                <span class="text-blue-600">➔</span>
                                <span class="font-bold text-slate-800">${escapeHtml(staffName)}</span>
                            </div>
                            <div class="font-medium text-[11px] truncate leading-tight text-slate-900" title="${escapeHtml(title)}">
                                ${escapeHtml(title)}
                            </div>
                        `;
                    }

                    return { domNodes: [el] };
                },
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    if (info.event.extendedProps?.type === 'note') {
                        openModalDetailNote(info.event);
                    } else {
                        openModalEvent(info.event);
                    }
                },
                eventDidMount: function(info) {
                    if (info.event.extendedProps?.type === 'note') {
                        info.el.setAttribute('title', `Catatan: ${info.event.title}`);
                    } else {
                        const props = info.event.extendedProps || {};
                        const div = props.divisi_nama || '';
                        const from = props.pengirim_nama || '';
                        const to = props.staff_nama || '';
                        info.el.setAttribute('title', `[${div}] Dari ${from} ke ${to}: ${props.judul_murni || info.event.title}`);
                    }
                }
            });

            calendarInstance.render();

            // ResizeObserver & listener agar saat sidebar dibuka/ditutup (w-64 vs w-20),
            // ukuran 7 kolom kalender langsung otomatis menyesuaikan secara proporsional
            if (window.ResizeObserver) {
                const resizeObserver = new ResizeObserver(() => {
                    if (calendarInstance) {
                        calendarInstance.updateSize();
                    }
                });
                resizeObserver.observe(calendarEl);
            }

            document.getElementById('btn-sidebar-toggle')?.addEventListener('click', function() {
                setTimeout(() => {
                    if (calendarInstance) {
                        calendarInstance.updateSize();
                    }
                }, 320);
            });

            // Event listener filter otomatis saat dropdown diganti
            const divisiSelect = document.getElementById('hrdDivisiFilter');
            const staffSelect = document.getElementById('hrdStaffFilter');
            const statusSelect = document.getElementById('hrdStatusFilter');

            divisiSelect?.addEventListener('change', function() {
                const selectedDivisi = this.value;
                // Filter dropdown karyawan sesuai divisi yang dipilih (jika ada)
                if (staffSelect) {
                    const options = staffSelect.querySelectorAll('option');
                    options.forEach(opt => {
                        if (!opt.value) return; // 'Semua Karyawan' selalu tampil
                        const optDivisi = opt.getAttribute('data-divisi');
                        if (!selectedDivisi || optDivisi === selectedDivisi) {
                            opt.style.display = '';
                        } else {
                            opt.style.display = 'none';
                        }
                    });
                    // Jika staff yang terpilih tidak sesuai divisi baru, reset staff ke semua
                    const currentSelectedOpt = staffSelect.querySelector(`option[value="${staffSelect.value}"]`);
                    if (currentSelectedOpt && selectedDivisi && currentSelectedOpt.getAttribute('data-divisi') !== selectedDivisi) {
                        staffSelect.value = '';
                    }
                }
                applyHrdFilters();
            });

            staffSelect?.addEventListener('change', applyHrdFilters);
            statusSelect?.addEventListener('change', applyHrdFilters);
        });
    </script>

</body>

</html>
