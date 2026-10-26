<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender Kegiatan Divisi - PT Silindo</title>

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
        }
        /* Tabel FullCalendar kotak tegas 90 derajat (tanpa lengkungan) */
        .fc-scrollgrid,
        .fc-theme-standard .fc-scrollgrid {
            border: 1px solid #CBD5E1 !important;
            border-radius: 0 !important;
        }
        .fc-scrollgrid-section-header,
        .fc-scrollgrid-section-header > th,
        .fc-scrollgrid-section-header > td,
        .fc-scroller-harness {
            padding: 0 !important;
            margin: 0 !important;
        }
        /* Semua sel dan garis menggunakan #CBD5E1 */
        .fc-theme-standard th,
        .fc-theme-standard td,
        .fc-scrollgrid-sync-table td {
            border-color: #CBD5E1 !important;
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
        .fc-daygrid-day-number {
            color: #334155 !important;
            font-weight: 600 !important;
            padding: 6px 8px !important;
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

    @include('component_kadiv.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        @include('component.topbar')
        @include('component.breadcrumbs', [
            'parentText' => 'Dashboard',
            'parentUrl' => url('/kadiv/dashboard'),
            'currentPage' => 'Kalender Divisi',
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

            <!-- Toolbar Filter Khusus Kadiv -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm mb-5 flex flex-wrap items-center gap-3">
                <!-- Filter Staff -->
                <div class="flex items-center gap-2">
                    <label for="kadivStaffFilter" class="text-xs font-bold text-slate-600 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                        <span>Penerima:</span>
                    </label>
                    <select id="kadivStaffFilter" class="text-xs bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 font-medium text-slate-700 outline-none focus:border-[#004A65]">
                        <option value="">Semua Karyawan (Divisi)</option>
                        @isset($staffList)
                            @foreach($staffList as $staff)
                                <option value="{{ $staff->id_karyawan }}">{{ $staff->nama }} ({{ $staff->jabatan ?? 'Staff' }})</option>
                            @endforeach
                        @endisset
                    </select>
                </div>

                <!-- Filter Status -->
                <div class="flex items-center gap-2">
                    <label for="kadivStatusFilter" class="text-xs font-bold text-slate-600">Status:</label>
                    <select id="kadivStatusFilter" class="text-xs bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 font-medium text-slate-700 outline-none focus:border-[#004A65]">
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
                <div id="calendar"></div>
            </div>

        </main>

    </div>

    <!-- Modal Detail Event / Tugas Khusus Kadiv -->
    <div id="modal-event" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4" style="display:none; pointer-events:none;">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-lg overflow-hidden transform transition-all duration-200" onclick="event.stopPropagation()">
            <!-- Header Modal -->
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span id="modal-badge" class="px-2.5 py-1 rounded-md text-xs font-semibold">Status</span>
                    <span class="text-xs bg-blue-100 text-[#004A65] font-semibold px-2 py-0.5 rounded">Tugas Divisi</span>
                </div>
                <button type="button" onclick="closeModalEvent()" class="text-slate-400 hover:text-slate-600 transition p-1 rounded-lg hover:bg-slate-200/60">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Body Modal -->
            <div class="p-6 space-y-4">
                <!-- Info Karyawan Penerima Tugas (Nama saja tanpa avatar/PP) -->
                <div class="bg-sky-50 border border-sky-200 rounded-xl p-3.5">
                    <span class="text-[10px] font-bold text-sky-800 uppercase tracking-wider block">Ditugaskan Kepada:</span>
                    <span id="modal-staff-name" class="font-bold text-slate-900 text-base leading-tight block mt-0.5">Nama Karyawan</span>
                    <span id="modal-staff-sub" class="text-xs text-slate-500 block mt-0.5">Jabatan Staff</span>
                </div>

                <div>
                    <span class="text-xs text-slate-500 font-medium block mb-1">Judul Tugas:</span>
                    <h3 id="modal-title" class="text-lg font-bold text-slate-800 leading-snug">Judul Tugas</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-200 space-y-1 text-sm">
                        <span class="text-xs text-slate-500 font-medium block">Tenggat Waktu:</span>
                        <span id="modal-deadline" class="font-semibold text-slate-800 block">-</span>
                    </div>

                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-200 space-y-1 text-sm">
                        <span class="text-xs text-slate-500 font-medium block">Pengumpulan Tugas:</span>
                        <span id="modal-submission-status" class="inline-block px-2 py-0.5 rounded text-xs font-bold">-</span>
                    </div>
                </div>

                <div>
                    <span class="text-xs text-slate-500 font-medium block mb-1">Deskripsi Tugas:</span>
                    <div id="modal-deskripsi" class="text-sm text-slate-600 bg-white border border-slate-200 rounded-xl p-3 max-h-36 overflow-y-auto whitespace-pre-line leading-relaxed">
                        -
                    </div>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModalEvent()"
                        class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-100 transition">
                    Tutup
                </button>
                <a id="modal-detail-btn" href="#"
                   class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-[#004A65] rounded-lg hover:bg-[#003347] transition">
                    <span>Buka & Review Tugas</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <script>
        let calendarInstance = null;

        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function openModalEvent(event) {
            const props = event.extendedProps || {};
            const modal = document.getElementById('modal-event');
            const titleEl = document.getElementById('modal-title');
            const badgeEl = document.getElementById('modal-badge');
            const staffNameEl = document.getElementById('modal-staff-name');
            const staffSubEl = document.getElementById('modal-staff-sub');
            const deadlineEl = document.getElementById('modal-deadline');
            const submissionStatusEl = document.getElementById('modal-submission-status');
            const deskripsiEl = document.getElementById('modal-deskripsi');
            const detailBtn = document.getElementById('modal-detail-btn');

            titleEl.textContent = props.judul_murni || event.title;
            badgeEl.className = 'px-2.5 py-1 rounded-md text-xs font-semibold ' + (props.badge_class || 'bg-slate-100 text-slate-700');
            badgeEl.textContent = props.status_label || props.status || 'Event';

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

            if (props.detail_url) {
                detailBtn.href = props.detail_url;
                detailBtn.style.display = 'inline-flex';
            } else {
                detailBtn.style.display = 'none';
            }

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

        function applyKadivFilters() {
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

                    const staff = document.getElementById('kadivStaffFilter')?.value || '';
                    const status = document.getElementById('kadivStatusFilter')?.value || '';

                    const params = new URLSearchParams();
                    if (staff) params.set('staff', staff);
                    if (status) params.set('status', status);

                    const query = params.toString();
                    fetch('/kadiv/kalender/events' + (query ? '?' + query : ''), {
                        headers: headers
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Gagal mengambil data event divisi');
                        return res.json();
                    })
                    .then(result => {
                        successCallback(result.data || []);
                    })
                    .catch(err => {
                        console.error('Error load events kadiv:', err);
                        failureCallback(err);
                    });
                },
                /* Tampilan Kustom Event untuk Kadiv: Menampilkan Nama Karyawan dengan warna lingkaran senada */
                eventContent: function(arg) {
                    const props = arg.event.extendedProps || {};
                    const staffName = props.staff_nama || 'Staff';
                    const staffInitial = props.staff_initial || staffName.charAt(0).toUpperCase();
                    const title = props.judul_murni || arg.event.title;
                    const time = arg.timeText;
                    const dotColor = props.dot_color || '#004A65';
                    const isList = arg.view.type.startsWith('list');

                    const el = document.createElement('div');

                    if (isList) {
                        // List view: nama penuh tanpa truncate, layout horizontal
                        el.className = 'flex items-center gap-2 py-0.5 w-full';
                        el.innerHTML = `
                            <span class="w-5 h-5 rounded-full text-white flex items-center justify-center text-[9px] font-extrabold shrink-0"
                                  style="background-color: ${escapeHtml(dotColor)};">
                                ${escapeHtml(staffInitial)}
                            </span>
                            <span class="font-bold text-[12px] text-slate-800 shrink-0">${escapeHtml(staffName)}</span>
                            <span class="text-slate-400 shrink-0">—</span>
                            <span class="text-[12px] text-slate-700 font-medium" title="${escapeHtml(title)}">${escapeHtml(title)}</span>
                        `;
                    } else {
                        // Month view: layout compact dengan truncate
                        el.className = 'w-full px-1.5 py-1 leading-snug flex flex-col gap-0.5 overflow-hidden';
                        el.innerHTML = `
                            <div class="flex items-center justify-between gap-1 overflow-hidden">
                                <span class="inline-flex items-center gap-1.5 font-bold text-[10.5px] truncate max-w-[85%] text-slate-800">
                                    <span class="w-3.5 h-3.5 rounded-full text-white flex items-center justify-center text-[8px] font-extrabold shrink-0"
                                          style="background-color: ${escapeHtml(dotColor)};">
                                        ${escapeHtml(staffInitial)}
                                    </span>
                                    <span class="truncate font-bold">${escapeHtml(staffName)}</span>
                                </span>
                                ${time ? `<span class="text-[9.5px] font-bold opacity-75 shrink-0">${time}</span>` : ''}
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
                    openModalEvent(info.event);
                },
                eventDidMount: function(info) {
                    const staff = info.event.extendedProps.staff_nama || 'Staff';
                    info.el.setAttribute('title', `[${staff}] ${info.event.title}`);
                }
            });

            calendarInstance.render();

            // Event listener filter otomatis saat dropdown diganti
            document.getElementById('kadivStaffFilter')?.addEventListener('change', applyKadivFilters);
            document.getElementById('kadivStatusFilter')?.addEventListener('change', applyKadivFilters);
        });
    </script>

</body>

</html>
