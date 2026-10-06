@php
    $isCompact = $compact ?? false;
    $cellPadding = $isCompact ? 'px-4 py-3' : 'px-6 py-4';
@endphp

<div class="flex flex-col gap-3">
    <h2 class="text-xl font-bold text-slate-800">Tabel Tugas</h2>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="{{ $isCompact ? 'overflow-hidden' : 'overflow-x-auto' }}">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-medium whitespace-nowrap">Judul Tugas</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap">Tenggat Waktu</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 font-medium whitespace-nowrap text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbody-tugas" class="divide-y divide-slate-200">
                    <tr id="tugas-loading">
                        <td colspan="4" class="text-center py-6 text-slate-400">Memuat data tugas dari database...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', async function() {
        const token = sessionStorage.getItem('staff_token');
        const tbody = document.getElementById('tbody-tugas');
        if (!token || !tbody) return;

        // Utility untuk warna status
        const STATUS_CLASS = {
            'baru':         'bg-[#C0E7FF] text-blue-700',
            'berjalan':     'bg-[#DFE4EA] text-black-700',
            'menunggu acc': 'bg-[#FFF7ED] text-[#C2410C]',
            'menunggu_acc': 'bg-[#FFF7ED] text-[#C2410C]',
            'sudah acc':    'bg-[#CCF4DB] text-emerald-700',
            'sudah_acc':    'bg-[#CCF4DB] text-emerald-700',
            'telat':        'bg-red-100 text-red-700',
        };

        // Fungsi mengubah huruf pertama tiap kata jadi kapital (misal: "menunggu acc" -> "Menunggu Acc")
        function ucwords(str) {
            return (str + '').replace(/_/g, ' ').replace(/^(.)|\s+(.)/g, function ($1) {
                return $1.toUpperCase();
            });
        }

        try {
            const res = await fetch('/api/staff/tugas', {
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            const json = await res.json();
            let tugas = json.data;
            const isCompact = {{ $isCompact ? 'true' : 'false' }};

            tbody.innerHTML = ''; // Hapus tulisan loading

            if (!tugas || tugas.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center py-6 text-slate-400">Belum ada tugas yang ditugaskan kepada Anda.</td></tr>';
                return;
            }

            // Urutkan tugas berdasarkan yang terbaru
            tugas.sort((a, b) => new Date(b.tanggal_update || b.tanggal_dibuat) - new Date(a.tanggal_update || a.tanggal_dibuat));
            
            // Jika dipanggil dari Dashboard (compact), hanya tampilkan 4 terbaru
            if (isCompact) {
                tugas = tugas.slice(0, 4);
            }

            // Looping untuk memunculkan semua tugas
            tugas.forEach(t => {
                const statusRaw = (t.status || 'baru').toLowerCase();
                const sc = STATUS_CLASS[statusRaw] || 'bg-slate-100 text-slate-700';
                
                // Format Tanggal (cth: YYYY-MM-DD ke DD/MM/YYYY)
                let deadline = '-';
                if (t.deadline) {
                    const d = new Date(t.deadline);
                    const tgl = String(d.getDate()).padStart(2, '0');
                    const bln = String(d.getMonth() + 1).padStart(2, '0');
                    const thn = d.getFullYear();
                    deadline = `${tgl}/${bln}/${thn}`;
                }

                const rowHtml = `
                    <tr class="hover:bg-slate-50 transition">
                        <td class="{{ $cellPadding }} font-medium text-slate-800 whitespace-nowrap">
                            ${t.judul_tugas}
                        </td>
                        <td class="{{ $cellPadding }} whitespace-nowrap text-slate-600">
                            ${deadline}
                        </td>
                        <td class="{{ $cellPadding }} whitespace-nowrap">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold ${sc}">
                                ${ucwords(t.status)}
                            </span>
                        </td>
                        <td class="{{ $cellPadding }} whitespace-nowrap text-center">
                            <a href="/tugas/detail/${t.id_tugas}" class="text-[#0097B2] hover:text-[#008199] font-medium hover:underline">
                                Lihat
                            </a>
                        </td>
                    </tr>`;
                
                tbody.innerHTML += rowHtml;
            });

        } catch (error) {
            console.error('Gagal meload tugas', error);
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-6 text-red-500">Gagal mengambil data tugas</td></tr>';
        }
    });
</script>