<div id="notifikasi-container" class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-5">
    <!-- Teks ini akan terganti oleh notifikasi asli via JavaScript -->
    <p class="text-sm text-slate-500 col-span-3">Memuat notifikasi...</p>
</div>

<script>
    document.addEventListener('DOMContentLoaded', async function() {
        const token = sessionStorage.getItem('staff_token');
        const container = document.getElementById('notifikasi-container');
        if (!token || !container) return;

        try {
            const response = await fetch('/api/staff/dashboard', {
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });
            const json = await response.json();
            const notifikasi = json.data.notifikasi_terbaru;

            container.innerHTML = ''; // Hapus tulisan 'Memuat...'

            if (!notifikasi || notifikasi.length === 0) {
                container.innerHTML = '<p class="text-sm text-slate-500 col-span-3">Tidak ada notifikasi baru.</p>';
                return;
            }

            // Looping data notifikasi maksimal 3
            notifikasi.slice(0, 3).forEach(n => {
                const item = `
                <div class="rounded-xl border border-slate-100 shadow-sm bg-white p-5 flex flex-col w-full max-w-sm">
                    <div class="flex justify-start">
                        <span class="text-[11px] font-semibold bg-slate-100 text-slate-600 px-3 py-1 rounded-lg">
                            Notifikasi
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
                            <span class="text-xs font-medium">${n.tanggal_notifikasi}</span>
                        </div>
                    </div>
                </div>`;
                container.innerHTML += item;
            });

        } catch (error) {
            console.error('Gagal memuat notifikasi:', error);
            container.innerHTML = '<p class="text-sm text-red-500 col-span-3">Gagal memuat notifikasi dari server.</p>';
        }
    });
</script>
