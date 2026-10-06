<div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="w-full min-w-[650px] text-left text-sm text-slate-600">
        <thead class="bg-slate-100 text-xs uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3 font-medium">Nama Staff</th>
                <th class="px-4 py-3 text-center font-medium">Tugas</th>
                <th class="px-4 py-3 text-center font-medium">Terakhir Login</th>
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
    async function loadKadivStaffTable() {
        const rows = document.getElementById('kadiv-staff-rows');
        const token = sessionStorage.getItem('staff_token');
        if (!token) {
            window.location.href = '/login';
            return;
        }

        try {
            const response = await fetch('/api/kadiv/dashboard', {
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
        } catch (error) {
            console.error('Gagal memuat staff Kadiv:', error);
            rows.innerHTML = '<tr><td colspan="4" class="h-28 px-4 text-center text-red-600">Data staff gagal dimuat. Silakan muat ulang halaman.</td></tr>';
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadKadivStaffTable);
    } else {
        loadKadivStaffTable();
    }
</script>
