@php
    $employee = auth()->user()?->karyawan;
    $namaKaryawan = $employee?->nama ?? auth()->user()?->username ?? '-';
    $divisiKaryawan = $employee?->divisi?->nama_divisi ?? '-';
    $jenisGroups = \App\Models\SuratIzin::JENIS_PENGAJUAN_GROUPS;
    $selectedJenis = old('jenis_pengajuan');
    $selectedKeperluan = old('keperluan');
@endphp

<div id="modalSuratIzin"
     class="opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4
            bg-[rgba(15,40,55,.45)] transition-opacity duration-150 ease-in-out"
     role="presentation">

    <form data-modal-panel
          action="{{ route($routeSurat) }}"
          method="POST"
          enctype="multipart/form-data"
          role="dialog"
          aria-modal="true"
          aria-labelledby="modalSuratIzinTitle"
          class="bg-white rounded-xl w-full max-w-[820px] max-h-[calc(100dvh_-_1.5rem)] sm:max-h-[90vh]
                 flex flex-col overflow-hidden
                 shadow-[0_8px_16px_rgba(0,0,0,0.12)]
                 scale-95 translate-y-2 transition-all duration-150 ease-in-out">
        @csrf
        <input type="hidden" name="judul_surat" id="judulSuratInput" value="{{ old('judul_surat') }}">

        {{-- HEADER --}}
        <div class="flex items-start justify-between gap-4 px-5 sm:px-6 py-4 border-b border-[#E2E8F0]">
            <div class="flex items-start gap-3">
                <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#D9EEF5] text-[#146C94]">
                    <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Zm0 2.25h.008v.008H16.5v-.008Z" />
                    </svg>
                </div>
                <div>
                    <h3 id="modalSuratIzinTitle" class="text-[15px] font-semibold leading-6 text-[#172B36]">
                        Pengajuan Perizinan
                    </h3>
                    <p class="mt-0.5 text-[12px] leading-5 text-[#4B5563]">
                        Ajukan izin, cuti, atau keperluan dinas Anda.
                    </p>
                </div>
            </div>
            <button type="button" data-modal-close="modalSuratIzin" aria-label="Tutup"
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-[#565E74] transition hover:bg-[#F1F5F9] hover:text-[#172B36] cursor-pointer">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- BODY --}}
        <div class="flex-1 overflow-y-auto px-5 sm:px-6 py-5 space-y-6">

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[12px] text-red-700" role="alert">
                    Periksa kembali data yang wajib diisi sebelum mengirim pengajuan.
                </div>
            @endif

            {{-- INFORMASI PENGAJUAN --}}
            <section aria-labelledby="informasiPengajuanTitle">
                <h4 id="informasiPengajuanTitle"
                    class="mb-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-[#4B5563]">
                    Informasi Pengajuan
                </h4>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label for="namaKaryawan" class="mb-1.5 block text-[12px] font-medium text-[#334155]">Nama</label>
                        <input id="namaKaryawan" type="text" value="{{ $namaKaryawan }}" readonly
                               class="h-10 w-full rounded-lg border border-[#E2E8EC] bg-[#F8FAFC] px-3 text-sm text-[#475569] outline-none cursor-default">
                    </div>

                    <div>
                        <label for="divisiKaryawan" class="mb-1.5 block text-[12px] font-medium text-[#334155]">Divisi</label>
                        <input id="divisiKaryawan" type="text" value="{{ $divisiKaryawan }}" readonly
                               class="h-10 w-full rounded-lg border border-[#E2E8EC] bg-[#F8FAFC] px-3 text-sm text-[#475569] outline-none cursor-default">
                    </div>

                    <div>
                        <label for="tanggalPengajuan" class="mb-1.5 block text-[12px] font-medium text-[#334155]">
                            Tanggal Pengajuan <span class="text-[#B91C1C]" aria-hidden="true">*</span>
                        </label>
                        <input id="tanggalPengajuan" type="date" name="tanggal_pengajuan"
                               value="{{ old('tanggal_pengajuan', now()->toDateString()) }}" required
                               aria-required="true"
                               class="h-10 w-full rounded-lg border px-3 text-sm text-[#283044] outline-none cursor-pointer
                                      focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]
                                      @error('tanggal_pengajuan') border-red-400 focus:border-red-500 focus:ring-red-200 @else border-[#CBD5E1] @enderror">
                        @error('tanggal_pengajuan')
                            <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- DETAIL PENGAJUAN --}}
            <section aria-labelledby="detailPengajuanTitle">
                <h4 id="detailPengajuanTitle"
                    class="mb-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-[#4B5563]">
                    Detail Pengajuan
                </h4>

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                    <div class="lg:col-span-4">
                        <span class="mb-1.5 block text-[12px] font-medium text-[#334155]">
                            Keperluan <span class="text-[#B91C1C]" aria-hidden="true">*</span>
                        </span>
                        <div class="grid grid-cols-2 gap-1 rounded-lg bg-[#F1F5F9] p-1 @error('keperluan') ring-1 ring-red-400 @enderror">
                            @foreach (\App\Models\SuratIzin::KEPERLUAN_OPTIONS as $keperluan)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="keperluan" value="{{ $keperluan }}"
                                           class="peer sr-only" required @checked($selectedKeperluan === $keperluan)>
                                    <span class="flex h-8 items-center justify-center rounded-md text-[13px] font-medium text-[#4B5563] transition
                                                 peer-checked:bg-white peer-checked:text-[#0F5878] peer-checked:shadow-sm peer-checked:ring-1 peer-checked:ring-[#CFE8F0]
                                                 peer-focus-visible:ring-2 peer-focus-visible:ring-[#146C94]/30">
                                        {{ $keperluan }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('keperluan')
                            <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="lg:col-span-8">
                        <span class="mb-1.5 block text-[12px] font-medium text-[#334155]">
                            Untuk Tanggal <span class="text-[#B91C1C]" aria-hidden="true">*</span>
                        </span>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label for="tanggalMulai" class="mb-1 block text-[11px] text-[#64748B]">Dari tanggal</label>
                                <input id="tanggalMulai" type="date" name="tanggal_mulai"
                                       value="{{ old('tanggal_mulai') }}" required aria-required="true"
                                       class="h-10 w-full rounded-lg border px-3 text-sm text-[#283044] outline-none cursor-pointer
                                              focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]
                                              @error('tanggal_mulai') border-red-400 focus:border-red-500 focus:ring-red-200 @else border-[#CBD5E1] @enderror">
                                @error('tanggal_mulai')
                                    <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="tanggalSelesai" class="mb-1 block text-[11px] text-[#64748B]">Sampai tanggal</label>
                                <input id="tanggalSelesai" type="date" name="tanggal_selesai"
                                       value="{{ old('tanggal_selesai') }}" required aria-required="true"
                                       class="h-10 w-full rounded-lg border px-3 text-sm text-[#283044] outline-none cursor-pointer
                                              focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]
                                              @error('tanggal_selesai') border-red-400 focus:border-red-500 focus:ring-red-200 @else border-[#CBD5E1] @enderror">
                                @error('tanggal_selesai')
                                    <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-12">
                        <span class="mb-1.5 block text-[12px] font-medium text-[#334155]">
                            Jenis Pengajuan <span class="text-[#B91C1C]" aria-hidden="true">*</span>
                        </span>

                        <div class="grid grid-cols-1 gap-3 md:grid-cols-3 @error('jenis_pengajuan') rounded-lg ring-1 ring-red-400 p-2 @enderror">
                            @foreach ($jenisGroups as $kategori => $options)
                                <div class="rounded-lg border border-[#E2E8EC] bg-[#FBFDFE] p-3" data-jenis-group="{{ $kategori }}">
                                    <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.06em] text-[#146C94]">
                                        {{ $kategori }}
                                    </p>
                                    <div class="space-y-1">
                                        @foreach ($options as $jenis)
                                            <label class="flex cursor-pointer items-start gap-2 rounded-md px-2 py-1.5 transition
                                                          hover:bg-white has-[:checked]:bg-[#E8F4F8]">
                                                <input type="radio" name="jenis_pengajuan" value="{{ $jenis }}"
                                                       class="peer sr-only" required @checked($selectedJenis === $jenis)>
                                                <span class="mt-0.5 flex h-3.5 w-3.5 shrink-0 items-center justify-center rounded-full border border-[#CBD5E1] bg-white transition
                                                             peer-checked:border-[#146C94] peer-checked:bg-[#146C94]">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                                                </span>
                                                <span class="text-[12.5px] leading-5 text-[#334155] transition
                                                             peer-checked:font-semibold peer-checked:text-[#0F5878]">
                                                    {{ $jenis }}
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('jenis_pengajuan')
                            <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- KETERANGAN --}}
            <section aria-labelledby="keteranganTitle">

                <div>
                    <label for="keterangan" class="mb-1.5 block text-[12px] font-medium text-[#334155]">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" rows="3" maxlength="1000"
                              placeholder="Jelaskan alasan atau detail tambahan pengajuan Anda (opsional)."
                              class="w-full resize-none rounded-lg border px-3 py-2 text-sm text-[#283044] outline-none
                                     placeholder:text-[#94A3B8] focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]
                                     @error('keterangan') border-red-400 focus:border-red-500 focus:ring-red-200 @else border-[#CBD5E1] @enderror">{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                        <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-3 flex flex-col gap-2 rounded-lg border border-dashed border-[#CBD5E1] bg-[#F8FAFC] px-3 py-2.5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <p class="text-[12px] font-medium text-[#334155]">Lampiran <span class="font-normal text-[#64748B]">(opsional)</span></p>
                        <p id="namaFileSurat" class="truncate text-[11px] text-[#94A3B8]">PDF, DOC, JPG, PNG, atau ZIP. Maks 20 MB.</p>
                    </div>
                    <label for="fileSurat"
                           class="inline-flex h-8 shrink-0 cursor-pointer items-center justify-center rounded-lg border border-[#D5DEE3] bg-white px-3 text-[12px] font-medium text-[#172B36] transition hover:bg-[#F1F5F9]">
                        Pilih file
                    </label>
                    <input id="fileSurat" type="file" name="file" class="hidden">
                    @error('file')
                        <p class="w-full text-[11px] text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </section>
        </div>

        {{-- FOOTER --}}
        <div class="flex items-center justify-between gap-3 border-t border-[#E2E8F0] bg-[#F8FAFC] px-5 sm:px-6 py-4">
            <button type="button" data-modal-close="modalSuratIzin"
                    class="inline-flex h-9 items-center justify-center rounded-lg border border-[#D5DEE3] bg-white px-4 text-[13px] font-medium text-[#172B36] transition hover:bg-[#F1F5F9] cursor-pointer">
                Batal
            </button>
            <button type="submit"
                    class="inline-flex h-9 items-center justify-center gap-2 rounded-lg bg-[#146C94] px-4 text-[13px] font-semibold text-white transition hover:bg-[#0f5878] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]/40 cursor-pointer">
                Ajukan Perizinan
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </button>
        </div>
    </form>
</div>

<script>
(function () {
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
            const open = e.target.closest('[data-modal-open]');
            const close = e.target.closest('[data-modal-close]');
            if (open) return setModal(open.dataset.modalOpen, true);
            if (close) return setModal(close.dataset.modalClose, false);
            if (e.target.matches('[id^="modal"]')) setModal(e.target.id, false);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            document.querySelectorAll('[id^="modal"].opacity-100').forEach(function (modal) {
                setModal(modal.id, false);
            });
        });
    }

    const modal = document.getElementById('modalSuratIzin');
    if (!modal) return;

    const judulInput = document.getElementById('judulSuratInput');
    const jenisRadios = modal.querySelectorAll('input[name="jenis_pengajuan"]');
    const tanggalMulai = document.getElementById('tanggalMulai');
    const tanggalSelesai = document.getElementById('tanggalSelesai');
    const fileInput = document.getElementById('fileSurat');
    const fileLabel = document.getElementById('namaFileSurat');

    function syncJudul() {
        const selected = modal.querySelector('input[name="jenis_pengajuan"]:checked');
        if (!selected || !judulInput) return;
        const group = selected.closest('[data-jenis-group]');
        const kategori = group?.dataset.jenisGroup || '';
        judulInput.value = kategori ? ('Pengajuan ' + kategori + ' - ' + selected.value) : selected.value;
    }

    jenisRadios.forEach(function (radio) {
        radio.addEventListener('change', syncJudul);
    });

    if (tanggalMulai && tanggalSelesai) {
        const syncEndDate = function () {
            if (tanggalMulai.value) {
                tanggalSelesai.min = tanggalMulai.value;
                if (tanggalSelesai.value && tanggalSelesai.value < tanggalMulai.value) {
                    tanggalSelesai.value = tanggalMulai.value;
                }
            } else {
                tanggalSelesai.removeAttribute('min');
            }
        };
        tanggalMulai.addEventListener('change', syncEndDate);
        syncEndDate();
    }

    if (fileInput && fileLabel) {
        const defaultLabel = fileLabel.textContent;
        fileInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) {
                fileLabel.textContent = defaultLabel;
                return;
            }
            if (file.size > 20 * 1024 * 1024) {
                window.appToast?.('Ukuran file maksimal 20 MB', 'error');
                this.value = '';
                fileLabel.textContent = defaultLabel;
                return;
            }
            fileLabel.textContent = file.name;
        });
    }

    syncJudul();

    @if ($errors->any())
        window.setModal?.('modalSuratIzin', true);
    @endif
})();
</script>
