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
        /* Fokus ring jelas untuk aksesibilitas (input, select, textarea, tombol, link) */
        button:focus-visible,
        a:focus-visible,
        input:focus-visible,
        select:focus-visible,
        textarea:focus-visible {
            outline: 2px solid #146C94;
            outline-offset: 2px;
        }
        /* Custom styling for FullCalendar to match Tailwind dashboard */
        :root {
            --fc-border-color: #EEF2F5;
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
            border: 1px solid #EEF2F5 !important;
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
        .fc-daygrid .fc-col-header-cell,
        .fc-daygrid-day {
            width: 14.2857% !important;
            max-width: 14.2857% !important;
        }
        /* TimeGrid week table & axis layout */
        .fc-timegrid-axis {
            width: 50px !important;
            min-width: 50px !important;
            max-width: 50px !important;
            text-align: center !important;
        }
        .fc-timegrid-axis-frame {
            justify-content: center !important;
            padding: 0 4px !important;
        }
        .fc-timegrid-slot-label-cushion {
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            color: #64748B !important;
        }
        .fc-timegrid-col {
            min-width: 0 !important;
        }
        .fc-scrollgrid-section-header,
        .fc-scrollgrid-section-header > th,
        .fc-scrollgrid-section-header > td,
        .fc-scroller-harness {
            padding: 0 !important;
            margin: 0 !important;
        }
        /* Semua sel dan garis menggunakan #EEF2F5 dan selalu rata ATAS */
        .fc-theme-standard th,
        .fc-theme-standard td,
        .fc-scrollgrid-sync-table td,
        .fc-daygrid-day {
            border-color: #EEF2F5 !important;
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
            color: #334155 !important;
            opacity: 0.4;
        }
        .fc-daygrid-day-events {
            margin-top: 2px !important;
            padding: 0 2px !important;
        }
        /* Header hari menyambung sempurna ke garis atas dan bawah */
        .fc-col-header-cell {
            background-color: #EAF4F8 !important;
            border-color: #EEF2F5 !important;
            border-top: none !important;
            padding: 0 !important;
            text-transform: capitalize;
        }
        .fc-scrollgrid-section-header th,
        .fc-scrollgrid-section-header td {
            border-bottom: 1px solid #EEF2F5 !important;
        }
        .fc-col-header-cell-cushion {
            display: inline-block !important;
            padding: 8px 4px !important;
            color: #475569 !important;
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
        /* Tombol calendar: putih sekunder dengan border tipis */
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
            background-color: #FFFFFF;
            border: 1px solid #CBD5E1;
            color: #334155;
            border-radius: 0.5rem;
            padding: 0.4rem 0.85rem;
            font-size: 0.8125rem;
            font-weight: 500;
            text-transform: capitalize;
            box-shadow: none !important;
            transition: all 0.1s ease-out;
        }
        .fc .fc-button-primary:hover:not(:disabled) {
            background-color: #F8FAFC;
            border-color: #94A3B8;
            color: #1E293B;
        }
        /* Segmen aktif (pandangan aktif) */
        .fc .fc-button-primary:not(:disabled).fc-button-active,
        .fc .fc-button-primary:not(:disabled):active {
            background-color: #146C94;
            border-color: #146C94;
            color: #FFFFFF;
        }
        /* Grup tombol menyatu: < > dan pemilih pandangan */
        .fc .fc-button-group {
            display: inline-flex;
        }
        .fc .fc-button-group > .fc-button {
            margin-left: -1px;
        }
        .fc .fc-button-group > .fc-button:first-child {
            margin-left: 0;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
        .fc .fc-button-group > .fc-button:not(:first-child):not(:last-child) {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
        .fc .fc-button-group > .fc-button:last-child {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
        /* Tombol Hari Ini selalu tampil putih sekunder */
        .fc .fc-today-button:not(:disabled).fc-button-active,
        .fc .fc-today-button:not(:disabled):active {
            background-color: #FFFFFF;
            border-color: #CBD5E1;
            color: #334155;
        }
        .fc .fc-today-button {
            margin-left: 0.5rem !important;
        }
        /* Panah navigasi (< >) berwarna #146C94 */
        .fc .fc-prev-button,
        .fc .fc-next-button {
            color: #146C94;
        }
        .fc .fc-prev-button:hover:not(:disabled),
        .fc .fc-next-button:hover:not(:disabled) {
            color: #0F4F6D;
        }
        /* Hari ini: lingkaran teal penuh di belakang angka tanggal (tanpa blok kolom biru) */
        .fc-day-today {
            background-color: transparent !important;
        }
        .fc-day-today .fc-daygrid-day-number {
            background-color: #146C94 !important;
            color: #FFFFFF !important;
            border-radius: 9999px;
            width: 24px;
            height: 24px;
            padding: 0 !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
        }
        /* Event kotak di DayGrid (Bulan) */
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
        /* Event kotak di TimeGrid (Minggu) - tanpa margin rusak dan dengan overflow hidden ketat */
        .fc-timegrid-event {
            margin: 0 !important;
            border-radius: 6px !important;
            border-width: 1px !important;
            border-style: solid !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
            cursor: pointer;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.08) !important;
            transition: transform 0.1s ease, box-shadow 0.1s ease;
        }
        .fc-timegrid-event .fc-event-main {
            padding: 0 !important;
            width: 100% !important;
            height: 100% !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
        }
        .fc-timegrid-event-harness {
            overflow: visible !important;
        }
        .fc-timegrid .fc-event {
            margin: 0 !important;
        }
        /* Kartu tugas: isi pucat + 1px border tipis; aksen status via --tugas-accent */
        .tugas-card {
            border-width: 1px !important;
            border-style: solid !important;
        }
        .tugas-status-baru      { background-color: #DCEEFE !important; border-color: #93C5FD !important; --tugas-accent: #2563EB; }
        .tugas-status-berjalan  { background-color: #D9F5E5 !important; border-color: #86D9AE !important; --tugas-accent: #059669; }
        .tugas-status-menunggu  { background-color: #FEF0C3 !important; border-color: #F5C85C !important; --tugas-accent: #D97706; }
        .tugas-status-disetujui { background-color: #D5F3F0 !important; border-color: #7DD3C8 !important; --tugas-accent: #0D9488; }
        .tugas-status-terlambat { background-color: #FDE0E0 !important; border-color: #F5A3A3 !important; --tugas-accent: #DC2626; }

        /* Hover: isi sedikit lebih gelap (~4%), border tetap */
        .tugas-status-baru:hover      { background-color: #D3E4F4 !important; }
        .tugas-status-berjalan:hover  { background-color: #D0EBDC !important; }
        .tugas-status-menunggu:hover  { background-color: #F4E6BB !important; }
        .tugas-status-disetujui:hover { background-color: #CCE9E6 !important; }
        .tugas-status-terlambat:hover { background-color: #F3D7D7 !important; }
        /* Sembunyikan dot/bullet bawaan FullCalendar */
        .fc-daygrid-event-dot {
            display: none !important;
        }

        /* Popover dan link "+X lainnya" */
        .fc-more-link {
            font-size: 12px !important;
            font-weight: 500 !important;
            color: #146C94 !important;
            background: none !important;
            padding: 0 !important;
            margin: 2px 0 1px 4px !important;
            text-decoration: none !important;
            display: inline-block !important;
        }
        .fc-more-link:hover {
            color: #105A78 !important;
            text-decoration: underline !important;
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

        /* ===== Layout halaman: hanya baris minggu yang scroll ===== */
        /* Halaman = tinggi viewport - topbar (h-16 = 4rem) */
        .calendar-page {
            height: calc(100vh - 4rem);
            height: calc(100dvh - 4rem);
            min-height: 0;
            overflow: hidden;
        }
        /* Breadcrumb statis: 16px atas, 8px bawah (scoped ke halaman kalender) */
        .calendar-breadcrumb {
            position: static !important;
            flex: none;
        }
        .calendar-breadcrumb > div {
            padding-top: 16px !important;
            padding-bottom: 8px !important;
        }
        /* Kartu kalender mengisi sisa tinggi */
        .calendar-card {
            display: flex;
            flex-direction: column;
            min-height: 0;
            overflow: hidden;
        }
        #calendar {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            overflow: hidden;
        }
        /* Toolbar (judul bulan, navigasi, view switcher) di luar area scroll */
        #calendar .fc-toolbar {
            flex: 0 0 auto;
            background-color: #FFFFFF;
        }
        /* Area view mengisi sisa tinggi kartu */
        #calendar .fc-view-harness {
            flex: 1 1 auto;
            min-height: 0;
            overflow: hidden;
        }
        /* Header hari (Sen, Sel, ...) tetap di luar scroller & latar opak */
        #calendar .fc-scrollgrid-section-header,
        #calendar .fc-scrollgrid-section-header > th {
            background-color: #EAF4F8;
        }
        #calendar .fc-scrollgrid-section-header .fc-scroller {
            overflow: hidden !important;
            scrollbar-gutter: stable;
            background-color: #EAF4F8;
        }
        /* Hanya baris minggu yang bisa di-scroll */
        #calendar .fc-scrollgrid-section-body .fc-scroller {
            min-height: 0;
            overflow-x: hidden !important;
            overflow-y: auto !important;
            scrollbar-gutter: stable;
            padding-top: 0 !important;
        }
        /* Target scroll minggu "Hari ini" */
        #calendar .fc-daygrid-body tr,
        #calendar .fc-day-today {
            scroll-margin-top: 8px;
        }
    </style>
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">

    @include('component_hrd.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        @include('component.topbar')

        <div class="calendar-page flex-1 min-h-0 flex flex-col overflow-hidden">

            <div class="calendar-breadcrumb flex-none">
                @include('component.breadcrumbs', [
                    'parentText' => 'Dashboard',
                    'parentUrl' => url('/hrd/dashboard'),
                    'currentPage' => 'Kalender',
                ])
            </div>

            <main class="flex-1 min-h-0 flex flex-col overflow-hidden p-8 pt-0 pb-8">

            <!-- Page Title -->
            <div class="flex-none flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
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
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md border border-[#93C5FD] bg-[#DCEEFE] text-[#172B36] font-medium">
                        <span class="w-2 h-2 rounded-full bg-[#2563EB]"></span> Baru
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md border border-[#86D9AE] bg-[#D9F5E5] text-[#172B36] font-medium">
                        <span class="w-2 h-2 rounded-full bg-[#059669]"></span> Berjalan
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md border border-[#F5C85C] bg-[#FEF0C3] text-[#172B36] font-medium">
                        <span class="w-2 h-2 rounded-full bg-[#D97706]"></span> Menunggu persetujuan
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md border border-[#7DD3C8] bg-[#D5F3F0] text-[#172B36] font-medium">
                        <span class="w-2 h-2 rounded-full bg-[#0D9488]"></span> Disetujui
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md border border-[#F5A3A3] bg-[#FDE0E0] text-[#172B36] font-medium">
                        <span class="w-2 h-2 rounded-full bg-[#DC2626]"></span> Terlambat
                    </span>
                </div>
            </div>

            <!-- Toolbar Filter Khusus HRD: Divisi + Penerima + Status -->
            <div class="flex-none bg-white p-4 rounded-xl border border-slate-200 shadow-sm mb-5 flex flex-wrap items-center gap-3">
                <!-- Filter Divisi -->
                <div class="flex items-center gap-2">
                    <label for="hrdDivisiFilter" class="text-[13px] font-medium text-[#4B5563]">
                        <span>Divisi:</span>
                    </label>
                    <select id="hrdDivisiFilter" class="h-10 text-[13px] bg-white border border-slate-300 rounded-lg px-3 font-medium text-slate-700 outline-none focus:border-[#146C94]">
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
                    <label for="hrdStaffFilter" class="text-[13px] font-medium text-[#4B5563]">
                        <span>Penerima:</span>
                    </label>
                    <select id="hrdStaffFilter" class="h-10 text-[13px] bg-white border border-slate-300 rounded-lg px-3 font-medium text-slate-700 outline-none focus:border-[#146C94]">
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
                    <label for="hrdStatusFilter" class="text-[13px] font-medium text-[#4B5563]"><span>Status:</span></label>
                    <select id="hrdStatusFilter" class="h-10 text-[13px] bg-white border border-slate-300 rounded-lg px-3 font-medium text-slate-700 outline-none focus:border-[#146C94]">
                        <option value="">Semua Status</option>
                        <option value="baru">Baru</option>
                        <option value="berjalan">Berjalan</option>
                        <option value="menunggu di-acc">Menunggu persetujuan</option>
                        <option value="sudah di-acc">Disetujui</option>
                        <option value="terlambat">Terlambat</option>
                    </select>
                </div>
            </div>

            <!-- Calendar Card -->
            <div class="calendar-card flex-1 min-h-0 flex flex-col overflow-hidden bg-white p-6 pb-8 rounded-2xl shadow-sm border border-slate-200">
                <!-- Tombol tambah note -->
                <div class="flex-none flex items-center justify-end mb-3">
                    <button type="button" onclick="openModalNote()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#0097B2] hover:bg-[#008199] text-white text-xs font-bold rounded-lg transition shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah catatan
                    </button>
                </div>
                <div id="calendar" class="flex-1 min-h-0 flex flex-col overflow-hidden"></div>
            </div>

        </main>

        </div>

    </div>

    <!-- Modal Detail Tugas (HRD) -->
    <div id="modal-event" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4" style="display:none; pointer-events:none;">
        <div data-modal-panel
             class="bg-white rounded-xl shadow-xl border border-slate-200 w-full max-w-[520px] overflow-hidden flex flex-col max-h-[90vh]"
             onclick="event.stopPropagation()">
            <!-- Header Modal -->
            <div class="shrink-0 px-6 py-4 border-b border-slate-200 flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 id="modal-title" class="text-xl font-semibold text-[#172B36] leading-snug break-words">Judul Tugas</h2>
                    <div class="flex flex-wrap items-center gap-2 mt-1.5">
                        <span id="modal-badge" class="px-2.5 py-0.5 rounded-md text-xs font-semibold">Status</span>
                        <span id="modal-divisi" class="text-xs text-[#4B5563]"></span>
                    </div>
                </div>
                <button type="button" onclick="closeModalEvent()" class="shrink-0 text-slate-400 hover:text-slate-600 transition p-1 rounded-lg hover:bg-slate-200/60" aria-label="Tutup">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Body Modal -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4">
                <div>
                    <div class="text-[13px] font-medium text-[#4B5563]">Dari</div>
                    <div id="modal-pengirim" class="text-sm text-[#172B36]">-</div>
                </div>

                <div>
                    <div class="text-[13px] font-medium text-[#4B5563]">Kepada</div>
                    <div id="modal-staff" class="text-sm text-[#172B36]">-</div>
                </div>

                <div>
                    <div class="text-[13px] font-medium text-[#4B5563]">Tenggat</div>
                    <div id="modal-deadline" class="text-sm text-[#172B36]">-</div>
                </div>

                <div>
                    <div class="text-[13px] font-medium text-[#4B5563]">Pengumpulan</div>
                    <span id="modal-submission-status" class="inline-block px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-[#4B5563]">-</span>
                </div>

                <div>
                    <div class="text-[13px] font-medium text-[#4B5563]">Deskripsi</div>
                    <div id="modal-deskripsi" class="text-sm text-[#172B36] whitespace-pre-line leading-relaxed max-h-40 overflow-y-auto">-</div>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="shrink-0 px-6 py-4 border-t border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModalEvent()"
                        class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-100 transition">
                    Tutup
                </button>
                <a id="modal-detail-btn" href="#"
                   class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-[#146C94] rounded-lg hover:bg-[#105A78] transition">
                    <span>Lihat detail</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Animasi Modal Calendar -->
    <style>
        @keyframes modal-backdrop-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes modal-backdrop-out {
            from { opacity: 1; }
            to { opacity: 0; }
        }

        @keyframes modal-panel-in {
            from { opacity: 0; transform: translateY(16px) scale(0.96); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes modal-panel-out {
            from { opacity: 1; transform: translateY(0) scale(1); }
            to { opacity: 0; transform: translateY(12px) scale(0.96); }
        }

        #modal-event.modal-fade-in,
        #modal-tambah-note.modal-fade-in,
        #modal-detail-note.modal-fade-in { animation: modal-backdrop-in 0.2s ease-out both; }

        #modal-event.modal-fade-out,
        #modal-tambah-note.modal-fade-out,
        #modal-detail-note.modal-fade-out { animation: modal-backdrop-out 0.18s ease-in both; }

        #modal-event [data-modal-panel].modal-pop-in,
        #modal-tambah-note [data-modal-panel].modal-pop-in,
        #modal-detail-note [data-modal-panel].modal-pop-in { animation: modal-panel-in 0.25s cubic-bezier(0.16, 1, 0.3, 1) both; }

        #modal-event [data-modal-panel].modal-pop-out,
        #modal-tambah-note [data-modal-panel].modal-pop-out,
        #modal-detail-note [data-modal-panel].modal-pop-out { animation: modal-panel-out 0.18s ease-in both; }

        @media (prefers-reduced-motion: reduce) {
            .modal-fade-in,
            .modal-fade-out,
            [data-modal-panel].modal-pop-in,
            [data-modal-panel].modal-pop-out {
                animation-duration: 0.01ms;
            }
        }
    </style>

    <!-- Modal Tambah Catatan -->
    <div id="modal-tambah-note" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4" style="display:none; pointer-events:none;">
        <div data-modal-panel
             class="bg-white rounded-xl shadow-xl border border-slate-200 w-full max-w-[520px] overflow-hidden flex flex-col max-h-[90vh]"
             onclick="event.stopPropagation()">
            <!-- Header Modal -->
            <div class="shrink-0 px-6 py-4 border-b border-slate-200 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-blue-100 text-[#004A65]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </span>
                    <h3 class="text-xl font-semibold text-[#172B36]">Tambah catatan</h3>
                </div>
                <button type="button" onclick="closeModalNote()" class="shrink-0 text-slate-400 hover:text-slate-600 transition p-1 rounded-lg hover:bg-slate-200/60" aria-label="Tutup">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Form Body -->
            <form id="form-tambah-note" onsubmit="submitTambahNote(event)" class="flex flex-col flex-1 min-h-0">
                <div class="flex-1 overflow-y-auto p-6 space-y-4">
                    <div>
                        <label for="note-input-tanggal" class="block text-[13px] font-medium text-[#4B5563] mb-1.5">Tanggal <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="date" id="note-input-tanggal" required onchange="updateTanggalLengkap()"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            <div id="note-input-tanggal-display" class="w-full h-10 rounded-lg border border-slate-300 px-3 text-sm text-[#172B36] flex items-center bg-white pointer-events-none">-</div>
                        </div>
                    </div>

                    <div>
                        <label for="note-input-judul" class="block text-[13px] font-medium text-[#4B5563] mb-1.5">Judul catatan <span class="text-red-500">*</span></label>
                        <input type="text" id="note-input-judul" required placeholder="Contoh: Evaluasi mingguan"
                            class="w-full h-10 rounded-lg border border-slate-300 px-3 text-sm text-[#172B36] outline-none focus:border-[#146C94]">
                    </div>

                    <div>
                        <label for="note-input-detail" class="block text-[13px] font-medium text-[#4B5563] mb-1.5">Keterangan (opsional)</label>
                        <textarea id="note-input-detail" rows="3" placeholder="Catatan detail tambahan..."
                            class="w-full resize-none rounded-lg border border-slate-300 p-3 text-sm text-[#172B36] outline-none focus:border-[#146C94]"></textarea>
                    </div>

                    <div>
                        <span class="block text-[13px] font-medium text-[#4B5563] mb-2">Warna</span>
                        <div class="flex items-center gap-3" id="note-color-picker">
                            <label class="cursor-pointer" title="Abu-abu">
                                <input type="radio" name="note-warna" value="slate" class="peer sr-only" checked aria-label="Abu-abu">
                                <span class="block w-6 h-6 rounded-full bg-[#94A3B8] peer-checked:ring-2 peer-checked:ring-[#146C94]"></span>
                            </label>
                            <label class="cursor-pointer" title="Biru">
                                <input type="radio" name="note-warna" value="blue" class="peer sr-only" aria-label="Biru">
                                <span class="block w-6 h-6 rounded-full bg-[#3B82F6] peer-checked:ring-2 peer-checked:ring-[#146C94]"></span>
                            </label>
                            <label class="cursor-pointer" title="Hijau">
                                <input type="radio" name="note-warna" value="green" class="peer sr-only" aria-label="Hijau">
                                <span class="block w-6 h-6 rounded-full bg-[#10B981] peer-checked:ring-2 peer-checked:ring-[#146C94]"></span>
                            </label>
                            <label class="cursor-pointer" title="Kuning">
                                <input type="radio" name="note-warna" value="amber" class="peer sr-only" aria-label="Kuning">
                                <span class="block w-6 h-6 rounded-full bg-[#F59E0B] peer-checked:ring-2 peer-checked:ring-[#146C94]"></span>
                            </label>
                            <label class="cursor-pointer" title="Merah">
                                <input type="radio" name="note-warna" value="red" class="peer sr-only" aria-label="Merah">
                                <span class="block w-6 h-6 rounded-full bg-[#EF4444] peer-checked:ring-2 peer-checked:ring-[#146C94]"></span>
                            </label>
                        </div>
                    </div>

                    <div id="note-error-message" class="text-xs text-red-600 hidden"></div>
                </div>

                <!-- Footer Modal -->
                <div class="shrink-0 flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-200">
                    <button type="button" onclick="closeModalNote()"
                        class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-100 transition">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit-note"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#146C94] hover:bg-[#105A78] text-white text-sm font-medium rounded-lg transition shadow-sm">
                        <span>Simpan catatan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Detail Note -->
    <div id="modal-detail-note" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4" style="display:none; pointer-events:none;">
        <div data-modal-panel
             class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-md overflow-hidden flex flex-col max-h-[calc(100vh-2rem)]"
             onclick="event.stopPropagation()">
            <!-- Header Modal -->
            <div class="shrink-0 px-5 py-3.5 sm:px-6 sm:py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
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
            <div class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-4">
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
            <div class="shrink-0 px-5 py-3.5 sm:px-6 sm:py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
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

        // Label status berbahasa rapi + kelas kartu kalender sesuai status
        const STATUS_LABELS = {
            'baru': 'Baru',
            'berjalan': 'Berjalan',
            'menunggu di-acc': 'Menunggu persetujuan',
            'sudah di-acc': 'Disetujui',
            'terlambat': 'Terlambat',
        };

        function tugasStatusLabel(status) {
            if (!status) return 'Event';
            const raw = String(status);
            return STATUS_LABELS[raw.toLowerCase()] || (raw.charAt(0).toUpperCase() + raw.slice(1));
        }

        function tugasStatusClass(status) {
            switch (String(status || '').toLowerCase()) {
                case 'berjalan': return 'tugas-status-berjalan';
                case 'menunggu di-acc': return 'tugas-status-menunggu';
                case 'sudah di-acc': return 'tugas-status-disetujui';
                case 'terlambat': return 'tugas-status-terlambat';
                default: return 'tugas-status-baru';
            }
        }

        function formatTanggalIndonesia(iso) {
            if (!iso) return '-';
            const parts = String(iso).split('-');
            if (parts.length !== 3) return iso;
            const d = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
            if (isNaN(d.getTime())) return iso;
            return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }).format(d);
        }

        function updateTanggalLengkap() {
            const raw = document.getElementById('note-input-tanggal')?.value || '';
            const target = document.getElementById('note-input-tanggal-display');
            if (target) target.textContent = raw ? formatTanggalIndonesia(raw) : '-';
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

            updateTanggalLengkap();

            modal.style.display = 'flex';
            modal.style.pointerEvents = '';
            animateModalIn(modal);
            setTimeout(() => judulInput.focus(), 100);
        }

        function closeModalNote() {
            const modal = document.getElementById('modal-tambah-note');
            if (!modal || modal.style.display === 'none') return;
            animateModalOut(modal);
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
                btn.innerHTML = '<span>Simpan catatan</span>';
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
            animateModalIn(modal);
        }

        function closeModalDetailNote() {
            const modal = document.getElementById('modal-detail-note');
            if (!modal || modal.style.display === 'none') {
                currentSelectedNoteId = null;
                return;
            }
            animateModalOut(modal, () => { currentSelectedNoteId = null; });
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

        function animateModalIn(modal) {
            const panel = modal.querySelector('[data-modal-panel]');

            modal.classList.remove('modal-fade-out');
            void modal.offsetWidth;
            modal.classList.add('modal-fade-in');

            panel.classList.remove('modal-pop-out');
            void panel.offsetWidth;
            panel.classList.add('modal-pop-in');
        }

        function animateModalOut(modal, onHidden) {
            const panel = modal.querySelector('[data-modal-panel]');

            modal.classList.remove('modal-fade-in');
            void modal.offsetWidth;
            modal.classList.add('modal-fade-out');

            panel.classList.remove('modal-pop-in');
            void panel.offsetWidth;
            panel.classList.add('modal-pop-out');

            setTimeout(() => {
                if (!panel.classList.contains('modal-pop-out')) return;
                modal.style.display = 'none';
                modal.style.pointerEvents = 'none';
                modal.classList.remove('modal-fade-out');
                panel.classList.remove('modal-pop-out');
                if (onHidden) onHidden();
            }, 180);
        }

        function openModalEvent(event) {
            const props = event.extendedProps || {};
            const modal = document.getElementById('modal-event');
            const titleEl = document.getElementById('modal-title');
            const badgeEl = document.getElementById('modal-badge');
            const divisiEl = document.getElementById('modal-divisi');
            const pengirimEl = document.getElementById('modal-pengirim');
            const staffEl = document.getElementById('modal-staff');
            const deadlineEl = document.getElementById('modal-deadline');
            const submissionStatusEl = document.getElementById('modal-submission-status');
            const deskripsiEl = document.getElementById('modal-deskripsi');
            const detailBtn = document.getElementById('modal-detail-btn');

            titleEl.textContent = props.judul_murni || event.title;
            badgeEl.className = 'px-2.5 py-0.5 rounded-md text-xs font-semibold ' + (props.badge_class || 'bg-slate-100 text-slate-700');
            badgeEl.textContent = props.status ? tugasStatusLabel(props.status) : (props.status_label || 'Event');
            divisiEl.textContent = props.divisi_nama || '';

            const pengirimParts = [props.pengirim_nama, props.pengirim_jabatan].filter(Boolean);
            pengirimEl.textContent = pengirimParts.length ? pengirimParts.join(', ') : '-';

            const staffParts = [props.staff_nama, props.staff_jabatan, props.staff_id].filter(Boolean);
            staffEl.textContent = staffParts.length ? staffParts.join(', ') : '-';

            deadlineEl.textContent = props.deadline_label || '-';

            if (props.has_submission) {
                submissionStatusEl.className = 'inline-block px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-700';
                submissionStatusEl.textContent = 'Sudah dikumpulkan' + (props.submission_date ? ` (${props.submission_date})` : '');
            } else {
                submissionStatusEl.className = 'inline-block px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-[#4B5563]';
                submissionStatusEl.textContent = 'Belum dikumpulkan';
            }

            deskripsiEl.textContent = props.deskripsi || 'Tidak ada deskripsi.';

            if (props.detail_url) {
                detailBtn.href = props.detail_url;
                detailBtn.style.display = 'inline-flex';
            } else {
                detailBtn.style.display = 'none';
            }

            modal.style.display = 'flex';
            modal.style.pointerEvents = '';
            animateModalIn(modal);
        }

        function closeModalEvent() {
            const modal = document.getElementById('modal-event');
            if (!modal || modal.style.display === 'none') return;
            animateModalOut(modal);
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModalEvent();
                closeModalNote();
                closeModalDetailNote();
            }
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

        // Scroll minggu berjalan ke dalam area scroll kalender (dipakai tombol "Hari Ini")
        let pendingTodayScroll = false;

        function scrollToTodayWeek() {
            const scroller = document.querySelector('#calendar .fc-scrollgrid-section-body .fc-scroller');
            const todayCell = document.querySelector('#calendar .fc-day-today');
            const weekRow = todayCell ? todayCell.closest('tr') : null;
            if (!scroller || !weekRow) return;

            const scrollerRect = scroller.getBoundingClientRect();
            const rowRect = weekRow.getBoundingClientRect();
            const offset = 8; // scroll-margin-top

            if (rowRect.top < scrollerRect.top + offset || rowRect.bottom > scrollerRect.bottom) {
                scroller.scrollTop += (rowRect.top - scrollerRect.top) - offset;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const token = localStorage.getItem('staff_token') || sessionStorage.getItem('staff_token');

            calendarInstance = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                firstDay: 1, // Senin
                height: '100%',
                expandRows: true,
                dayMaxEvents: 3, // Maksimal 3 event tampil per hari
                moreLinkText: function(n) {
                    return '+' + n + ' lainnya';
                },
                defaultTimedEventDuration: '00:45:00',
                slotMinTime: '07:00:00',
                slotMaxTime: '21:00:00',
                slotDuration: '00:30:00',
                slotEventOverlap: false, // Event di jam yang sama TIDAK saling tumpang tindih
                eventMinHeight: 48, // Card tidak gepeng di week view
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
                datesSet: function() {
                    if (pendingTodayScroll) {
                        pendingTodayScroll = false;
                        requestAnimationFrame(function() {
                            requestAnimationFrame(scrollToTodayWeek);
                        });
                    }
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
                    params.set('start', fetchInfo.start.toISOString());
                    params.set('end', fetchInfo.end.toISOString());

                    fetch('/hrd/kalender/data?' + params.toString(), { headers })
                    .then(r => r.ok ? r.json() : { data: [] })
                    .then(result => {
                        successCallback(result.data || []);
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
                    const isTimeGrid = arg.view.type.startsWith('timeGrid');
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
                    const dotColor = props.dot_color || '#004A65';

                    // Alur penugasan: pemberi tugas -> penerima tugas
                    const flowText = `${pengirimNama} → ${staffName}`;

                    // Tampilkan jam mulai (HH:MM) kecuali 17:00
                    const startAt = arg.event.start;
                    let time = '';
                    if (startAt && !arg.event.allDay) {
                        const pad = (n) => String(n).padStart(2, '0');
                        const hh = pad(startAt.getHours());
                        const mm = pad(startAt.getMinutes());
                        if (!(hh === '17' && mm === '00')) {
                            time = `${hh}:${mm}`;
                        }
                    }

                    if (isList) {
                        // List view: badge divisi + alur penugasan (Dari -> Kepada) + judul
                        el.className = 'flex items-center gap-2 py-0.5 w-full flex-wrap md:flex-nowrap min-w-0';
                        el.innerHTML = `
                            <span class="w-5 h-5 rounded-full text-white flex items-center justify-center text-[9px] font-extrabold shrink-0"
                                  style="background-color: ${escapeHtml(dotColor)};">
                                ${escapeHtml(staffInitial)}
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold text-slate-700 shrink-0">
                                ${escapeHtml(divisiNama)}
                            </span>
                            <span class="font-bold text-[11.5px] text-slate-800 shrink-0">${escapeHtml(pengirimNama)}</span>
                            <span class="text-blue-500 font-bold shrink-0 mx-0.5">→</span>
                            <span class="font-bold text-[11.5px] text-[#004A65] shrink-0">${escapeHtml(staffName)}</span>
                            <span class="text-slate-300 shrink-0 hidden md:inline">•</span>
                            <span class="text-[12px] text-slate-700 font-medium truncate min-w-0" title="${escapeHtml(title)}">${escapeHtml(title)}</span>
                        `;
                    } else if (isTimeGrid) {
                        // Minggu view: avatar + divisi + jam, alur penugasan, lalu judul
                        el.className = 'w-full h-full px-1.5 py-1 leading-snug flex flex-col justify-start gap-0.5 overflow-hidden min-w-0';
                        el.innerHTML = `
                            <div class="flex items-center justify-between gap-1 min-w-0 overflow-hidden">
                                <span class="inline-flex items-center gap-1 font-bold text-[10px] min-w-0 overflow-hidden text-[#172B36]">
                                    <span class="w-3.5 h-3.5 rounded-full text-white flex items-center justify-center text-[8px] font-extrabold shrink-0"
                                          style="background-color: var(--tugas-accent, ${escapeHtml(dotColor)});">
                                        ${escapeHtml(staffInitial)}
                                    </span>
                                    <span class="text-[9px] font-bold text-[#4B5563] px-1 rounded truncate min-w-0" title="${escapeHtml(divisiNama)}">
                                        ${escapeHtml(divisiNama)}
                                    </span>
                                </span>
                                ${time ? `<span class="text-[9px] font-bold text-[#4B5563] shrink-0 ml-auto whitespace-nowrap">${escapeHtml(time)}</span>` : ''}
                            </div>
                            <div class="text-[10px] text-[#4B5563] font-semibold truncate min-w-0 leading-tight">
                                ${escapeHtml(flowText)}
                            </div>
                            <div class="font-medium text-[11px] truncate min-w-0 leading-tight text-[#172B36]">
                                ${escapeHtml(title)}
                            </div>
                        `;
                    } else {
                        // Bulan view: avatar + divisi + jam, alur penugasan, lalu judul
                        el.className = 'w-full px-1.5 py-1 leading-snug flex flex-col gap-0.5 overflow-hidden min-w-0';
                        el.innerHTML = `
                            <div class="flex items-center justify-between gap-1 overflow-hidden min-w-0">
                                <span class="inline-flex items-center gap-1 font-bold text-[10px] truncate max-w-[80%] text-[#172B36] min-w-0">
                                    <span class="w-3.5 h-3.5 rounded-full text-white flex items-center justify-center text-[8px] font-extrabold shrink-0"
                                          style="background-color: var(--tugas-accent, ${escapeHtml(dotColor)});">
                                        ${escapeHtml(staffInitial)}
                                    </span>
                                    <span class="text-[9px] font-bold text-[#4B5563] px-1 rounded truncate min-w-0" title="${escapeHtml(divisiNama)}">
                                        ${escapeHtml(divisiNama)}
                                    </span>
                                </span>
                                ${time ? `<span class="text-[9px] font-bold text-[#4B5563] shrink-0 ml-auto">${escapeHtml(time)}</span>` : ''}
                            </div>
                            <div class="text-[10px] text-[#4B5563] font-semibold truncate leading-tight min-w-0">
                                ${escapeHtml(flowText)}
                            </div>
                            <div class="font-medium text-[11px] truncate leading-tight text-[#172B36] min-w-0">
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
                    const props = info.event.extendedProps || {};
                    const isList = info.view.type.startsWith('list');

                    if (props.type === 'note') {
                        info.el.setAttribute('title', `Catatan: ${info.event.title}`);
                        return;
                    }

                    // Tooltip: judul lengkap + divisi
                    const judulTooltip = props.judul_murni || info.event.title;
                    const divisiTooltip = props.divisi_nama || '';
                    info.el.setAttribute('title', divisiTooltip ? `${judulTooltip} — ${divisiTooltip}` : judulTooltip);

                    // Kartu status di grid (bulan & minggu); list view tetap polos
                    if (!isList) {
                        info.el.style.backgroundColor = '';
                        info.el.style.borderColor = '';
                        info.el.style.color = '';
                        info.el.classList.add('tugas-card', tugasStatusClass(props.status));
                    }
                }
            });

            calendarInstance.render();

            // Tombol "Hari Ini": scroll minggu berjalan ke dalam area scroll kalender
            calendarEl.addEventListener('click', function(event) {
                if (!event.target.closest('.fc-today-button')) return;

                pendingTodayScroll = true;
                scrollToTodayWeek();
                setTimeout(scrollToTodayWeek, 50);
                setTimeout(scrollToTodayWeek, 150);
                setTimeout(function() {
                    pendingTodayScroll = false;
                    scrollToTodayWeek();
                }, 350);
            });

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
                }, 160);
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
