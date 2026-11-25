<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Staff - PT Silindo</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">
    
    @include('component_kadiv.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        @include('component.topbar')

        <main class="flex-1 overflow-y-auto">
            
            <div class="px-9 pt-7 pb-16">

                @include('component.breadcrumbs', ['parentText' => 'Manajemen Staff', 'parentUrl' => url('/kadiv/manajemenStaff'), 'currentPage' => 'Detail Staff', 'inline' => true])
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

                <div class="mt-6 bg-white rounded-xl border border-[#E2E8EC] shadow-sm p-6">
                    <h2 class="text-base font-bold text-[#0F172A] border-b border-slate-100 pb-3 mb-4">Informasi Staff</h2>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                        <div>
                            <dt class="text-xs text-slate-400 mb-0.5">Divisi</dt>
                            <dd id="detail-staff-divisi" class="font-medium text-slate-900">-</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-400 mb-0.5">Job Level</dt>
                            <dd id="detail-staff-job-level" class="font-medium text-slate-900">-</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-400 mb-0.5">Pendidikan Terakhir</dt>
                            <dd id="detail-staff-pendidikan" class="font-medium text-slate-900">-</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-400 mb-0.5">Tanggal Masuk</dt>
                            <dd id="detail-staff-tanggal-masuk" class="font-medium text-slate-900">-</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-400 mb-0.5">Email</dt>
                            <dd id="detail-staff-email" class="font-medium text-slate-900 break-all">-</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-400 mb-0.5">No. Telepon</dt>
                            <dd id="detail-staff-telepon" class="font-medium text-slate-900">-</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-400 mb-0.5">No. Rekening</dt>
                            <dd id="detail-staff-rekening" class="font-medium text-slate-900">-</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-400 mb-0.5">Alamat</dt>
                            <dd id="detail-staff-alamat" class="font-medium text-slate-900">-</dd>
                        </div>
                    </dl>
                </div>

                <h2 class="mt-8 text-xl font-bold text-[#0F172A]">Tugas <span id="detail-staff-task-name">-</span></h2>

                <div class="mt-4 bg-white rounded-xl border border-[#E2E8F0] shadow-sm overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-[#EAF4F8] text-[#334155] text-xs">
                            <tr>
                                <th class="px-6 py-4 text-left font-medium">Tugas</th>
                                <th class="px-6 py-4 text-center font-medium">Tenggat</th>
                                <th class="px-6 py-4 text-center font-medium">Approval</th>
                                <th class="px-6 py-4 text-center font-medium">Aksi</th>
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

@include('components.status-badge-js')

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
            const crumb = document.querySelector('[data-breadcrumb-current]');
            if (crumb) crumb.textContent = staff.nama || 'Detail Staff';
            document.getElementById('detail-staff-task-name').textContent = staff.nama || '-';
            const jabatanLabel = staff.jabatan_ref?.nama
                ? (staff.jabatan_ref.aktif === false ? `${staff.jabatan_ref.nama} (nonaktif)` : staff.jabatan_ref.nama)
                : (staff.jabatan || staff.user?.role?.nama_role || '-');
            document.getElementById('detail-staff-role').textContent = jabatanLabel;
            document.getElementById('detail-staff-status').textContent = staff.user?.last_login_at ? 'Aktif' : 'Belum pernah login';

            const valOrDash = (val) => (val !== null && val !== undefined && String(val).trim() !== '') ? String(val).trim() : '-';
            const tglMasukRaw = staff.tanggal_masuk || staff.tanggal_rekrut;
            const tglMasukLabel = tglMasukRaw
                ? new Date(String(tglMasukRaw).replace(' ', 'T')).toLocaleDateString('id-ID', { dateStyle: 'medium' })
                : '-';
            const pendidikanRaw = valOrDash(staff.pendidikan);
            const pendidikanLabel = ['SMK', 'SMA/SMK'].includes(pendidikanRaw.toUpperCase()) ? 'SMK' : pendidikanRaw;

            document.getElementById('detail-staff-divisi').textContent = valOrDash(staff.divisi?.nama_divisi);
            document.getElementById('detail-staff-job-level').textContent = valOrDash(staff.job_level);
            document.getElementById('detail-staff-pendidikan').textContent = pendidikanLabel;
            document.getElementById('detail-staff-tanggal-masuk').textContent = tglMasukLabel;
            document.getElementById('detail-staff-email').textContent = valOrDash(staff.email);
            document.getElementById('detail-staff-telepon').textContent = valOrDash(staff.no_telepon);
            document.getElementById('detail-staff-rekening').textContent = valOrDash(staff.no_rekening);
            document.getElementById('detail-staff-alamat').textContent = valOrDash(staff.alamat);

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
                    ? new Date(String(task.deadline).replace(' ', 'T')).toLocaleString('id-ID', {
                        dateStyle: 'medium',
                        timeStyle: 'short'
                    })
                    : '-';
                row.appendChild(deadline);
                const approval = document.createElement('td');
                approval.className = 'px-6 py-4 text-center';
                const st = (task.status_efektif || task.status || '').toLowerCase().trim();
                const badgeHolder = document.createElement('span');
                badgeHolder.innerHTML = window.statusBadgeHtml(st, { label: status });
                approval.appendChild(badgeHolder.firstElementChild);
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
