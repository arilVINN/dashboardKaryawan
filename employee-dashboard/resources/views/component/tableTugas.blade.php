@php
    $isCompact = $compact ?? false;
    $cellPadding = $isCompact ? 'px-4 py-3' : 'px-6 py-4';
@endphp

<div class="flex flex-col gap-3">
    @if ($isCompact)
        <h2 class="text-xl font-bold text-slate-800">Tabel Tugas</h2>
    @endif

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
                    <option value="terlambat">Telat / Terlambat</option>
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
                        <td colspan="{{ $isCompact ? 4 : 5 }}" class="text-center py-6 text-slate-400">Memuat data tugas dari database...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    window.staffOverviewData = window.staffOverviewData || null;
    window.staffOverviewRequest = window.staffOverviewRequest || null;

    const staffTugasSearch = document.getElementById('staffTugasSearch');
    const staffTugasStatus = document.getElementById('staffTugasStatus');
    let staffTugasSort = null;
    let staffTugasDir = 'asc';
    let staffTugasRequestId = 0;
    let staffTugasAbortController = null;

    async function fetchStaffOverview(params = {}) {
        const token = localStorage.getItem('staff_token') || sessionStorage.getItem('staff_token');
        if (!token) return { tasks: [], projects: [] };

        if (Object.keys(params).length === 0) {
            if (window.staffOverviewData) {
                return window.staffOverviewData;
            }

            if (window.staffOverviewRequest) {
                return window.staffOverviewRequest;
            }
        }

        const qs = new URLSearchParams(params).toString();
        const request = fetch('/api/staff/overview' + (qs ? '?' + qs : ''), {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        }).then(async (res) => {
            const json = await res.json();
            if (!res.ok) {
                throw new Error(json.message || 'Gagal memuat data halaman tugas.');
            }
            return json.data || { tasks: [], projects: [] };
        });

        if (Object.keys(params).length === 0) {
            window.staffOverviewRequest = request;
        }

        try {
            const data = await request;
            if (Object.keys(params).length === 0) {
                window.staffOverviewData = data;
            }
            return data;
        } finally {
            if (Object.keys(params).length === 0) {
                window.staffOverviewRequest = null;
            }
        }
    }

    const STATUS_CLASS = {
        'baru':            'bg-[#C0E7FF] text-blue-700',
        'berjalan':        'bg-emerald-100 text-emerald-700',
        'menunggu acc':    'bg-amber-100 text-amber-700',
        'menunggu_acc':    'bg-amber-100 text-amber-700',
        'menunggu di-acc': 'bg-amber-100 text-amber-700',
        'sudah acc':       'bg-emerald-700 text-white',
        'sudah_acc':       'bg-emerald-700 text-white',
        'sudah di-acc':    'bg-emerald-700 text-white',
        'telat':           'bg-red-100 text-red-700',
        'terlambat':       'bg-red-100 text-red-700',
    };

    function ucwords(str) {
        return (str + '').replace(/_/g, ' ').replace(/^(.)|\s+(.)/g, function ($1) {
            return $1.toUpperCase();
        });
    }

    async function loadStaffTugas() {
        const token = localStorage.getItem('staff_token') || sessionStorage.getItem('staff_token');
        const tbody = document.getElementById('tbody-tugas');
        if (!token || !tbody) return;

        const isCompact = {{ $isCompact ? 'true' : 'false' }};

        if (staffTugasAbortController) {
            staffTugasAbortController.abort();
        }

        staffTugasAbortController = new AbortController();
        const requestId = ++staffTugasRequestId;

        try {
            const params = {};
            if (staffTugasSort) {
                params.sort = staffTugasSort;
                params.dir = staffTugasDir;
            }
            if (staffTugasSearch && staffTugasSearch.value) params.q = staffTugasSearch.value;
            if (staffTugasStatus && staffTugasStatus.value) params.status = staffTugasStatus.value;

            const overview = await fetchStaffOverview(params);
            let tugas = overview.tasks || [];

            if (requestId !== staffTugasRequestId) {
                return;
            }

            tbody.innerHTML = '';

            if (!tugas || tugas.length === 0) {
                tbody.innerHTML = `<tr><td colspan="${isCompact ? 4 : 5}" class="text-center py-6 text-slate-400">Belum ada tugas yang ditugaskan kepada Anda.</td></tr>`;
                return;
            }

            if (isCompact) {
                tugas = tugas.slice(0, 4);
            }

            const rows = tugas.map(t => {
                const effectiveStatus = (t.status_efektif || t.status || 'baru').toLowerCase();
                const sc = STATUS_CLASS[effectiveStatus] || 'bg-slate-100 text-slate-700';

                let deadline = '-';
                if (t.deadline) {
                    deadline = new Date(String(t.deadline).replace(' ', 'T'))
                        .toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
                }

                return `
                    <tr class="hover:bg-slate-50 transition">
                        <td class="{{ $cellPadding }} font-medium text-slate-800 whitespace-nowrap">
                            ${t.judul_tugas}
                        </td>
                        <td class="{{ $cellPadding }} whitespace-nowrap text-slate-600">
                            ${deadline}
                        </td>
                        <td class="{{ $cellPadding }} whitespace-nowrap">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold ${sc}">
                                ${ucwords(effectiveStatus)}
                            </span>
                        </td>
                        <td class="{{ $cellPadding }} whitespace-nowrap text-center">
                            <a href="/tugas/detail/${t.id_tugas}" class="text-[#0097B2] hover:text-[#008199] font-medium hover:underline">
                                Lihat
                            </a>
                        </td>
                    </tr>`;
            }).join('');

            tbody.innerHTML = rows;
            updateStaffTugasSortIndicators();
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }
            console.error('Gagal meload tugas', error);
            if (requestId === staffTugasRequestId) {
                tbody.innerHTML = `<tr><td colspan="${isCompact ? 4 : 5}" class="text-center py-6 text-red-500">Gagal mengambil data tugas</td></tr>`;
            }
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

    window.loadStaffTugas = loadStaffTugas;

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
