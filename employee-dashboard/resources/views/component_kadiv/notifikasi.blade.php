<div class="flex flex-col gap-2">
    <div class="flex items-center justify-between px-1">
        <h2 class="text-lg font-semibold text-[#172B36]">Notifikasi</h2>
        <a href="{{ url('/kadiv/pesan') }}" class="text-xs text-[#146C94] hover:underline">
            Lihat semua &rsaquo;
        </a>
    </div>

    <div class="overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">
        <div id="kadiv-notifikasi-list" class="divide-y divide-[#EEF2F5]">
            <p class="px-4 py-3 text-[13px] text-[#4B5563]">Memuat notifikasi...</p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('kadiv-notifikasi-list');
        if (!container) return;

        let readNotifications = new Set();

        try {
            readNotifications = new Set(JSON.parse(localStorage.getItem('kadiv_read_notifikasi') || '[]'));
        } catch (error) {
            readNotifications = new Set();
        }

        const escapeHtml = (value) => String(value || '').replace(/[&<>"']/g, (character) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        })[character]);

        const formatTime = (value) => {
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

        const configFor = (judul = '') => {
            const lower = judul.toLowerCase();

            if (lower.includes('pesan') || lower.includes('surat')) {
                return {
                    title: 'Pesan baru',
                    iconClass: 'bg-[#E3F1F7] text-[#146C94]',
                    linkUrl: '/kadiv/pesan',
                    iconSvg: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />`
                };
            }

            if (lower.includes('ulang tahun')) {
                return {
                    title: 'Ulang tahun karyawan',
                    iconClass: 'bg-sky-100 text-sky-600',
                    linkUrl: '/kadiv/dashboard',
                    iconSvg: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h16v5.5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V12Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 14.5c1.3 0 1.3 1.2 2.6 1.2s1.3-1.2 2.6-1.2 1.3 1.2 2.6 1.2 1.3-1.2 2.6-1.2 1.3 1.2 2.6 1.2 1.3-1.2 2.6-1.2 1.3 1.2 2.6 1.2M8 9V5m4 4V3m4 6V5" /><circle cx="8" cy="3.5" r=".8" fill="currentColor" stroke="none" /><circle cx="12" cy="1.8" r=".8" fill="currentColor" stroke="none" /><circle cx="16" cy="3.5" r=".8" fill="currentColor" stroke="none" />`
                };
            }

            if (lower.includes('revisi')) {
                return {
                    title: 'Revisi tugas',
                    iconClass: 'bg-[#FEF0C3] text-[#B45309]',
                    linkUrl: '/kadiv/tugas',
                    iconSvg: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" />`
                };
            }

            if (lower.includes('pengumpulan') || lower.includes('dikumpulkan') || lower.includes('submit')) {
                return {
                    title: 'Pengumpulan tugas',
                    iconClass: 'bg-[#E3F1F7] text-[#146C94]',
                    linkUrl: '/kadiv/tugas',
                    iconSvg: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />`
                };
            }

            if (lower.includes('tugas')) {
                return {
                    title: 'Tugas baru',
                    iconClass: 'bg-[#DCEEFE] text-[#2563EB]',
                    linkUrl: '/kadiv/tugas',
                    iconSvg: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14" />`
                };
            }

            return {
                title: 'Notifikasi',
                iconClass: 'bg-[#FEF0C3] text-[#B45309]',
                linkUrl: '/kadiv/pesan',
                iconSvg: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />`
            };
        };

        const notificationUrl = (notification, fallback) => {
            if (notification.pesan_id_pesan) {
                return '/kadiv/detailPesan/' + encodeURIComponent(notification.pesan_id_pesan);
            }

            if (notification.tugas_id_tugas) {
                return '/kadiv/detailTugas/' + encodeURIComponent(notification.tugas_id_tugas);
            }

            return fallback;
        };

        const render = (notifications) => {
            container.replaceChildren();

            if (!notifications || notifications.length === 0) {
                container.innerHTML = '<p class="px-4 py-3 text-[13px] text-[#4B5563]">Belum ada notifikasi</p>';
                return;
            }

            notifications.slice(0, 5).forEach((notification) => {
                const config = configFor(notification.judul_notifikasi);
                const readKey = `${notification.id_notifikasi || ''}|${String(notification.tanggal_notifikasi || notification.created_at || '').slice(0, 10)}`;
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
                            <span class="shrink-0 whitespace-nowrap text-[11px] text-[#4B5563]">${formatTime(notification.created_at || notification.tanggal_notifikasi)}</span>
                        </span>
                        <span class="mt-0.5 block text-xs leading-[1.45] text-[#4B5563]">${escapeHtml(notification.isi_notif || '')}</span>
                    </span>
                `;

                row.addEventListener('click', () => {
                    readNotifications.add(readKey);
                    localStorage.setItem('kadiv_read_notifikasi', JSON.stringify([...readNotifications]));
                    row.querySelector('[data-unread-dot]')?.remove();
                });

                container.appendChild(row);
            });
        };

        const loadNotifications = () => {
            window.fetchKadivDashboardData?.()
                .then((dashboardData) => render(dashboardData?.notifikasi_terbaru || []))
                .catch((error) => {
                    console.error('Gagal memuat notifikasi Kadiv:', error);
                });
        };

        loadNotifications();
        window.setInterval(loadNotifications, 10000);
    });
</script>
