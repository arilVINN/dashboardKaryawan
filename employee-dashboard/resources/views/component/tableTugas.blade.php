@php
    $isCompact = $compact ?? false;
    $cellPadding = $isCompact ? 'px-4 py-2' : 'px-6 py-4';
@endphp

@if ($isCompact)
    <div class="flex flex-col gap-2">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-lg font-semibold text-[#172B36]">Tugas saya</h2>
            <a href="/tugas" class="text-xs text-[#146C94] hover:underline">Lihat semua &rsaquo;</a>
        </div>

        <div class="overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">
            <div class="overflow-x-auto">
            <table class="w-full min-w-[520px] table-fixed text-left text-[13px] text-[#475569]">
                <thead class="bg-[#EAF4F8]">
                    <tr class="h-8">
                        <th class="w-[50%] px-4 text-left text-xs font-medium text-[#334155]">Judul tugas</th>
                        <th class="w-[20%] px-4 text-left text-xs font-medium text-[#334155]">Status</th>
                        <th class="w-[22%] px-4 text-left text-xs font-medium text-[#334155]">Tenggat</th>
                        <th class="w-16 px-2"></th>
                    </tr>
                </thead>
                <tbody id="tbody-tugas" class="divide-y divide-[#EEF2F5]">
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-[13px] text-[#4B5563]">Memuat data tugas dari database...</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>
    </div>
@else
    <div class="flex flex-col gap-3">
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
                    <option value="menunggu di-acc">Menunggu persetujuan</option>
                    <option value="sudah di-acc">Disetujui</option>
                    <option value="terlambat">Terlambat</option>
                </select>
            </div>
            <div class="flex shrink-0 gap-2 sm:ml-auto">
                <button type="button" id="staffTugasApply"
                    class="h-10 inline-flex items-center px-4 bg-[#004A65] text-white text-sm font-medium rounded-md hover:bg-[#003347] transition">Terapkan</button>
                <button type="button" id="staffTugasReset"
                    class="h-10 inline-flex items-center px-4 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">Reset</button>
            </div>
        </form>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-100 text-slate-500">
                        <tr>
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
                            <th class="px-4 py-3 font-medium whitespace-nowrap text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-tugas" class="divide-y divide-slate-200">
                        <tr id="tugas-loading">
                            <td colspan="5" class="text-center py-6 text-slate-400">Memuat data tugas dari database...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

<script>
    window.staffOverviewData = window.staffOverviewData || null;
    window.staffOverviewRequest = window.staffOverviewRequest || null;

    const STAFF_STATUS_PALETTE = @json(\App\Support\StatusPalette::all());
    const STAFF_STATUS_HIGH_CONTRAST = {
        'baru': 'bg-[#C0E7FF] text-blue-700 border border-blue-500',
        'berjalan': 'bg-emerald-100 text-emerald-700 border border-emerald-500',
        'menunggu': 'bg-amber-100 text-amber-700 border border-amber-500',
        'disetujui': 'bg-emerald-700 text-white border border-emerald-800',
        'terlambat': 'bg-red-100 text-red-700 border border-red-500',
    };
    const staffTugasIsCompact = {{ $isCompact ? 'true' : 'false' }};
    const staffTugasSearch = document.getElementById('staffTugasSearch');
    const staffTugasStatus = document.getElementById('staffTugasStatus');
    let staffTugasSort = staffTugasIsCompact ? 'tenggat' : null;
    let staffTugasDir = 'asc';
    let staffTugasRequestId = 0;
    let staffTugasAbortController = null;

    function staffStatusKey(status) {
        const value = String(status || '').toLowerCase().trim();

        if (value === 'baru') return 'baru';
        if (value === 'berjalan') return 'berjalan';
        if (value.includes('menunggu')) return 'menunggu';
        if (value.includes('sudah') || value.includes('disetujui') || value.includes('selesai')) return 'disetujui';
        if (value.includes('terlambat') || value.includes('telat')) return 'terlambat';

        return null;
    }

    function staffStatusBadgeHtml(status, shortLabel = false) {
        const key = staffStatusKey(status);
        const palette = STAFF_STATUS_PALETTE[key];
        const label = palette ? (shortLabel ? palette.short : palette.label) : (status || '-');

        if (!palette) {
            return `<span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-300">${status || '-'}</span>`;
        }

        const highContrast = STAFF_STATUS_HIGH_CONTRAST[key] || 'bg-slate-100 text-slate-700 border border-slate-300';

        return `<span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold ${highContrast}">${label}</span>`;
    }

    function formatStaffDeadline(value) {
        if (!value) return '-';

        const date = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) return '-';

        const options = { day: 'numeric', month: 'short' };
        if (date.getFullYear() !== new Date().getFullYear()) {
            options.year = 'numeric';
        }

        return date.toLocaleDateString('id-ID', options);
    }

    function staffDeadlineHint(value, isLate) {
        if (!value) return { hint: '', className: 'text-[#4B5563]' };

        const date = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) return { hint: '', className: 'text-[#4B5563]' };

        const deadlineDay = new Date(date);
        deadlineDay.setHours(0, 0, 0, 0);

        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const difference = Math.round((deadlineDay - today) / 86400000);

        if (isLate) {
            return {
                hint: `Terlambat ${Math.max(1, Math.abs(difference))} hari`,
                className: 'text-[#B91C1C] font-medium'
            };
        }

        // Tugas yang sudah di-ACC/dikirim bisa punya tenggat lampau tanpa terlambat.
        if (difference < 0) {
            return { hint: '', className: 'text-[#4B5563]' };
        }

        if (difference === 1) {
            return { hint: 'Besok', className: 'text-[#B45309] font-medium' };
        }

        if (difference === 0) {
            return { hint: 'Hari ini', className: 'text-[#B45309] font-medium' };
        }

        return { hint: `${difference} hari lagi`, className: 'text-[#4B5563]' };
    }

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

    async function loadStaffTugas() {
        const token = localStorage.getItem('staff_token') || sessionStorage.getItem('staff_token');
        const tbody = document.getElementById('tbody-tugas');
        if (!token || !tbody) return;

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
                tbody.innerHTML = `<tr><td colspan="${staffTugasIsCompact ? 4 : 5}" class="text-center py-6 text-[13px] text-[#4B5563]">Belum ada tugas yang ditugaskan kepada Anda.</td></tr>`;
                return;
            }

            if (staffTugasIsCompact) {
                tugas = tugas.slice(0, 4);
            }

            const rows = tugas.map(t => {
                const effectiveStatus = (t.status_efektif || t.status || 'baru').toLowerCase();

                if (staffTugasIsCompact) {
                    const deadline = formatStaffDeadline(t.deadline);
                    const deadlineHint = staffDeadlineHint(t.deadline, !!t.is_terlambat);

                    return `
                        <tr class="h-11 cursor-pointer hover:bg-[#F5FAFC] focus:bg-[#F5FAFC] focus:outline-none" data-href="/tugas/detail/${encodeURIComponent(t.id_tugas)}" tabindex="0" role="link">
                            <td class="px-4 py-2">
                                <span class="block truncate text-[13px] font-medium text-[#172B36]" title="${t.judul_tugas}">${t.judul_tugas}</span>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                ${staffStatusBadgeHtml(effectiveStatus, true)}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                <div class="text-[13px] text-[#475569]">${deadline}</div>
                                ${deadlineHint.hint ? `<div class="text-[11px] ${deadlineHint.className}">${deadlineHint.hint}</div>` : ''}
                            </td>
                            <td class="px-2 py-2 text-right">
                                <a href="/tugas/detail/${encodeURIComponent(t.id_tugas)}" onclick="event.stopPropagation()"
                                    class="inline-flex items-center justify-center rounded px-1 py-1 text-sm font-semibold text-[#146C94] transition hover:text-[#0B4A66] hover:underline focus:outline-none focus:ring-2 focus:ring-[#146C94]/30">
                                    Detail
                                </a>
                            </td>
                        </tr>`;
                }

                let deadline = '-';
                if (t.deadline) {
                    deadline = new Date(String(t.deadline).replace(' ', 'T'))
                        .toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' })
                        .replace(/\./g, ':');
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
                            ${staffStatusBadgeHtml(effectiveStatus, false)}
                        </td>
                        <td class="{{ $cellPadding }} whitespace-nowrap text-center">
                            <a href="/tugas/detail/${encodeURIComponent(t.id_tugas)}" class="text-[#0097B2] hover:text-[#008199] font-medium hover:underline">
                                Lihat
                            </a>
                        </td>
                    </tr>`;
            }).join('');

            tbody.innerHTML = rows;

            tbody.querySelectorAll('tr[data-href]').forEach((row) => {
                row.addEventListener('click', () => {
                    window.location.href = row.dataset.href;
                });
                row.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        window.location.href = row.dataset.href;
                    }
                });
            });

            updateStaffTugasSortIndicators();
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }
            console.error('Gagal meload tugas', error);
            if (requestId === staffTugasRequestId) {
                tbody.innerHTML = `<tr><td colspan="${staffTugasIsCompact ? 4 : 5}" class="text-center py-6 text-[13px] text-red-500">Gagal mengambil data tugas</td></tr>`;
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
