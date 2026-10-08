@php
    $isCompact = $compact ?? false;
    $cellPadding = $isCompact ? 'px-4 py-3' : 'px-6 py-4';

@endphp

<div class="flex flex-col gap-3 w-full mt-2">
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold text-slate-800">Pesan Masuk</h2>
        <button onclick="bukaModalPesan()" class="text-sm px-4 py-2 bg-[#0097B2] text-white rounded-lg font-medium hover:bg-[#008199] transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
            </svg>
            Kirim Pesan
        </button>
    </div>
    
    <form id="kadivPesanFilter" onsubmit="return false;"
        class="flex flex-wrap items-end gap-4 bg-white border border-slate-200 rounded-xl shadow-sm p-5">
        <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
            <label for="kadivPesanSearch" class="text-xs font-bold text-slate-500">Cari</label>
            <input type="text" id="kadivPesanSearch" placeholder="Judul / isi pesan..."
                class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
        </div>
        <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
            <label for="kadivPesanTipe" class="text-xs font-bold text-slate-500">Tipe</label>
            <select id="kadivPesanTipe"
                class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                <option value="">Semua</option>
                <option value="pesan">Pesan</option>
                <option value="surat">Surat</option>
            </select>
        </div>
        <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
            <label for="kadivPesanArah" class="text-xs font-bold text-slate-500">Arah</label>
            <select id="kadivPesanArah"
                class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                <option value="">Semua</option>
                <option value="masuk">Masuk</option>
                <option value="keluar">Keluar</option>
            </select>
        </div>
        <div class="flex shrink-0 gap-2 sm:ml-auto">
            <button type="button" id="kadivPesanApply"
                class="h-10 inline-flex items-center px-4 bg-[#004A65] text-white text-sm font-medium rounded-md hover:bg-[#003347] transition">Terapkan</button>
            <button type="button" id="kadivPesanReset"
                class="h-10 inline-flex items-center px-4 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">Reset</button>
        </div>
    </form>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
        <div class="{{ $isCompact ? 'overflow-hidden' : 'overflow-x-auto' }}">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">
                            <button type="button" onclick="setKadivPesanSort('judul')"
                                class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Isi Pesan <span data-sort-indicator="judul" class="text-slate-300">&#8597;</span>
                            </button>
                        </th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">
                            <button type="button" onclick="setKadivPesanSort('pengirim')"
                                class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Pengirim <span data-sort-indicator="pengirim" class="text-slate-300">&#8597;</span>
                            </button>
                        </th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">
                            <button type="button" onclick="setKadivPesanSort('tanggal')"
                                class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                Tanggal <span data-sort-indicator="tanggal" class="text-slate-300">&#8597;</span>
                            </button>
                        </th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="kadiv-pesan-list" class="divide-y divide-slate-200">
                    <tr><td colspan="4" class="{{ $cellPadding }} text-center text-slate-500">Memuat pesan...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL KIRIM PESAN --}}
