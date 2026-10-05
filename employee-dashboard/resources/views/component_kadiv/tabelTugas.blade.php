@php
    $isCompact = $compact ?? false;
    $cellPadding = $isCompact ? 'px-4 py-3' : 'px-6 py-4';

    // Data dummy tugas
    $dummyTugas = [
        [
            'id' => 1,
            'nama_tugas' => 'Integrasi API Dashboard',
            'keterangan' => 'Belum Selesai',
            'tanggal' => '08 Oct 2026',
            'status' => 'Proses',
        ],
        [
            'id' => 2,
            'nama_tugas' => 'Review Desain UI/UX',
            'keterangan' => 'Menunggu ACC Kadiv',
            'tanggal' => '06 Oct 2026',
            'status' => 'Review',
        ],
        [
            'id' => 3,
            'nama_tugas' => 'Fix Bug Authentication',
            'keterangan' => 'Telah Diselesaikan',
            'tanggal' => '02 Oct 2026',
            'status' => 'Selesai',
        ],
        [
            'id' => 4,
            'nama_tugas' => 'Setup Database PostgreSQL',
            'keterangan' => 'Telah Diselesaikan',
            'tanggal' => '28 Sep 2026',
            'status' => 'Selesai',
        ],
    ];
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

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
        <div class="{{ $isCompact ? 'overflow-hidden' : 'overflow-x-auto' }}">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Nama Tugas</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">Keterangan</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">Tanggal</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($dummyTugas as $tugas)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="{{ $cellPadding }} font-semibold text-slate-800 whitespace-nowrap">
                                {{ $tugas['nama_tugas'] }}
                            </td>

                            <td class="{{ $cellPadding }} whitespace-nowrap text-center">
                                @if ($tugas['status'] == 'Selesai')
                                    <span
                                        class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">
                                        {{ $tugas['keterangan'] }}
                                    </span>
                                @elseif($tugas['status'] == 'Review')
                                    <span
                                        class="px-2.5 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-bold">
                                        {{ $tugas['keterangan'] }}
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-bold">
                                        {{ $tugas['keterangan'] }}
                                    </span>
                                @endif
                            </td>

                            <td class="{{ $cellPadding }} text-slate-500 text-center whitespace-nowrap">
                                {{ $tugas['tanggal'] }}
                            </td>

                            {{-- Kolom Aksi --}}
                            <td class="{{ $cellPadding }} whitespace-nowrap text-center text-xs">
                                <a href="/kadiv/detailTugas/{{ $tugas['id'] }}"
                                    class="text-[#0c88a9] hover:text-[#104958] transition font-medium">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
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

    <form data-modal-panel
          action="{{ route('kadiv.tugas.store') }}"
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
        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">

            <div>
                <label class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">1. JUDUL TUGAS</label>
                <input type="text" name="judul" required placeholder="Judul tugas"
                       class="w-full h-10 rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] placeholder:text-[#94A3B8] outline-none focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]">
            </div>

            <div>
                <label class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">2. TUJUAN PENERIMA</label>
                <div class="space-y-3">

                    <select name="penerima" required
                            class="w-full h-10 rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] bg-white outline-none focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]">
                        <option value="Samuel">Samu</option>
                        <option value="Aril">Aril</option>
                        <option value="Fitri">Fitri</option>
                        <option value="jerremy">Jerremy</option>
                        <option value="semua">Semua anggota divisi</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">3. DESKRIPSI</label>
                <textarea name="deskripsi" rows="4" placeholder="Tulis deskripsi tugas..."
                          class="w-full resize-none rounded-lg border border-[#CBD5E1] px-3 py-2.5 text-sm text-[#283044] placeholder:text-[#94A3B8] outline-none focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">TENGGAT</label>
                <input type="datetime-local" name="tenggat" required
                       class="w-full h-10 rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] outline-none cursor-pointer focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]">
            </div>

            <div class="bg-[#F1F5F9] rounded-lg p-4">
                <label for="fileTugas"
                       class="flex flex-col items-center justify-center text-center gap-1 h-[110px] rounded-lg border-2 border-dashed border-[#CBD5E1] bg-white cursor-pointer hover:border-[#19A7CE] transition">
                    <span id="namaFileTugas" class="text-sm font-bold text-[#283044] px-3 truncate max-w-full">Pilih File</span>
                    <span class="text-xs text-[#94A3B8]">Maks 20 MB</span>
                </label>
                <input id="fileTugas" type="file" name="lampiran" class="hidden">
            </div>
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

{{-- Buka/tutup + animasi ditangani script global di app.js. Di sini hanya nama file + validasi 20 MB --}}
<script>
(function () {
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

    // nama file + validasi 5 MB
    const input = document.getElementById('fileTugas');
    const label = document.getElementById('namaFileTugas');
    input.addEventListener('change', function () {
        const f = this.files[0];
        if (!f) { label.textContent = 'Pilih File'; return; }
        if (f.size > 20 * 1024 * 1024) {
            alert('Ukuran file maksimal 5 MB');
            this.value = '';
            label.textContent = 'Pilih File';
            return;
        }
        label.textContent = f.name;
    });
})();
</script>
