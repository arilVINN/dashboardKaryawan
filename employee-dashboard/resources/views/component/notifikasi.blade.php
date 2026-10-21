<!-- Container Notifikasi di Dashboard -->
<div id="notifikasi-container" class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-5">
    <p class="text-sm text-slate-500 col-span-3">Memuat notifikasi...</p>
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

        // Format tanggal Indonesia
        const formatNotificationDate = (value) => {
            if (!value) return '-';
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return value;

            return new Intl.DateTimeFormat('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hourCycle: 'h23'
            }).format(date);
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

            if (lower.includes('tugas')) {
                return {
                    badgeText: 'Tugas Baru',
                    badgeClass: 'text-blue-700 bg-blue-100 border-blue-200',
                    borderClass: 'border-blue-200',
                    iconBg: 'bg-blue-100 text-blue-600',
                    linkUrl: '/tugas',
                    actionLabel: 'Lihat Tugas',
                    iconSvg: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />`
                };
            }

            // Default / Pengumuman
            return {
                badgeText: 'Notifikasi',
                badgeClass: 'text-amber-700 bg-amber-100 border-amber-200',
                borderClass: 'border-amber-200',
                iconBg: 'bg-amber-100 text-amber-600',
                linkUrl: '#',
                actionLabel: 'Lihat',
                iconSvg: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />`
            };
        }

        // Tampilkan pop-up Toast
        function showNotificationToast(notif) {
            if (!toastContainer) return;

            const config = getNotifConfig(notif.judul_notifikasi);
            const toast = document.createElement('div');
            toast.className = `pointer-events-auto flex items-start gap-3 p-4 bg-white border ${config.borderClass} shadow-xl rounded-2xl w-80 md:w-96 transform transition-all duration-300 ease-out translate-y-[-20px] opacity-0`;

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
                    <p class="text-sm font-bold text-slate-800 mt-1 truncate">${notif.judul_notifikasi || 'Pemberitahuan Baru'}</p>
                    <p class="text-xs text-slate-600 mt-0.5 line-clamp-2 leading-relaxed">${notif.isi_notif || ''}</p>
                    
                    <div class="mt-2.5 flex items-center justify-between">
                        <span class="text-[10px] text-slate-400 font-medium">Baru saja</span>
                        <a href="${config.linkUrl}" class="text-xs font-semibold text-[#004A65] hover:underline inline-flex items-center gap-1">
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

        // Render 3 kartu notifikasi teratas di halaman dashboard
        function renderCards(notifikasiList) {
            container.innerHTML = '';

            if (!notifikasiList || notifikasiList.length === 0) {
                container.innerHTML = '<p class="text-sm text-slate-500 col-span-3">Tidak ada notifikasi baru.</p>';
                return;
            }

            notifikasiList.slice(0, 3).forEach(n => {
                const config = getNotifConfig(n.judul_notifikasi);
                const item = `
                <div class="rounded-xl border border-slate-100 shadow-sm bg-white p-5 flex flex-col w-full max-w-sm transition hover:shadow-md">
                    <div class="flex justify-start">
                        <span class="text-[11px] font-semibold px-2.5 py-1 rounded-lg border ${config.badgeClass}">
                            ${config.badgeText}
                        </span>
                    </div>
                    <div class="mt-4 flex flex-col">
                        <h3 class="text-lg font-bold text-slate-900">${n.judul_notifikasi}</h3>
                        <p class="text-sm text-slate-600 mt-1.5 leading-relaxed">${n.isi_notif}</p>
                    </div>
                    <div class="flex justify-between items-center mt-8">
                        <div class="flex items-center gap-2 text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-xs font-medium">${formatNotificationDate(n.created_at || n.tanggal_notifikasi)}</span>
                        </div>
                    </div>
                </div>`;
                container.innerHTML += item;
            });
        }

        // Fungsi polling data notifikasi ke backend
        async function fetchNotifikasi() {
            try {
                const response = await fetch('/api/staff/dashboard', {
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) return;

                const json = await response.json();
                const notifikasiList = json.data?.notifikasi_terbaru || [];

                if (isInitialLoad) {
                    // Simpan daftar ID notifikasi yang sudah ada saat pertama buka web
                    notifikasiList.forEach(n => knownNotifIds.add(n.id_notifikasi));
                    renderCards(notifikasiList);
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
                        renderCards(notifikasiList);

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