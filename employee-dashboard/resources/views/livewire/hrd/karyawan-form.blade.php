@php
    $field = 'w-full h-9 px-3 text-[13px] text-[#172B36] bg-white border border-[#D5DEE3] rounded-lg outline-none transition focus:ring-2 focus:ring-[#146C94] focus:border-[#146C94] placeholder:text-[#6B7680] disabled:bg-[#F4F7F9] disabled:text-[#4B5563]';
    $label = 'block text-[13px] font-medium text-[#172B36] mb-1.5';
    $sectionTitle = 'text-[14px] font-semibold text-[#172B36]';
    $hint = 'mt-1 text-[12px] text-[#4B5563]';
    $errorText = 'mt-1 text-[12px] text-[#B91C1C]';
    $btnSecondary = 'h-9 inline-flex items-center justify-center gap-1.5 px-4 text-[13px] font-medium text-[#172B36] bg-white border border-[#D5DEE3] rounded-lg hover:bg-[#F4F7F9] transition disabled:opacity-50';
    $btnPrimary = 'h-9 inline-flex items-center justify-center gap-1.5 px-4 text-[13px] font-medium text-white bg-[#146C94] rounded-lg hover:bg-[#0F5678] transition disabled:opacity-50';
    $judul = $mode === 'edit' ? 'Edit karyawan' : ($judulTambah ?: 'Tambah karyawan');
    $tombolUtama = $mode === 'edit' ? 'Simpan perubahan' : 'Tambah karyawan';
@endphp

