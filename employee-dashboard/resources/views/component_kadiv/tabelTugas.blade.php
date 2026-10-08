@php
    $isCompact = $compact ?? false;
    $cellPadding = $isCompact ? 'px-4 py-3' : 'px-6 py-4';

@endphp

<div class="flex flex-col gap-3 w-full mt-2">
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold text-slate-800">Tugas Divisi</h2>
        <button type="button" data-modal-open="modalTugasBaru"
                class="text-sm px-4 py-2 bg-[#0097B2] text-white rounded-lg font-medium hover:bg-[#008199] transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Tugas
        </button>
    </div>

    <form id="kadivTaskFilter" onsubmit="return false;"
        class="flex flex-wrap items-end gap-4 bg-white border border-slate-200 rounded-xl shadow-sm p-5">
        <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
            <label for="kadivTaskSearch" class="text-xs font-bold text-slate-500">Cari Judul</label>
            <input type="text" id="kadivTaskSearch" placeholder="Judul tugas..."
                class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
        </div>
        <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
            <label for="kadivTaskStatus" class="text-xs font-bold text-slate-500">Status</label>
            <select id="kadivTaskStatus"
                class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                <option value="">Semua</option>
                <option value="baru">Baru</option>
                <option value="berjalan">Berjalan</option>
                <option value="menunggu di-acc">Menunggu di-acc</option>
                <option value="sudah di-acc">Sudah di-acc</option>
                <option value="telat">Telat</option>
            </select>
        </div>
        <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
            <label for="kadivTaskStaff" class="text-xs font-bold text-slate-500">Staff</label>
            <select id="kadivTaskStaff"
                class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                <option value="">Semua Staff</option>
            </select>
        </div>
        <div class="flex shrink-0 gap-2 sm:ml-auto">
            <button type="button" id="kadivTaskApply"
                class="h-10 inline-flex items-center px-4 bg-[#004A65] text-white text-sm font-medium rounded-md hover:bg-[#003347] transition">Terapkan</button>
            <button type="button" id="kadivTaskReset"
                class="h-10 inline-flex items-center px-4 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">Reset</button>
        </div>
    </form>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
        <div class="{{ $isCompact ? 'overflow-hidden' : 'overflow-x-auto' }}">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">
                            <button type="button" onclick="setKadivTaskSort('judul')"
                                class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Nama Tugas <span data-sort-indicator="judul" class="text-slate-300">&#8597;</span>
                            </button>
                        </th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">
                            <button type="button" onclick="setKadivTaskSort('status')"
                                class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Keterangan <span data-sort-indicator="status" class="text-slate-300">&#8597;</span>
                            </button>
                        </th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">
                            <button type="button" onclick="setKadivTaskSort('tanggal')"
                                class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Tanggal <span data-sort-indicator="tanggal" class="text-slate-300">&#8597;</span>
                            </button>
                        </th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="kadiv-tugas-list" class="divide-y divide-slate-200">
                    <tr><td colspan="4" class="{{ $cellPadding }} text-center text-slate-500">Memuat tugas...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL BUAT TUGAS BARU --}}
