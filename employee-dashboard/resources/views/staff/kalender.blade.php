<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender Kegiatan - PT Silindo</title>

    @vite('resources/css/app.css')

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
            padding: 3px 6px !important;
            margin: 2px 2px !important;
            font-size: 0.78rem !important;
            font-weight: 600 !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
            border-width: 1px !important;
            border-style: solid !important;
            cursor: pointer;
            transition: transform 0.1s ease, box-shadow 0.1s ease;
        }
        .fc-event:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        /* Sembunyikan dot/bullet agar full kotak */
        .fc-daygrid-event-dot {
            display: none !important;
        }
        .fc-event-time {
            font-weight: 700 !important;
            margin-right: 4px !important;
        }
    </style>
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">

    @include('component.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        @include('component.topbar')
        @include('component.breadcrumbs', ['parentText' => 'Dashboard', 'parentUrl' => url('/'), 'currentPage' => 'Kalender'])

        <main class="flex-1 overflow-y-auto p-8 pt-4">

            <!-- Page Title -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2.5">
                        <span class="p-2 rounded-lg bg-blue-100 text-[#004A65]">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </span>
                        <span>Kalender Jadwal & Tugas</span>
                    </h1>
                </div>

                <!-- Legend & Color indicators -->
                <div class="flex flex-wrap items-center gap-2 bg-white px-4 py-2.5 rounded-xl border border-slate-200 shadow-sm text-xs">
                    <span class="font-bold text-slate-600 mr-1">Status:</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-[#C0E7FF] text-blue-700 font-medium">
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span> Baru
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-100 text-emerald-700 font-medium">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Berjalan
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-amber-100 text-amber-700 font-medium">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> Menunggu di-acc
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-[#CCF4DB] text-emerald-700 font-medium">
                        <span class="w-2 h-2 rounded-full bg-[#059669]"></span> Sudah di-acc
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-red-100 text-red-700 font-medium">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span> Terlambat
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-purple-100 text-purple-700 font-medium">
                        <span class="w-2 h-2 rounded-full bg-purple-500"></span> Izin
                    </span>
                </div>
            </div>

            <!-- Calendar Card -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <div id="calendar"></div>
            </div>

        </main>

    </div>

    <!-- Modal Detail Event / Tugas -->
    <div id="modal-event" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4" style="display:none; pointer-events:none;">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-lg overflow-hidden transform transition-all duration-200" onclick="event.stopPropagation()">
            <!-- Header Modal -->
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span id="modal-badge" class="px-2.5 py-1 rounded-md text-xs font-semibold">Status</span>
                    <span id="modal-type" class="text-xs text-slate-500 font-medium"></span>
                </div>
                <button type="button" onclick="closeModalEvent()" class="text-slate-400 hover:text-slate-600 transition p-1 rounded-lg hover:bg-slate-200/60">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Body Modal -->
            <div class="p-6 space-y-4">
                <div>
                    <h3 id="modal-title" class="text-lg font-bold text-slate-800 leading-snug">Judul Tugas</h3>
                </div>

                <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200 space-y-2 text-sm">
                    <div class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <span id="modal-date-label" class="text-xs text-slate-500 font-medium block">Tenggat Waktu:</span>
                            <span id="modal-deadline" class="font-semibold text-slate-800">-</span>
                        </div>
                    </div>
                </div>

                <div>
                    <span class="text-xs text-slate-500 font-medium block mb-1">Deskripsi / Keterangan:</span>
                    <div id="modal-deskripsi" class="text-sm text-slate-600 bg-white border border-slate-200 rounded-xl p-3 max-h-40 overflow-y-auto whitespace-pre-line leading-relaxed">
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
                    <span>Lihat Detail Tugas</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <script>
        let calendarInstance = null;

        function openModalEvent(event) {
            const props = event.extendedProps || {};
            const modal = document.getElementById('modal-event');
            const titleEl = document.getElementById('modal-title');
            const badgeEl = document.getElementById('modal-badge');
            const typeEl = document.getElementById('modal-type');
            const deadlineEl = document.getElementById('modal-deadline');
            const dateLabelEl = document.getElementById('modal-date-label');
            const deskripsiEl = document.getElementById('modal-deskripsi');
            const detailBtn = document.getElementById('modal-detail-btn');

            titleEl.textContent = event.title;
            badgeEl.className = 'px-2.5 py-1 rounded-md text-xs font-semibold ' + (props.badge_class || 'bg-slate-100 text-slate-700');
            badgeEl.textContent = props.status_label || props.status || 'Event';
            typeEl.textContent = props.type === 'izin' ? 'Surat Izin' : 'Tugas Staff';

            if (dateLabelEl) {
                dateLabelEl.textContent = props.type === 'izin' ? 'Tanggal Izin:' : 'Tenggat Waktu:';
            }

            deadlineEl.textContent = props.deadline_label || '-';
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

                    // Ambil event dari endpoint kalender (bisa via web session atau API token)
                    fetch('/kalender/events', {
                        headers: headers
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Gagal mengambil data event');
                        return res.json();
                    })
                    .then(result => {
                        successCallback(result.data || []);
                    })
                    .catch(err => {
                        console.error('Error load events:', err);
                        failureCallback(err);
                    });
                },
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    openModalEvent(info.event);
                },
                eventDidMount: function(info) {
                    // Beri title tooltip bawaan browser
                    info.el.setAttribute('title', `${info.event.title} (${info.event.extendedProps.status_label || ''})`);
                }
            });

            calendarInstance.render();
        });
    </script>

</body>

</html>
