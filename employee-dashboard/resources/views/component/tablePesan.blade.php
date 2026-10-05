@php
    $isCompact = $compact ?? false;
    $cellPadding = $isCompact ? 'px-4 py-3' : 'px-6 py-4';
@endphp

<div class="flex flex-col gap-3 w-full">
    <h2 class="text-xl font-bold text-slate-800">Tabel Pesan</h2>
    
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
        <div class="{{ $isCompact ? 'overflow-hidden' : 'overflow-x-auto' }}">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Pengirim</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Topik / Judul</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Tanggal</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbody-pesan" class="divide-y divide-slate-200">
                    <tr id="pesan-loading">
                        <td colspan="4" class="text-center py-6 text-slate-400">Memuat data pesan...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', async function() {
        const token = localStorage.getItem('staff_token');
        const tbody = document.getElementById('tbody-pesan');
        if (!token || !tbody) return;

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
        }

        try {
            const res = await fetch('/api/staff/pesan', {
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            const json = await res.json();

            // API mengembalikan 2 tipe pesan (Pesan dalam Tugas & Pesan Langsung)
            // Kita olah dan gabung jadi satu
            const threads = (json.data || [])
                .filter(t => t.pesan_terakhir != null) 
                .map(t => ({
                    link_id: t.id_tugas, 
                    judul: 'Tugas: ' + t.judul_tugas,
                    pengirim: t.pesan_terakhir.pengirim ? t.pesan_terakhir.pengirim.username : '-',
                    tanggal_raw: t.pesan_terakhir.tanggal_pesan,
                    tanggal: formatDate(t.pesan_terakhir.tanggal_pesan)
                }));
                
            const langsung = (json.pesan_langsung || []).map(p => ({
                link_id: p.id_pesan,
                judul: p.judul_pesan || 'Pesan Langsung',
                pengirim: p.pengirim ? p.pengirim.username : '-',
                tanggal_raw: p.tanggal_pesan,
                tanggal: formatDate(p.tanggal_pesan)
            }));

            // Gabung array dan urutkan berdasar tanggal
            let semuaPesan = [...threads, ...langsung].sort((a, b) => {
                return new Date(b.tanggal_raw) - new Date(a.tanggal_raw);
            });
            
            const isCompact = {{ $isCompact ? 'true' : 'false' }};
            if (isCompact) {
                semuaPesan = semuaPesan.slice(0, 4);
            }

            tbody.innerHTML = ''; // Hapus loading

            if (semuaPesan.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center py-6 text-slate-400">Belum ada pesan masuk.</td></tr>';
                return;
            }

            semuaPesan.forEach(p => {
                const rowHtml = `
                    <tr class="hover:bg-slate-50 transition">
                        <td class="{{ $cellPadding }} font-semibold text-slate-800 whitespace-nowrap">
                            ${p.pengirim}
                        </td>
                        <td class="{{ $cellPadding }} max-w-[160px] truncate text-slate-500">
                            ${p.judul}
                        </td>
                        <td class="{{ $cellPadding }} whitespace-nowrap text-slate-500">
                            ${p.tanggal}
                        </td>
                        <td class="{{ $cellPadding }} whitespace-nowrap text-center">
                            <a href="/pesan/detail/${p.link_id}" class="text-[#0097B2] hover:text-[#008199] font-medium hover:underline">
                                Baca
                            </a>
                        </td>
                    </tr>`;
                tbody.innerHTML += rowHtml;
            });

        } catch (error) {
            console.error('Gagal meload pesan', error);
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-6 text-red-500">Gagal mengambil data pesan</td></tr>';
        }
    });
</script>