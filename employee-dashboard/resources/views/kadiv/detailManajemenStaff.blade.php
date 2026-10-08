<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Staff - PT SILINDO</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#F3F4F6] flex h-screen overflow-hidden">
    
    @include('component_kadiv.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        @include('component_kadiv.topbar')

        <main class="flex-1 overflow-y-auto">
            
            <div class="px-9 pt-7 pb-16">

                @include('component.breadcrumbs', ['parentText' => 'Manajemen Staff', 'parentUrl' => url('/kadiv/manajemenStaff'), 'currentPage' => 'Detail Staff'])
                <div class="mt-4 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-full bg-[#19A7CE] text-white text-xl font-bold flex items-center justify-center">
                            <span id="detail-staff-initial">-</span>
                        </div>
                        <div>
                            <h1 id="detail-staff-name" class="text-[28px] leading-[36px] font-bold text-black">-</h1>
                            <p class="text-sm text-[#565E74]">
                                <span id="detail-staff-role">-</span> ·
                                <span id="detail-staff-status" class="text-[#15803D] font-semibold">-</span>
                            </p>
                        </div>
                    </div>
                </div>

                <h2 class="mt-8 text-xl font-bold text-[#0F172A]">Tugas <span id="detail-staff-task-name">-</span></h2>

                <div class="mt-4 bg-white rounded-xl border border-[#E2E8F0] shadow-sm overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-[#F1F5F9] text-[#565E74] uppercase">
                            <tr>
                                <th class="px-6 py-4 text-left font-normal">Tugas</th>
                                <th class="px-6 py-4 text-center font-normal">Tenggat</th>
                                <th class="px-6 py-4 text-center font-normal">Approval</th>
                                <th class="px-6 py-4 text-center font-normal">Aksi</th>
                            </tr>
                        </thead>

                        <tbody id="detail-staff-tasks" class="divide-y divide-[#E2E8F0]">
                            <tr><td colspan="4" class="px-6 py-10 text-center text-[#94A3B8]">Memuat data tugas...</td></tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </main>
    </div>

<script>
    async function loadKadivStaffDetail() {
        const token = sessionStorage.getItem('staff_token');
        if (!token) {
            window.location.href = '/login';
            return;
        }

        try {
            const response = await fetch('/api/kadiv/staff/{{ $id }}', {
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Gagal memuat detail staff.');
            const staff = result.data;
            document.getElementById('detail-staff-initial').textContent = (staff.nama || '-').charAt(0).toUpperCase();
            document.getElementById('detail-staff-name').textContent = staff.nama || '-';
            document.getElementById('detail-staff-task-name').textContent = staff.nama || '-';
            document.getElementById('detail-staff-role').textContent = staff.jabatan || staff.user?.role?.nama_role || '-';
            document.getElementById('detail-staff-status').textContent = staff.user?.last_login_at ? 'Aktif' : 'Belum pernah login';

            const rows = document.getElementById('detail-staff-tasks');
            rows.replaceChildren();
            if (!staff.tugas?.length) {
                rows.innerHTML = '<tr><td colspan="4" class="px-6 py-10 text-center text-[#94A3B8]">Staff ini belum punya tugas.</td></tr>';
                return;
            }
            staff.tugas.forEach((task) => {
                const status = (task.status_efektif || task.status || '-').replaceAll('-', ' ');
                const row = document.createElement('tr');
                row.className = 'hover:bg-[#F8FAFC] transition';
                const title = document.createElement('td');
                title.className = 'px-6 py-4 font-semibold text-[#0F172A]';
                title.textContent = task.judul_tugas || '-';
                row.appendChild(title);
                const deadline = document.createElement('td');
                deadline.className = 'px-6 py-4 text-center text-[#565E74]';
                deadline.textContent = task.deadline
                    ? new Date(task.deadline + 'T00:00:00').toLocaleDateString('id-ID')
                    : '-';
                row.appendChild(deadline);
                const approval = document.createElement('td');
                approval.className = 'px-6 py-4 text-center';
                const badge = document.createElement('span');
                badge.className = 'inline-block px-3 py-1 rounded-full text-xs font-semibold ' + (status === 'sudah di acc' ? 'bg-[#DCFCE7] text-[#15803D]' : (status === 'menunggu di acc' ? 'bg-[#FEF3C7] text-[#B45309]' : 'bg-slate-100 text-slate-600'));
                badge.textContent = status;
                approval.appendChild(badge);
                row.appendChild(approval);
                const actionCell = document.createElement('td');
                actionCell.className = 'px-6 py-4 text-center';
                const action = document.createElement('a');
                action.href = '/kadiv/detailTugas/' + encodeURIComponent(task.id_tugas);
                action.className = 'text-[#0e9dc3] hover:text-[#0c88a9] hover:underline transition font-medium';
                action.textContent = 'Lihat';
                actionCell.appendChild(action);
                row.appendChild(actionCell);
                rows.appendChild(row);
            });
        } catch (error) {
            console.error('Gagal memuat detail staff Kadiv:', error);
            document.getElementById('detail-staff-tasks').innerHTML = '<tr><td colspan="4" class="px-6 py-10 text-center text-red-600">Data staff gagal dimuat.</td></tr>';
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadKadivStaffDetail);
    } else {
        loadKadivStaffDetail();
    }
</script>
</body>
</html>
