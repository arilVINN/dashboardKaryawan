@php
    $isCompact = $compact ?? false;
    $cellPadding = $isCompact ? 'px-4 py-3' : 'px-6 py-4';
@endphp

<div class="flex flex-col gap-3 w-full">
    <h2 class="text-xl font-bold text-slate-800">Tabel Pesan</h2>

    @unless ($isCompact)
        <form id="staffPesanFilter" onsubmit="return false;"
            class="flex flex-wrap items-end gap-4 bg-white border border-slate-200 rounded-xl shadow-sm p-5">
            <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
                <label for="staffPesanSearch" class="text-xs font-bold text-slate-500">Cari</label>
                <input type="text" id="staffPesanSearch" placeholder="Pengirim / topik..."
                    class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
            </div>
            <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
                <label for="staffPesanJenis" class="text-xs font-bold text-slate-500">Jenis</label>
                <select id="staffPesanJenis"
                    class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                    <option value="">Semua</option>
                    <option value="tugas">Pesan Tugas</option>
                    <option value="langsung">Pesan Langsung</option>
                </select>
            </div>
            <div class="flex shrink-0 gap-2 sm:ml-auto">
                <button type="button" id="staffPesanApply"
                    class="h-10 inline-flex items-center px-4 bg-[#004A65] text-white text-sm font-medium rounded-md hover:bg-[#003347] transition">Terapkan</button>
                <button type="button" id="staffPesanReset"
                    class="h-10 inline-flex items-center px-4 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">Reset</button>
            </div>
        </form>
    @endunless

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
        <div class="{{ $isCompact ? 'overflow-hidden' : 'overflow-x-auto' }}">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                    <tr>
                        @if (!$isCompact)
                            <th class="{{ $cellPadding }} font-medium whitespace-nowrap">
                                <button type="button" onclick="setStaffPesanSort('pengirim')"
                                    class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                    Pengirim <span data-sort-indicator="pengirim" class="text-slate-300">&#8597;</span>
                                </button>
                            </th>
                            <th class="{{ $cellPadding }} font-medium whitespace-nowrap">
                                <button type="button" onclick="setStaffPesanSort('judul')"
                                    class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                    Topik / Judul <span data-sort-indicator="judul" class="text-slate-300">&#8597;</span>
                                </button>
                            </th>
                            <th class="{{ $cellPadding }} font-medium whitespace-nowrap">
                                <button type="button" onclick="setStaffPesanSort('tanggal')"
                                    class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                    Tanggal <span data-sort-indicator="tanggal" class="text-slate-300">&#8597;</span>
                                </button>
                            </th>
                        @else
                            <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Pengirim</th>
                            <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Topik / Judul</th>
                            <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Tanggal</th>
                        @endif
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
    const staffPesanSearch = document.getElementById('staffPesanSearch');
    const staffPesanJenis = document.getElementById('staffPesanJenis');
    let staffPesanSort = null;
    let staffPesanDir = 'desc';
    let allStaffPesan = [];

    function staffPesanFormatDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
    }

    function staffPesanRows() {
        const q = (staffPesanSearch?.value || '').toLowerCase();
        const jenis = staffPesanJenis?.value || '';

        let list = allStaffPesan.slice();
        if (jenis) list = list.filter((p) => p.jenis === jenis);
        if (q) {
            list = list.filter((p) =>
                (p.judul || '').toLowerCase().includes(q) ||
                (p.pengirim || '').toLowerCase().includes(q)
            );
        }

        const sort = staffPesanSort || 'tanggal';
        const dir = staffPesanSort ? staffPesanDir : 'desc';

        list.sort((a, b) => {
            if (sort === 'tanggal') {
                const at = new Date(a.tanggal_raw || 0).getTime();
                const bt = new Date(b.tanggal_raw || 0).getTime();
                return dir === 'asc' ? at - bt : bt - at;
            }
            const av = (a[sort] || '').toLowerCase();
            const bv = (b[sort] || '').toLowerCase();
            return dir === 'asc' ? av.localeCompare(bv) : bv.localeCompare(av);
        });

        return list;
    }

    function renderStaffPesan() {
        const tbody = document.getElementById('tbody-pesan');
        if (!tbody) return;

        const isCompact = {{ $isCompact ? 'true' : 'false' }};
        let semuaPesan = staffPesanRows();
        if (isCompact) semuaPesan = semuaPesan.slice(0, 4);

        tbody.innerHTML = '';
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

        updateStaffPesanSortIndicators();
    }

    async function loadStaffPesan() {
        const token = localStorage.getItem('staff_token');
        const tbody = document.getElementById('tbody-pesan');
        if (!token || !tbody) return;

        try {
            const res = await fetch('/api/staff/pesan', {
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            const json = await res.json();

            // API mengembalikan 2 tipe pesan (dalam tugas & langsung); gabung jadi satu.
            const threads = (json.data || [])
                .filter(t => t.pesan_terakhir != null)
                .map(t => ({
                    jenis: 'tugas',
                    link_id: t.id_tugas,
                    judul: 'Tugas: ' + t.judul_tugas,
                    pengirim: t.pesan_terakhir.pengirim ? t.pesan_terakhir.pengirim.username : '-',
                    tanggal_raw: t.pesan_terakhir.tanggal_pesan,
                    tanggal: staffPesanFormatDate(t.pesan_terakhir.tanggal_pesan),
                }));

            const langsung = (json.pesan_langsung || []).map(p => ({
                jenis: 'langsung',
                link_id: p.id_pesan,
                judul: p.judul_pesan || 'Pesan Langsung',
                pengirim: p.pengirim ? p.pengirim.username : '-',
                tanggal_raw: p.tanggal_pesan,
                tanggal: staffPesanFormatDate(p.tanggal_pesan),
            }));

            allStaffPesan = [...threads, ...langsung];
            renderStaffPesan();
        } catch (error) {
            console.error('Gagal meload pesan', error);
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-6 text-red-500">Gagal mengambil data pesan</td></tr>';
        }
    }

    function updateStaffPesanSortIndicators() {
        document.querySelectorAll('[data-sort-indicator]').forEach((el) => {
            if (el.dataset.sortIndicator === staffPesanSort) {
                el.textContent = staffPesanDir === 'asc' ? '\u25B2' : '\u25BC';
                el.classList.remove('text-slate-300');
                el.classList.add('text-[#004A65]');
            } else {
                el.textContent = '\u2195';
                el.classList.add('text-slate-300');
                el.classList.remove('text-[#004A65]');
            }
        });
    }

    window.setStaffPesanSort = function (col) {
        if (staffPesanSort === col) {
            staffPesanDir = staffPesanDir === 'asc' ? 'desc' : 'asc';
        } else {
            staffPesanSort = col;
            staffPesanDir = col === 'tanggal' ? 'desc' : 'asc';
        }
        renderStaffPesan();
    };

    document.addEventListener('DOMContentLoaded', function () {
        loadStaffPesan();
        document.getElementById('staffPesanApply')?.addEventListener('click', renderStaffPesan);
        document.getElementById('staffPesanReset')?.addEventListener('click', () => {
            if (staffPesanSearch) staffPesanSearch.value = '';
            if (staffPesanJenis) staffPesanJenis.value = '';
            staffPesanSort = null;
            staffPesanDir = 'desc';
            renderStaffPesan();
        });
    });
</script>

