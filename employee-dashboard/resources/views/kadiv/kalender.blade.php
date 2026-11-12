<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kalender Kegiatan Divisi - PT Silindo</title>

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
            --fc-border-color: #E2E8EC;
        }
        .fc {
            font-family: inherit;
            border: none !important;
        }
        /* Toolbar navigasi di luar tabel */
        .fc .fc-toolbar {
            border: none !important;
            margin-bottom: 0.5rem !important;
        }
        /* Tabel FullCalendar kotak tegas 90 derajat & selalu 100% fit container (7 kolom sama rata) */
        .fc-scrollgrid,
        .fc-theme-standard .fc-scrollgrid {
            border: 1px solid #E2E8EC !important;
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
        /* Semua sel dan garis menggunakan #E2E8EC dan selalu rata ATAS */
        .fc-theme-standard th,
        .fc-theme-standard td,
        .fc-scrollgrid-sync-table td,
        .fc-daygrid-day {
            border-color: #E2E8EC !important;
            vertical-align: top !important;
        }
        .fc-daygrid-day-frame {
            vertical-align: top !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: flex-start !important;
            min-height: 96px !important;
        }
        .fc-daygrid-day-top {
            display: flex !important;
            flex-direction: row-reverse !important;
            justify-content: flex-start !important;
            align-items: center !important;
            padding: 3px 6px 0 !important;
        }
        .fc-daygrid-day-number {
            color: #334155 !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            padding: 0 !important;
            text-decoration: none !important;
            line-height: 1 !important;
        }
        .fc-day-other .fc-daygrid-day-number {
            color: #334155 !important;
            opacity: 0.4;
        }
        .fc-daygrid-day-events {
            margin-top: 0 !important;
            padding: 1px 4px 4px !important;
        }
        /* Header hari menyambung sempurna ke garis atas dan bawah */
        .fc-col-header-cell {
            background-color: #EAF4F8 !important;
            border-color: #E2E8EC !important;
            border-top: none !important;
            padding: 0 !important;
            text-transform: capitalize;
        }
        .fc-scrollgrid-section-header th,
        .fc-scrollgrid-section-header td {
            border-bottom: 1px solid #E2E8EC !important;
        }
        .fc-col-header-cell-cushion {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 32px !important;
            padding: 0 4px !important;
            color: #475569 !important;
            font-weight: 600 !important;
            font-size: 0.8125rem !important;
            line-height: 1.25rem !important;
            text-decoration: none !important;
        }
        /* ===== WEEK VIEW HEADER ===== */
        #calendar .fc-timegrid .fc-col-header-cell {
            height: 56px !important;
            background-color: #EAF4F8 !important;
        }
        #calendar .fc-timegrid .fc-col-header-cell-cushion {
            height: 56px !important;
            padding: 0 !important;
        }
        .cal-week-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1px;
            line-height: 1;
        }
        .cal-week-header-weekday {
            font-size: 12px;
            font-weight: 500;
            line-height: 1.2;
            color: #4B5563;
        }
        .cal-week-header-weekday.is-today {
            color: #146C94;
        }
        .cal-week-header-day {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 9999px;
            line-height: 1;
        }
        .cal-week-header-day.is-today {
            background-color: #146C94;
        }
        .cal-week-header-daynum {
            font-size: 16px;
            font-weight: 500;
            line-height: 1;
            color: #172B36;
        }
        .cal-week-header-day.is-today .cal-week-header-daynum {
            color: #FFFFFF;
        }
        #calendar .fc-timegrid .fc-scroller {
            scrollbar-gutter: stable;
        }
        .fc .fc-toolbar-title {
            font-size: 1.25rem;
            font-weight: 600;
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
            width: 28px;
            height: 28px;
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
            padding: 0 3px !important;
            margin: 1px 2px !important;
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
        /* Kartu tugas: isi kontras tinggi + border tegas; aksen status via --tugas-accent */
        .tugas-card {
            border-width: 1px !important;
            border-style: solid !important;
        }
        .tugas-status-baru      { background-color: #C0E7FF !important; border-color: #3B82F6 !important; color: #1D4ED8 !important; --tugas-accent: #1D4ED8; }
        .tugas-status-berjalan  { background-color: #D1FAE5 !important; border-color: #10B981 !important; color: #047857 !important; --tugas-accent: #047857; }
        .tugas-status-menunggu  { background-color: #FEF3C7 !important; border-color: #F59E0B !important; color: #B45309 !important; --tugas-accent: #B45309; }
        .tugas-status-disetujui { background-color: #047857 !important; border-color: #065F46 !important; color: #FFFFFF !important; --tugas-accent: #D1FAE5; }
        .tugas-status-terlambat { background-color: #FEE2E2 !important; border-color: #EF4444 !important; color: #B91C1C !important; --tugas-accent: #B91C1C; }
        .tugas-status-izin      { background-color: #EDE9FE !important; border-color: #8B5CF6 !important; color: #6D28D9 !important; --tugas-accent: #6D28D9; }

        /* Hover: isi sedikit lebih gelap, border tetap */
        .tugas-status-baru:hover      { background-color: #A9DAFF !important; }
        .tugas-status-berjalan:hover  { background-color: #A7F3D0 !important; }
        .tugas-status-menunggu:hover  { background-color: #FDE68A !important; }
        .tugas-status-disetujui:hover { background-color: #065F46 !important; }
        .tugas-status-terlambat:hover { background-color: #FECACA !important; }
        .tugas-status-izin:hover      { background-color: #DDD6FE !important; }
        /* Sembunyikan dot/bullet bawaan FullCalendar */
        .fc-daygrid-event-dot {
            display: none !important;
        }

        /* Popover "+X lainnya": header tetap, hanya daftar yang scroll */
        .fc-more-link {
            font-size: 11px !important;
            line-height: 1.2 !important;
            font-weight: 500 !important;
            color: #146C94 !important;
            background: none !important;
            padding: 0 !important;
            margin: 0 0 0 4px !important;
            text-decoration: none !important;
            display: inline-block !important;
        }
        .fc-more-link:hover {
            color: #105A78 !important;
            text-decoration: underline !important;
        }
        .fc .fc-popover {
            display: flex;
            flex-direction: column;
            width: 360px !important;
            max-width: calc(100vw - 16px) !important;
            max-height: calc(100vh - 16px) !important;
            background-color: #FFFFFF !important;
            border: 1px solid #E2E8EC !important;
            border-radius: 12px !important;
            box-shadow: 0 12px 28px -8px rgba(15, 23, 42, 0.18), 0 4px 10px -6px rgba(15, 23, 42, 0.12) !important;
            overflow: visible !important;
            z-index: 10000 !important;
        }
        .fc .fc-popover-header {
            flex: 0 0 auto;
            position: relative;
            z-index: 2;
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) auto;
            grid-template-rows: auto auto;
            column-gap: 8px;
            row-gap: 2px;
            align-items: center;
            background-color: #FFFFFF !important;
            border-bottom: 1px solid #E2E8EC !important;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            padding: 12px 16px !important;
            color: #172B36 !important;
        }
        .fc .fc-popover-title {
            grid-column: 1;
            grid-row: 1;
            margin: 0 !important;
            font-size: 15px !important;
            font-weight: 500 !important;
            color: #172B36 !important;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            min-width: 0;
        }
        .fc .fc-popover-count {
            grid-column: 1;
            grid-row: 2;
            font-size: 12px !important;
            font-weight: 400 !important;
            color: #4B5563 !important;
            white-space: nowrap;
        }
        .fc .fc-popover-close {
            grid-column: 2;
            grid-row: 1 / span 2;
            align-self: start;
            margin-left: auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 6px;
            color: #64748B;
            font-size: 18px !important;
            line-height: 1;
            opacity: 1 !important;
            cursor: pointer;
            flex: 0 0 auto;
        }
        .fc .fc-popover-close:hover {
            background-color: #F1F5F9;
            color: #172B36;
        }
        .fc .fc-popover-body {
            flex: 1 1 auto;
            min-height: 0;
            max-height: 340px !important;
            overflow-y: auto !important;
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 10px !important;
            background-color: #FFFFFF;
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
            position: relative;
            z-index: 1;
        }
        .fc .fc-popover-body .fc-daygrid-event-harness,
        .fc .fc-popover-body .fc-timegrid-event-harness {
            width: 100% !important;
            margin: 0 !important;
            flex: 0 0 auto;
        }
        .fc .fc-popover-body .fc-daygrid-event,
        .fc .fc-popover-body .fc-timegrid-event {
            width: 100% !important;
            margin: 0 !important;
            min-height: 44px;
            box-sizing: border-box;
            padding: 6px 10px !important;
            border-radius: 8px !important;
            cursor: pointer;
            display: flex !important;
            flex-direction: column;
            justify-content: center;
        }
        .popover-event-row {
            display: flex;
            flex-direction: column;
            gap: 1px;
            min-width: 0;
        }
        .popover-event-main {
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
        }
        .popover-event-dot {
            width: 6px;
            height: 6px;
            border-radius: 9999px;
            flex: 0 0 auto;
        }
        .popover-event-title {
            flex: 1 1 auto;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 13px;
            font-weight: 500;
            line-height: 1.25;
            color: #172B36;
        }
        .popover-event-time {
            flex: 0 0 auto;
            margin-left: auto;
            font-size: 12px;
            line-height: 1.25;
            color: #4B5563;
        }
        .popover-event-flow {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 12px;
            line-height: 1.25;
            color: #4B5563;
        }
        .popover-event-approved .popover-event-title { color: #FFFFFF; }
        .popover-event-approved .popover-event-flow,
        .popover-event-approved .popover-event-time { color: #D1FAE5; }
        .fc .fc-popover-body .fc-event-main {
            padding: 0 !important;
        }
        .fc .fc-popover-body .fc-daygrid-event:not(.tugas-card):hover,
        .fc .fc-popover-body .fc-timegrid-event:not(.tugas-card):hover {
            filter: brightness(0.96);
        }
        /* Panah popover menunjuk ke tanggal */
        .fc .fc-popover::before,
        .fc .fc-popover::after {
            content: "";
            position: absolute;
            border-style: solid;
            border-width: 7px;
            border-color: transparent;
            z-index: 3;
            pointer-events: none;
        }
        /* Animasi masuk/keluar popover "+N lainnya" */
        .fc .fc-popover.popover-side-right {
            animation: fc-popover-in-right 0.16s ease-out both;
        }
        .fc .fc-popover.popover-side-left {
            animation: fc-popover-in-left 0.16s ease-out both;
        }
        .fc .fc-popover.popover-closing {
            z-index: 40 !important;
            pointer-events: none !important;
        }
        .fc .fc-popover.popover-closing.popover-side-right {
            animation: fc-popover-out-right 0.13s ease-in both;
        }
        .fc .fc-popover.popover-closing.popover-side-left {
            animation: fc-popover-out-left 0.13s ease-in both;
        }
        @keyframes fc-popover-in-right {
            from { opacity: 0; transform: translateX(-6px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes fc-popover-in-left {
            from { opacity: 0; transform: translateX(6px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes fc-popover-out-right {
            from { opacity: 1; transform: translateX(0); }
            to { opacity: 0; transform: translateX(-6px); }
        }
        @keyframes fc-popover-out-left {
            from { opacity: 1; transform: translateX(0); }
            to { opacity: 0; transform: translateX(6px); }
        }
        @media (prefers-reduced-motion: reduce) {
            .fc .fc-popover.popover-side-right,
            .fc .fc-popover.popover-side-left,
            .fc .fc-popover.popover-closing.popover-side-right,
            .fc .fc-popover.popover-closing.popover-side-left {
                animation: none;
            }
        }
        .fc .fc-popover.popover-side-right::before {
            left: -7px;
            top: calc(var(--popover-arrow-top, 20px) - 7px);
            border-right-color: #E2E8EC;
        }
        .fc .fc-popover.popover-side-right::after {
            left: -6px;
            top: calc(var(--popover-arrow-top, 20px) - 7px);
            border-right-color: #FFFFFF;
        }
        .fc .fc-popover.popover-side-left::before {
            right: -7px;
            top: calc(var(--popover-arrow-top, 20px) - 7px);
            border-left-color: #E2E8EC;
        }
        .fc .fc-popover.popover-side-left::after {
            right: -6px;
            top: calc(var(--popover-arrow-top, 20px) - 7px);
            border-left-color: #FFFFFF;
        }
        /* Modal detail event */
        #modal-event [data-modal-panel] {
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            background: #FFFFFF;
            border: 1px solid #E2E8EC;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }
        .event-modal-header {
            flex: 0 0 auto;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 20px 24px;
            border-bottom: 1px solid #E2E8EC;
        }
        .event-modal-title {
            margin: 0;
            font-size: 18px;
            font-weight: 500;
            line-height: 1.35;
            color: #172B36;
            overflow-wrap: anywhere;
        }
        .event-modal-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
        }
        .event-modal-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 2px 8px;
            border: 1px solid #E2E8EC;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 500;
            line-height: 1.5;
            white-space: nowrap;
        }
        .event-modal-badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 9999px;
            background: #94A3B8;
            flex: 0 0 auto;
        }
        .event-modal-divisi {
            font-size: 13px;
            color: #4B5563;
        }
        .event-modal-close {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 6px;
            color: #94A3B8;
            transition: all 0.15s ease-out;
        }
        .event-modal-close:hover {
            background: #F1F5F9;
            color: #475569;
        }
        .event-modal-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            padding: 20px 24px 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .event-modal-people {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
            align-items: center;
            gap: 12px;
        }
        .event-modal-person {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            min-width: 0;
        }
        .event-avatar {
            width: 32px;
            height: 32px;
            border-radius: 9999px;
            background: #AFD3E2;
            color: #0B4A66;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            flex: 0 0 auto;
        }
        .event-person-label {
            font-size: 12px;
            color: #4B5563;
            margin-bottom: 2px;
        }
        .event-person-name {
            font-size: 14px;
            font-weight: 500;
            color: #172B36;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .event-person-sub {
            font-size: 12px;
            color: #4B5563;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .event-people-arrow {
            color: #94A3B8;
            font-size: 16px;
            line-height: 1;
        }
        .event-modal-section {
            border-top: 1px solid #EEF2F5;
            padding-top: 16px;
        }
        .event-modal-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .event-field-label {
            font-size: 12px;
            color: #4B5563;
            margin-bottom: 6px;
        }
        .event-field-date {
            font-size: 14px;
            color: #172B36;
            line-height: 1.45;
        }
        .event-field-time {
            font-size: 14px;
            color: #172B36;
            line-height: 1.45;
            margin-top: 2px;
        }
        .event-overdue {
            color: #B91C1C;
        }
        .event-submission {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 6px;
            background: #F1F5F9;
            color: #475569;
            font-size: 12px;
            font-weight: 500;
            line-height: 1.5;
        }
        .event-submission.is-submitted {
            background: #D1FAE5;
            color: #047857;
        }
        .event-description {
            font-size: 14px;
            line-height: 1.6;
            color: #172B36;
            white-space: pre-line;
            overflow-wrap: anywhere;
        }
        .event-modal-footer {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            padding: 16px 24px;
            border-top: 1px solid #E2E8EC;
        }
        .event-modal-btn {
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 0 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            line-height: 1;
            transition: all 0.15s ease-out;
        }
        .event-modal-btn-close {
            background: #FFFFFF;
            border: 1px solid #CBD5E1;
            color: #475569;
        }
        .event-modal-btn-close:hover {
            background: #F8FAFC;
        }
        .event-modal-btn-detail {
            background: #146C94;
            border: 1px solid #146C94;
            color: #FFFFFF;
        }
        .event-modal-btn-detail:hover {
            background: #105A78;
            border-color: #105A78;
        }
        /* Ring 2px pada tanggal yang dipilih */
        .fc-daygrid-day.fc-day-popover-selected .fc-daygrid-day-number {
            box-shadow: 0 0 0 2px #146C94 !important;
            border-radius: 9999px;
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
        .fc-list-event td {
            padding-top: 7px !important;
            padding-bottom: 7px !important;
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

        /* ===== Layout halaman: hanya area grid kalender yang menggulir ===== */
        .calendar-page {
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-gutter: stable;
        }
        /* Toolbar kustom kalender: navigasi, filter, dan view switcher */
        .cal-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 34px;
            padding: 0 0.75rem;
            border: 1px solid #CBD5E1;
            border-radius: 0.5rem;
            background-color: #FFFFFF;
            color: #334155;
            font-size: 13px;
            font-weight: 500;
            line-height: 1;
            text-transform: capitalize;
            white-space: nowrap;
            cursor: pointer;
            transition: all 0.1s ease-out;
        }
        .cal-btn:hover {
            background-color: #F8FAFC;
            border-color: #94A3B8;
            color: #1E293B;
        }
        .cal-btn.cal-nav {
            color: #146C94;
            padding-left: 0.6rem;
            padding-right: 0.6rem;
        }
        .cal-btn.cal-nav:hover {
            color: #0F4F6D;
        }
        .cal-btn.is-active {
            background-color: #146C94;
            border-color: #146C94;
            color: #FFFFFF;
        }
        .cal-btn.is-today {
            background-color: #798592;
            border-color: #798592;
            color: #FFFFFF;
        }
        .cal-btn-group {
            display: inline-flex;
            align-items: center;
        }
        .cal-btn-group .cal-btn {
            border-radius: 0;
        }
        .cal-btn-group .cal-btn + .cal-btn {
            margin-left: -1px;
        }
        .cal-btn-group .cal-btn:first-child {
            border-top-left-radius: 0.5rem;
            border-bottom-left-radius: 0.5rem;
        }
        .cal-btn-group .cal-btn:last-child {
            border-top-right-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
        }
        .cal-btn-group .cal-btn.is-active {
            position: relative;
            z-index: 1;
        }
        /* Header kalender: navigasi + judul di kiri, aksi di kanan */
        .cal-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }
        .cal-head-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 0;
        }
        .cal-head-title {
            margin: 0;
            font-size: 1.25rem;
            line-height: 1.3;
            font-weight: 600;
            color: #1E293B;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .cal-note-btn {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            height: 34px;
            padding: 0 0.875rem;
            border-radius: 0.5rem;
            background-color: #0097B2;
            color: #FFFFFF;
            font-size: 12px;
            font-weight: 700;
            box-shadow: 0 1px 2px 0 rgba(15, 23, 42, 0.08);
            transition: background-color 0.12s ease;
        }
        .cal-note-btn:hover {
            background-color: #008199;
        }
        /* Baris filter + legenda + pemilih tampilan */
        .cal-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }
        .cal-toolbar-left {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem;
            min-width: 0;
        }
        .cal-legend {
            position: relative;
        }
        .cal-legend > summary {
            list-style: none;
            cursor: pointer;
            gap: 0.375rem;
        }
        .cal-legend > summary::-webkit-details-marker {
            display: none;
        }
        .cal-legend > summary::after {
            content: "";
            width: 0.35rem;
            height: 0.35rem;
            margin-left: 0.15rem;
            border-right: 1.5px solid currentColor;
            border-bottom: 1.5px solid currentColor;
            transform: rotate(45deg) translateY(-1px);
        }
        .cal-legend[open] > summary::after {
            transform: rotate(-135deg) translateY(-1px);
        }
        .cal-legend-panel {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            z-index: 50;
            display: grid;
            gap: 0.4rem;
            min-width: 190px;
            padding: 0.65rem 0.75rem;
            background: #FFFFFF;
            border: 1px solid #E2E8EC;
            border-radius: 10px;
            box-shadow: 0 12px 28px -8px rgba(15, 23, 42, 0.18), 0 4px 10px -6px rgba(15, 23, 42, 0.12);
        }
        .cal-legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 12px;
            font-weight: 500;
            color: #334155;
            white-space: nowrap;
        }
        .cal-legend-swatch {
            width: 8px;
            height: 8px;
            border-radius: 9999px;
            flex: 0 0 auto;
        }
        @media (max-width: 900px) {
            .cal-head-title {
                font-size: 1.05rem;
            }
            .cal-select {
                max-width: 140px;
            }
        }
        .cal-select {
            height: 34px;
            max-width: 160px;
            font-size: 13px;
            border-radius: 0.5rem;
        }
        .cal-select-wide {
            max-width: 210px;
        }
        /* Header hari (Sen, Sel, ...) tetap terlihat dan opaque saat grid digulir */
        #calendar .fc-scrollgrid-section-header,
        #calendar .fc-scrollgrid-section-header > th {
            background-color: #EAF4F8;
        }
        #calendar .fc-scrollgrid-section-header > th {
            position: sticky;
            top: 0;
            z-index: 8;
        }
        /* Hanya area grid kalender yang menggulir */
        #calendar .fc-scrollgrid-section-body .fc-scroller {
            overflow: auto !important;
            padding-top: 0 !important;
        }
        /* Target scroll minggu "Hari ini" */
        #calendar .fc-daygrid-body tr,
        #calendar .fc-day-today {
            scroll-margin-top: 8px;
        }

        /* ===== WEEK VIEW ===== */
        /* Baris "Sehari penuh" */
        .fc-timegrid .fc-timegrid-axis {
            width: 84px !important;
            min-width: 84px !important;
            max-width: 84px !important;
        }
        .fc-timegrid .fc-timegrid-axis-cushion {
            max-width: none !important;
            white-space: nowrap;
            font-size: 12px !important;
            color: #4B5563 !important;
        }
        .fc-timegrid .fc-daygrid-day-frame {
            min-height: 32px !important;
        }
        /* Garis jam dan label */
        .fc-timegrid .fc-scrollgrid,
        .fc-timegrid .fc-timegrid-col,
        .fc-timegrid .fc-timegrid-axis {
            border-color: #E2E8EC !important;
        }
        .fc-timegrid .fc-timegrid-slot {
            border-top: 1px solid #CBD5E1 !important;
        }
        .fc-timegrid .fc-timegrid-slot-minor {
            border-top: 1px solid #E2E8EC !important;
            border-top-style: solid !important;
        }
        .fc-timegrid .fc-timegrid-divider td {
            border-color: #E2E8EC !important;
        }
        .fc-timegrid .fc-timegrid-slot-label-cushion {
            font-size: 12px !important;
            font-weight: 500 !important;
            color: #4B5563 !important;
            font-variant-numeric: tabular-nums;
        }
        /* Kartu event ringkas di time grid */
        .fc-timegrid-event {
            min-height: 44px !important;
            border-radius: 6px !important;
            cursor: pointer;
        }
        .fc-timegrid-event .fc-event-main {
            padding: 0 !important;
        }
        .fc-timegrid-more-link {
            background-color: #F1F5F9 !important;
            border: 1px solid #CBD5E1 !important;
            border-radius: 6px !important;
            color: #146C94 !important;
            font-size: 11px !important;
            cursor: pointer;
        }
        .fc-timegrid-more-link-inner {
            font-weight: 600 !important;
        }
        /* Garis waktu sekarang */
        .fc-timegrid-now-indicator-line {
            border-color: #EF4444 !important;
            border-top-width: 1px !important;
        }
        .fc-timegrid-now-indicator-arrow {
            border-color: #EF4444 !important;
            color: #EF4444 !important;
        }
    </style>
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">

    @include('component_kadiv.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        @include('component.topbar')

        <div class="calendar-page flex-1 min-h-0 flex flex-col overflow-y-auto">

            <main class="flex flex-col p-8 pt-6 pb-6">
            @include('component.breadcrumbs', [
                'parentText' => 'Dashboard',
                'parentUrl' => url('/kadiv/dashboard'),
                'currentPage' => 'Kalender Tugas Divisi',
                'inline' => true,
            ])

            <!-- Page Title -->
            <div class="flex-none mb-6">
                <x-page-heading title="Kalender Tugas Divisi" icon="calendar" />
            </div>

            <!-- Calendar Card -->
            <div class="calendar-card bg-white p-4 rounded-xl shadow-sm border border-[#E2E8EC] flex flex-col">
                <!-- Header kalender: navigasi + judul + tambah catatan -->
                <div class="cal-head flex-none mb-3">
                    <div class="cal-head-left">
                        <div class="cal-nav-group cal-btn-group flex items-center">
                            <button type="button" id="calendar-prev" class="cal-btn cal-nav" aria-label="Sebelumnya">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <button type="button" id="calendar-next" class="cal-btn cal-nav" aria-label="Berikutnya">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                            <button type="button" id="calendar-today" class="cal-btn">Hari ini</button>
                        </div>
                        <h2 id="calendar-title" class="cal-head-title"></h2>
                    </div>
                    <button type="button" onclick="openModalNote()" class="cal-note-btn">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah catatan
                    </button>
                </div>

                <!-- Toolbar kalender: filter, legenda, pemilih tampilan -->
                <div class="cal-toolbar flex-none mb-4">
                    <div class="cal-toolbar-left">
                    <!-- Filter Penerima (Staff) -->
                    <select id="kadivStaffFilter" class="cal-select cal-select-wide h-[30px] max-w-full text-[13px] bg-white border border-slate-300 rounded-lg px-2.5 font-medium text-slate-700 outline-none focus:border-[#146C94]">
                        <option value="">Semua karyawan</option>
                        @isset($staffList)
                            @foreach($staffList as $staff)
                                <option value="{{ $staff->id_karyawan }}">{{ $staff->nama }} ({{ $staff->jabatan ?? 'Staff' }})</option>
                            @endforeach
                        @endisset
                    </select>

                    <!-- Filter Status -->
                    <select id="kadivStatusFilter" class="cal-select h-[30px] max-w-full text-[13px] bg-white border border-slate-300 rounded-lg px-2.5 font-medium text-slate-700 outline-none focus:border-[#146C94]">
                        <option value="">Semua status</option>
                        <option value="baru">Baru</option>
                        <option value="berjalan">Berjalan</option>
                        <option value="menunggu di-acc">Menunggu persetujuan</option>
                        <option value="sudah di-acc">Disetujui</option>
                        <option value="terlambat">Terlambat</option>
                    </select>



                        <details class="cal-legend">
                            <summary class="cal-btn">Legenda</summary>
                            <div class="cal-legend-panel">
                            <div class="cal-legend-item"><span class="cal-legend-swatch" style="background-color: #2563EB"></span> Baru</div>
                            <div class="cal-legend-item"><span class="cal-legend-swatch" style="background-color: #059669"></span> Berjalan</div>
                            <div class="cal-legend-item"><span class="cal-legend-swatch" style="background-color: #D97706"></span> Menunggu persetujuan</div>
                            <div class="cal-legend-item"><span class="cal-legend-swatch" style="background-color: #047857"></span> Disetujui</div>
                            <div class="cal-legend-item"><span class="cal-legend-swatch" style="background-color: #DC2626"></span> Terlambat</div>
                            </div>
                        </details>
                    </div>
                    <div class="cal-btn-group inline-flex items-center">
                        <button type="button" class="cal-btn cal-view-btn is-active" data-view="dayGridMonth" aria-pressed="true">Bulan</button>
                        <button type="button" class="cal-btn cal-view-btn" data-view="timeGridWeek" aria-pressed="false">Minggu</button>
                        <button type="button" class="cal-btn cal-view-btn" data-view="listMonth" aria-pressed="false">Daftar</button>
                    </div>
                </div>

                <div id="calendar" class="w-full"></div>

            </div>

        </main>

        </div>

    </div>

    <!-- Modal Detail Tugas (Kadiv) -->
    <div id="modal-event" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4" style="display:none; pointer-events:none;">
        <div data-modal-panel
             class="bg-white w-full flex flex-col overflow-hidden"
             onclick="event.stopPropagation()">
            <!-- Header Modal -->
            <div class="event-modal-header">
                <div class="min-w-0">
                    <h2 id="modal-title" class="event-modal-title">Judul Tugas</h2>
                    <div class="event-modal-meta">
                        <span id="modal-badge" class="event-modal-badge bg-slate-100 text-slate-700">
                            <span id="modal-badge-dot" class="event-modal-badge-dot"></span>
                            <span id="modal-badge-text">Status</span>
                        </span>
                        <span id="modal-divisi" class="event-modal-divisi"></span>
                    </div>
                </div>
                <button type="button" onclick="closeModalEvent()" class="event-modal-close" aria-label="Tutup">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Body Modal -->
            <div class="event-modal-body">
                <!-- Dari & Kepada -->
                <div class="event-modal-people">
                    <div class="event-modal-person">
                        <span id="modal-pengirim-avatar" class="event-avatar">?</span>
                        <div class="min-w-0">
                            <div class="event-person-label">Dari</div>
                            <div id="modal-pengirim-nama" class="event-person-name">-</div>
                            <div id="modal-pengirim-peran" class="event-person-sub">-</div>
                        </div>
                    </div>
                    <div class="event-people-arrow" aria-hidden="true">→</div>
                    <div class="event-modal-person">
                        <span id="modal-staff-avatar" class="event-avatar">?</span>
                        <div class="min-w-0">
                            <div class="event-person-label">Kepada</div>
                            <div id="modal-staff-nama" class="event-person-name">-</div>
                            <div id="modal-staff-peran" class="event-person-sub">-</div>
                        </div>
                    </div>
                </div>

                <!-- Tenggat & Pengumpulan -->
                <div class="event-modal-section event-modal-grid">
                    <div>
                        <div class="event-field-label">Tenggat</div>
                        <div id="modal-deadline-date" class="event-field-date">-</div>
                        <div class="event-field-time">
                            <span id="modal-deadline-time">-</span><span id="modal-deadline-overdue" class="event-overdue" style="display:none;"> · Terlambat</span>
                        </div>
                    </div>
                    <div>
                        <div class="event-field-label">Pengumpulan</div>
                        <span id="modal-submission-status" class="event-submission">-</span>
                    </div>
                </div>

                <!-- Deskripsi -->
                <div class="event-modal-section">
                    <div class="event-field-label">Deskripsi</div>
                    <div id="modal-deskripsi" class="event-description">-</div>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="event-modal-footer">
                <button type="button" onclick="closeModalEvent()" class="event-modal-btn event-modal-btn-close">Tutup</button>
                <a id="modal-detail-btn" href="#" class="event-modal-btn event-modal-btn-detail">
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
                        <input type="date" id="note-input-tanggal" required
                            class="w-full h-10 rounded-lg border border-slate-300 px-3 text-sm text-[#172B36] outline-none cursor-pointer focus:border-[#146C94]">
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

                    <div id="note-reminder-section" class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p id="note-reminder-label" class="text-[13px] font-medium text-[#4B5563]">Ingatkan saya</p>
                                <p class="text-xs text-[#4B5563] mt-0.5">Notifikasi sebelum tanggal catatan</p>
                            </div>
                            <button type="button" id="note-reminder-toggle" role="switch" aria-checked="false" aria-label="Ingatkan saya"
                                class="relative inline-flex shrink-0 cursor-pointer items-center w-[38px] h-[22px] rounded-full bg-[#CBD5E1] shadow-inner transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] focus-visible:ring-offset-2"
                                onclick="toggleNoteReminder()">
                                <span id="note-reminder-knob" class="inline-block w-[18px] h-[18px] rounded-full bg-white shadow-[0_1px_2px_rgba(15,23,42,0.25)] transition-transform duration-200 ease-out translate-x-[2px]"></span>
                            </button>
                        </div>

                        <div id="note-reminder-controls" class="hidden mt-3">
                            <div class="flex flex-wrap items-center gap-x-2.5 gap-y-2">
                                <div class="inline-flex h-[38px] items-center rounded-lg border border-[#D5DEE3] bg-white transition focus-within:border-[#146C94] focus-within:ring-2 focus-within:ring-[#146C94]/15">
                                    <input type="number" id="note-reminder-value" min="1" max="60" value="1"
                                        inputmode="numeric" aria-label="Jumlah waktu pengingat"
                                        class="h-full w-16 appearance-none rounded-l-lg bg-transparent px-2 text-center text-sm font-medium text-[#172B36] outline-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">
                                    <span class="h-5 w-px shrink-0 bg-[#D5DEE3]"></span>
                                    <div class="relative h-full">
                                        <select id="note-reminder-unit" aria-label="Satuan waktu pengingat"
                                            class="h-full cursor-pointer appearance-none rounded-r-lg bg-transparent pl-3 pr-8 text-sm text-[#172B36] outline-none">
                                            <option value="day">hari</option>
                                            <option value="week">minggu</option>
                                        </select>
                                        <svg class="pointer-events-none absolute right-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </div>
                                <span class="text-sm text-[#4B5563]">sebelum tanggal catatan</span>
                            </div>

                            <div class="mt-2.5 flex flex-wrap items-center gap-1.5">
                                <button type="button" class="rounded-full border border-[#D5DEE3] bg-white px-2.5 py-1 text-[13px] text-[#146C94] transition hover:border-[#146C94]/40 hover:bg-[#146C94]/5" onclick="setNoteReminderPreset(1, 'day')">1 hari</button>
                                <button type="button" class="rounded-full border border-[#D5DEE3] bg-white px-2.5 py-1 text-[13px] text-[#146C94] transition hover:border-[#146C94]/40 hover:bg-[#146C94]/5" onclick="setNoteReminderPreset(3, 'day')">3 hari</button>
                                <button type="button" class="rounded-full border border-[#D5DEE3] bg-white px-2.5 py-1 text-[13px] text-[#146C94] transition hover:border-[#146C94]/40 hover:bg-[#146C94]/5" onclick="setNoteReminderPreset(1, 'week')">1 minggu</button>
                            </div>

                            <div id="note-reminder-hint" class="mt-2.5 flex items-start gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-[#4B5563]">
                                <svg id="note-reminder-hint-bell" class="w-3.5 h-3.5 shrink-0 mt-px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <svg id="note-reminder-hint-alert" class="hidden w-3.5 h-3.5 shrink-0 mt-px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span id="note-reminder-hint-text">Pengingat muncul pada 4 Nov 2026.</span>
                            </div>
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

        // --- PENGINGAT CATATAN ---
        let noteReminderEnabled = false;

        function setNoteReminderSwitchState(enabled) {
            noteReminderEnabled = !!enabled;

            const toggle = document.getElementById('note-reminder-toggle');
            const knob = document.getElementById('note-reminder-knob');
            const controls = document.getElementById('note-reminder-controls');

            if (toggle) {
                toggle.setAttribute('aria-checked', enabled ? 'true' : 'false');
                toggle.classList.toggle('bg-[#146C94]', enabled);
                toggle.classList.toggle('bg-[#CBD5E1]', !enabled);
            }

            if (knob) {
                knob.classList.toggle('translate-x-[18px]', enabled);
                knob.classList.toggle('translate-x-[2px]', !enabled);
            }

            if (controls) {
                controls.classList.toggle('hidden', !enabled);
            }

            updateNoteReminderHint();
        }

        function toggleNoteReminder() {
            setNoteReminderSwitchState(!noteReminderEnabled);
        }

        function setNoteReminderPreset(amount, unit) {
            const valueInput = document.getElementById('note-reminder-value');
            const unitSelect = document.getElementById('note-reminder-unit');

            if (valueInput) valueInput.value = String(amount);
            if (unitSelect) unitSelect.value = unit;

            updateNoteReminderHint();
        }

        function getNoteReminderAmount() {
            const input = document.getElementById('note-reminder-value');
            let amount = parseInt(input?.value || '1', 10);

            if (!Number.isFinite(amount) || amount < 1) amount = 1;
            if (amount > 60) amount = 60;

            return amount;
        }

        function normalizeNoteReminderValue() {
            const input = document.getElementById('note-reminder-value');
            if (input) input.value = String(getNoteReminderAmount());
        }

        function getNoteReminderDays() {
            if (!noteReminderEnabled) return null;

            const unit = document.getElementById('note-reminder-unit')?.value;
            return getNoteReminderAmount() * (unit === 'week' ? 7 : 1);
        }

        function getNoteReminderDate() {
            const rawDate = document.getElementById('note-input-tanggal')?.value || '';
            const parts = rawDate.split('-');

            if (parts.length !== 3) return null;

            const noteDate = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
            const days = getNoteReminderDays();

            if (isNaN(noteDate.getTime()) || days === null) return null;

            noteDate.setDate(noteDate.getDate() - days);

            return noteDate;
        }

        function toLocalIsoDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${year}-${month}-${day}`;
        }

        function updateNoteReminderHint() {
            const controls = document.getElementById('note-reminder-controls');

            if (!noteReminderEnabled) {
                if (controls) controls.classList.add('hidden');
                return true;
            }

            if (controls) controls.classList.remove('hidden');

            const hint = document.getElementById('note-reminder-hint');
            const hintText = document.getElementById('note-reminder-hint-text');
            const bellIcon = document.getElementById('note-reminder-hint-bell');
            const alertIcon = document.getElementById('note-reminder-hint-alert');
            const reminderDate = getNoteReminderDate();

            if (!reminderDate) {
                if (hintText) hintText.textContent = 'Pilih tanggal catatan untuk melihat tanggal pengingat.';
                if (hint) {
                    hint.classList.remove('text-[#B91C1C]', 'border-red-200', 'bg-red-50');
                    hint.classList.add('text-[#4B5563]', 'border-slate-200', 'bg-white');
                }
                if (bellIcon) bellIcon.classList.remove('hidden');
                if (alertIcon) alertIcon.classList.add('hidden');
                return false;
            }

            const now = new Date();
            const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            const isPast = reminderDate.getTime() < today.getTime();
            const label = formatTanggalIndonesia(toLocalIsoDate(reminderDate));

            if (hintText) {
                hintText.textContent = isPast
                    ? `Tanggal pengingat (${label}) sudah lewat. Pilih waktu yang lebih singkat.`
                    : `Pengingat muncul pada ${label}.`;
            }

            if (hint) {
                hint.classList.toggle('text-[#B91C1C]', isPast);
                hint.classList.toggle('text-[#4B5563]', !isPast);
                hint.classList.toggle('border-red-200', isPast);
                hint.classList.toggle('bg-red-50', isPast);
                hint.classList.toggle('border-slate-200', !isPast);
                hint.classList.toggle('bg-white', !isPast);
            }
            if (bellIcon) bellIcon.classList.toggle('hidden', isPast);
            if (alertIcon) alertIcon.classList.toggle('hidden', !isPast);

            return !isPast;
        }

        document.getElementById('note-input-tanggal')?.addEventListener('input', updateNoteReminderHint);
        document.getElementById('note-input-tanggal')?.addEventListener('change', updateNoteReminderHint);
        document.getElementById('note-reminder-value')?.addEventListener('input', updateNoteReminderHint);
        document.getElementById('note-reminder-value')?.addEventListener('blur', () => {
            normalizeNoteReminderValue();
            updateNoteReminderHint();
        });
        document.getElementById('note-reminder-unit')?.addEventListener('change', updateNoteReminderHint);

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

            const reminderValue = document.getElementById('note-reminder-value');
            const reminderUnit = document.getElementById('note-reminder-unit');
            if (reminderValue) reminderValue.value = '1';
            if (reminderUnit) reminderUnit.value = 'day';

            if (defaultDate) {
                dateInput.value = String(defaultDate).slice(0, 10);
            } else {
                const today = new Date();
                const yyyy = today.getFullYear();
                const mm = String(today.getMonth() + 1).padStart(2, '0');
                const dd = String(today.getDate()).padStart(2, '0');
                dateInput.value = `${yyyy}-${mm}-${dd}`;
            }

            setNoteReminderSwitchState(false);

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

            if (noteReminderEnabled) {
                normalizeNoteReminderValue();
                if (!updateNoteReminderHint()) {
                    return;
                }
            }

            const reminderDaysBefore = getNoteReminderDays();

            btn.disabled = true;
            btn.innerHTML = '<span>Menyimpan...</span>';

            fetch('/kadiv/kalender/notes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({ tanggal, judul, detail, warna, reminder_days_before: reminderDaysBefore })
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

            fetch('/kadiv/kalender/notes/' + currentSelectedNoteId, {
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
            const badgeTextEl = document.getElementById('modal-badge-text');
            const badgeDotEl = document.getElementById('modal-badge-dot');
            const pengirimAvatarEl = document.getElementById('modal-pengirim-avatar');
            const pengirimNamaEl = document.getElementById('modal-pengirim-nama');
            const pengirimPeranEl = document.getElementById('modal-pengirim-peran');
            const staffAvatarEl = document.getElementById('modal-staff-avatar');
            const staffNamaEl = document.getElementById('modal-staff-nama');
            const staffPeranEl = document.getElementById('modal-staff-peran');
            const deadlineDateEl = document.getElementById('modal-deadline-date');
            const deadlineTimeEl = document.getElementById('modal-deadline-time');
            const deadlineOverdueEl = document.getElementById('modal-deadline-overdue');
            const submissionStatusEl = document.getElementById('modal-submission-status');
            const deskripsiEl = document.getElementById('modal-deskripsi');
            const detailBtn = document.getElementById('modal-detail-btn');

            titleEl.textContent = props.judul_murni || event.title;
            badgeEl.className = 'event-modal-badge ' + (props.badge_class || 'bg-slate-100 text-slate-700');
            badgeTextEl.textContent = props.status ? tugasStatusLabel(props.status) : (props.status_label || 'Event');
            badgeDotEl.style.backgroundColor = props.dot_color || '#94A3B8';
            divisiEl.textContent = props.divisi_nama || '';

            const pengirimNama = props.pengirim_nama || '-';
            const pengirimPeran = props.pengirim_jabatan || '';
            pengirimNamaEl.textContent = pengirimNama;
            pengirimPeranEl.textContent = pengirimPeran || '-';
            pengirimAvatarEl.textContent = getInitials(pengirimNama);

            const staffNama = props.staff_nama || '-';
            const staffPeran = props.staff_jabatan || props.staff_id || '';
            staffNamaEl.textContent = staffNama;
            staffPeranEl.textContent = staffPeran || '-';
            staffAvatarEl.textContent = getInitials(staffNama);

            const deadlineParts = splitDeadlineLabel(props.deadline_label);
            deadlineDateEl.textContent = deadlineParts.date || '-';
            deadlineTimeEl.textContent = deadlineParts.time || '';
            const isOverdue = String(props.status || '').toLowerCase() === 'terlambat'
                || String(props.status_label || '').toLowerCase().includes('terlambat');
            deadlineOverdueEl.style.display = isOverdue ? 'inline' : 'none';

            if (props.has_submission) {
                submissionStatusEl.className = 'event-submission is-submitted';
                submissionStatusEl.textContent = 'Sudah dikumpulkan';
            } else {
                submissionStatusEl.className = 'event-submission';
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
            animateModalOut(modal, restoreEventFocus);
        }

        function restoreEventFocus() {
            let target = lastEventTrigger;
            if (!target || !document.contains(target)) {
                target = (lastMoreLink && document.contains(lastMoreLink)) ? lastMoreLink : null;
            }
            if (target) {
                if (!target.hasAttribute('tabindex')) target.setAttribute('tabindex', '-1');
                target.focus({ preventScroll: true });
            }
            lastEventTrigger = null;
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

        function applyKadivFilters() {
            if (calendarInstance) {
                calendarInstance.refetchEvents();
            }
        }

        // Scroll minggu berjalan ke dalam area scroll kalender (dipakai tombol "Hari Ini")
        let pendingTodayScroll = false;

        function getCalendarScrollContainer() {
            const candidates = document.querySelectorAll('#calendar .fc-scroller');
            for (const el of candidates) {
                if (el.scrollHeight > el.clientHeight + 1) return el;
            }
            return document.querySelector('#calendar .fc-scroller') || null;
        }

        function scrollToTodayWeek() {
            const scroller = getCalendarScrollContainer();
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

        let appliedAllDaySlot = true;

        function syncAllDaySlot() {
            if (!calendarInstance || calendarInstance.view.type !== 'timeGridWeek') return;
            const view = calendarInstance.view;
            const hasAllDay = calendarInstance.getEvents().some(function(ev) {
                return ev.allDay && ev.start && ev.start < view.activeEnd && (!ev.end || ev.end > view.activeStart);
            });
            if (appliedAllDaySlot !== hasAllDay) {
                appliedAllDaySlot = hasAllDay;
                calendarInstance.setOption('allDaySlot', hasAllDay);
            }
        }

        let lastMoreLink = null;
        let lastMoreCell = null;
        let lastEventTrigger = null;

        // Tutup popover "+N lainnya" dan kembalikan fokus ke tanggal
        function closeDayPopover(options = {}) {
            const popover = document.querySelector('.fc-popover');

            function finalize() {
                document.querySelectorAll('.fc-day-popover-selected').forEach(function(el) {
                    el.classList.remove('fc-day-popover-selected');
                });
                if (options.focus !== false) {
                    setTimeout(function() {
                        const focusEl = lastMoreCell
                            ? lastMoreCell.querySelector('.fc-daygrid-day-number')
                            : lastMoreLink;
                        if (!focusEl) return;
                        if (!focusEl.hasAttribute('tabindex')) focusEl.setAttribute('tabindex', '-1');
                        focusEl.focus({ preventScroll: true });
                    }, 0);
                }
            }

            if (!popover) {
                finalize();
                return;
            }

            if (popover.classList.contains('popover-closing')) return;

            popover.classList.add('popover-closing');

            setTimeout(function() {
                const closeBtn = popover.querySelector('.fc-popover-close');
                if (closeBtn) closeBtn.click();
                else popover.remove();
                finalize();
            }, 130);
        }

        // Posisikan popover di samping sel tanggal, tetap di dalam viewport
        function positionDayPopover(popover) {
            const anchor = lastMoreCell || lastMoreLink;
            if (!anchor) return;
            const parent = popover.offsetParent || popover.parentElement;
            if (!parent) return;

            const parentRect = parent.getBoundingClientRect();
            const cellRect = anchor.getBoundingClientRect();
            const popRect = popover.getBoundingClientRect();
            const gap = 10;
            const margin = 8;

            let side = 'right';
            let left = cellRect.right + gap;
            if (left + popRect.width > window.innerWidth - margin) {
                side = 'left';
                left = cellRect.left - gap - popRect.width;
            }
            left = Math.max(margin, Math.min(left, window.innerWidth - margin - popRect.width));

            let top = cellRect.top;
            top = Math.max(margin, Math.min(top, window.innerHeight - margin - popRect.height));

            popover.style.left = (left - parentRect.left) + 'px';
            popover.style.top = (top - parentRect.top) + 'px';
            const sideClass = 'popover-side-' + side;
            if (!popover.classList.contains(sideClass)) {
                popover.classList.remove('popover-side-left', 'popover-side-right');
                popover.classList.add(sideClass);
            }

            const dayNumber = lastMoreCell ? lastMoreCell.querySelector('.fc-daygrid-day-number') : null;
            let arrowTop = 24;
            if (dayNumber) {
                const numRect = dayNumber.getBoundingClientRect();
                arrowTop = (numRect.top + numRect.height / 2) - top;
            } else {
                arrowTop = (cellRect.top + cellRect.height / 2) - top;
            }
            arrowTop = Math.max(14, Math.min(arrowTop, popRect.height - 14));
            popover.style.setProperty('--popover-arrow-top', arrowTop + 'px');
        }

        // Lengkapi header popover (jumlah tugas + ring tanggal)
        function decorateDayPopover() {
            const popover = document.querySelector('.fc-popover');
            if (!popover || (!lastMoreCell && !lastMoreLink)) return;
            const header = popover.querySelector('.fc-popover-header');
            const body = popover.querySelector('.fc-popover-body');
            if (!header || !body) return;

            const items = body.querySelectorAll('.fc-daygrid-event, .fc-timegrid-event');
            let countEl = header.querySelector('.fc-popover-count');
            if (!countEl) {
                countEl = document.createElement('span');
                countEl.className = 'fc-popover-count';
                const closeBtn = header.querySelector('.fc-popover-close');
                if (closeBtn) header.insertBefore(countEl, closeBtn);
                else header.appendChild(countEl);
            }
            countEl.textContent = items.length + ' tugas';

            document.querySelectorAll('.fc-day-popover-selected').forEach(function(el) {
                el.classList.remove('fc-day-popover-selected');
            });
            if (lastMoreCell) {
                lastMoreCell.classList.add('fc-day-popover-selected');
                const dayNumber = lastMoreCell.querySelector('.fc-daygrid-day-number');
                if (dayNumber) dayNumber.setAttribute('tabindex', '-1');
            }

            requestAnimationFrame(function() {
                positionDayPopover(popover);
            });
        }

        function getInitials(name) {
            if (!name) return '?';
            const parts = String(name).trim().split(/\s+/).filter(Boolean);
            if (!parts.length) return '?';
            if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
            return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
        }

        function splitDeadlineLabel(label) {
            const raw = String(label || '').trim();
            if (!raw || raw.toLowerCase() === 'tidak ditentukan') {
                return { date: raw || '-', time: '' };
            }
            const idx = raw.lastIndexOf(',');
            if (idx === -1) return { date: raw, time: '' };
            return { date: raw.slice(0, idx).trim(), time: raw.slice(idx + 1).trim() };
        }

        function formatTime12(date) {
            if (!date) return '';
            let hours = date.getHours();
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const suffix = hours >= 12 ? 'pm' : 'am';
            hours = hours % 12 || 12;
            return `${hours}:${minutes} ${suffix}`;
        }

        // Ubah HH:MM di dalam label tanggal backend menjadi format AM/PM
        function formatLabelTime12(label) {
            if (!label) return '-';
            return String(label).replace(/(\d{1,2}):(\d{2})(?![\d:])/g, function(_, h, m) {
                let hours = Number(h);
                const suffix = hours >= 12 ? 'pm' : 'am';
                hours = hours % 12 || 12;
                return `${hours}:${m} ${suffix}`;
            });
        }

        // Kartu event versi popover (lebih lengkap dari kartu bulan)
        function buildDayPopoverEventHtml(props, event) {
            const staffName = props.staff_nama || 'Staff';
            const title = props.judul_murni || event.title;
            const pengirimNama = props.pengirim_nama || 'Kadiv';
            const flowText = `${pengirimNama} → ${staffName}`;
            const dotColor = props.dot_color || '#004A65';
            const isApproved = String(props.status || '').toLowerCase() === 'sudah di-acc';
            const time = event.start && !event.allDay
                ? String(event.start.getHours()).padStart(2, '0') + ':' + String(event.start.getMinutes()).padStart(2, '0')
                : '';

            return `
                <div class="popover-event-row${isApproved ? ' popover-event-approved' : ''}">
                    <div class="popover-event-main">
                        <span class="popover-event-dot" style="background-color: var(--tugas-accent, ${escapeHtml(dotColor)});"></span>
                        <span class="popover-event-title" title="${escapeHtml(title)}">${escapeHtml(title)}</span>
                        ${time ? `<span class="popover-event-time">${escapeHtml(time)}</span>` : ''}
                    </div>
                    <div class="popover-event-flow">${escapeHtml(flowText)}</div>
                </div>
            `;
        }

        function buildDayPopoverNoteHtml(event) {
            return `
                <div class="flex items-center gap-2 min-w-0">
                    <svg class="w-4 h-4 shrink-0 opacity-80" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"/>
                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-semibold text-[14px] leading-tight text-[#172B36] truncate min-w-0">
                        ${escapeHtml(event.title)}
                    </span>
                </div>
            `;
        }

        let dayPopoverObserver = null;

        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const token = localStorage.getItem('staff_token') || sessionStorage.getItem('staff_token');

            calendarInstance = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                firstDay: 1, // Senin
                allDayText: 'Sehari penuh',
                dayHeaderFormat: { weekday: 'short' },
                dayHeaderContent: function(arg) {
                    if (arg.view.type !== 'timeGridWeek') return arg.text || '';

                    const wrap = document.createElement('div');
                    wrap.className = 'cal-week-header';

                    const weekday = document.createElement('span');
                    weekday.className = 'cal-week-header-weekday' + (arg.isToday ? ' is-today' : '');
                    weekday.textContent = arg.text || '';

                    const day = document.createElement('span');
                    day.className = 'cal-week-header-day' + (arg.isToday ? ' is-today' : '');

                    const dayNumber = document.createElement('span');
                    dayNumber.className = 'cal-week-header-daynum';
                    dayNumber.textContent = arg.date.getDate();

                    day.appendChild(dayNumber);
                    wrap.appendChild(weekday);
                    wrap.appendChild(day);

                    return { domNodes: [wrap] };
                },
                height: 'auto',
                stickyHeaderDates: true,
                dayMaxEvents: 2, // Maksimal 2 event ringkas tampil per hari
                dayPopoverFormat: { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' },
                moreLinkText: function(n) {
                    return '+' + n + ' lainnya';
                },
                moreLinkClick: function(arg) {
                    const clickedLink = arg.jsEvent?.currentTarget || arg.jsEvent?.target;
                    lastMoreLink = clickedLink?.closest?.('.fc-more-link, .fc-timegrid-more-link') || clickedLink || null;
                    lastMoreCell = lastMoreLink ? lastMoreLink.closest('.fc-daygrid-day') : null;
                    setTimeout(decorateDayPopover, 30);
                    return 'popover';
                },
                moreLinkContent: function(arg) {
                    return '+' + arg.num + ' lainnya';
                },
                defaultTimedEventDuration: '00:45:00',
                slotMinTime: '07:00:00',
                slotMaxTime: '21:00:00',
                slotDuration: '00:30:00',
                slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
                slotLabelContent: function(arg) {
                    if (arg.view.type !== 'timeGridWeek') return undefined;
                    const d = arg.date;
                    return String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
                },
                slotEventOverlap: false, // Event di jam yang sama TIDAK saling tumpang tindih
                eventMinHeight: 44, // Tinggi minimum kartu week view
                eventMaxStack: 2, // Maksimal 2 kartu bertumpuk; sisanya +N lainnya
                nextDayThreshold: '24:00:00',
                eventDisplay: 'block',
                displayEventTime: true,
                eventTimeFormat: {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true,
                    meridiem: 'short'
                },
                headerToolbar: false,
                datesSet: function(arg) {
                    const titleEl = document.getElementById('calendar-title');
                    if (titleEl) titleEl.textContent = arg.view.title;

                    document.querySelectorAll('.cal-view-btn').forEach(function(btn) {
                        const isActive = btn.getAttribute('data-view') === arg.view.type;
                        btn.classList.toggle('is-active', isActive);
                        btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                    });

                    const todayBtn = document.getElementById('calendar-today');
                    if (todayBtn) {
                        const now = new Date();
                        const isViewingToday = now >= arg.view.currentStart && now < arg.view.currentEnd;
                        todayBtn.classList.toggle('is-today', isViewingToday);
                    }

                    if (pendingTodayScroll) {
                        pendingTodayScroll = false;
                        requestAnimationFrame(function() {
                            requestAnimationFrame(scrollToTodayWeek);
                        });
                    }

                    if (calendarInstance && calendarInstance.view.type === 'timeGridWeek') {
                        setTimeout(syncAllDaySlot, 60);
                    }
                },
                viewDidMount: function(arg) {
                    if (arg.view.type === 'timeGridWeek') {
                        const resetScroll = function() {
                            const page = getCalendarScrollContainer();
                            if (page) page.scrollTop = 0;
                        };
                        resetScroll();
                        setTimeout(resetScroll, 120);
                        setTimeout(syncAllDaySlot, 60);
                    }
                },
                eventsSet: function() {
                    syncAllDaySlot();
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

                    const staff = document.getElementById('kadivStaffFilter')?.value || '';
                    const status = document.getElementById('kadivStatusFilter')?.value || '';

                    const params = new URLSearchParams();
                    if (staff) params.set('staff', staff);
                    if (status) params.set('status', status);
                    params.set('start', fetchInfo.start.toISOString());
                    params.set('end', fetchInfo.end.toISOString());

                    fetch('/kadiv/kalender/data?' + params.toString(), { headers })
                    .then(r => {
                        if (!r.ok) throw new Error('Gagal memuat kalender (HTTP ' + r.status + ').');
                        return r.json();
                    })
                    .then(result => {
                        successCallback(result.data || []);
                    })
                    .catch(err => {
                        console.error('Error load events Kadiv:', err);
                        failureCallback(err);
                    });
                },
                dateClick: function(info) {
                    openModalNote((info.dateStr || '').slice(0, 10));
                },
                /* Tampilan Kustom Event untuk Kadiv: Menampilkan Divisi, Alur (Dari Kadiv ➔ Ke Staff), dan Judul Tugas */
                eventContent: function(arg) {
                    const props = arg.event.extendedProps || {};
                    const isList = arg.view.type.startsWith('list');
                    const isTimeGrid = arg.view.type.startsWith('timeGrid');
                    const isApproved = String(props.status || '').toLowerCase() === 'sudah di-acc';
                    const titleColor = isApproved ? 'text-white' : 'text-[#172B36]';
                    const flowColor = isApproved ? 'text-emerald-100' : 'text-[#4B5563]';
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
                        // Minggu view: kartu ringkas — judul + alur penugasan
                        if (arg.event.allDay) {
                            el.className = 'w-full px-1 py-0 leading-tight flex items-center gap-1 overflow-hidden min-w-0';
                            el.innerHTML = `
                                <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                      style="background-color: var(--tugas-accent, ${escapeHtml(dotColor)});"></span>
                                <span class="font-semibold text-[10px] truncate leading-tight ${titleColor} min-w-0">
                                    ${escapeHtml(title)}
                                </span>
                            `;
                        } else {
                            el.className = 'w-full h-full px-1 py-0.5 leading-tight flex flex-col justify-center gap-0 overflow-hidden min-w-0';
                            el.innerHTML = `
                                <div class="flex items-center gap-1 min-w-0 overflow-hidden">
                                    <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                          style="background-color: var(--tugas-accent, ${escapeHtml(dotColor)});"></span>
                                    <span class="font-semibold text-[11px] truncate leading-tight ${titleColor} min-w-0">
                                        ${escapeHtml(title)}
                                    </span>
                                </div>
                                <div class="text-[9.5px] ${flowColor} font-medium truncate leading-tight min-w-0">
                                    ${escapeHtml(flowText)}
                                </div>
                            `;
                        }
                    } else {
                        // Bulan view: ringkas — judul + alur penugasan (divisi/jam tidak ditampilkan)
                        el.className = 'w-full px-0.5 py-0 leading-tight flex flex-col justify-center gap-0 overflow-hidden min-w-0';
                        el.innerHTML = `
                            <div class="flex items-center gap-1 min-w-0 overflow-hidden">
                                <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                      style="background-color: var(--tugas-accent, ${escapeHtml(dotColor)});"></span>
                                <span class="font-semibold text-[11px] truncate leading-tight ${titleColor} min-w-0">
                                    ${escapeHtml(title)}
                                </span>
                            </div>
                            <div class="text-[10px] ${flowColor} font-medium truncate leading-tight min-w-0">
                                ${escapeHtml(flowText)}
                            </div>
                        `;
                    }

                    return { domNodes: [el] };
                },
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    if (info.el.closest('.fc-popover')) {
                        closeDayPopover({ focus: false });
                    }
                    if (info.event.extendedProps?.type === 'note') {
                        openModalDetailNote(info.event);
                    } else {
                        lastEventTrigger =
                            info.el.closest?.('tr.fc-list-event')?.querySelector('.fc-list-event-title')
                            || info.el;
                        openModalEvent(info.event);
                    }
                },
                eventDidMount: function(info) {
                    const props = info.event.extendedProps || {};
                    const isList = info.view.type.startsWith('list');

                    if (props.type === 'note') {
                        info.el.setAttribute('title', `Catatan: ${info.event.title}`);
                        if (info.el.closest('.fc-popover-body')) {
                            const main = info.el.querySelector('.fc-event-main') || info.el;
                            main.innerHTML = buildDayPopoverNoteHtml(info.event);
                            setTimeout(decorateDayPopover, 0);
                        }
                        return;
                    }

                    // Tooltip: judul lengkap + divisi (+ alur & jam di week view)
                    const judulTooltip = props.judul_murni || info.event.title;
                    const divisiTooltip = props.divisi_nama || '';
                    let tooltip = divisiTooltip ? `${judulTooltip} — ${divisiTooltip}` : judulTooltip;

                    if (info.view.type === 'timeGridWeek') {
                        const flowTooltip = [props.pengirim_nama, props.staff_nama].filter(Boolean).join(' → ');
                        const timeTooltip = info.event.start && !info.event.allDay ? formatTime12(info.event.start) : '';
                        tooltip = [judulTooltip, flowTooltip, timeTooltip, divisiTooltip].filter(Boolean).join(' • ');
                    }
                    info.el.setAttribute('title', tooltip);

                    // Kartu status di grid (bulan & minggu); list view tetap polos
                    if (!isList) {
                        info.el.style.backgroundColor = '';
                        info.el.style.borderColor = '';
                        info.el.style.color = '';
                        info.el.classList.add('tugas-card', tugasStatusClass(props.status));
                    }

                    if (info.el.closest('.fc-popover-body')) {
                        const main = info.el.querySelector('.fc-event-main') || info.el;
                        main.innerHTML = buildDayPopoverEventHtml(props, info.event);
                        setTimeout(decorateDayPopover, 0);
                    }
                }
            });

            calendarInstance.render();

            // Kontrol toolbar kustom
            document.getElementById('calendar-prev')?.addEventListener('click', function() {
                calendarInstance.prev();
            });
            document.getElementById('calendar-next')?.addEventListener('click', function() {
                calendarInstance.next();
            });
            document.getElementById('calendar-today')?.addEventListener('click', function() {
                pendingTodayScroll = true;
                calendarInstance.today();
                scrollToTodayWeek();
                setTimeout(scrollToTodayWeek, 50);
                setTimeout(scrollToTodayWeek, 150);
                setTimeout(function() {
                    pendingTodayScroll = false;
                    scrollToTodayWeek();
                }, 350);
            });
            document.querySelectorAll('.cal-view-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const view = btn.getAttribute('data-view');
                    if (view && calendarInstance.view.type !== view) {
                        calendarInstance.changeView(view);
                    }
                });
            });

            // Observer: bersihkan ring tanggal saat popover ditutup oleh FullCalendar
            if (window.MutationObserver) {
                dayPopoverObserver = new MutationObserver(function() {
                    if (!document.querySelector('.fc-popover')) {
                        document.querySelectorAll('.fc-day-popover-selected').forEach(function(el) {
                            el.classList.remove('fc-day-popover-selected');
                        });
                    }
                });
                dayPopoverObserver.observe(calendarEl, { childList: true, subtree: true });
            }

            // Esc menutup popover + fokus kembali ke tanggal
            document.addEventListener('keydown', function(event) {
                if (event.key !== 'Escape') return;
                if (!document.querySelector('.fc-popover')) return;
                event.preventDefault();
                closeDayPopover();
            }, true);

            // Klik di luar popover menutup popover + fokus kembali ke tanggal
            document.addEventListener('mousedown', function(event) {
                const popover = document.querySelector('.fc-popover');
                if (!popover || popover.contains(event.target)) return;
                if (event.target.closest && event.target.closest('.fc-more-link')) return;
                closeDayPopover();
            }, true);

            // Tombol close popover: mainkan animasi keluar sebelum ditutup
            document.addEventListener('click', function(event) {
                const closeBtn = event.target.closest && event.target.closest('.fc-popover-close');
                if (!closeBtn) return;
                const popover = closeBtn.closest('.fc-popover');
                if (popover && popover.classList.contains('popover-closing')) return;
                event.preventDefault();
                event.stopPropagation();
                closeDayPopover();
            }, true);

            // Reposisi popover saat viewport berubah
            window.addEventListener('resize', function() {
                setTimeout(function() {
                    const popover = document.querySelector('.fc-popover');
                    if (popover) positionDayPopover(popover);
                }, 60);
            });

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
            const staffSelect = document.getElementById('kadivStaffFilter');
            const statusSelect = document.getElementById('kadivStatusFilter');

            staffSelect?.addEventListener('change', applyKadivFilters);
            statusSelect?.addEventListener('change', applyKadivFilters);
        });
    </script>

<script>
    // Tutup panel legenda saat klik di luar
    document.addEventListener('click', function (event) {
        document.querySelectorAll('.cal-legend[open]').forEach(function (legend) {
            if (!legend.contains(event.target)) {
                legend.removeAttribute('open');
            }
        });
    });
</script>

</body>

</html>