<div id="modalTugasBaru"
     class="opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center p-4
            bg-[rgba(86,94,116,0.4)] backdrop-blur-sm
            transition-opacity duration-300 ease-in-out">

    <form id="formTugasBaru" data-modal-panel
          action="#"
          method="POST"
          enctype="multipart/form-data"
          class="bg-white rounded-xl w-full max-w-[420px] max-h-[90vh] flex flex-col
                 shadow-[0_8px_16px_rgba(0,0,0,0.12)]
                 scale-95 translate-y-2 transition-all duration-300 ease-in-out">
        @csrf

        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#E2E8F0] bg-[#F8FAFC] rounded-t-xl">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-[#D9EEF5] text-[#146C94] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-black">Buat Tugas Baru</h3>
            </div>

            <button type="button" data-modal-close="modalTugasBaru"
                    class="text-[#565E74] hover:text-black text-2xl leading-none cursor-pointer">&times;</button>
        </div>

        {{-- BODY --}}
        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-6">

            <section class="space-y-4">
                <div class="flex items-center gap-2">
                    <span class="h-4 w-1 rounded bg-[#19A7CE]"></span>
                    <h4 class="text-xs font-bold uppercase tracking-wide text-[#565E74]">Informasi Tugas</h4>
                </div>
                <div>
                    <label class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">JUDUL TUGAS</label>
                    <input type="text" name="judul" required placeholder="Judul tugas"
                           class="w-full h-10 rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] placeholder:text-[#94A3B8] outline-none focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]">
                </div>
                <div>
                    <label class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">DESKRIPSI</label>
                    <textarea name="deskripsi" rows="4" placeholder="Tulis deskripsi tugas..."
                              class="w-full resize-none rounded-lg border border-[#CBD5E1] px-3 py-2.5 text-sm text-[#283044] placeholder:text-[#94A3B8] outline-none focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]"></textarea>
                </div>
            </section>

            <section class="space-y-4">
                <div class="flex items-center gap-2">
                    <span class="h-4 w-1 rounded bg-[#19A7CE]"></span>
                    <h4 class="text-xs font-bold uppercase tracking-wide text-[#565E74]">Penugasan</h4>
                </div>
                <div>
                    <label class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">PENERIMA</label>
                    <select name="penerima" required
                            id="penerimaTugas"
                            class="w-full h-10 rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] bg-white outline-none focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]">
                        <option value="">Memuat staff...</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">TENGGAT</label>
                    <input type="datetime-local" name="tenggat" required
                           class="w-full h-10 rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] outline-none cursor-pointer focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]">
                </div>
            </section>

            <section class="space-y-4">
                <div class="flex items-center gap-2">
                    <span class="h-4 w-1 rounded bg-[#19A7CE]"></span>
                    <h4 class="text-xs font-bold uppercase tracking-wide text-[#565E74]">Lampiran</h4>
                </div>
                <div class="bg-[#F1F5F9] rounded-lg p-4">
                    <label for="fileTugas"
                           class="flex flex-col items-center justify-center text-center gap-1 h-[110px] rounded-lg border-2 border-dashed border-[#CBD5E1] bg-white cursor-pointer hover:border-[#19A7CE] transition">
                        <span id="namaFileTugas" class="text-sm font-bold text-[#283044] px-3 truncate max-w-full">Pilih File</span>
                        <span class="text-xs text-[#94A3B8]">Maks 20 MB</span>
                    </label>
                    <input id="fileTugas" type="file" name="lampiran" class="hidden">
                </div>
            </section>
        </div>

        {{-- FOOTER --}}
        <div class="flex items-center justify-between px-6 py-4 border-t border-[#E2E8F0]">
            <button type="button" data-modal-close="modalTugasBaru"
                    class="h-9 px-4 bg-[#E8E7E9] rounded-lg text-sm text-[#333335] hover:bg-[#D9D9D9] transition cursor-pointer">
                Batal
            </button>

            <button type="submit"
                    class="h-9 px-4 bg-[#146C94] rounded-lg text-sm text-white font-bold flex items-center gap-2 hover:bg-[#0f5878] transition cursor-pointer">
                Kirim Tugas
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M2 21l21-9L2 3v7l15 2-15 2z"/></svg>
            </button>
        </div>
    </form>
</div>

