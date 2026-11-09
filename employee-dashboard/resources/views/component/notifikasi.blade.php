<div class="flex flex-col gap-2">
    <div class="flex items-center justify-between px-1">
        <h2 class="text-lg font-semibold text-[#172B36]">Notifikasi</h2>
        <a href="/tugas" class="text-xs text-[#146C94] hover:underline">Lihat semua &rsaquo;</a>
    </div>

    <div class="overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">
        <div id="notifikasi-container" class="divide-y divide-[#EEF2F5]">
            <p class="px-4 py-3 text-[13px] text-[#4B5563]">Memuat notifikasi...</p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const token = sessionStorage.getItem('staff_token') || localStorage.getItem('staff_token');
        const container = document.getElementById('notifikasi-container');
        const toastContainer = document.getElementById('toast-container');
        if (!token || !container) return;

        // Interval polling: 10 detik (10000 ms)
        const POLL_INTERVAL = 10000;

        // Menyimpan ID notifikasi yang sudah pernah diterima
        let knownNotifIds = new Set();
        let isInitialLoad = true;
        let readNotifications = new Set();

        try {
            readNotifications = new Set(JSON.parse(localStorage.getItem('staff_read_notifications') || '[]'));
        } catch (error) {
            readNotifications = new Set();
        }

        const escapeNotificationHtml = (value) => String(value || '').replace(/[&<>"']/g, (character) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        })[character]);

        const notificationReadKey = (notification) => {
            const date = String(notification.tanggal_notifikasi || notification.created_at || '').slice(0, 10);
            return `${notification.id_notifikasi || ''}|${date}`;
        };

        // Format tanggal dan waktu Indonesia (24 jam dengan titik dua).
        const formatNotificationTime = (value) => {
            if (!value) return '-';

            const date = new Date(String(value).replace(' ', 'T'));
            if (Number.isNaN(date.getTime())) return value;

            const options = { day: 'numeric', month: 'short' };
            if (date.getFullYear() !== new Date().getFullYear()) {
                options.year = 'numeric';
            }

            const day = date.toLocaleDateString('id-ID', options);
            const time = date.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                hourCycle: 'h23'
            }).replace(/\./g, ':');

            return `${day}, ${time}`;
        };

        // Efek nada dering halus browser (Web Audio API)
        function playChime() {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                const ctx = new AudioContext();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();

                osc.type = 'sine';
                osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
                osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.15); // A5

                gain.gain.setValueAtTime(0.15, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.35);

                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.35);
            } catch (e) {
                // Abaikan jika browser membatasi autoplay suara
            }
        }

        // Konfigurasi tampilan Toast berdasarkan jenis notifikasi (Tugas vs Pesan)
        function getNotifConfig(judul = '') {
            const lower = judul.toLowerCase();

            if (lower.includes('pesan') || lower.includes('chat')) {
                return {
                    badgeText: 'Pesan Baru',
                    badgeClass: 'text-emerald-700 bg-emerald-100 border-emerald-200',
                    borderClass: 'border-emerald-200',
                    iconBg: 'bg-emerald-100 text-emerald-600',
                    linkUrl: '/pesan',
                    actionLabel: 'Buka Pesan',
                    iconSvg: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />`
                };
            }

            // Pengingat (Catatan Kalender & Tenggat Tugas)
            if (lower.includes('pengingat') || lower.includes('catatan') || lower.includes('kalender') || lower.includes('tenggat')) {
                const isTugas = lower.includes('tugas');
                return {
                    badgeText: 'Pengingat',
                    badgeClass: isTugas ? 'text-amber-700 bg-amber-100 border-amber-200' : 'text-violet-700 bg-violet-100 border-violet-200',
                    borderClass: isTugas ? 'border-amber-200' : 'border-violet-200',
                    iconBg: isTugas ? 'bg-amber-100 text-amber-600' : 'bg-violet-100 text-violet-600',
                    linkUrl: isTugas ? '/tugas' : '/kalender',
                    actionLabel: isTugas ? 'Lihat Tugas' : 'Buka Kalender',
                    iconSvg: isTugas
                        ? `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />`
                        : `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />`
                };
            }

            if (lower.includes('revisi')) {
                return {
                    badgeText: 'Revisi Tugas',
                    badgeClass: 'text-amber-700 bg-amber-100 border-amber-200',
                    borderClass: 'border-amber-200',
                    iconBg: 'bg-amber-100 text-amber-600',
                    linkUrl: '/tugas',
                    actionLabel: 'Lihat Revisi',
                    iconSvg: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" />`
                };
            }

            if (lower.includes('tugas')) {
                return {
                    badgeText: 'Tugas Baru',
                    badgeClass: 'text-blue-700 bg-blue-100 border-blue-200',
                    borderClass: 'border-blue-200',
                    iconBg: 'bg-blue-100 text-blue-600',
                    linkUrl: '/tugas',
                    actionLabel: 'Lihat Tugas',
                    iconSvg: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2H9m2 0a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />`
                };
            }

            // Default / Pengumuman
            return {
                badgeText: 'Notifikasi',
                badgeClass: 'text-amber-700 bg-amber-100 border-amber-200',
                borderClass: 'border-amber-200',
                iconBg: 'bg-amber-100 text-amber-600',
                linkUrl: '/tugas',
                actionLabel: 'Lihat',
                iconSvg: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />`
            };
        }

        function getNotifListConfig(judul = '') {
            const lower = judul.toLowerCase();

            if (lower.includes('pesan') || lower.includes('chat')) {
                return {
                    title: 'Pesan baru',
                    iconClass: 'bg-emerald-100 text-emerald-600',
                    linkUrl: '/pesan',
                    iconSvg: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />`
                };
            }

            if (lower.includes('pengingat') || lower.includes('tenggat') || lower.includes('catatan') || lower.includes('kalender')) {
                return {
                    title: 'Pengingat tenggat',
                    iconClass: 'bg-[#FEF0C3] text-[#B45309]',
                    linkUrl: lower.includes('kalender') || lower.includes('catatan') ? '/kalender' : '/tugas',
                    iconSvg: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />`
                };
            }

            if (lower.includes('revisi')) {
                return {
                    title: 'Revisi tugas',
                    iconClass: 'bg-[#FEF0C3] text-[#B45309]',
                    linkUrl: '/tugas',
                    iconSvg: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" />`
                };
            }

            if (lower.includes('tugas')) {
                return {
                    title: 'Tugas baru',
                    iconClass: 'bg-[#DCEEFE] text-[#2563EB]',
                    linkUrl: '/tugas',
                    iconSvg: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14" />`
                };
            }

            return {
                title: 'Notifikasi',
                iconClass: 'bg-[#FEF0C3] text-[#B45309]',
                linkUrl: '/tugas',
                iconSvg: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />`
            };
        }

        function notificationUrl(notification, fallback) {
            if (notification.pesan_id_pesan) {
                return '/pesan/detail/' + encodeURIComponent(notification.pesan_id_pesan);
            }

            if (notification.tugas_id_tugas) {
                return '/tugas/detail/' + encodeURIComponent(notification.tugas_id_tugas);
            }

            return fallback;
        }

        function saveReadNotifications() {
            localStorage.setItem('staff_read_notifications', JSON.stringify([...readNotifications]));
        }

        function markNotificationRead(notification, row) {
            readNotifications.add(notificationReadKey(notification));
            saveReadNotifications();
            row.querySelector('[data-unread-dot]')?.remove();
        }

        // Tampilkan pop-up Toast
        function showNotificationToast(notif) {
            if (!toastContainer) return;

            const config = getNotifConfig(notif.judul_notifikasi);
            const toastUrl = notificationUrl(notif, config.linkUrl);
            const toast = document.createElement('div');
            toast.className = `pointer-events-auto flex items-start gap-3 p-4 bg-white border ${config.borderClass} shadow-xl rounded-2xl w-80 md:w-96 transform transition-all duration-150 ease-out translate-y-[-20px] opacity-0`;

            toast.innerHTML = `
                <div class="p-2.5 ${config.iconBg} rounded-xl shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        ${config.iconSvg}
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full border ${config.badgeClass}">
                            ${config.badgeText}
                        </span>
                        <button type="button" class="btn-close text-slate-400 hover:text-slate-600 focus:outline-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <p class="text-sm font-bold text-slate-800 mt-1 truncate">${escapeNotificationHtml(notif.judul_notifikasi || 'Pemberitahuan Baru')}</p>
                    <p class="text-xs text-slate-600 mt-0.5 line-clamp-2 leading-relaxed">${escapeNotificationHtml(notif.isi_notif || '')}</p>

                    <div class="mt-2.5 flex items-center justify-between">
                        <span class="text-[10px] text-slate-400 font-medium">Baru saja</span>
                        <a href="${toastUrl}" class="text-xs font-semibold text-[#004A65] hover:underline inline-flex items-center gap-1">
                            ${config.actionLabel} &rarr;
                        </a>
                    </div>
                </div>
            `;

            toastContainer.appendChild(toast);

            // Animasi masuk
            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-[-20px]', 'opacity-0');
                toast.classList.add('translate-y-0', 'opacity-100');
            });

            // Fungsi tutup toast
            const closeBtn = toast.querySelector('.btn-close');
            const removeToast = () => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('translate-y-[-20px]', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            };

            closeBtn?.addEventListener('click', removeToast);
            setTimeout(removeToast, 7000); // Otomatis hilang dalam 7 detik
        }

        function renderList(notifikasiList) {
            container.replaceChildren();

            if (!notifikasiList || notifikasiList.length === 0) {
                container.innerHTML = '<p class="px-4 py-3 text-[13px] text-[#4B5563]">Belum ada notifikasi</p>';
                return;
            }

            notifikasiList.slice(0, 5).forEach((notification) => {
                const config = getNotifListConfig(notification.judul_notifikasi);
                const readKey = notificationReadKey(notification);
                const isUnread = !readNotifications.has(readKey);
                const row = document.createElement('a');
                row.href = notificationUrl(notification, config.linkUrl);
                row.className = 'flex items-start gap-3 px-4 py-3 transition hover:bg-[#F5FAFC] focus:bg-[#F5FAFC] focus:outline-none';
                row.innerHTML = `
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg ${config.iconClass}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            ${config.iconSvg}
                        </svg>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="flex items-center justify-between gap-2">
                            <span class="flex min-w-0 items-center gap-1.5">
                                <span class="truncate text-[13px] font-medium text-[#172B36]">${config.title}</span>
                                ${isUnread ? '<span class="h-1.5 w-1.5 shrink-0 rounded-full bg-[#146C94]" data-unread-dot></span>' : ''}
                            </span>
                            <span class="shrink-0 whitespace-nowrap text-[11px] text-[#4B5563]">${formatNotificationTime(notification.created_at || notification.tanggal_notifikasi)}</span>
                        </span>
                        <span class="mt-0.5 block text-xs leading-[1.45] text-[#4B5563]">${escapeNotificationHtml(notification.isi_notif || '')}</span>
                    </span>
                `;
                row.addEventListener('click', () => {
                    markNotificationRead(notification, row);
                });
                container.appendChild(row);
            });
        }

        // Fungsi polling data notifikasi ke backend
        async function fetchNotifikasi() {
            try {
                const dashboardData = await window.fetchStaffDashboardData?.();
                if (!dashboardData) return;

                const notifikasiList = dashboardData.notifikasi_terbaru || [];

                if (isInitialLoad) {
                    // Simpan daftar ID notifikasi yang sudah ada saat pertama buka web
                    notifikasiList.forEach(n => knownNotifIds.add(n.id_notifikasi));
                    renderList(notifikasiList);
                    isInitialLoad = false;
                } else {
                    // Cari notifikasi yang baru masuk sejak load sebelumnya
                    let hasNewNotification = false;
                    const newItems = [];

                    notifikasiList.forEach(n => {
                        if (!knownNotifIds.has(n.id_notifikasi)) {
                            knownNotifIds.add(n.id_notifikasi);
                            newItems.push(n);
                            hasNewNotification = true;
                        }
                    });

                    if (hasNewNotification) {
                        playChime();
                        newItems.forEach(n => showNotificationToast(n));
                        renderList(notifikasiList);

                        // Cek apakah ada notifikasi tugas baru atau pesan baru untuk refresh tabel otomatis
                        const hasNewTugas = newItems.some(n => (n.judul_notifikasi || '').toLowerCase().includes('tugas'));
                        const hasNewPesan = newItems.some(n => (n.judul_notifikasi || '').toLowerCase().includes('pesan'));

                        if (hasNewTugas && typeof window.loadStaffTugas === 'function') {
                            window.loadStaffTugas();
                        }
                        if (hasNewPesan && typeof window.loadStaffPesan === 'function') {
                            window.loadStaffPesan();
                        }
                    }
                }
            } catch (error) {
                console.error('Gagal mengambil notifikasi:', error);
            }
        }

        // Panggil pertama kali
        fetchNotifikasi();

        // Polling berjalan otomatis setiap 10 detik
        const intervalId = setInterval(fetchNotifikasi, POLL_INTERVAL);
        window.addEventListener('beforeunload', () => clearInterval(intervalId));
    });
</script>
