<div class="mt-5 overflow-x-auto">
    <div class="min-w-[700px] rounded-md bg-white shadow-md overflow-hidden">
        <div class="grid grid-cols-[2fr_1fr_1.5fr_1fr] items-center bg-slate-100 px-4 py-3">
            <span class="text-sm font-medium text-slate-600">Nama</span>
            <span class="text-sm font-medium text-slate-600 text-center">Tugas</span>
            <span class="text-sm font-medium text-slate-600 text-center">Terakhir Login</span>
            <span class="text-sm font-medium text-slate-600 text-center">Aksi</span>
        </div>
        <div id="kadiv-staff-rows">
            <div class="h-52 flex items-center justify-center text-sm text-slate-500">Memuat data staff...</div>
        </div>
    </div>
</div>

<script>
    async function loadKadivStaffTable() {
        const rows = document.getElementById('kadiv-staff-rows');
        const token = localStorage.getItem('staff_token');
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
                rows.innerHTML = '<div class="h-52 flex items-center justify-center text-sm text-slate-500">Belum ada staff.</div>';
                return;
            }

            staff.forEach((person) => {
                const row = document.createElement('div');
                row.className = 'grid grid-cols-[2fr_1fr_1.5fr_1fr] items-center border-t border-slate-100 px-4 py-3 text-sm text-slate-700';
                const lastLogin = person.terakhir_login
                    ? new Date(person.terakhir_login).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' })
                    : 'Belum pernah login';

                [person.nama || '-', person.jumlah_tugas_dikerjakan ?? 0, lastLogin].forEach((value, index) => {
                    const cell = document.createElement('span');
                    cell.className = index === 0 ? 'font-medium' : 'text-center';
                    cell.textContent = value;
                    row.appendChild(cell);
                });

                const actionCell = document.createElement('span');
                actionCell.className = 'text-center';
                const action = document.createElement('a');
                action.href = '/kadiv/manajemenStaff';
                action.className = 'font-semibold text-cyan-700 hover:underline';
                action.textContent = 'Kelola';
                actionCell.appendChild(action);
                row.appendChild(actionCell);
                rows.appendChild(row);
            });
        } catch (error) {
            console.error('Gagal memuat staff Kadiv:', error);
            rows.innerHTML = '<div class="h-52 flex items-center justify-center px-4 text-center text-sm text-red-600">Data staff gagal dimuat. Silakan muat ulang halaman.</div>';
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadKadivStaffTable);
    } else {
        loadKadivStaffTable();
    }
</script>
