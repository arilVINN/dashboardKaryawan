<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $profileTitle = match ($profileRole) {
            'hrd' => 'HRD',
            'kadiv' => 'Kadiv',
            default => 'Saya',
        };
    @endphp
    <title>Profil {{ $profileTitle }} - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="flex h-screen overflow-hidden bg-[#F4F7F9] text-slate-800">
    @if ($profileRole === 'kadiv')
        @include('component_kadiv.sidebar')
    @elseif ($profileRole === 'hrd')
        @include('component_hrd.sidebar')
    @else
        @include('component.sidebar')
    @endif

    <div class="flex h-screen min-w-0 flex-1 flex-col overflow-hidden">
        @include('component.topbar')

        <main class="flex-1 overflow-y-auto px-6 pb-8 pt-6 sm:px-8">
            @include('component.breadcrumbs', [
                'parentText' => 'Dashboard',
                'parentUrl' => $profileRole === 'staff' ? url('/') : url('/'.$profileRole.'/dashboard'),
                'currentPage' => 'Profil Saya',
                'inline' => true,
            ])

            <div id="profile-endpoints" class="hidden"
                 data-profile="{{ '/api/'.$profileRole.'/profile' }}"
                 data-foto="{{ '/api/'.$profileRole.'/profile/foto' }}"
                 data-password="{{ '/api/'.$profileRole.'/profile/password' }}"></div>

            {{-- Header profil (tanpa card) --}}
            <header class="mb-6 flex flex-wrap items-center gap-4 sm:gap-5">
                <div class="relative shrink-0">
                    <div id="prof-inisial"
                         class="flex h-24 w-24 items-center justify-center rounded-full bg-[#AFD3E2] text-[28px] font-semibold text-[#0B4A66]">U</div>
                    <img id="prof-foto" alt="Foto profil" class="hidden h-24 w-24 shrink-0 rounded-full object-cover"
                         onerror="this.classList.add('hidden'); document.getElementById('prof-inisial').classList.remove('hidden');">
                    <input type="file" id="input-foto" accept="image/jpeg,image/png,image/webp" class="hidden">
                    <button type="button" id="btn-ubah-foto" aria-label="Ubah foto"
                            class="absolute -bottom-1 -right-1 flex h-8 w-8 items-center justify-center rounded-full border border-[#D5DEE3] bg-white text-[#146C94] shadow-sm transition hover:bg-[#F4F7F9] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                        </svg>
                    </button>
                </div>

                <div class="min-w-0">
                    <h1 id="hdr-nama" class="text-2xl font-semibold leading-tight text-[#172B36]">Memuat...</h1>
                    <p id="hdr-jabatan-divisi" class="mt-1 text-sm text-[#4B5563]">Memuat...</p>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span id="hdr-status-aktif-wrap" class="hidden">
                            <x-status-badge id="prof-status-aktif" status="aktif" label="Aktif" dot size="md" />
                        </span>
                        <span id="hdr-status-karyawan"
                              class="inline-flex h-[22px] items-center rounded-md border border-[#E2E8F0] bg-[#F1F5F9] px-2 text-[12px] font-medium text-[#475569]">Belum diisi</span>
                    </div>
                </div>
            </header>

            <div class="grid grid-cols-1 items-start gap-6 min-[900px]:grid-cols-2">
                <div class="space-y-6">
                    {{-- Informasi pribadi --}}
                    <div id="view-info" class="rounded-xl border border-[#E2E8EC] bg-white p-4">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <h2 class="text-[15px] font-semibold text-[#172B36]">Informasi pribadi</h2>
                            <button type="button" id="btn-edit-info"
                                    class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-[#D5DEE3] bg-white px-3 text-[12px] font-semibold text-[#172B36] transition hover:bg-[#F4F7F9] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                                Edit
                            </button>
                        </div>

                        <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-[12px] text-[#4B5563]">Email</dt>
                                <dd id="prof-email" class="mt-1 break-words text-[13px] font-medium text-[#4B5563]">Belum diisi</dd>
                            </div>
                            <div>
                                <dt class="text-[12px] text-[#4B5563]">No. telepon</dt>
                                <dd id="prof-telepon" class="mt-1 break-words text-[13px] font-medium text-[#4B5563]">Belum diisi</dd>
                            </div>
                            <div>
                                <dt class="text-[12px] text-[#4B5563]">Jenis kelamin</dt>
                                <dd id="prof-gender" class="mt-1 text-[13px] font-medium text-[#4B5563]">Belum diisi</dd>
                            </div>
                            <div>
                                <dt class="text-[12px] text-[#4B5563]">Tanggal lahir</dt>
                                <dd id="prof-tgl-lahir" class="mt-1 text-[13px] font-medium text-[#4B5563]">Belum diisi</dd>
                            </div>
                            <div>
                                <dt class="text-[12px] text-[#4B5563]">Pendidikan terakhir</dt>
                                <dd id="prof-pendidikan" class="mt-1 text-[13px] font-medium text-[#4B5563]">Belum diisi</dd>
                            </div>
                            <div>
                                <dt class="text-[12px] text-[#4B5563]">Alamat</dt>
                                <dd id="prof-alamat" class="mt-1 break-words text-[13px] font-medium text-[#4B5563]">Belum diisi</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-[12px] text-[#4B5563]">No. rekening</dt>
                                <dd class="mt-1 flex items-center gap-1.5">
                                    <span id="prof-rekening" class="text-[13px] font-medium text-[#4B5563]">Belum diisi</span>
                                    <button type="button" id="btn-toggle-rekening" aria-label="Tampilkan nomor rekening"
                                            aria-pressed="false"
                                            class="hidden items-center justify-center rounded p-0.5 text-[#4B5563] transition hover:text-[#146C94] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                                        <svg class="h-4 w-4" data-eye-open fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75 9.75 6.75 9.75 6.75-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                        <svg class="hidden h-4 w-4" data-eye-off fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a2 2 0 002.8 2.8M9.9 5.4A9.8 9.8 0 0112 5.25c6 0 9.75 6.75 9.75 6.75a17 17 0 01-2.2 3.1M6.2 6.2A17 17 0 002.25 12s3.75 6.75 9.75 6.75a9.7 9.7 0 003.4-.6" />
                                        </svg>
                                    </button>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Keamanan akun --}}
                    <div class="rounded-xl border border-[#E2E8EC] bg-white p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="text-[15px] font-semibold text-[#172B36]">Keamanan akun</h2>
                                <p class="mt-1 text-[12px] text-[#4B5563]">Ganti password secara berkala untuk menjaga akun tetap aman.</p>
                            </div>
                            <button type="button" id="btn-open-password"
                                    class="inline-flex h-9 shrink-0 items-center gap-1.5 rounded-lg border border-[#D5DEE3] bg-white px-3 text-[12px] font-semibold text-[#172B36] transition hover:bg-[#F4F7F9] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                </svg>
                                Ubah password
                            </button>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    {{-- Pekerjaan (read-only) --}}
                    <div class="rounded-xl border border-[#E2E8EC] bg-white p-4">
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                            <h2 class="text-[15px] font-semibold text-[#172B36]">Pekerjaan</h2>
                            <p class="inline-flex items-center gap-1 text-[12px] text-[#4B5563]">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                </svg>
                                Diubah oleh HRD
                            </p>
                        </div>

                        <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-[12px] text-[#4B5563]">ID karyawan</dt>
                                <dd id="prof-id" class="mt-1 text-[13px] font-medium text-[#4B5563]">Belum diisi</dd>
                            </div>
                            <div>
                                <dt class="text-[12px] text-[#4B5563]">Jabatan</dt>
                                <dd id="prof-jabatan" class="mt-1 text-[13px] font-medium text-[#4B5563]">Belum diisi</dd>
                            </div>
                            <div>
                                <dt class="text-[12px] text-[#4B5563]">Divisi</dt>
                                <dd id="prof-divisi" class="mt-1 text-[13px] font-medium text-[#4B5563]">Belum diisi</dd>
                            </div>
                            <div>
                                <dt class="text-[12px] text-[#4B5563]">Job level</dt>
                                <dd id="prof-job-level" class="mt-1 text-[13px] font-medium text-[#4B5563]">Belum diisi</dd>
                            </div>
                            <div>
                                <dt class="text-[12px] text-[#4B5563]">Tanggal masuk</dt>
                                <dd id="prof-rekrut" class="mt-1 text-[13px] font-medium text-[#4B5563]">Belum diisi</dd>
                            </div>
                            <div>
                                <dt class="text-[12px] text-[#4B5563]">Tanggal akhir kontrak</dt>
                                <dd id="prof-akhir-kontrak" class="mt-1 text-[13px] font-medium text-[#4B5563]">Belum diisi</dd>
                            </div>
                        </dl>

                        <div class="my-4 h-px bg-[#EEF2F5]"></div>

                        <h3 class="text-sm font-semibold text-[#172B36]">Akun</h3>
                        <dl class="mt-3 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-[12px] text-[#4B5563]">Role akun</dt>
                                <dd id="prof-role" class="mt-1 text-[13px] font-medium text-[#4B5563]">Belum diisi</dd>
                            </div>
                            <div>
                                <dt class="text-[12px] text-[#4B5563]">Akun dibuat</dt>
                                <dd id="prof-dibuat" class="mt-1 text-[13px] font-medium text-[#4B5563]">Belum diisi</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </main>
    </div>

    {{-- Dialog ubah password --}}
    <div id="password-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4" role="dialog"
         aria-modal="true" aria-labelledby="password-modal-title">
        <div id="password-backdrop" class="absolute inset-0 bg-[rgba(15,40,55,.45)]"></div>
        <div class="modal-panel-in relative w-full max-w-[400px] rounded-xl bg-white p-5 shadow-xl">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 id="password-modal-title" class="text-[16px] font-semibold text-[#172B36]">Ubah password</h2>
                <button type="button" id="btn-close-password" aria-label="Tutup dialog"
                        class="flex h-7 w-7 items-center justify-center rounded-lg text-[#4B5563] transition hover:bg-[#F4F7F9] hover:text-[#172B36] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="form-password" class="space-y-3" novalidate>
                <div>
                    <label for="current_password" class="mb-1 block text-[12px] text-[#4B5563]">Password saat ini</label>
                    <div class="relative">
                        <input type="password" id="current_password" autocomplete="current-password"
                               class="h-10 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 pr-10 text-[13px] text-[#172B36] outline-none focus:border-[#146C94] focus:ring-2 focus:ring-[#146C94]">
                        <button type="button" data-toggle-password="current_password" aria-label="Tampilkan password" aria-pressed="false"
                                class="toggle-password absolute inset-y-0 right-0 flex w-9 items-center justify-center text-[#4B5563] transition hover:text-[#146C94] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                            <svg class="h-4 w-4" data-eye-open fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75 9.75 6.75 9.75 6.75-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <svg class="hidden h-4 w-4" data-eye-off fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a2 2 0 002.8 2.8M9.9 5.4A9.8 9.8 0 0112 5.25c6 0 9.75 6.75 9.75 6.75a17 17 0 01-2.2 3.1M6.2 6.2A17 17 0 002.25 12s3.75 6.75 9.75 6.75a9.7 9.7 0 003.4-.6" />
                            </svg>
                        </button>
                    </div>
                    <p id="current_password-error" class="mt-1 hidden text-[12px] text-[#B91C1C]" role="alert"></p>
                </div>

                <div>
                    <label for="new_password" class="mb-1 block text-[12px] text-[#4B5563]">Password baru</label>
                    <div class="relative">
                        <input type="password" id="new_password" autocomplete="new-password"
                               class="h-10 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 pr-10 text-[13px] text-[#172B36] outline-none focus:border-[#146C94] focus:ring-2 focus:ring-[#146C94]">
                        <button type="button" data-toggle-password="new_password" aria-label="Tampilkan password" aria-pressed="false"
                                class="toggle-password absolute inset-y-0 right-0 flex w-9 items-center justify-center text-[#4B5563] transition hover:text-[#146C94] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                            <svg class="h-4 w-4" data-eye-open fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75 9.75 6.75 9.75 6.75-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <svg class="hidden h-4 w-4" data-eye-off fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a2 2 0 002.8 2.8M9.9 5.4A9.8 9.8 0 0112 5.25c6 0 9.75 6.75 9.75 6.75a17 17 0 01-2.2 3.1M6.2 6.2A17 17 0 002.25 12s3.75 6.75 9.75 6.75a9.7 9.7 0 003.4-.6" />
                            </svg>
                        </button>
                    </div>
                    <p id="new_password-error" class="mt-1 hidden text-[12px] text-[#B91C1C]" role="alert"></p>
                </div>

                <div>
                    <label for="confirm_password" class="mb-1 block text-[12px] text-[#4B5563]">Ulangi password baru</label>
                    <div class="relative">
                        <input type="password" id="confirm_password" autocomplete="new-password"
                               class="h-10 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 pr-10 text-[13px] text-[#172B36] outline-none focus:border-[#146C94] focus:ring-2 focus:ring-[#146C94]">
                        <button type="button" data-toggle-password="confirm_password" aria-label="Tampilkan password" aria-pressed="false"
                                class="toggle-password absolute inset-y-0 right-0 flex w-9 items-center justify-center text-[#4B5563] transition hover:text-[#146C94] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                            <svg class="h-4 w-4" data-eye-open fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75 9.75 6.75 9.75 6.75-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <svg class="hidden h-4 w-4" data-eye-off fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a2 2 0 002.8 2.8M9.9 5.4A9.8 9.8 0 0112 5.25c6 0 9.75 6.75 9.75 6.75a17 17 0 01-2.2 3.1M6.2 6.2A17 17 0 002.25 12s3.75 6.75 9.75 6.75a9.7 9.7 0 003.4-.6" />
                            </svg>
                        </button>
                    </div>
                    <p id="confirm_password-error" class="mt-1 hidden text-[12px] text-[#B91C1C]" role="alert"></p>
                </div>

                <p class="text-[12px] text-[#4B5563]">Minimal 8 karakter.</p>
                <p id="password-error" class="hidden text-[12px] text-[#B91C1C]" role="alert"></p>

                <div class="flex justify-end gap-2 pt-1">
                    <button type="button" id="btn-cancel-password"
                            class="inline-flex h-9 items-center rounded-lg border border-[#D5DEE3] bg-white px-3 text-[12px] font-semibold text-[#172B36] transition hover:bg-[#F4F7F9] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">Batal</button>
                    <button type="submit" id="btn-submit-password" disabled
                            class="inline-flex h-9 items-center rounded-lg bg-[#146C94] px-4 text-[12px] font-semibold text-white transition hover:bg-[#0F5678] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] disabled:cursor-not-allowed disabled:opacity-50">Perbarui password</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Dialog edit informasi pribadi --}}
    <div id="edit-profile-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4" role="dialog"
         aria-modal="true" aria-labelledby="edit-profile-title">
        <div id="edit-profile-backdrop" class="absolute inset-0 bg-[rgba(15,40,55,.45)]"></div>
        <div class="modal-panel-in relative max-h-[calc(100vh-2rem)] w-full max-w-[520px] overflow-y-auto rounded-xl bg-white p-5 shadow-xl">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 id="edit-profile-title" class="text-[16px] font-semibold text-[#172B36]">Edit informasi pribadi</h2>
                <button type="button" id="btn-close-edit-profile" aria-label="Tutup dialog"
                        class="flex h-7 w-7 items-center justify-center rounded-lg text-[#4B5563] transition hover:bg-[#F4F7F9] hover:text-[#172B36] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="form-edit-profil" class="space-y-4" novalidate>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="input-nama" class="mb-1 block text-[12px] text-[#4B5563]">Nama</label>
                        <input type="text" id="input-nama"
                               class="h-10 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] text-[#172B36] outline-none focus:border-[#146C94] focus:ring-2 focus:ring-[#146C94]">
                    </div>
                    <div>
                        <label for="input-email" class="mb-1 block text-[12px] text-[#4B5563]">Email</label>
                        <input type="email" id="input-email"
                               class="h-10 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] text-[#172B36] outline-none focus:border-[#146C94] focus:ring-2 focus:ring-[#146C94]">
                    </div>
                    <div>
                        <label for="input-telepon" class="mb-1 block text-[12px] text-[#4B5563]">No. telepon</label>
                        <input type="text" id="input-telepon"
                               class="h-10 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] text-[#172B36] outline-none focus:border-[#146C94] focus:ring-2 focus:ring-[#146C94]">
                    </div>
                    <div>
                        <label for="input-rekening" class="mb-1 block text-[12px] text-[#4B5563]">No. rekening</label>
                        <input type="text" id="input-rekening" placeholder="Contoh: BCA 1234567890"
                               class="h-10 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-[13px] text-[#172B36] outline-none focus:border-[#146C94] focus:ring-2 focus:ring-[#146C94]">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="input-alamat" class="mb-1 block text-[12px] text-[#4B5563]">Alamat</label>
                        <textarea id="input-alamat" rows="3"
                                  class="w-full rounded-lg border border-[#D5DEE3] bg-white px-3 py-2 text-[13px] text-[#172B36] outline-none focus:border-[#146C94] focus:ring-2 focus:ring-[#146C94]"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-1">
                    <button type="button" id="btn-cancel-edit"
                            class="inline-flex h-9 items-center rounded-lg border border-[#D5DEE3] bg-white px-3 text-[12px] font-semibold text-[#172B36] transition hover:bg-[#F4F7F9] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">Batal</button>
                    <button type="submit" id="btn-submit-edit"
                            class="inline-flex h-9 items-center rounded-lg bg-[#146C94] px-4 text-[12px] font-semibold text-white transition hover:bg-[#0F5678] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] disabled:cursor-not-allowed disabled:opacity-50">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async function () {
            const token = sessionStorage.getItem('staff_token');
            const endpoints = document.getElementById('profile-endpoints').dataset;
            const endpoint = endpoints.profile;
            const fotoEndpoint = endpoints.foto;
            const passwordEndpoint = endpoints.password;

            function headers(json) {
                const h = { 'Accept': 'application/json' };
                if (token) h['Authorization'] = 'Bearer ' + token;
                if (json) h['Content-Type'] = 'application/json';
                return h;
            }

            function isKosong(nilai) {
                if (nilai === null || nilai === undefined) return true;
                const teks = String(nilai).trim();
                return teks === '' || teks === '-';
            }

            function tampil(nilai) {
                return isKosong(nilai) ? 'Belum diisi' : String(nilai).trim();
            }

            function teks(id, nilai) {
                const el = document.getElementById(id);
                if (!el) return;
                const kosong = isKosong(nilai);
                el.textContent = kosong ? 'Belum diisi' : String(nilai).trim();
                el.classList.toggle('text-[#4B5563]', kosong);
                el.classList.toggle('text-[#172B36]', !kosong);
            }

            function isi(id, nilai) {
                const el = document.getElementById(id);
                if (el) el.value = isKosong(nilai) ? '' : String(nilai).trim();
            }

            function inisial(nama) {
                const kata = String(nama || '')
                    .trim()
                    .split(/\s+/)
                    .filter(Boolean);
                if (kata.length === 0) return 'U';
                return (kata[0].charAt(0) + (kata.length > 1 ? kata[1].charAt(0) : ''))
                    .toUpperCase();
            }

            let profileData = null;
            let rekeningValue = '';
            let rekeningVisible = false;

            function isiFormProfil(profil) {
                isi('input-nama', profil.nama);
                isi('input-email', profil.email);
                isi('input-telepon', profil.no_telepon);
                isi('input-rekening', profil.no_rekening);
                isi('input-alamat', profil.alamat);
            }

            function maskRekening(nilai) {
                const teks = String(nilai || '').replace(/\s+/g, '');
                if (teks.length === 0) return 'Belum diisi';
                return '••••' + teks.slice(-4);
            }

            function renderRekening() {
                const el = document.getElementById('prof-rekening');
                const btn = document.getElementById('btn-toggle-rekening');
                if (!el || !btn) return;

                if (rekeningValue === '') {
                    el.textContent = 'Belum diisi';
                    el.classList.add('text-[#4B5563]');
                    el.classList.remove('text-[#172B36]');
                    btn.classList.add('hidden');
                    btn.classList.remove('inline-flex');
                    return;
                }

                el.textContent = rekeningVisible ? rekeningValue : maskRekening(rekeningValue);
                el.classList.remove('text-[#4B5563]');
                el.classList.add('text-[#172B36]');
                btn.classList.remove('hidden');
                btn.classList.add('inline-flex');
                btn.setAttribute('aria-pressed', rekeningVisible ? 'true' : 'false');
                btn.setAttribute('aria-label', rekeningVisible ? 'Sembunyikan nomor rekening' : 'Tampilkan nomor rekening');
                btn.querySelector('[data-eye-open]').classList.toggle('hidden', rekeningVisible);
                btn.querySelector('[data-eye-off]').classList.toggle('hidden', !rekeningVisible);
            }

            async function loadProfile() {
                try {
                    const response = await fetch(endpoint, { headers: headers(false) });
                    const json = await response.json();
                    if (response.ok && json.data) {
                        const p = json.data;

                        teks('hdr-nama', p.nama);

                        const jabatan = isKosong(p.jabatan) ? '' : String(p.jabatan).trim();
                        const divisi = isKosong(p.divisi) ? '' : String(p.divisi).trim();
                        document.getElementById('hdr-jabatan-divisi').textContent =
                            [jabatan, divisi].filter(Boolean).join(' · ') || 'Belum diisi';

                        const aktif = String(p.status_aktif || '').trim().toLowerCase() === 'aktif';
                        document.getElementById('hdr-status-aktif-wrap').classList.toggle('hidden', !aktif);
                        document.getElementById('hdr-status-karyawan').textContent = tampil(p.status_karyawan);

                        const inisialEl = document.getElementById('prof-inisial');
                        inisialEl.textContent = inisial(p.nama);

                        const foto = document.getElementById('prof-foto');
                        if (p.foto_url) {
                            foto.src = p.foto_url;
                            foto.classList.remove('hidden');
                            inisialEl.classList.add('hidden');
                        } else {
                            foto.classList.add('hidden');
                            inisialEl.classList.remove('hidden');
                        }

                        ['id', 'role', 'jabatan', 'divisi', 'job-level', 'pendidikan', 'rekrut',
                            'akhir-kontrak', 'dibuat', 'gender', 'tgl-lahir', 'alamat', 'email', 'telepon'
                        ].forEach((k) => {
                            const field = {
                                id: 'id_karyawan', role: 'tingkatan', dibuat: 'tanggal_dibuat',
                                'job-level': 'job_level', 'tgl-lahir': 'tanggal_lahir',
                                rekrut: 'tanggal_masuk_label', 'akhir-kontrak': 'tanggal_akhir_kontrak',
                                gender: 'jenis_kelamin', alamat: 'alamat', jabatan: 'jabatan',
                                divisi: 'divisi', pendidikan: 'pendidikan', email: 'email',
                                telepon: 'no_telepon',
                            }[k];
                            teks('prof-' + k, p[field]);
                        });

                        rekeningValue = isKosong(p.no_rekening) ? '' : String(p.no_rekening).trim();
                        renderRekening();

                        profileData = p;
                        isiFormProfil(p);
                    }
                } catch (e) {
                    console.error('Gagal memuat profil:', e);
                }
            }

            await loadProfile();

            // Kamera avatar -> unggah foto langsung.
            const fotoInput = document.getElementById('input-foto');
            document.getElementById('btn-ubah-foto').addEventListener('click', () => fotoInput.click());
            fotoInput.addEventListener('change', async function () {
                if (!this.files.length) return;

                const formData = new FormData();
                formData.append('foto', this.files[0]);

                try {
                    const response = await fetch(fotoEndpoint, {
                        method: 'POST',
                        headers: headers(false),
                        body: formData,
                    });
                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        const pesan = data.message || 'Foto gagal diunggah.';
                        if (window.appToast) window.appToast(pesan, 'error');
                        else alert(pesan);
                        return;
                    }

                    const foto = document.getElementById('prof-foto');
                    foto.src = data.foto_url || foto.src;
                    foto.classList.remove('hidden');
                    document.getElementById('prof-inisial').classList.add('hidden');

                    if (window.appToast) window.appToast('Foto profil diperbarui');
                } catch (e) {
                    if (window.appToast) window.appToast('Foto gagal diunggah.', 'error');
                    else alert('Foto gagal diunggah.');
                } finally {
                    fotoInput.value = '';
                }
            });

            // Dialog edit informasi pribadi.
            const editProfileModal = document.getElementById('edit-profile-modal');
            const editProfileForm = document.getElementById('form-edit-profil');
            const submitEditProfile = document.getElementById('btn-submit-edit');

            function openEditProfileDialog() {
                if (profileData) isiFormProfil(profileData);
                editProfileModal.classList.remove('hidden');
                editProfileModal.classList.add('flex');
                setTimeout(() => document.getElementById('input-nama').focus(), 30);
            }

            function closeEditProfileDialog() {
                editProfileModal.classList.add('hidden');
                editProfileModal.classList.remove('flex');
                submitEditProfile.disabled = false;
                document.getElementById('btn-edit-info').focus();
            }

            document.getElementById('btn-edit-info').addEventListener('click', openEditProfileDialog);
            document.getElementById('btn-close-edit-profile').addEventListener('click', closeEditProfileDialog);
            document.getElementById('btn-cancel-edit').addEventListener('click', closeEditProfileDialog);
            document.getElementById('edit-profile-backdrop').addEventListener('click', closeEditProfileDialog);

            editProfileForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                submitEditProfile.disabled = true;

                try {
                    const res = await fetch(endpoint, {
                        method: 'PUT',
                        headers: headers(true),
                        body: JSON.stringify({
                            nama: document.getElementById('input-nama').value.trim(),
                            email: document.getElementById('input-email').value.trim(),
                            no_telepon: document.getElementById('input-telepon').value.trim(),
                            no_rekening: document.getElementById('input-rekening').value.trim() || null,
                            alamat: document.getElementById('input-alamat').value.trim(),
                        })
                    });
                    const data = await res.json().catch(() => ({}));

                    if (!res.ok) {
                        const pesan = data.message || 'Gagal memperbarui profil.';
                        if (window.appToast) window.appToast(pesan, 'error');
                        else alert(pesan);
                        submitEditProfile.disabled = false;
                        return;
                    }

                    await loadProfile();
                    closeEditProfileDialog();
                    if (window.appToast) window.appToast('Profil berhasil diperbarui.');
                    else alert('Profil berhasil diperbarui.');
                } catch (err) {
                    if (window.appToast) window.appToast('Kesalahan jaringan.', 'error');
                    else alert('Kesalahan jaringan.');
                    submitEditProfile.disabled = false;
                }
            });

            // Tampilkan / sembunyikan nomor rekening hanya untuk tampilan saat ini.
            document.getElementById('btn-toggle-rekening').addEventListener('click', () => {
                rekeningVisible = !rekeningVisible;
                renderRekening();
            });

            // Dialog ubah password.
            const passwordModal = document.getElementById('password-modal');
            const passwordForm = document.getElementById('form-password');
            const submitPassword = document.getElementById('btn-submit-password');
            const passwordFields = ['current_password', 'new_password', 'confirm_password'];

            function setFieldError(field, pesan) {
                const el = document.getElementById(field + '-error');
                if (!el) return;
                el.textContent = pesan || '';
                el.classList.toggle('hidden', !pesan);
            }

            function clearPasswordErrors() {
                passwordFields.forEach((field) => setFieldError(field, ''));
                const general = document.getElementById('password-error');
                general.textContent = '';
                general.classList.add('hidden');
            }

            function updatePasswordState() {
                const current = document.getElementById('current_password').value;
                const baru = document.getElementById('new_password').value;
                const konfirmasi = document.getElementById('confirm_password').value;
                submitPassword.disabled = current.length === 0
                    || baru.length < 8
                    || konfirmasi.length === 0
                    || baru !== konfirmasi;
            }

            function openPasswordDialog() {
                passwordForm.reset();
                clearPasswordErrors();
                updatePasswordState();
                passwordModal.classList.remove('hidden');
                passwordModal.classList.add('flex');
                setTimeout(() => document.getElementById('current_password').focus(), 30);
            }

            function closePasswordDialog() {
                passwordModal.classList.add('hidden');
                passwordModal.classList.remove('flex');
                passwordForm.reset();
                clearPasswordErrors();
                updatePasswordState();
            }

            document.getElementById('btn-open-password').addEventListener('click', openPasswordDialog);
            document.getElementById('btn-close-password').addEventListener('click', closePasswordDialog);
            document.getElementById('btn-cancel-password').addEventListener('click', closePasswordDialog);
            document.getElementById('password-backdrop').addEventListener('click', closePasswordDialog);

            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Escape') return;

                if (!passwordModal.classList.contains('hidden')) {
                    closePasswordDialog();
                    return;
                }

                if (!editProfileModal.classList.contains('hidden')) {
                    closeEditProfileDialog();
                }
            });

            passwordFields.forEach((field) => {
                document.getElementById(field).addEventListener('input', function () {
                    setFieldError(field, '');
                    updatePasswordState();
                });
            });

            // Show/hide password controls.
            document.querySelectorAll('[data-toggle-password]').forEach((button) => {
                const input = document.getElementById(button.dataset.togglePassword);
                if (!input) return;

                button.addEventListener('click', function () {
                    const visible = input.type === 'text';
                    input.type = visible ? 'password' : 'text';
                    this.setAttribute('aria-pressed', visible ? 'false' : 'true');
                    this.setAttribute('aria-label', visible ? 'Tampilkan password' : 'Sembunyikan password');
                    this.querySelector('[data-eye-open]').classList.toggle('hidden', !visible);
                    this.querySelector('[data-eye-off]').classList.toggle('hidden', visible);
                });
            });

            passwordForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                clearPasswordErrors();

                const current = document.getElementById('current_password').value;
                const baru = document.getElementById('new_password').value;
                const konfirmasi = document.getElementById('confirm_password').value;

                if (current.length === 0) {
                    setFieldError('current_password', 'Password saat ini wajib diisi.');
                    return;
                }
                if (baru.length < 8) {
                    setFieldError('new_password', 'Password minimal 8 karakter.');
                    return;
                }
                if (baru !== konfirmasi) {
                    setFieldError('confirm_password', 'Konfirmasi password tidak cocok.');
                    return;
                }

                submitPassword.disabled = true;

                try {
                    const res = await fetch(passwordEndpoint, {
                        method: 'PUT',
                        headers: headers(true),
                        body: JSON.stringify({
                            current_password: current,
                            new_password: baru,
                            confirm_password: konfirmasi,
                        }),
                    });

                    const data = await res.json().catch(() => ({}));

                    if (!res.ok) {
                        const fieldErrors = data.errors || data.data || {};
                        let shown = false;

                        passwordFields.forEach((field) => {
                            const pesan = fieldErrors[field];
                            if (pesan) {
                                setFieldError(field, Array.isArray(pesan) ? pesan[0] : pesan);
                                shown = true;
                            }
                        });

                        if (!shown) {
                            const pesan = data.message || 'Gagal memperbarui password.';
                            if (res.status === 400) {
                                setFieldError('current_password', pesan);
                            } else {
                                const general = document.getElementById('password-error');
                                general.textContent = pesan;
                                general.classList.remove('hidden');
                            }
                        }

                        updatePasswordState();
                        return;
                    }

                    closePasswordDialog();
                    if (window.appToast) window.appToast('Password diperbarui');
                    else alert('Password diperbarui');
                } catch (err) {
                    const general = document.getElementById('password-error');
                    general.textContent = 'Kesalahan jaringan.';
                    general.classList.remove('hidden');
                    updatePasswordState();
                }
            });
        });
    </script>
</body>

</html>
