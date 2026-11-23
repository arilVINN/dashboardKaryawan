<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Divisi - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">

    @include('component_hrd.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        @include('component.topbar')
        @include('component.breadcrumbs', ['parentText' => 'Manajemen Divisi', 'parentUrl' => url('/hrd/manajemenDivisi'), 'currentPage' => $divisi->nama_divisi])

        <div class="flex flex-col gap-3 w-full pt-9 ml-8">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">{{ $divisi->nama_divisi }}</h2>
                    <p class="text-sm text-slate-500">Kode divisi: {{ $divisi->kode_divisi }}</p>
                </div>
            </div>
        </div>

        <main class="flex overflow-y-auto p-8 pt-2 items-center gap-5 drop-shadow-2sm">
            <div class="w-20 h-20 rounded-full bg-cyan-100 text-cyan-700 flex items-center justify-center font-bold text-2xl shrink-0">
                @if ($ketuaDivisi?->foto)
                    <img src="{{ route('hrd.karyawan.foto', $ketuaDivisi->id_karyawan) }}"
                        alt="Foto profil {{ $ketuaDivisi->nama }}"
                        class="h-full w-full rounded-full object-cover">
                @else
                    {{ $ketuaDivisi ? strtoupper(substr($ketuaDivisi->nama, 0, 1)) : '?' }}
                @endif
            </div>
            <div class="items-center">
                <h3 class="font-medium">{{ $ketuaDivisi->nama ?? 'Belum ditentukan' }}</h3>
                <h4 class="font-light">Ketua divisi</h4>
            </div>

        </main>

        @include('component_hrd.tabelAnggotaDivisi', ['bisaTambahAnggota' => $bisaTambahAnggota])

        {{-- Jabatan --}}
        @php($bisaKelolaJabatan = \App\Support\EmployeeAccess::canManage(auth()->user()))
        <div class="mx-8 mb-6 rounded-xl border border-[#E2E8EC] bg-white">
            <div class="flex items-center justify-between border-b border-[#EEF2F5] px-4 py-3">
                <h3 class="text-[14px] font-semibold text-[#172B36]">Jabatan</h3>
                @if ($bisaKelolaJabatan)
                    <button type="button" onclick="bukaDialogJabatan('tambah')"
                            class="inline-flex h-8 items-center gap-1.5 rounded-lg bg-[#146C94] px-3 text-[12px] font-medium text-white hover:bg-[#0F5678]">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        Tambah jabatan
                    </button>
                @endif
            </div>

            @if ($daftarJabatan->isEmpty())
                <p class="px-4 py-4 text-[13px] text-[#4B5563]">Belum ada jabatan di divisi ini.</p>
            @else
                <div class="divide-y divide-[#EEF2F5]">
                    @foreach ($daftarJabatan as $jabatan)
                        <div class="flex min-h-10 items-center gap-3 px-4 py-2">
                            <span class="flex-1 text-[13px] font-medium text-[#172B36]">{{ $jabatan->nama }}</span>
                            <span class="text-[12px] text-[#4B5563]">{{ $jabatan->jumlah_karyawan }} karyawan</span>
                            <span class="rounded-full border px-2 py-0.5 text-[11px] {{ $jabatan->aktif ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-100 text-slate-500' }}">
                                {{ $jabatan->aktif ? 'Aktif' : 'Nonaktif' }}
                            </span>
                            @if ($bisaKelolaJabatan)
                                <button type="button" onclick="bukaDialogJabatan('ubah', {{ $jabatan->id }}, @js($jabatan->nama))"
                                        class="text-[12px] font-medium" style="color:#146C94">Ubah nama</button>
                                @if ($jabatan->aktif)
                                    <button type="button" onclick="nonaktifkanJabatan({{ $jabatan->id }}, @js($jabatan->nama))"
                                            class="text-[12px] font-medium text-[#B91C1C]">Nonaktifkan</button>
                                @endif
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($bisaKelolaJabatan)
            <div id="dialogJabatanDivisi" class="modal-overlay-in fixed inset-0 z-50 hidden items-center justify-center p-4 bg-[rgba(15,40,55,.45)]">
                <div class="modal-panel-in w-full max-w-[400px] max-w-[92vw] rounded-xl border border-[#E2E8EC] bg-white p-5 shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="dialogJabatanDivisiTitle">
                    <h3 id="dialogJabatanDivisiTitle" class="text-[16px] font-semibold text-[#172B36]">Tambah jabatan baru</h3>
                    <form id="formJabatanDivisi" class="mt-4 space-y-3">
                        @csrf
                        <input type="hidden" name="division_id" value="{{ $divisi->id_divisi }}">
                        <input type="hidden" name="_method" id="jabatanMethod" value="POST">
                        <input type="hidden" name="jabatan_id" id="jabatanIdEdit" value="">
                        <div>
                            <label class="block text-[13px] font-medium text-[#172B36] mb-1.5">Divisi</label>
                            <input type="text" value="{{ $divisi->nama_divisi }}" readonly
                                   class="w-full h-9 pl-3 pr-3 text-[13px] border border-[#D5DEE3] rounded-lg bg-[#F4F7F9] outline-none">
                        </div>
                        <div>
                            <label for="jabatanNamaDivisi" class="block text-[13px] font-medium text-[#172B36] mb-1.5">Nama jabatan <span class="text-[#DC2626]">*</span></label>
                            <input id="jabatanNamaDivisi" type="text" name="nama" maxlength="80" placeholder="Nama jabatan"
                                   class="w-full h-9 px-3 text-[13px] border border-[#D5DEE3] rounded-lg outline-none focus:ring-2 focus:ring-[#146C94] placeholder:text-[#6B7680]">
                            <p id="jabatanError" class="mt-1 hidden text-[12px] text-[#B91C1C]"></p>
                        </div>
                        <div class="flex justify-end gap-2 pt-1">
                            <button type="button" onclick="tutupDialogJabatan()" class="inline-flex h-9 items-center rounded-lg border border-[#D5DEE3] bg-white px-4 text-[13px] font-medium text-[#172B36]">Batal</button>
                            <button type="submit" class="inline-flex h-9 items-center rounded-lg bg-[#146C94] px-4 text-[13px] font-medium text-white hover:bg-[#0F5678]">Simpan jabatan</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                function bukaDialogJabatan(mode, id, nama) {
                    const dialog = document.getElementById('dialogJabatanDivisi');
                    document.getElementById('formJabatanDivisi').reset();
                    document.getElementById('jabatanError').classList.add('hidden');
                    document.getElementById('jabatanMethod').value = mode === 'ubah' ? 'PUT' : 'POST';
                    document.getElementById('jabatanIdEdit').value = id || '';
                    document.getElementById('jabatanNamaDivisi').value = nama || '';
                    document.getElementById('dialogJabatanDivisiTitle').textContent = mode === 'ubah' ? 'Ubah nama jabatan' : 'Tambah jabatan baru';
                    dialog.classList.remove('hidden');
                    dialog.classList.add('flex');
                    document.getElementById('jabatanNamaDivisi').focus();
                }

                function tutupDialogJabatan() {
                    const dialog = document.getElementById('dialogJabatanDivisi');
                    dialog.classList.add('hidden');
                    dialog.classList.remove('flex');
                }

                async function kirimJabatan(e) {
                    e.preventDefault();
                    const form = e.currentTarget;
                    const method = document.getElementById('jabatanMethod').value;
                    const id = document.getElementById('jabatanIdEdit').value;
                    const url = method === 'PUT' ? '{{ url('/hrd/jabatan') }}/' + id : '{{ url('/hrd/jabatan') }}';
                    const body = new FormData(form);
                    body.delete('_method');
                    body.delete('jabatan_id');
                    if (method === 'PUT') body.append('_method', 'PUT');

                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': form.querySelector('[name=_token]').value },
                            body,
                        });
                        const result = await response.json();
                        if (!response.ok) {
                            const el = document.getElementById('jabatanError');
                            el.textContent = (result.errors && result.errors.nama) ? result.errors.nama[0] : (result.message || 'Gagal menyimpan jabatan.');
                            el.classList.remove('hidden');
                            return;
                        }
                        window.location.reload();
                    } catch (error) {
                        alert('Gagal menyimpan jabatan.');
                    }
                }

                async function nonaktifkanJabatan(id, nama) {
                    if (!confirm('Nonaktifkan jabatan "' + nama + '"?')) return;
                    try {
                        const response = await fetch('{{ url('/hrd/jabatan') }}/' + id + '/nonaktifkan', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            body: new URLSearchParams({ _method: 'PATCH' }),
                        });
                        if (!response.ok) {
                            const result = await response.json();
                            alert(result.message || 'Gagal menonaktifkan jabatan.');
                            return;
                        }
                        window.location.reload();
                    } catch (error) {
                        alert('Gagal menonaktifkan jabatan.');
                    }
                }

                document.getElementById('formJabatanDivisi').addEventListener('submit', kirimJabatan);
            </script>
        @endif

        @include('component_hrd.karyawanModals', [
            'daftarDivisi' => $daftarDivisi,
            'divisiTerkunci' => $divisi->id_divisi,
            'judulTambah' => 'Tambah Karyawan ke '.$divisi->nama_divisi,
        ])


    </div>

</body>

</html>