<div id="modalKirimPesan" class="opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center transition-opacity duration-300">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupModalPesan()"></div>

    <div id="modalBoxPesan" class="transform scale-95 translate-y-4 relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden transition-all duration-300">
        
        <div class="flex items-center justify-between p-4 border-b border-dashed border-gray-300">
            <div class="flex items-center gap-3">
                <div class="bg-cyan-100 p-1.5 rounded text-cyan-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900">Kirim Pesan ke Staff</h3>
            </div>
            
            <button onclick="tutupModalPesan()" class="text-slate-400 hover:text-red-500 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form id="formKirimPesan" action="#" method="POST" class="p-5 flex flex-col gap-4">
            @csrf 
            
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">PENERIMA</label>
                <select name="penerima_id" id="penerimaPesan" class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-[#004A65] focus:border-[#004A65] outline-none text-sm text-slate-700" required>
                    <option value="">-- Pilih Staff Penerima --</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">ISI PESAN</label>
                <textarea name="isi_pesan" id="isiPesanBaru" rows="4" class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-[#004A65] focus:border-[#004A65] outline-none text-sm placeholder-gray-400" placeholder="Tuliskan pesan kamu di sini..." required></textarea>
            </div>

            <div class="flex justify-between items-center mt-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="tutupModalPesan()" class="px-5 py-1.5 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">
                    Batal
                </button>
                
                <button type="submit" class="px-4 py-1.5 bg-[#004A65] text-white text-sm font-medium rounded-md hover:bg-[#003347] transition flex items-center gap-1">
                    Kirim
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const modalPesan = document.getElementById('modalKirimPesan');
    const modalBoxPesan = document.getElementById('modalBoxPesan');
    const kadivMessageToken = localStorage.getItem('staff_token');

    const filterSearch = document.getElementById('kadivPesanSearch');
    const filterTipe = document.getElementById('kadivPesanTipe');
    const filterArah = document.getElementById('kadivPesanArah');
    let kadivPesanSort = null;
    let kadivPesanDir = 'asc';

    async function loadKadivMessages() {
        const rows = document.getElementById('kadiv-pesan-list');
        try {
            const params = new URLSearchParams({ per_page: '100' });
            if (kadivPesanSort) {
                params.set('sort', kadivPesanSort);
                params.set('dir', kadivPesanDir);
            }
            if (filterSearch && filterSearch.value) params.set('q', filterSearch.value);
            if (filterTipe && filterTipe.value) params.set('tipe', filterTipe.value);
            if (filterArah && filterArah.value) params.set('arah', filterArah.value);

            const response = await fetch('/api/kadiv/pesan?' + params.toString(), {
                headers: { 'Authorization': 'Bearer ' + kadivMessageToken, 'Accept': 'application/json' }
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Gagal memuat pesan.');
            rows.replaceChildren();
            if (!result.data?.length) {
                rows.innerHTML = '<tr><td colspan="4" class="{{ $cellPadding }} text-center text-slate-500">Belum ada pesan.</td></tr>';
                return;
            }

            result.data.forEach((message) => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-slate-50 transition';
                const content = document.createElement('td');
                content.className = '{{ $cellPadding }} font-semibold text-slate-800 truncate max-w-[200px]';
                content.textContent = message.judul_pesan || message.deskripsi || '-';
                row.appendChild(content);
                const sender = document.createElement('td');
                sender.className = '{{ $cellPadding }} text-slate-700 text-center whitespace-nowrap';
                sender.textContent = message.pengirim?.nama || message.pengirim?.username || '-';
                row.appendChild(sender);
                const date = document.createElement('td');
                date.className = '{{ $cellPadding }} text-slate-500 text-center whitespace-nowrap';
                const sentAt = message.tanggal_pesan || message.created_at;
                date.textContent = sentAt
                    ? new Date(sentAt.length === 10 ? sentAt + 'T00:00:00' : sentAt).toLocaleDateString('id-ID')
                    : '-';
                row.appendChild(date);
                const actionCell = document.createElement('td');
                actionCell.className = '{{ $cellPadding }} whitespace-nowrap text-center text-xs';
                const action = document.createElement('a');
                action.href = '/kadiv/detailPesan/' + encodeURIComponent(message.id_pesan);
                action.className = 'px-4 py-1.5 text-[#0e9dc3] hover:text-[#06627b] transition font-medium inline-block';
                action.textContent = 'Lihat';
                actionCell.appendChild(action);
                row.appendChild(actionCell);
                rows.appendChild(row);
            });
            updateSortIndicators();
        } catch (error) {
            console.error('Gagal memuat pesan Kadiv:', error);
            rows.innerHTML = '<tr><td colspan="4" class="{{ $cellPadding }} text-center text-red-600">Pesan gagal dimuat.</td></tr>';
        }
    }

    async function loadKadivMessageRecipients() {
        const select = document.getElementById('penerimaPesan');
        try {
            const response = await fetch('/api/kadiv/staff', {
                headers: { 'Authorization': 'Bearer ' + kadivMessageToken, 'Accept': 'application/json' }
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Gagal memuat staff.');
            select.replaceChildren(new Option('-- Pilih Staff Penerima --', ''));
            (result.data || []).forEach((staff) => {
                if (staff.user?.role?.nama_role?.toLowerCase() === 'staff') {
                    select.add(new Option(staff.nama, staff.user.id_user));
                }
            });
        } catch (error) {
            console.error('Gagal memuat penerima pesan:', error);
            select.replaceChildren(new Option('Staff gagal dimuat', ''));
        }
    }

    function updateSortIndicators() {
        document.querySelectorAll('[data-sort-indicator]').forEach((el) => {
            if (el.dataset.sortIndicator === kadivPesanSort) {
                el.textContent = kadivPesanDir === 'asc' ? '\u25B2' : '\u25BC';
                el.classList.remove('text-slate-300');
                el.classList.add('text-[#004A65]');
            } else {
                el.textContent = '\u2195';
                el.classList.add('text-slate-300');
                el.classList.remove('text-[#004A65]');
            }
        });
    }

    window.setKadivPesanSort = function (col) {
        if (kadivPesanSort === col) {
            kadivPesanDir = kadivPesanDir === 'asc' ? 'desc' : 'asc';
        } else {
            kadivPesanSort = col;
            kadivPesanDir = 'asc';
        }
        loadKadivMessages();
    };

    if (!kadivMessageToken) {
        window.location.href = '/login';
    } else {
        loadKadivMessages();
        loadKadivMessageRecipients();
    }

    document.getElementById('kadivPesanApply')?.addEventListener('click', () => loadKadivMessages());
    document.getElementById('kadivPesanReset')?.addEventListener('click', () => {
        if (filterSearch) filterSearch.value = '';
        if (filterTipe) filterTipe.value = '';
        if (filterArah) filterArah.value = '';
        kadivPesanSort = null;
        kadivPesanDir = 'asc';
        loadKadivMessages();
    });

    document.getElementById('formKirimPesan').addEventListener('submit', async function (event) {
        event.preventDefault();
        const description = document.getElementById('isiPesanBaru').value.trim();
        const payload = {
            penerima_id_user: document.getElementById('penerimaPesan').value,
            tipe: 'pesan',
            judul_pesan: description.slice(0, 200) || 'Pesan baru',
            deskripsi: description
        };
        try {
            const response = await fetch('/api/kadiv/pesan', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + kadivMessageToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Pesan gagal dikirim.');
            this.reset();
            tutupModalPesan();
            await loadKadivMessages();
        } catch (error) {
            alert(error.message);
        }
    });

    function bukaModalPesan() {
        modalPesan.classList.remove('opacity-0', 'pointer-events-none');
        modalBoxPesan.classList.remove('scale-95', 'translate-y-4');
        modalBoxPesan.classList.add('scale-100', 'translate-y-0');
    }

    function tutupModalPesan() {
        modalPesan.classList.add('opacity-0', 'pointer-events-none');
        modalBoxPesan.classList.remove('scale-100', 'translate-y-0');
        modalBoxPesan.classList.add('scale-95', 'translate-y-4');
    }
</script>