<div>
    {{-- Legacy selectors kept for existing page tests and deep links --}}
    <span class="hidden" data-staff-modal="create"></span>
    <span class="hidden" data-staff-modal="edit"></span>

        <div id="karyawanModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 {{ $show ? '' : 'hidden' }}"
             role="dialog" aria-modal="{{ $show ? 'true' : 'false' }}" aria-labelledby="karyawan-form-title">
            <div class="absolute inset-0 bg-[rgba(15,40,55,.45)]" wire:click="tutup" aria-hidden="true"></div>

            <div id="karyawanModalBox"
                 class="karyawan-modal-box relative flex w-full max-w-[720px] max-w-[92vw] max-h-[90vh] flex-col overflow-hidden rounded-xl bg-white shadow-2xl">
                {{-- Header (fixed) --}}
                <div class="flex shrink-0 items-center justify-between border-b border-[#E2E8EC] px-5 py-3.5">
                    <div class="flex items-center gap-3">
                        <h3 id="karyawan-form-title" class="font-semibold text-lg text-[#172B36]">{{ $judul }}</h3>
                    </div>
                    <button type="button" wire:click="tutup" aria-label="Tutup form karyawan"
                            class="flex h-8 w-8 items-center justify-center rounded-lg text-[#4B5563] hover:bg-[#F4F7F9] hover:text-[#172B36] transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="simpan" class="flex min-h-0 flex-1 flex-col" novalidate>
                    {{-- Body (only this scrolls) --}}
                    <div class="min-h-0 flex-1 space-y-6 overflow-y-auto px-5 py-5" id="karyawanFormBody">
                        @error('nama')
                            <p data-error class="{{ $errorText }}">{{ $message }}</p>
                        @enderror

                        {{-- 1. Data karyawan --}}
                        <section class="space-y-3">
                            <h4 class="{{ $sectionTitle }}">Data karyawan</h4>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label class="{{ $label }}" for="kf-nama">Nama lengkap <span class="text-[#DC2626]">*</span></label>
                                    <input id="kf-nama" type="text" wire:model.blur="nama" maxlength="100"
                                           placeholder="Nama lengkap karyawan" class="{{ $field }}" autocomplete="name">
                                    @error('nama') <p data-error class="{{ $errorText }}">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="{{ $label }}" for="kf-gender">Jenis kelamin <span class="text-[#DC2626]">*</span></label>
                                    <select id="kf-gender" wire:model.live="jenisKelamin" class="{{ $field }}">
                                        @foreach (config('employee.jenis_kelamin') as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                    @error('jenisKelamin') <p data-error class="{{ $errorText }}">{{ $message }}</p> @enderror
                                </div>

                                <div wire:ignore wire:key="dp-lahir-{{ $mode }}-{{ $karyawanId ?? 'new' }}">
                                    @include('components.karyawan-date-picker', [
                                        'name' => 'tanggalLahir',
                                        'id' => 'kf-lahir',
                                        'label' => 'Tanggal lahir',
                                        'optional' => true,
                                        'max' => 'today',
                                    ])
                                </div>
                                @error('tanggalLahir') <p data-error class="{{ $errorText }}">{{ $message }}</p> @enderror

                                <div>
                                    <label class="{{ $label }}" for="kf-telepon">No. telepon <span class="text-[#4B5563]">(opsional)</span></label>
                                    <input id="kf-telepon" type="tel" wire:model.blur="noTelepon" maxlength="20"
                                           placeholder="0812-3456-7890" class="{{ $field }}">
                                    @error('noTelepon') <p data-error class="{{ $errorText }}">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="{{ $label }}" for="kf-email">Email <span class="text-[#DC2626]">*</span></label>
                                    <input id="kf-email" type="email" wire:model.blur="email" maxlength="100"
                                           placeholder="nama@silindo.co.id" class="{{ $field }}" autocomplete="email">
                                    @error('email') <p data-error class="{{ $errorText }}">{{ $message }}</p> @enderror
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="{{ $label }}" for="kf-alamat">Alamat <span class="text-[#4B5563]">(opsional)</span></label>
                                    <textarea id="kf-alamat" wire:model.blur="alamat" rows="2"
                                              placeholder="Jalan, nomor, kota"
                                              class="w-full px-3 py-2 text-[13px] text-[#172B36] bg-white border border-[#D5DEE3] rounded-lg outline-none transition focus:ring-2 focus:ring-[#146C94] focus:border-[#146C94] placeholder:text-[#6B7680] resize-none"></textarea>
                                    @error('alamat') <p data-error class="{{ $errorText }}">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="{{ $label }}" for="kf-rekening">No. rekening <span class="text-[#4B5563]">(opsional)</span></label>
                                    <input id="kf-rekening" type="text" wire:model.blur="noRekening" maxlength="50"
                                           placeholder="Contoh: BCA 1234567890" class="{{ $field }}">
                                    @error('noRekening') <p data-error class="{{ $errorText }}">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="{{ $label }}" for="kf-foto">Foto profil <span class="text-[#4B5563]">(opsional)</span></label>
                                    <div class="flex items-center gap-3">
                                        <input id="kf-foto" type="file" wire:model="foto"
                                               accept="image/jpeg,image/png,image/webp" class="hidden"
                                               aria-label="Pilih foto profil">
                                        <label for="kf-foto"
                                               class="h-9 inline-flex cursor-pointer items-center px-3 text-[13px] font-medium text-[#172B36] bg-white border border-[#D5DEE3] rounded-lg hover:bg-[#F4F7F9] transition">Pilih foto</label>
                                        <span class="text-[12px] text-[#4B5563] truncate" id="kf-foto-nama">
                                            {{ $foto ? $foto->getClientOriginalName() : ($hapusFoto ? 'Belum ada file dipilih' : 'Belum ada file dipilih') }}
                                        </span>
                                        @if ($foto)
                                            <img src="{{ $foto->temporaryUrl() }}" alt="Pratinjau foto profil"
                                                 class="h-9 w-9 shrink-0 rounded-full object-cover border border-[#E2E8EC]">
                                        @elseif ($fotoLama && ! $hapusFoto)
                                            <img src="{{ route('hrd.karyawan.foto', $karyawanId) }}" alt="Foto profil saat ini"
                                                 class="h-9 w-9 shrink-0 rounded-full object-cover border border-[#E2E8EC]">
                                        @endif
                                        @if ($foto || ($fotoLama && ! $hapusFoto))
                                            <button type="button"
                                                    wire:click="{{ $foto ? '$set(\'foto\', null)' : '$set(\'hapusFoto\', true)' }}"
                                                    class="text-[12px] font-medium text-[#146C94] hover:underline">Hapus</button>
                                        @endif
                                    </div>
                                    <p class="{{ $hint }}">JPG, PNG, WEBP · maks. 2 MB</p>
                                    @error('foto') <p data-error class="{{ $errorText }}">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </section>

                        {{-- 2. Kepegawaian --}}
                        <section class="space-y-3">
                            <h4 class="{{ $sectionTitle }}">Kepegawaian</h4>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="{{ $label }}" for="kf-divisi">Divisi <span class="text-[#DC2626]">*</span></label>
                                    @if ($divisiTerkunci)
                                        <select class="{{ $field }} bg-[#F4F7F9]" disabled aria-label="Divisi terkunci">
                                            @foreach ($this->daftarDivisi as $d)
                                                <option value="{{ $d->id_divisi }}" @selected($d->id_divisi === $divisiTerkunci)>{{ $d->nama_divisi }}</option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="divisi_id_divisi" value="{{ $divisiTerkunci }}">
                                    @else
                                        <select name="divisi_id_divisi" required id="kf-divisi" wire:model.live="divisiId" class="{{ $field }}">
                                            <option value="">Pilih divisi</option>
                                            @foreach ($this->daftarDivisi as $d)
                                                <option value="{{ $d->id_divisi }}" @disabled($d->isGrouping())>{{ $d->nama_divisi }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                    @error('divisiId') <p data-error class="{{ $errorText }}">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="{{ $label }}" for="kf-jabatan">Jabatan <span class="text-[#DC2626]">*</span></label>
                                    <div class="flex items-start gap-2">
                                        <div class="flex-1">
                                            <select id="kf-jabatan" wire:model.live="jabatanId" required class="{{ $field }}"
                                                    @disabled($divisiId === '') aria-label="Jabatan">
                                                @if ($divisiId === '')
                                                    <option value="">Pilih divisi dulu</option>
                                                @elseif ($this->jabatanOptions->isEmpty())
                                                    <option value="">Belum ada jabatan</option>
                                                @else
                                                    <option value="">Pilih jabatan</option>
                                                    @foreach ($this->jabatanOptions as $option)
                                                        <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <input type="hidden" name="jabatan" value="{{ $jabatanNama }}">
                                            @if ($divisiId !== '' && $this->jabatanOptions->isEmpty())
                                                <p class="{{ $hint }}">Tambahkan jabatan pertama untuk divisi ini.</p>
                                            @endif
                                            @if ($jabatanResetNote)
                                                <p class="{{ $hint }}">{{ $jabatanResetNote }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    @error('jabatanId') <p data-error class="{{ $errorText }}">{{ $message }}</p> @enderror
                                </div>

                                <div wire:ignore wire:key="dp-masuk-{{ $mode }}-{{ $karyawanId ?? 'new' }}">
                                    @include('components.karyawan-date-picker', [
                                        'name' => 'tanggalMasuk',
                                        'id' => 'kf-masuk',
                                        'label' => 'Tanggal masuk',
                                        'required' => true,
                                    ])
                                </div>
                                @error('tanggalMasuk') <p data-error class="{{ $errorText }}">{{ $message }}</p> @enderror

                                <div wire:ignore wire:key="dp-kontrak-{{ $mode }}-{{ $karyawanId ?? 'new' }}">
                                    @include('components.karyawan-date-picker', [
                                        'name' => 'tanggalAkhirKontrak',
                                        'id' => 'kf-kontrak',
                                        'label' => 'Tanggal akhir kontrak',
                                        'optional' => true,
                                        'minProperty' => 'tanggalMasuk',
                                    ])
                                </div>
                                @error('tanggalAkhirKontrak') <p data-error class="{{ $errorText }}">{{ $message }}</p> @enderror

                                <div>
                                    <label class="{{ $label }}" for="kf-pendidikan">Pendidikan terakhir <span class="text-[#4B5563]">(opsional)</span></label>
                                    <select id="kf-pendidikan" wire:model.live="pendidikan" class="{{ $field }}">
                                        <option value="">Pilih pendidikan</option>
                                        @foreach (\App\Models\Karyawan::pendidikanOptions() as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                    @error('pendidikan') <p data-error class="{{ $errorText }}">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="{{ $label }}" for="kf-joblevel">Job level <span class="text-[#4B5563]">(opsional)</span></label>
                                    <select id="kf-joblevel" wire:model.live="jobLevel" class="{{ $field }}">
                                        <option value="">Pilih job level</option>
                                        @foreach (\App\Models\Karyawan::jobLevelOptions() as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                    @error('jobLevel') <p data-error class="{{ $errorText }}">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="{{ $label }}" for="kf-jobdesk">Keterangan jobdesk <span class="text-[#4B5563]">(opsional)</span></label>
                                    <input id="kf-jobdesk" type="text" wire:model.blur="jobdesk" maxlength="50"
                                           class="{{ $field }}">
                                    <p class="{{ $hint }}">Keterangan pekerjaan staff; tidak memengaruhi hak akses.</p>
                                    @error('jobdesk') <p data-error class="{{ $errorText }}">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="{{ $label }}" for="kf-status">Status</label>
                                    <select id="kf-status" wire:model.live="statusAktif" class="{{ $field }}">
                                        @foreach (\App\Models\Karyawan::STATUS_OPTIONS as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                    @error('statusAktif') <p data-error class="{{ $errorText }}">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </section>

                        {{-- 3. Akun login --}}
                        <section class="space-y-3">
                            <h4 class="{{ $sectionTitle }}">Akun login</h4>
                            @if ($mode === 'create' || ! $hasAccount)
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="{{ $label }}" for="kf-username">Username <span class="text-[#DC2626]">*</span></label>
                                        <input id="kf-username" type="text" wire:model.live.debounce.300ms="username" maxlength="50"
                                               placeholder="budi.santoso" class="{{ $field }}" autocomplete="off">
                                        @if ($usernameStatus === 'tersedia')
                                            <p class="mt-1 flex items-center gap-1 text-[12px]" style="color:#047857">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" /></svg>
                                                Tersedia
                                            </p>
                                        @elseif ($usernameStatus === 'dipakai')
                                            <p data-error class="mt-1 flex items-center gap-1 text-[12px] text-[#B91C1C]">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                                Sudah dipakai
                                            </p>
                                        @endif
                                        <p class="{{ $hint }}">Dipakai untuk masuk ke aplikasi.</p>
                                        @error('username') <p data-error class="{{ $errorText }}">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="{{ $label }}" for="kf-password">Password <span class="text-[#DC2626]">*</span></label>
                                        <div class="relative">
                                            <input id="kf-password" type="{{ $showPassword ? 'text' : 'password' }}"
                                                   wire:model.blur="password" minlength="8" placeholder="Minimal 8 karakter"
                                                   class="{{ $field }} pr-16" autocomplete="new-password">
                                            <button type="button" wire:click="$toggle('showPassword')"
                                                    aria-label="Tampilkan password" title="{{ $showPassword ? 'Sembunyikan password' : 'Tampilkan password' }}"
                                                    class="absolute inset-y-0 right-0 flex w-9 items-center justify-center text-[#4B5563] hover:text-[#146C94]">
                                                @if ($showPassword)
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 4.2A10.8 10.8 0 0 1 12 4c5 0 9 4.5 9 8 0 1.3-.5 2.6-1.3 3.7M6.2 6.2C4.2 7.5 3 9.6 3 12c0 3.5 4 8 9 8 1.5 0 2.9-.4 4.1-1" /></svg>
                                                @else
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-7 9.5-7 9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z" /><circle cx="12" cy="12" r="3" /></svg>
                                                @endif
                                            </button>
                                        </div>
                                        <div class="mt-1 flex items-center justify-between gap-2">
                                            <p class="text-[12px] text-[#4B5563]">Minimal 8 karakter.</p>
                                            <button type="button" wire:click="generatePassword"
                                                    class="text-[12px] font-medium" style="color:#146C94">Buat otomatis</button>
                                        </div>
                                        @error('password') <p data-error class="{{ $errorText }}">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            @else
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="{{ $label }}" for="kf-username">Username</label>
                                        <div class="relative">
                                            @if ($this->canChangeUsername)
                                                <input id="kf-username" type="text" wire:model.live.debounce.300ms="username" maxlength="50"
                                                       class="{{ $field }} pl-9" aria-label="Username karyawan">
                                            @else
                                                <input id="kf-username" type="text" value="{{ $username }}" readonly
                                                       class="{{ $field }} pl-9 bg-[#F4F7F9]" aria-label="Username karyawan (terkunci)">
                                            @endif
                                            <svg class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-[#4B5563]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2" /><path d="M8 10V7a4 4 0 0 1 8 0v3" /></svg>
                                        </div>
                                        @if ($usernameStatus === 'tersedia' && $this->canChangeUsername)
                                            <p class="mt-1 text-[12px]" style="color:#047857">Tersedia</p>
                                        @elseif ($usernameStatus === 'dipakai' && $this->canChangeUsername)
                                            <p data-error class="mt-1 text-[12px] text-[#B91C1C]">Sudah dipakai</p>
                                        @endif
                                        <p class="{{ $hint }}">Hanya admin yang dapat mengubah username.</p>
                                        @error('username') <p data-error class="{{ $errorText }}">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <span class="{{ $label }}">Password</span>
                                        <button type="button" wire:click="konfirmasiResetPassword"
                                                class="inline-flex h-9 items-center gap-2 rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] font-medium text-[#172B36] transition hover:bg-[#F4F7F9]">
                                            <svg class="h-4 w-4" style="color:#146C94" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><circle cx="8" cy="15" r="4" /><path stroke-linecap="round" d="m10.8 12.2 8-8m-3 3 2 2m-4.5 1.5 2 2" /></svg>
                                            Atur ulang password
                                        </button>
                                        <p class="{{ $hint }}">Password lama tidak ditampilkan. Karyawan diminta menggantinya saat login berikutnya.</p>
                                    </div>
                                </div>
                            @endif

                            <label class="mt-3 flex items-center gap-2 text-[13px] text-[#172B36]">
                                <input type="checkbox" wire:model="mustChangePassword"
                                       class="h-4 w-4 rounded border-[#D5DEE3] text-[#146C94] focus:ring-2 focus:ring-[#146C94]">
                                Wajib ganti password saat login pertama
                            </label>

                            @if ($generatedPassword && $mode === 'create')
                                <div class="rounded-lg border border-[#E2E8EC] bg-[#F4F7F9] p-3">
                                    <p class="text-[12px] text-[#4B5563]">Password yang dibuat (tampil sekali):</p>
                                    <div class="mt-1 flex items-center gap-2">
                                        <code id="kf-generated-password" class="flex-1 truncate rounded border border-[#D5DEE3] bg-white px-2 py-1.5 text-[12px] text-[#172B36]">{{ $generatedPassword }}</code>
                                        <button type="button" id="kf-salin-password"
                                                class="h-8 rounded-lg border border-[#D5DEE3] bg-white px-3 text-[12px] font-medium text-[#146C94]">Salin</button>
                                    </div>
                                </div>
                            @endif
                        </section>

                        {{-- 4. Lampiran --}}
                        <section class="space-y-3">
                            <button type="button" wire:click="$toggle('lampiranOpen')"
                                    aria-expanded="{{ $lampiranOpen ? 'true' : 'false' }}"
                                    class="flex w-full items-center gap-2 text-left">
                                <span class="{{ $sectionTitle }}">Lampiran</span>
                                <span class="text-[12px] text-[#4B5563]">(opsional · PDF/JPG/PNG, maks. 5 MB per file)</span>
                                <svg class="ml-auto h-4 w-4 text-[#4B5563] transition-transform {{ $lampiranOpen ? 'rotate-180' : '' }}"
                                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" /></svg>
                            </button>

                            @if ($lampiranOpen)
                                <div class="divide-y divide-[#EEF2F5] rounded-lg border border-[#E2E8EC]">
                                    @foreach ($this->dokumenJenis as $jenis => $namaDokumen)
                                        @php
                                            $lama = $lampiranLama[$jenis] ?? null;
                                            $baru = $lampiran[$jenis] ?? null;
                                            $dihapus = ! empty($hapusLampiran[$jenis]);
                                        @endphp
                                        <div class="flex min-h-10 items-center gap-3 px-3 py-2">
                                            <span class="flex-1 text-[13px] font-medium text-[#172B36]">{{ $namaDokumen }}</span>

                                            <div class="flex flex-1 items-center gap-1.5 text-[12px]">
                                                @if ($baru)
                                                    <svg class="h-3.5 w-3.5 shrink-0" style="color:#146C94" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" /><path d="M14 2v6h6" /></svg>
                                                    <span class="truncate font-medium" style="color:#146C94">{{ $baru->getClientOriginalName() }}</span>
                                                    <span class="shrink-0 text-[#4B5563]">{{ round($baru->getSize() / 1024) }} KB</span>
                                                @elseif ($lama && ! $dihapus)
                                                    <svg class="h-3.5 w-3.5 shrink-0" style="color:#146C94" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" /><path d="M14 2v6h6" /></svg>
                                                    <span class="truncate font-medium" style="color:#146C94">{{ $lama['nama'] }}</span>
                                                    <span class="shrink-0 text-[#4B5563]">{{ $lama['ukuran'] }}</span>
                                                    @if ($mode === 'edit')
                                                        <a href="{{ route('hrd.lampiran.download', $lama['id']) }}"
                                                           class="shrink-0 font-medium" style="color:#146C94">Lihat</a>
                                                    @endif
                                                @else
                                                    <span class="text-[#4B5563]">Belum ada file</span>
                                                @endif
                                            </div>

                                            <div class="shrink-0">
                                                @if ($baru || ($lama && ! $dihapus))
                                                    <button type="button"
                                                            wire:click="{{ $baru ? '$set(\'lampiran.' . $jenis . '\', null)' : '$set(\'hapusLampiran.' . $jenis . '\', true)' }}"
                                                            class="text-[12px] font-medium text-[#146C94] hover:underline">Hapus</button>
                                                @else
                                                    <label for="kf-lampiran-{{ $jenis }}"
                                                           class="inline-flex h-8 cursor-pointer items-center rounded-lg border border-[#D5DEE3] bg-white px-3 text-[12px] font-medium text-[#172B36] hover:bg-[#F4F7F9]">Pilih file</label>
                                                @endif
                                                <input id="kf-lampiran-{{ $jenis }}" type="file" wire:model="lampiran.{{ $jenis }}"
                                                       accept="application/pdf,image/jpeg,image/png" class="hidden"
                                                       aria-label="Pilih file {{ $namaDokumen }}">
                                            </div>
                                            @error('lampiran.'.$jenis) <p data-error class="{{ $errorText }}">{{ $message }}</p> @enderror
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    </div>

                    {{-- Footer (fixed) --}}
                    <div class="flex shrink-0 items-center justify-end gap-2 border-t border-[#E2E8EC] bg-white px-5 py-3.5">
                        <button type="button" wire:click="tutup" wire:loading.attr="disabled"
                                class="{{ $btnSecondary }}">Batal</button>
                        <button type="submit" wire:loading.attr="disabled"
                                class="{{ $btnPrimary }}">
                            <span wire:loading.remove>{{ $tombolUtama }}</span>
                            <span wire:loading>Menyimpan…</span>
                        </button>
                    </div>
                </form>

                {{-- Dialog: konfirmasi atur ulang password --}}
                @if ($showResetDialog)
                    <div class="absolute inset-0 z-20 flex items-center justify-center p-4 bg-[rgba(15,40,55,.45)]">
                        <div class="w-full max-w-[400px] max-w-[92vw] rounded-xl border border-[#E2E8EC] bg-white p-5 shadow-2xl"
                             role="dialog" aria-modal="true" aria-labelledby="reset-dialog-title">
                            <h3 id="reset-dialog-title" class="text-[16px] font-semibold text-[#172B36]">Atur ulang password untuk {{ $nama }}?</h3>
                            <p class="mt-2 text-[12px] text-[#4B5563]">Karyawan akan diminta mengganti password pada login berikutnya. Sesi yang sedang aktif akan berakhir.</p>
                            <div class="mt-5 flex justify-end gap-2">
                                <button type="button" id="kf-reset-batal" wire:click="$set('showResetDialog', false)" class="{{ $btnSecondary }}">Batal</button>
                                <button type="button" wire:click="resetPassword" wire:loading.attr="disabled" class="{{ $btnPrimary }}">Atur ulang</button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    {{-- Toast --}}
    <div id="karyawanToast" aria-live="polite" class="pointer-events-none fixed bottom-5 right-5 z-[60] hidden">
        <div class="flex items-center gap-2 rounded-lg border border-[#E2E8EC] bg-white px-4 py-3 shadow-lg">
            <svg class="h-4 w-4" style="color:#047857" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" /></svg>
            <span id="karyawanToastPesan" class="text-[13px] font-medium text-[#172B36]"></span>
        </div>
    </div>
</div>

@script
<script>
    (() => {
        let kfDirty = false;
        let kfTrigger = null;
        let kfToastTimer = null;

        const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const HARI = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

        function kfTanggal(v) {
            if (!v || !/^\d{4}-\d{2}-\d{2}/.test(v)) return null;
            const [y, m, d] = v.slice(0, 10).split('-').map(Number);
            return new Date(y, m - 1, d);
        }

        function kfFormat(date) {
            return date.getDate() + ' ' + BULAN[date.getMonth()] + ' ' + date.getFullYear();
        }

        function kfIso(date) {
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return date.getFullYear() + '-' + m + '-' + d;
        }

        function kfHariPertamaSenin(date) {
            return (date.getDay() + 6) % 7;
        }

        function kfRenderCalendar(picker) {
            const view = picker._kfView || new Date();
            picker._kfView = view;
            const year = view.getFullYear();
            const month = view.getMonth();
            picker.querySelector('[data-kf-bulan]').textContent = BULAN[month];
            const tahunSelect = picker.querySelector('[data-kf-tahun]');
            if (tahunSelect) tahunSelect.value = String(year);

            const first = new Date(year, month, 1);
            const offset = kfHariPertamaSenin(first);
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const prevDays = new Date(year, month, 0).getDate();
            const grid = picker.querySelector('[data-kf-grid]');
            grid.innerHTML = '';

            const maxAttr = picker.dataset.max || '';
            let maxDate = null;
            if (maxAttr === 'today') maxDate = new Date();
            else if (maxAttr) maxDate = kfTanggal(maxAttr);
            if (maxDate) maxDate.setHours(23, 59, 59, 999);

            const minProp = picker.dataset.minProperty;
            let minDate = null;
            if (minProp) {
                const other = document.querySelector('[data-date-picker][data-name="' + minProp + '"] [data-date-value]');
                minDate = other && other.value ? kfTanggal(other.value) : null;
                if (minDate) minDate.setHours(0, 0, 0, 0);
            }

            const selected = kfTanggal(picker.querySelector('[data-date-value]').value);
            const cells = [];
            for (let i = 0; i < offset; i++) {
                cells.push({ day: prevDays - offset + i + 1, muted: true, date: new Date(year, month - 1, prevDays - offset + i + 1) });
            }
            for (let d = 1; d <= daysInMonth; d++) {
                cells.push({ day: d, muted: false, date: new Date(year, month, d) });
            }
            while (cells.length % 7 !== 0 && cells.length < 42) {
                const d = cells.length - (offset + daysInMonth) + 1;
                cells.push({ day: d, muted: true, date: new Date(year, month + 1, d) });
            }

            cells.forEach((cell) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = cell.day;
                btn.className = 'h-8 w-8 rounded text-[12px] hover:bg-[#E3F1F7] text-[#172B36]';
                if (cell.muted) btn.classList.add('text-[#4B5563]', 'opacity-50');
                if (selected && kfIso(cell.date) === kfIso(selected)) {
                    btn.classList.remove('hover:bg-[#E3F1F7]');
                    btn.classList.add('bg-[#146C94]', 'text-white');
                }
                if ((maxDate && cell.date > maxDate) || (minDate && cell.date < minDate)) {
                    btn.disabled = true;
                    btn.classList.add('opacity-30', 'cursor-not-allowed');
                }
                btn.addEventListener('click', () => {
                    const value = picker.querySelector('[data-date-value]');
                    value.value = kfIso(cell.date);
                    value.dispatchEvent(new Event('input', { bubbles: true }));
                    value.dispatchEvent(new Event('change', { bubbles: true }));
                    picker.querySelector('[data-date-display]').value = kfFormat(cell.date);
                    picker.querySelector('[data-kf-popover]').classList.add('hidden');
                    picker.querySelector('[data-date-display]').dispatchEvent(new Event('blur', { bubbles: true }));
                    kfDirty = true;
                });
                grid.appendChild(btn);
            });
        }

        function kfInitDatePickers() {
            document.querySelectorAll('[data-date-picker]').forEach((picker) => {
                if (picker._kfReady) return;
                picker._kfReady = true;
                const value = picker.querySelector('[data-date-value]');
                const display = picker.querySelector('[data-date-display]');
                const popover = picker.querySelector('[data-kf-popover]');
                const date = kfTanggal(value.value);
                if (date) {
                    display.value = kfFormat(date);
                    picker._kfView = new Date(date.getFullYear(), date.getMonth(), 1);
                } else {
                    // Always give an empty picker a view month, otherwise the
                    // prev/next handlers cannot navigate to previous years.
                    const start = picker.dataset.name === 'tanggalLahir'
                        ? new Date(1990, 0, 1)
                        : new Date();
                    picker._kfView = new Date(start.getFullYear(), start.getMonth(), 1);
                }

                const bukaPicker = (e) => {
                    if (e) e.stopPropagation();
                    const willOpen = popover.classList.contains('hidden');
                    document.querySelectorAll('[data-kf-popover]').forEach((p) => p.classList.add('hidden'));
                    if (!willOpen) return;

                    // The form body scrolls, so flip the calendar upwards when
                    // there is not enough room below the field.
                    const spaceBawah = window.innerHeight - display.getBoundingClientRect().bottom;
                    const keAtas = spaceBawah < 300;
                    popover.classList.toggle('top-full', !keAtas);
                    popover.classList.toggle('mt-1', !keAtas);
                    popover.classList.toggle('bottom-full', keAtas);
                    popover.classList.toggle('mb-1', keAtas);

                    kfRenderCalendar(picker);
                    popover.classList.remove('hidden');
                };
                const tahunSelect = picker.querySelector('[data-kf-tahun]');
                if (tahunSelect) {
                    const tahunSekarang = new Date().getFullYear();
                    for (let tahun = tahunSekarang + 5; tahun >= tahunSekarang - 120; tahun--) {
                        const option = document.createElement('option');
                        option.value = String(tahun);
                        option.textContent = String(tahun);
                        tahunSelect.appendChild(option);
                    }
                    tahunSelect.value = String(picker._kfView.getFullYear());
                    tahunSelect.addEventListener('change', () => {
                        picker._kfView = new Date(Number(tahunSelect.value), picker._kfView.getMonth(), 1);
                        kfRenderCalendar(picker);
                    });
                }

                picker.querySelector('[data-kf-toggle]').addEventListener('click', bukaPicker);
                display.addEventListener('click', bukaPicker);
                picker.querySelector('[data-kf-prev]').addEventListener('click', () => {
                    const view = picker._kfView || new Date();
                    picker._kfView = new Date(view.getFullYear(), view.getMonth() - 1, 1);
                    kfRenderCalendar(picker);
                });
                picker.querySelector('[data-kf-next]').addEventListener('click', () => {
                    const view = picker._kfView || new Date();
                    picker._kfView = new Date(view.getFullYear(), view.getMonth() + 1, 1);
                    kfRenderCalendar(picker);
                });
            });
        }

        function kfTutupDenganKonfirmasi() {
            if (kfDirty && !confirm('Perubahan belum disimpan. Tutup form karyawan?')) return;
            $wire.tutup();
            kfDirty = false;
        }

        function kfFocusPertama() {
            const box = document.getElementById('karyawanModalBox');
            if (!box) return;
            const target = box.querySelector('#kf-nama') || box.querySelector('input:not([type=hidden]):not([disabled]), select:not([disabled]), button:not([disabled])');
            if (target) target.focus();
        }

        function kfTrapFocus(e) {
            if (e.key !== 'Tab') return;
            const box = document.getElementById('karyawanModalBox');
            if (!box) return;
            const focusables = Array.from(box.querySelectorAll('a[href], button:not([disabled]), input:not([type=hidden]):not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'))
                .filter((el) => el.offsetParent !== null);
            if (!focusables.length) return;
            const first = focusables[0];
            const last = focusables[focusables.length - 1];
            if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
            else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        }

        function kfToast(pesan) {
            const el = document.getElementById('karyawanToast');
            const label = document.getElementById('karyawanToastPesan');
            if (!el || !label) return;
            label.textContent = pesan;
            el.classList.remove('hidden');
            clearTimeout(kfToastTimer);
            kfToastTimer = setTimeout(() => el.classList.add('hidden'), 3500);
        }

        // Livewire dispatches `livewire:init` before script blocks run, so
        // registering handlers inside that event would never fire.
        Livewire.on('buka-karyawan-form', () => {
            kfTrigger = window.__karyawanTrigger || document.activeElement;
            kfDirty = false;
            requestAnimationFrame(kfInitDatePickers);
        });

        Livewire.on('karyawan-modal-opened', () => {
            requestAnimationFrame(() => { kfInitDatePickers(); kfFocusPertama(); });
        });

        Livewire.on('karyawan-modal-closed', () => {
            kfDirty = false;
            if (kfTrigger && document.contains(kfTrigger)) kfTrigger.focus();
            kfTrigger = null;
        });

        Livewire.on('karyawan-validation-error', () => {
            requestAnimationFrame(() => {
                const first = document.querySelector('#karyawanFormBody [data-error]');
                if (first) {
                    first.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    const field = first.parentElement.querySelector('input:not([type=hidden]), select, textarea');
                    if (field) field.focus({ preventScroll: true });
                }
            });
        });

        Livewire.on('toast', ({ message }) => kfToast(message || 'Perubahan disimpan'));

        Livewire.on('karyawan-password-diatur', () => {
            kfFocusPertama();
        });

        document.addEventListener('input', (e) => {
            if (e.target.closest && e.target.closest('#karyawanFormBody')) kfDirty = true;
        });
        document.addEventListener('change', (e) => {
            if (e.target.closest && e.target.closest('#karyawanFormBody')) kfDirty = true;
        });

        document.addEventListener('keydown', (e) => {
            if (!document.getElementById('karyawanModal')) return;
            const resetDialog = document.getElementById('kf-reset-batal');
            const jabatanDialog = document.getElementById('kf-jabatan-dialog');

            kfTrapFocus(e);
            if (e.key !== 'Escape') return;
            e.preventDefault();
            if (resetDialog) { $wire.set('showResetDialog', false); return; }
            if (jabatanDialog) { $wire.tutupJabatanDialog(); return; }
            kfTutupDenganKonfirmasi();
        });

        document.addEventListener('click', async (e) => {
            const salin = e.target.closest && e.target.closest('#kf-salin-password');
            if (salin) {
                const teks = document.getElementById('kf-generated-password');
                if (teks) {
                    try { await navigator.clipboard.writeText(teks.textContent.trim()); } catch (err) {}
                    salin.textContent = 'Tersalin';
                }
            }
            const resetBatal = e.target.closest && e.target.closest('#kf-reset-batal');
            if (resetBatal) requestAnimationFrame(() => kfFocusPertama());
        });

        Livewire.on('karyawan-reset-dialog-opened', () => {
            requestAnimationFrame(() => {
                const batal = document.getElementById('kf-reset-batal');
                if (batal) batal.focus();
            });
        });

        // Client-side type/size checks. The server repeats these with real
        // MIME detection and is the authoritative check.
        document.addEventListener('change', (e) => {
            const input = e.target;
            if (!input || input.type !== 'file' || !input.closest('#karyawanModal')) return;

            const foto = input.id === 'kf-foto';
            const types = foto
                ? ['image/jpeg', 'image/png', 'image/webp']
                : ['application/pdf', 'image/jpeg', 'image/png'];
            const maxBytes = foto ? 2 * 1024 * 1024 : 5 * 1024 * 1024;
            const file = input.files && input.files[0];
            if (!file) return;

            let pesan = '';
            if (!types.includes(file.type)) {
                pesan = foto ? 'Foto harus JPG, PNG, atau WEBP.' : 'Dokumen harus PDF, JPG, atau PNG.';
            } else if (file.size > maxBytes) {
                pesan = foto ? 'Ukuran foto maksimal 2 MB.' : 'Ukuran dokumen maksimal 5 MB.';
            }

            if (pesan) {
                input.value = '';
                kfToast(pesan);
            }
        });

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => requestAnimationFrame(kfInitDatePickers));
        } else {
            requestAnimationFrame(kfInitDatePickers);
        }

        window.kfTutupDenganKonfirmasi = kfTutupDenganKonfirmasi;
    })();
</script>
@endscript
