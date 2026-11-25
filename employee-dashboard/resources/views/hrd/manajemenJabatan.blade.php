<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Jabatan - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">
    @include('component_hrd.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">
        @include('component.topbar')
        <main class="flex-1 overflow-y-auto p-8 pt-6">
            @include('component.breadcrumbs', [
                'parentText' => 'Manajemen Divisi',
                'parentUrl' => url('/hrd/manajemenDivisi'),
                'currentPage' => 'Manajemen Jabatan',
                'inline' => true,
            ])

            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-semibold text-[#172B36]">Manajemen Jabatan</h1>
                    <p class="mt-1 text-[12px] text-[#4B5563]">Jabatan menentukan divisi dan role akun karyawan.</p>
                </div>
                <button type="button" onclick="bukaDialogJabatan('tambah')"
                        class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-[#146C94] px-4 text-[13px] font-medium text-white hover:bg-[#0F5678]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah jabatan
                </button>
            </div>

            <div class="mb-5 flex flex-wrap items-end gap-3">
                <div class="min-w-[12rem] flex-1 sm:max-w-xs">
                    <label for="filterDivisi" class="mb-1.5 block text-[13px] font-medium text-[#172B36]">Divisi</label>
                    <select id="filterDivisi" onchange="location.href=this.value ? '{{ route('hrd.jabatan.index') }}?divisi='+encodeURIComponent(this.value) : '{{ route('hrd.jabatan.index') }}'"
                            class="h-9 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] outline-none focus:ring-2 focus:ring-[#146C94]">
                        <option value="">Semua divisi</option>
                        @foreach ($divisis as $d)
                            <option value="{{ $d->id_divisi }}" @selected($divisiFilter === $d->id_divisi)>{{ $d->nama_divisi }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @forelse ($jabatans as $divisionId => $daftarJabatan)
                @php($divisi = $daftarJabatan->first()?->divisi)
                <section class="mb-5 overflow-hidden rounded-xl border border-[#E2E8EC] bg-white">
                    <div class="flex items-center justify-between border-b border-[#EEF2F5] px-4 py-3">
                        <div>
                            <h2 class="text-[14px] font-semibold text-[#172B36]">{{ $divisi?->nama_divisi ?? $divisionId }}</h2>
                            <p class="mt-0.5 text-[12px] text-[#4B5563]">{{ $daftarJabatan->count() }} jabatan</p>
                        </div>
                        <button type="button" onclick="bukaDialogJabatan('tambah', null, null, @js((string) $divisionId))"
                                class="text-[12px] font-medium" style="color:#146C94">Tambah jabatan di divisi ini</button>
                    </div>
                    <div class="divide-y divide-[#EEF2F5]">
                        @foreach ($daftarJabatan as $jabatan)
                            <div class="flex flex-wrap items-center gap-3 px-4 py-2.5">
                                <span class="flex-1 text-[13px] font-medium text-[#172B36]">{{ $jabatan->nama }}</span>
                                <span class="rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] text-[#334155]">
                                    {{ strtoupper((string) $jabatan->role?->nama_role) }}
                                </span>
                                <span class="text-[12px] text-[#4B5563]">{{ $jabatan->jumlah_karyawan }} karyawan</span>
                                <span class="rounded-full border px-2 py-0.5 text-[11px] {{ $jabatan->aktif ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-100 text-slate-500' }}">
                                    {{ $jabatan->aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                                <button type="button" onclick="bukaDialogJabatan('ubah', {{ $jabatan->id }}, @js($jabatan->nama), @js((string) $jabatan->division_id), @js((string) $jabatan->role_id_role))"
                                        class="text-[12px] font-medium" style="color:#146C94">Ubah jabatan</button>
                                @if ($jabatan->aktif)
                                    <button type="button" onclick="nonaktifkanJabatan({{ $jabatan->id }}, @js($jabatan->nama))"
                                            class="text-[12px] font-medium text-[#B91C1C]">Nonaktifkan</button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @empty
                <div class="rounded-xl border border-[#E2E8EC] bg-white px-4 py-10 text-center">
                    <p class="text-[13px] text-[#4B5563]">Belum ada jabatan.</p>
                </div>
            @endforelse
        </main>
    </div>

    <div id="dialogJabatan" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-[rgba(15,40,55,.45)]">
        <div class="w-full max-w-[400px] max-w-[92vw] rounded-xl border border-[#E2E8EC] bg-white p-5 shadow-2xl"
             role="dialog" aria-modal="true" aria-labelledby="dialogJabatanTitle">
            <h3 id="dialogJabatanTitle" class="text-[16px] font-semibold text-[#172B36]">Tambah jabatan baru</h3>
            <form id="formJabatan" class="mt-4 space-y-3">
                @csrf
                <input type="hidden" name="_method" id="jabatanMethod" value="POST">
                <input type="hidden" name="jabatan_id" id="jabatanIdEdit" value="">
                <div>
                    <label for="jabatanDivisi" class="mb-1.5 block text-[13px] font-medium text-[#172B36]">Divisi <span class="text-[#DC2626]">*</span></label>
                    <select id="jabatanDivisi" name="division_id" class="h-9 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] outline-none focus:ring-2 focus:ring-[#146C94]">
                        @foreach ($divisis as $d)
                            <option value="{{ $d->id_divisi }}">{{ $d->nama_divisi }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="jabatanNama" class="mb-1.5 block text-[13px] font-medium text-[#172B36]">Nama jabatan <span class="text-[#DC2626]">*</span></label>
                    <input id="jabatanNama" type="text" name="nama" maxlength="80" placeholder="Nama jabatan"
                           class="h-9 w-full rounded-lg border border-[#D5DEE3] px-3 text-[13px] outline-none focus:ring-2 focus:ring-[#146C94] placeholder:text-[#6B7680]">
                </div>
                <div>
                    <label for="jabatanRole" class="mb-1.5 block text-[13px] font-medium text-[#172B36]">Role akun <span class="text-[#DC2626]">*</span></label>
                    <select id="jabatanRole" name="role_id_role" class="h-9 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] outline-none focus:ring-2 focus:ring-[#146C94]">
                        @foreach ($roleOptions as $role)
                            <option value="{{ $role->id_role }}">{{ ucfirst((string) $role->nama_role) }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[12px] text-[#4B5563]">Role mengikuti jabatan, bukan nama atau job level.</p>
                </div>
                <p id="jabatanError" class="hidden text-[12px] text-[#B91C1C]" role="alert"></p>
                <div class="flex justify-end gap-2 pt-1">
                    <button type="button" onclick="tutupDialogJabatan()"
                            class="inline-flex h-9 items-center rounded-lg border border-[#D5DEE3] bg-white px-4 text-[13px] font-medium text-[#172B36]">Batal</button>
                    <button type="submit"
                            class="inline-flex h-9 items-center rounded-lg bg-[#146C94] px-4 text-[13px] font-medium text-white hover:bg-[#0F5678]">Simpan jabatan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function bukaDialogJabatan(mode, id, nama, divisiId, roleId) {
            const dialog = document.getElementById('dialogJabatan');
            document.getElementById('formJabatan').reset();
            document.getElementById('jabatanError').classList.add('hidden');
            document.getElementById('jabatanMethod').value = mode === 'ubah' ? 'PUT' : 'POST';
            document.getElementById('jabatanIdEdit').value = id || '';
            document.getElementById('jabatanNama').value = nama || '';
            if (divisiId) document.getElementById('jabatanDivisi').value = divisiId;
            if (roleId) document.getElementById('jabatanRole').value = roleId;
            const selectDivisi = document.getElementById('jabatanDivisi');
            selectDivisi.disabled = mode === 'ubah';
            selectDivisi.classList.toggle('bg-[#F4F7F9]', mode === 'ubah');
            document.getElementById('dialogJabatanTitle').textContent = mode === 'ubah' ? 'Ubah jabatan' : 'Tambah jabatan baru';
            dialog.classList.remove('hidden');
            dialog.classList.add('flex');
            document.getElementById('jabatanNama').focus();
        }

        function tutupDialogJabatan() {
            const dialog = document.getElementById('dialogJabatan');
            dialog.classList.add('hidden');
            dialog.classList.remove('flex');
        }

        document.getElementById('formJabatan').addEventListener('submit', async function (e) {
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
                    const first = result.errors ? Object.values(result.errors)[0] : null;
                    el.textContent = first ? first[0] : (result.message || 'Gagal menyimpan jabatan.');
                    el.classList.remove('hidden');
                    return;
                }
                window.location.reload();
            } catch (error) {
                alert('Gagal menyimpan jabatan.');
            }
        });

        async function nonaktifkanJabatan(id, nama) {
            if (!confirm('Nonaktifkan jabatan "' + nama + '"?')) return;
            try {
                const response = await fetch('{{ url('/hrd/jabatan') }}/' + id + '/nonaktifkan', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
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
    </script>
</body>

</html>
