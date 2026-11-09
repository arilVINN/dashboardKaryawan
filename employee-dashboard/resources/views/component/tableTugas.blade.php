@php
    $isCompact = $compact ?? false;
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
        <div id="staffTugasFilter" class="flex flex-wrap items-center gap-2">
            <div class="relative w-full sm:w-[280px]">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6B7280]"
                    fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"></path>
                </svg>
                <input type="text" id="staffTugasSearch" placeholder="Cari judul tugas..." autocomplete="off"
                    class="h-10 w-full rounded-lg border border-[#D5DEE3] bg-white pl-9 pr-3 text-sm text-[#172B36] outline-none transition placeholder:text-[#6B7280] focus:border-[#146C94] focus:ring-1 focus:ring-[#146C94]">
            </div>

            <select id="staffTugasStatus"
                class="h-10 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-sm text-[#172B36] outline-none transition focus:border-[#146C94] focus:ring-1 focus:ring-[#146C94] sm:w-auto">
                <option value="">Semua status</option>
                <option value="baru">Baru</option>
                <option value="berjalan">Berjalan</option>
                <option value="menunggu di-acc">Menunggu persetujuan</option>
                <option value="sudah di-acc">Disetujui</option>
                <option value="terlambat">Terlambat</option>
            </select>

            <button type="button" id="staffTugasReset"
                class="hidden h-10 shrink-0 items-center gap-1 text-sm font-medium text-[#146C94] transition hover:text-[#0B4A66]">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                </svg>
                Reset
            </button>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-[#EAF4F8] text-[#334155] text-xs">
                        <tr>
                            <th class="px-4 py-3 font-medium whitespace-nowrap text-left">
                                <button type="button" onclick="setStaffTugasSort('judul')"
                                    class="inline-flex items-center gap-1 hover:text-[#146C94]">
                                    Judul tugas <span data-sort-indicator="judul" class="text-slate-300">&#8597;</span>
                                </button>
                            </th>
                            <th class="px-4 py-3 font-medium whitespace-nowrap text-left">
                                <button type="button" onclick="setStaffTugasSort('status')"
                                    class="inline-flex items-center gap-1 hover:text-[#146C94]">
                                    Status <span data-sort-indicator="status" class="text-slate-300">&#8597;</span>
                                </button>
                            </th>
                            <th class="px-4 py-3 font-medium whitespace-nowrap text-left">
                                <button type="button" onclick="setStaffTugasSort('tenggat')"
                                    class="inline-flex items-center gap-1 hover:text-[#146C94]">
                                    Tenggat <span data-sort-indicator="tenggat" class="text-slate-300">&#8597;</span>
                                </button>
                            </th>
                            <th class="w-12 px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody id="tbody-tugas" class="divide-y divide-[#EEF2F5]">
                        <tr id="tugas-loading">
                            <td colspan="4" class="text-center py-6 text-slate-400">Memuat data tugas dari database...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="staffTugasFooter" class="hidden border-t border-[#EEF2F5] px-4 pt-4 pb-6">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] sm:items-center">
                    <p id="staffTugasCount" class="text-[12px] text-[#4B5563] sm:text-[13px]"></p>
                    <div id="staffTugasPagination" class="flex flex-wrap justify-center gap-1.5"></div>
                    <div class="hidden sm:block" aria-hidden="true"></div>
                </div>
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
    const staffTugasReset = document.getElementById('staffTugasReset');
    const staffTugasFooter = document.getElementById('staffTugasFooter');
    const staffTugasCount = document.getElementById('staffTugasCount');
    const staffTugasPagination = document.getElementById('staffTugasPagination');
    const STAFF_TUGAS_PAGE_SIZE = 6;
    let staffTugasSort = staffTugasIsCompact ? 'tenggat' : null;
    let staffTugasDir = 'asc';
    let staffTugasPage = 1;
    let staffTugasFilterTimer = null;
    let staffTugasRequestId = 0;
    let staffTugasAbortController = null;

    function staffEscapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, (character) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
        }[character]));
    }

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

    function staffStatusCellHtml(task) {
        const effectiveStatus = (task.status_efektif || task.status || 'baru').toLowerCase();
        const storedStatus = String(task.status || '').toLowerCase();
        const lateButAccepted = !!task.is_terlambat
            && ['menunggu di-acc', 'sudah di-acc'].includes(storedStatus);

        const statusPill = staffStatusBadgeHtml(effectiveStatus, false);

        return lateButAccepted
            ? `${statusPill}${staffStatusBadgeHtml('terlambat', false)}`
            : statusPill;
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

    function formatStaffTugasDeadline(value) {
        if (!value) return '-';

        const date = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) return '-';

        const dateText = date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        const hours = date.getHours();
        const minutes = date.getMinutes();

        if (hours === 17 && minutes === 0) return dateText;

        return `${dateText}, ${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
    }

    function staffTugasStatusRank(status) {
        const key = staffStatusKey(status);

        if (key === 'terlambat') return 0;
        if (key === 'disetujui') return 2;

        return 1;
    }

    function staffTugasDeadlineTime(value) {
        if (!value) return Number.POSITIVE_INFINITY;

        const date = new Date(String(value).replace(' ', 'T'));

        return Number.isNaN(date.getTime()) ? Number.POSITIVE_INFINITY : date.getTime();
    }

    function sortStaffTugasDefault(tasks) {
        return tasks.slice().sort((a, b) => {
            const rank = staffTugasStatusRank(a.status_efektif || a.status)
                - staffTugasStatusRank(b.status_efektif || b.status);
            if (rank !== 0) return rank;

            const first = staffTugasDeadlineTime(a.deadline);
            const second = staffTugasDeadlineTime(b.deadline);
            if (first !== second) return first < second ? -1 : 1;

            return String(a.judul_tugas || '').localeCompare(String(b.judul_tugas || ''), 'id');
        });
    }

    function updateStaffTugasFilterStyles() {
        const statusActive = !!staffTugasStatus && staffTugasStatus.value !== '';

        if (staffTugasStatus) {
            staffTugasStatus.classList.toggle('bg-[#E3F1F7]', statusActive);
            staffTugasStatus.classList.toggle('border-[#146C94]', statusActive);
            staffTugasStatus.classList.toggle('text-[#0B4A66]', statusActive);
            staffTugasStatus.classList.toggle('font-medium', statusActive);
            staffTugasStatus.classList.toggle('bg-white', !statusActive);
            staffTugasStatus.classList.toggle('border-[#D5DEE3]', !statusActive);
            staffTugasStatus.classList.toggle('text-[#172B36]', !statusActive);
        }

        const searchActive = !!staffTugasSearch && staffTugasSearch.value.trim() !== '';

        if (staffTugasReset) {
            staffTugasReset.classList.toggle('hidden', !statusActive && !searchActive);
            staffTugasReset.classList.toggle('inline-flex', statusActive || searchActive);
        }
    }

    function renderStaffTugasFooter(total) {
        if (!staffTugasFooter) return;

        if (total === 0) {
            staffTugasFooter.classList.add('hidden');
            return;
        }

        staffTugasFooter.classList.remove('hidden');

        const totalPages = Math.max(1, Math.ceil(total / STAFF_TUGAS_PAGE_SIZE));
        if (staffTugasPage > totalPages) staffTugasPage = totalPages;

        const start = (staffTugasPage - 1) * STAFF_TUGAS_PAGE_SIZE + 1;
        const end = Math.min(total, staffTugasPage * STAFF_TUGAS_PAGE_SIZE);

        if (staffTugasCount) {
            staffTugasCount.textContent = `Menampilkan ${start}-${end} dari ${total} tugas`;
        }

        if (!staffTugasPagination) return;

        staffTugasPagination.innerHTML = '';

        if (totalPages <= 1) return;

        const pages = totalPages > 7
            ? [1, 2, 3, '...', totalPages]
            : Array.from({ length: totalPages }, (_, index) => index + 1);

        pages.forEach((page) => {
            if (page === '...') {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'px-3 py-1.5 text-sm text-slate-400 select-none';
                ellipsis.textContent = '\u2026';
                staffTugasPagination.appendChild(ellipsis);
                return;
            }

            const button = document.createElement('button');
            button.type = 'button';
            button.dataset.page = String(page);
            button.textContent = String(page);
            button.className = page === staffTugasPage
                ? 'min-w-9 rounded-md border border-[#004A65] bg-[#004A65] px-3 py-1.5 text-sm font-semibold text-white'
                : 'min-w-9 rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-600 transition hover:border-[#004A65] hover:text-[#004A65]';
            button.setAttribute('aria-label', `Ke halaman ${page}`);

            if (page === staffTugasPage) {
                button.setAttribute('aria-current', 'page');
            }

            staffTugasPagination.appendChild(button);
        });
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

        // Status terlambat ditampilkan lewat status pill, bukan teks di kolom tenggat.
        // Tugas yang sudah di-ACC/dikirim juga bisa punya tenggat lampau tanpa terlambat.
        if (isLate || difference < 0) {
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

            if (!staffTugasIsCompact) {
                if (!staffTugasSort) {
                    tugas = sortStaffTugasDefault(tugas);
                }
                renderStaffTugasFooter(tugas.length);
            }

            if (!tugas || tugas.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center py-6 text-[13px] text-[#4B5563]">Belum ada tugas yang ditugaskan kepada Anda.</td></tr>`;
                return;
            }

            if (staffTugasIsCompact) {
                tugas = tugas.slice(0, 4);
            }

            const visibleTugas = staffTugasIsCompact
                ? tugas
                : tugas.slice((staffTugasPage - 1) * STAFF_TUGAS_PAGE_SIZE, staffTugasPage * STAFF_TUGAS_PAGE_SIZE);

            const rows = visibleTugas.map(t => {
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

                const deadline = formatStaffTugasDeadline(t.deadline);
                const judul = staffEscapeHtml(t.judul_tugas);

                return `
                    <tr class="h-[52px] cursor-pointer hover:bg-[#F5FAFC] transition focus:bg-[#F5FAFC] focus:outline-none"
                        data-href="/tugas/detail/${encodeURIComponent(t.id_tugas)}" tabindex="0" role="link">
                        <td class="px-4 py-3">
                            <span class="block truncate text-[14px] font-medium text-[#172B36]" title="${judul}">${judul}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="inline-flex flex-wrap items-center justify-start gap-1">
                                ${staffStatusCellHtml(t)}
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-[14px] text-[#172B36]">${deadline}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="/tugas/detail/${encodeURIComponent(t.id_tugas)}" onclick="event.stopPropagation()"
                                aria-label="Buka tugas"
                                class="inline-flex items-center justify-center text-[#5B6470] transition hover:text-[#146C94]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"></path>
                                </svg>
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
                tbody.innerHTML = `<tr><td colspan="4" class="text-center py-6 text-[13px] text-red-500">Gagal mengambil data tugas</td></tr>`;
            }
        }
    }

    function updateStaffTugasSortIndicators() {
        document.querySelectorAll('[data-sort-indicator]').forEach((el) => {
            if (el.dataset.sortIndicator === staffTugasSort) {
                el.textContent = staffTugasDir === 'asc' ? '\u25B2' : '\u25BC';
                el.classList.remove('text-slate-300');
                el.classList.add('text-[#146C94]');
            } else {
                el.textContent = '\u2195';
                el.classList.add('text-slate-300');
                el.classList.remove('text-[#146C94]');
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
        staffTugasPage = 1;
        loadStaffTugas();
    };

    window.loadStaffTugas = loadStaffTugas;

    document.addEventListener('DOMContentLoaded', function () {
        updateStaffTugasFilterStyles();
        loadStaffTugas();

        if (staffTugasIsCompact) return;

        staffTugasSearch?.addEventListener('input', () => {
            updateStaffTugasFilterStyles();
            clearTimeout(staffTugasFilterTimer);
            staffTugasFilterTimer = setTimeout(() => {
                staffTugasPage = 1;
                loadStaffTugas();
            }, 300);
        });

        staffTugasStatus?.addEventListener('change', () => {
            clearTimeout(staffTugasFilterTimer);
            updateStaffTugasFilterStyles();
            staffTugasPage = 1;
            loadStaffTugas();
        });

        staffTugasReset?.addEventListener('click', () => {
            clearTimeout(staffTugasFilterTimer);
            if (staffTugasSearch) staffTugasSearch.value = '';
            if (staffTugasStatus) staffTugasStatus.value = '';
            staffTugasSort = null;
            staffTugasDir = 'asc';
            staffTugasPage = 1;
            updateStaffTugasFilterStyles();
            loadStaffTugas();
        });

        staffTugasPagination?.addEventListener('click', (event) => {
            const button = event.target.closest('button[data-page]');
            if (!button) return;

            staffTugasPage = Number(button.dataset.page) || 1;
            loadStaffTugas();
        });
    });
</script>