<script>
(function () {
    const token = sessionStorage.getItem('staff_token');
    const taskRows = document.getElementById('kadiv-tugas-list');
    const staffSelect = document.getElementById('penerimaTugas');
    const taskForm = document.getElementById('formTugasBaru');

    const filterSearch = document.getElementById('kadivTaskSearch');
    const filterStatus = document.getElementById('kadivTaskStatus');
    const filterStaff = document.getElementById('kadivTaskStaff');
    let taskSort = null;
    let taskDir = 'asc';

    async function apiRequest(url, options = {}) {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token,
                ...(options.headers || {})
            }
        });
        const result = await response.json();
        if (!response.ok) {
            throw new Error(result.message || 'Permintaan ke server gagal.');
        }
        return result;
    }

    async function loadTasks() {
        try {
            const params = new URLSearchParams();
            if (taskSort) {
                params.set('sort', taskSort);
                params.set('dir', taskDir);
            }
            if (filterSearch && filterSearch.value) params.set('q', filterSearch.value);
            if (filterStatus && filterStatus.value) params.set('status', filterStatus.value);
            if (filterStaff && filterStaff.value) params.set('staff', filterStaff.value);
            const query = params.toString();

            const result = await apiRequest('/api/kadiv/tugas' + (query ? '?' + query : ''));
            taskRows.replaceChildren();
            if (!Array.isArray(result.data) || result.data.length === 0) {
                taskRows.innerHTML = '<tr><td colspan="4" class="{{ $cellPadding }} text-center text-slate-500">Belum ada tugas.</td></tr>';
                return;
            }

            result.data.forEach((task) => {
                const status = (task.status_efektif || task.status || 'baru').replaceAll('-', ' ');
                const row = document.createElement('tr');
                row.className = 'hover:bg-slate-50 transition';

                const title = document.createElement('td');
                title.className = '{{ $cellPadding }} font-semibold text-slate-800 whitespace-nowrap';
                title.textContent = task.judul_tugas || '-';
                row.appendChild(title);

                const statusCell = document.createElement('td');
                statusCell.className = '{{ $cellPadding }} whitespace-nowrap text-center';
                const badge = document.createElement('span');
                badge.className = 'px-2.5 py-1 rounded-full text-xs font-bold';
                badge.classList.add(status === 'sudah di acc' ? 'bg-green-100' : (status === 'menunggu di acc' ? 'bg-amber-100' : 'bg-blue-100'));
                badge.classList.add(status === 'sudah di acc' ? 'text-green-700' : (status === 'menunggu di acc' ? 'text-amber-700' : 'text-blue-700'));
                badge.textContent = status;
                statusCell.appendChild(badge);
                row.appendChild(statusCell);

                const deadline = document.createElement('td');
                deadline.className = '{{ $cellPadding }} text-slate-500 text-center whitespace-nowrap';
                deadline.textContent = task.deadline
                    ? new Date(task.deadline + 'T00:00:00').toLocaleDateString('id-ID')
                    : '-';
                row.appendChild(deadline);

                const actionCell = document.createElement('td');
                actionCell.className = '{{ $cellPadding }} whitespace-nowrap text-center text-xs';

                const action = document.createElement('a');
                action.href = '/kadiv/detailTugas/' + encodeURIComponent(task.id_tugas);
                action.className = 'text-[#0c88a9] hover:text-[#104958] transition font-medium';
                action.textContent = 'Detail';

                actionCell.appendChild(action);
                row.appendChild(actionCell);
                taskRows.appendChild(row);
            });
            updateSortIndicators();
        } catch (error) {
            console.error('Gagal memuat tugas Kadiv:', error);
            taskRows.innerHTML = '<tr><td colspan="4" class="{{ $cellPadding }} text-center text-red-600">Tugas gagal dimuat.</td></tr>';
        }
    }

    async function loadStaffOptions() {
        try {
            const result = await apiRequest('/api/kadiv/staff');
            const staffOptions = [];
            (result.data || []).forEach((staff) => {
                const account = staff.user;
                if (account?.role?.nama_role?.toLowerCase() === 'staff') {
                    staffOptions.push({ value: staff.id_karyawan, label: staff.nama });
                }
            });

            staffSelect.replaceChildren(new Option('Semua anggota divisi', 'semua'));
            staffOptions.forEach((staff) => {
                staffSelect.add(new Option(staff.label, staff.value));
            });
        } catch (error) {
            console.error('Gagal memuat penerima tugas:', error);
            staffSelect.replaceChildren(new Option('Staff gagal dimuat', ''));
        }
    }

    function updateSortIndicators() {
        document.querySelectorAll('[data-sort-indicator]').forEach((el) => {
            if (el.dataset.sortIndicator === taskSort) {
                el.textContent = taskDir === 'asc' ? '\u25B2' : '\u25BC';
                el.classList.remove('text-slate-300');
                el.classList.add('text-[#004A65]');
            } else {
                el.textContent = '\u2195';
                el.classList.add('text-slate-300');
                el.classList.remove('text-[#004A65]');
            }
        });
    }

    window.setKadivTaskSort = function (col) {
        if (taskSort === col) {
            taskDir = taskDir === 'asc' ? 'desc' : 'asc';
        } else {
            taskSort = col;
            taskDir = 'asc';
        }
        loadTasks();
    };

    async function loadFilterStaffOptions() {
        if (!filterStaff) return;
        try {
            const result = await apiRequest('/api/kadiv/staff');
            filterStaff.replaceChildren(new Option('Semua Staff', ''));
            (result.data || []).forEach((staff) => {
                const account = staff.user;
                if (account?.role?.nama_role?.toLowerCase() === 'staff') {
                    filterStaff.add(new Option(staff.nama, staff.id_karyawan));
                }
            });
        } catch (error) {
            console.error('Gagal memuat filter staff:', error);
        }
    }

    if (!token) {
        window.location.href = '/login';
        return;
    }

    loadTasks();
    loadStaffOptions();
    loadFilterStaffOptions();

    document.getElementById('kadivTaskApply')?.addEventListener('click', () => loadTasks());
    document.getElementById('kadivTaskReset')?.addEventListener('click', () => {
        if (filterSearch) filterSearch.value = '';
        if (filterStatus) filterStatus.value = '';
        if (filterStaff) filterStaff.value = '';
        taskSort = null;
        taskDir = 'asc';
        loadTasks();
    });

    taskForm.addEventListener('submit', async function (event) {
        event.preventDefault();
        const formData = new FormData(taskForm);
        const recipient = formData.get('penerima');
        const staffIds = recipient === 'semua'
            ? Array.from(staffSelect.options).map((option) => option.value).filter((value) => value && value !== 'semua')
            : [recipient];
        const file = document.getElementById('fileTugas').files[0];

        if (!staffIds.length) {
            alert('Pilih staff penerima tugas.');
            return;
        }

        try {
            for (const staffId of staffIds) {
                const payload = new FormData();
                payload.append('karyawan_id_karyawan', staffId);
                payload.append('judul_tugas', formData.get('judul'));
                payload.append('deskripsi', formData.get('deskripsi') || '');
                payload.append('deadline', formData.get('tenggat'));
                if (file) payload.append('file_pendukung', file);
                await apiRequest('/api/kadiv/tugas', { method: 'POST', body: payload });
            }
            taskForm.reset();
            document.getElementById('namaFileTugas').textContent = 'Pilih File';
            if (window.setModal) window.setModal('modalTugasBaru', false);
            await loadTasks();
        } catch (error) {
            alert(error.message);
        }
    });

    // cegah dobel kalau script yang sama sudah ada di app.js
    if (!window.__modalReady) {
        window.__modalReady = true;

        window.setModal = function (id, show) {
            const modal = document.getElementById(id);
            if (!modal) return;
            const panel = modal.querySelector('[data-modal-panel]');

            modal.classList.toggle('opacity-0', !show);
            modal.classList.toggle('pointer-events-none', !show);
            modal.classList.toggle('opacity-100', show);

            if (panel) {
                panel.classList.toggle('scale-95', !show);
                panel.classList.toggle('translate-y-2', !show);
                panel.classList.toggle('scale-100', show);
                panel.classList.toggle('translate-y-0', show);
            }
            document.body.classList.toggle('overflow-hidden', show);
        };

        document.addEventListener('click', function (e) {
            const open  = e.target.closest('[data-modal-open]');
            const close = e.target.closest('[data-modal-close]');
            if (open)  return setModal(open.dataset.modalOpen, true);
            if (close) return setModal(close.dataset.modalClose, false);
            if (e.target.matches('[id^="modal"]')) setModal(e.target.id, false);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            document.querySelectorAll('[id^="modal"].opacity-100')
                    .forEach(m => setModal(m.id, false));
        });
    }

    // nama file + validasi 20 MB
    const input = document.getElementById('fileTugas');
    const label = document.getElementById('namaFileTugas');
    input.addEventListener('change', function () {
        const f = this.files[0];
        if (!f) { label.textContent = 'Pilih File'; return; }
        if (f.size > 20 * 1024 * 1024) {
            alert('Ukuran file maksimal 20 MB');
            this.value = '';
            label.textContent = 'Pilih File';
            return;
        }
        label.textContent = f.name;
    });
})();
</script>

