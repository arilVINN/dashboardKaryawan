@php
    $isCompact = $compact ?? false;
    $cellPadding = $isCompact ? 'px-4 py-3' : 'px-6 py-4';
@endphp

<div class="flex flex-col gap-3">
    <h2 class="text-xl font-bold text-slate-800">Tabel Tugas</h2>

    @unless ($isCompact)
        <form id="staffTugasFilter" onsubmit="return false;"
            class="flex flex-wrap items-end gap-4 bg-white border border-slate-200 rounded-xl shadow-sm p-5">
            <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
                <label for="staffTugasSearch" class="text-xs font-bold text-slate-500">Cari Judul</label>
                <input type="text" id="staffTugasSearch" placeholder="Judul tugas..."
                    class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
            </div>
            <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
                <label for="staffTugasStatus" class="text-xs font-bold text-slate-500">Status</label>
                <select id="staffTugasStatus"
                    class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                    <option value="">Semua</option>
                    <option value="baru">Baru</option>
                    <option value="berjalan">Berjalan</option>
                    <option value="menunggu di-acc">Menunggu di-acc</option>
                    <option value="sudah di-acc">Sudah di-acc</option>
                    <option value="telat">Telat</option>
                </select>
            </div>
            <div class="flex shrink-0 gap-2 sm:ml-auto">
                <button type="button" id="staffTugasApply"
                    class="h-10 inline-flex items-center px-4 bg-[#004A65] text-white text-sm font-medium rounded-md hover:bg-[#003347] transition">Terapkan</button>
                <button type="button" id="staffTugasReset"
                    class="h-10 inline-flex items-center px-4 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">Reset</button>
            </div>
        </form>
    @endunless

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="{{ $isCompact ? 'overflow-hidden' : 'overflow-x-auto' }}">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-500">
                    <tr>
                        @if (!$isCompact)
                            <th class="px-4 py-3 font-medium whitespace-nowrap">
                                <button type="button" onclick="setStaffTugasSort('judul')"
                                    class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                    Judul Tugas <span data-sort-indicator="judul" class="text-slate-300">&#8597;</span>
                                </button>
                            </th>
                            <th class="px-4 py-3 font-medium whitespace-nowrap">
                                <button type="button" onclick="setStaffTugasSort('tenggat')"
                                    class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                    Tenggat Waktu <span data-sort-indicator="tenggat" class="text-slate-300">&#8597;</span>
                                </button>
                            </th>
                            <th class="px-4 py-3 font-medium whitespace-nowrap">
                                <button type="button" onclick="setStaffTugasSort('status')"
                                    class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                    Status <span data-sort-indicator="status" class="text-slate-300">&#8597;</span>
                                </button>
                            </th>
                        @else
                            <th class="px-4 py-3 font-medium whitespace-nowrap">Judul Tugas</th>
                            <th class="px-4 py-3 font-medium whitespace-nowrap">Tenggat Waktu</th>
                            <th class="px-4 py-3 font-medium whitespace-nowrap">Status</th>
                        @endif
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
    const staffTugasSearch = document.getElementById('staffTugasSearch');
    const staffTugasStatus = document.getElementById('staffTugasStatus');
    let staffTugasSort = null;
    let staffTugasDir = 'asc';

    const STATUS_CLASS = {
        'baru':         'bg-[#C0E7FF] text-blue-700',
        'berjalan':     'bg-[#DFE4EA] text-black-700',
        'menunggu acc': 'bg-[#FFF7ED] text-[#C2410C]',
        'menunggu_acc': 'bg-[#FFF7ED] text-[#C2410C]',
        'sudah acc':    'bg-[#CCF4DB] text-emerald-700',
        'sudah_acc':    'bg-[#CCF4DB] text-emerald-700',
        'telat':        'bg-red-100 text-red-700',
    };

    function ucwords(str) {
        return (str + '').replace(/_/g, ' ').replace(/^(.)|\s+(.)/g, function ($1) {
            return $1.toUpperCase();
        });
    }

    async function loadStaffTugas() {
        const token = sessionStorage.getItem('staff_token');
        const tbody = document.getElementById('tbody-tugas');
        if (!token || !tbody) return;

        const isCompact = {{ $isCompact ? 'true' : 'false' }};

        try {
            const params = new URLSearchParams();
            if (staffTugasSort) {
                params.set('sort', staffTugasSort);
                params.set('dir', staffTugasDir);
            }
            if (staffTugasSearch && staffTugasSearch.value) params.set('q', staffTugasSearch.value);
            if (staffTugasStatus && staffTugasStatus.value) params.set('status', staffTugasStatus.value);
            const query = params.toString();

            const res = await fetch('/api/staff/tugas' + (query ? '?' + query : ''), {
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            const json = await res.json();
            let tugas = json.data;

            tbody.innerHTML = ''; // Hapus tulisan loading

            if (!tugas || tugas.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center py-6 text-slate-400">Belum ada tugas yang ditugaskan kepada Anda.</td></tr>';
                return;
            }

            // Server sudah mengurutkan; hanya batasi 4 baris untuk tampilan dashboard.
            if (isCompact) {
                tugas = tugas.slice(0, 4);
            }

            tugas.forEach(t => {
                const statusRaw = (t.status || 'baru').toLowerCase();
                const sc = STATUS_CLASS[statusRaw] || 'bg-slate-100 text-slate-700';

                let deadline = '-';
                if (t.deadline) {
                    deadline = new Date(String(t.deadline).replace(' ', 'T'))
                        .toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
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

            updateStaffTugasSortIndicators();
        } catch (error) {
            console.error('Gagal meload tugas', error);
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-6 text-red-500">Gagal mengambil data tugas</td></tr>';
        }
    }

    function updateStaffTugasSortIndicators() {
        document.querySelectorAll('[data-sort-indicator]').forEach((el) => {
            if (el.dataset.sortIndicator === staffTugasSort) {
                el.textContent = staffTugasDir === 'asc' ? '\u25B2' : '\u25BC';
                el.classList.remove('text-slate-300');
                el.classList.add('text-[#004A65]');
            } else {
                el.textContent = '\u2195';
                el.classList.add('text-slate-300');
                el.classList.remove('text-[#004A65]');
            }
        });
    }

    window.setStaffTugasSort = function (col) {
        if (staffTugasSort === col) {
            staffTugasDir = staffTugasDir === 'asc' ? 'desc' : 'asc';
        } else {
            staffTugasSort = col;
            staffTugasDir = 'asc';
        }
        loadStaffTugas();
    };

    document.addEventListener('DOMContentLoaded', function () {
        loadStaffTugas();
        document.getElementById('staffTugasApply')?.addEventListener('click', loadStaffTugas);
        document.getElementById('staffTugasReset')?.addEventListener('click', () => {
            if (staffTugasSearch) staffTugasSearch.value = '';
            if (staffTugasStatus) staffTugasStatus.value = '';
            staffTugasSort = null;
            staffTugasDir = 'asc';
            loadStaffTugas();
        });
    });
</script>
