<form id="kadivStaffFilter" onsubmit="return false;"
    class="flex flex-wrap items-end gap-4 bg-white border border-slate-200 rounded-xl shadow-sm p-5 mt-4">
    <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
        <label for="kadivStaffSearch" class="text-xs font-bold text-slate-500">Cari Nama</label>
        <input type="text" id="kadivStaffSearch" placeholder="Nama staff..."
            class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
    </div>
    <div class="flex shrink-0 gap-2 sm:ml-auto">
        <button type="button" id="kadivStaffApply"
            class="h-10 inline-flex items-center px-4 bg-[#004A65] text-white text-sm font-medium rounded-md hover:bg-[#003347] transition">Terapkan</button>
        <button type="button" id="kadivStaffReset"
            class="h-10 inline-flex items-center px-4 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">Reset</button>
    </div>
</form>

<div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="w-full min-w-[650px] text-left text-sm text-slate-600">
        <thead class="bg-slate-100 text-xs uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3 font-medium">
                    <button type="button" onclick="setKadivStaffSort('nama')"
                        class="inline-flex items-center gap-1 hover:text-[#004A65]">
                        Nama Staff <span data-sort-indicator="nama" class="text-slate-300">&#8597;</span>
                    </button>
                </th>
                <th class="px-4 py-3 text-center font-medium">
                    <button type="button" onclick="setKadivStaffSort('tugas')"
                        class="inline-flex items-center gap-1 hover:text-[#004A65]">
                        Tugas <span data-sort-indicator="tugas" class="text-slate-300">&#8597;</span>
                    </button>
                </th>
                <th class="px-4 py-3 text-center font-medium">
                    <button type="button" onclick="setKadivStaffSort('login')"
                        class="inline-flex items-center gap-1 hover:text-[#004A65]">
                        Terakhir Login <span data-sort-indicator="login" class="text-slate-300">&#8597;</span>
                    </button>
                </th>
                <th class="px-4 py-3 text-center font-medium">Aksi</th>
            </tr>
        </thead>
        <tbody id="kadiv-staff-rows">
            <tr>
                <td colspan="4" class="h-28 px-4 text-center text-slate-500">Memuat data staff...</td>
            </tr>
        </tbody>
    </table>
</div>

<script>
    const kadivStaffSearch = document.getElementById('kadivStaffSearch');
    let kadivStaffSort = null;
    let kadivStaffDir = 'asc';

    async function loadKadivStaffTable() {
        const rows = document.getElementById('kadiv-staff-rows');
        const token = sessionStorage.getItem('staff_token');
        if (!token) {
            window.location.href = '/login';
            return;
        }

        try {
            const params = new URLSearchParams();
            if (kadivStaffSort) {
                params.set('sort', kadivStaffSort);
                params.set('dir', kadivStaffDir);
            }
            if (kadivStaffSearch && kadivStaffSearch.value) params.set('q', kadivStaffSearch.value);
            const query = params.toString();

            const response = await fetch('/api/kadiv/dashboard' + (query ? '?' + query : ''), {
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });
            const result = await response.json();
            if (!response.ok) {
                throw new Error(result.message || 'Gagal mengambil daftar staff.');
            }

            const staff = result.data.staff || [];
            rows.replaceChildren();
            if (staff.length === 0) {
                rows.innerHTML = '<tr><td colspan="4" class="h-28 px-4 text-center text-slate-500">Belum ada staff di divisi ini.</td></tr>';
                return;
            }

            staff.forEach((person) => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-slate-50';

                const name = document.createElement('td');
                name.className = 'px-4 py-3 font-semibold text-slate-800';
                name.textContent = person.nama || '-';
                row.appendChild(name);

                const tasks = document.createElement('td');
                tasks.className = 'px-4 py-3 text-center';
                tasks.textContent = person.jumlah_tugas_dikerjakan ?? 0;
                row.appendChild(tasks);

                const lastLogin = document.createElement('td');
                lastLogin.className = 'px-4 py-3 text-center';
                lastLogin.textContent = person.terakhir_login
                    ? new Date(person.terakhir_login).toLocaleString('id-ID', {
                        dateStyle: 'medium',
                        timeStyle: 'short'
                    })
                    : 'Belum pernah login';
                row.appendChild(lastLogin);

                const actionCell = document.createElement('td');
                actionCell.className = 'px-4 py-3 text-center';
                const action = document.createElement('a');
                action.href = '/kadiv/manajemenStaff/' + encodeURIComponent(person.id_karyawan);
                action.className = 'font-semibold text-cyan-700 hover:underline';
                action.textContent = 'Detail';
                actionCell.appendChild(action);
                row.appendChild(actionCell);
                rows.appendChild(row);
            });
            updateKadivStaffSortIndicators();
        } catch (error) {
            console.error('Gagal memuat staff Kadiv:', error);
            rows.innerHTML = '<tr><td colspan="4" class="h-28 px-4 text-center text-red-600">Data staff gagal dimuat. Silakan muat ulang halaman.</td></tr>';
        }
    }

    function updateKadivStaffSortIndicators() {
        document.querySelectorAll('[data-sort-indicator]').forEach((el) => {
            if (el.dataset.sortIndicator === kadivStaffSort) {
                el.textContent = kadivStaffDir === 'asc' ? '\u25B2' : '\u25BC';
                el.classList.remove('text-slate-300');
                el.classList.add('text-[#004A65]');
            } else {
                el.textContent = '\u2195';
                el.classList.add('text-slate-300');
                el.classList.remove('text-[#004A65]');
            }
        });
    }

    window.setKadivStaffSort = function (col) {
        if (kadivStaffSort === col) {
            kadivStaffDir = kadivStaffDir === 'asc' ? 'desc' : 'asc';
        } else {
            kadivStaffSort = col;
            kadivStaffDir = 'asc';
        }
        loadKadivStaffTable();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadKadivStaffTable);
    } else {
        loadKadivStaffTable();
    }

    document.getElementById('kadivStaffApply')?.addEventListener('click', () => loadKadivStaffTable());
    document.getElementById('kadivStaffReset')?.addEventListener('click', () => {
        if (kadivStaffSearch) kadivStaffSearch.value = '';
        kadivStaffSort = null;
        kadivStaffDir = 'asc';
        loadKadivStaffTable();
    });
</script>

