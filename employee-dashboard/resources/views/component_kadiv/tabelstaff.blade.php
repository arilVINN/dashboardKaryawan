<div class="mt-6 overflow-hidden rounded-xl border border-[#E2E8EC] bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-[#EEF2F5] px-4 py-3">
        <h2 class="text-sm font-medium text-[#172B36]">Manajemen staff</h2>
        <a href="/kadiv/manajemenStaff" class="text-xs text-[#146C94] hover:underline">Lihat semua &rsaquo;</a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[650px] text-left text-[13px] text-[#475569]">
            <thead class="bg-[#EAF4F8]">
                <tr class="h-8">
                    <th class="px-4 text-left text-xs font-medium text-[#334155]">Staff</th>
                    <th class="px-4 text-left text-xs font-medium text-[#334155]">Tugas</th>
                    <th class="px-4 text-left text-xs font-medium text-[#334155]">Terakhir login</th>
                    <th class="w-10 px-2"></th>
                </tr>
            </thead>
            <tbody id="kadiv-staff-rows" class="divide-y divide-[#EEF2F5]">
                <tr>
                    <td colspan="4" class="h-28 px-4 text-center text-[13px] text-[#4B5563]">Memuat data staff...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    let kadivStaffLoading = false;

    function kadivStaffInitials(name) {
        return String(name || '?')
            .trim()
            .split(/\s+/)
            .slice(0, 2)
            .map((part) => part.charAt(0).toUpperCase())
            .join('');
    }

    function formatKadivLastLogin(value) {
        if (!value) return 'Belum pernah login';

        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return 'Belum pernah login';

        const day = date.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        });
        const time = date.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            hourCycle: 'h23'
        }).replace(/\./g, ':');

        return `${day}, ${time}`;
    }

    async function loadKadivStaffTable() {
        const rows = document.getElementById('kadiv-staff-rows');
        if (!rows || kadivStaffLoading) return;

        const token = sessionStorage.getItem('staff_token');
        if (!token) {
            window.location.href = '/login';
            return;
        }

        kadivStaffLoading = true;
        try {
            const dashboardData = await window.fetchKadivDashboardData?.();
            if (!dashboardData) {
                throw new Error('Gagal mengambil data dashboard Kadiv.');
            }

            const staff = dashboardData.staff || [];
            rows.replaceChildren();
            if (staff.length === 0) {
                rows.innerHTML = '<tr><td colspan="4" class="h-28 px-4 text-center text-[13px] text-[#4B5563]">Belum ada staff di divisi ini.</td></tr>';
                return;
            }

            staff.forEach((person) => {
                const url = '/kadiv/manajemenStaff/' + encodeURIComponent(person.id_karyawan);
                const row = document.createElement('tr');
                row.className = 'h-11 cursor-pointer hover:bg-[#F5FAFC] focus:bg-[#F5FAFC] focus:outline-none';
                row.tabIndex = 0;
                row.setAttribute('role', 'link');
                row.addEventListener('click', () => {
                    window.location.href = url;
                });
                row.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        window.location.href = url;
                    }
                });

                const staffCell = document.createElement('td');
                staffCell.className = 'px-4 py-2';
                const staffWrap = document.createElement('div');
                staffWrap.className = 'flex min-w-0 items-center gap-3';

                const avatar = document.createElement('span');
                avatar.className = 'flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#AFD3E2] text-[11px] font-semibold text-[#0B4A66]';
                avatar.textContent = kadivStaffInitials(person.nama);

                const identity = document.createElement('div');
                identity.className = 'min-w-0';

                const name = document.createElement('p');
                name.className = 'truncate text-[13px] font-medium text-[#172B36]';
                name.textContent = person.nama || '-';

                const position = document.createElement('p');
                position.className = 'truncate text-[11px] text-[#4B5563]';
                position.textContent = person.jabatan || '-';

                identity.append(name, position);
                staffWrap.append(avatar, identity);
                staffCell.appendChild(staffWrap);
                row.appendChild(staffCell);

                const tasks = document.createElement('td');
                tasks.className = 'px-4 py-2 text-left text-[13px]';
                tasks.textContent = person.jumlah_tugas_dikerjakan ?? 0;
                row.appendChild(tasks);

                const lastLogin = document.createElement('td');
                lastLogin.className = 'px-4 py-2 text-left text-[13px] text-[#4B5563]';
                lastLogin.textContent = formatKadivLastLogin(person.terakhir_login);
                row.appendChild(lastLogin);

                const chevronCell = document.createElement('td');
                chevronCell.className = 'px-3 py-2 text-right';
                chevronCell.innerHTML = '<svg class="ml-auto h-4 w-4 text-[#94A3B8]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>';
                row.appendChild(chevronCell);

                rows.appendChild(row);
            });
        } catch (error) {
            console.error('Gagal memuat staff Kadiv:', error);
            rows.innerHTML = '<tr><td colspan="4" class="h-28 px-4 text-center text-[13px] text-red-600">Data staff gagal dimuat. Silakan muat ulang halaman.</td></tr>';
        } finally {
            kadivStaffLoading = false;
        }
    }

    window.addEventListener('focus', loadKadivStaffTable);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) loadKadivStaffTable();
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadKadivStaffTable);
    } else {
        loadKadivStaffTable();
    }
</script>
