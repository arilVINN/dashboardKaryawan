<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Password - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F4F7F9] flex items-center justify-center p-4">
    <div class="w-full max-w-[420px] rounded-xl border border-[#E2E8EC] bg-white p-6 shadow-sm">
        <h1 class="text-[16px] font-semibold text-[#172B36]">Ganti password</h1>
        <p class="mt-1 text-[13px] text-[#4B5563]">Anda diminta mengganti password sebelum melanjutkan.</p>

        @if ($errors->any())
            <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2" role="alert">
                @foreach ($errors->all() as $error)
                    <p class="text-[12px] text-[#B91C1C]">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if (session('success'))
            <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2">
                <p class="text-[12px] text-emerald-700">{{ session('success') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('password.change.store') }}" class="mt-5 space-y-4" novalidate>
            @csrf
            <div>
                <label for="gp-lama" class="block text-[13px] font-medium text-[#172B36] mb-1.5">Password saat ini</label>
                <div class="relative">
                    <input id="gp-lama" type="password" name="password_lama" autocomplete="current-password"
                           placeholder="Password sementara" class="w-full h-9 pl-3 pr-10 text-[13px] border border-[#D5DEE3] rounded-lg outline-none focus:ring-2 focus:ring-[#146C94] placeholder:text-[#6B7680]">
                    <button type="button" data-target="gp-lama" aria-label="Tampilkan password" aria-pressed="false"
                            class="gp-toggle absolute right-1.5 top-1/2 -translate-y-1/2 flex h-7 w-7 items-center justify-center rounded-md text-[#64748B] transition hover:bg-[#F1F5F9] hover:text-[#172B36] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                        <svg class="h-4 w-4 gp-eye" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75 9.75 6.75 9.75 6.75-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="h-4 w-4 gp-eye-off hidden" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a2 2 0 002.8 2.8M9.9 5.4A9.8 9.8 0 0112 5.25c6 0 9.75 6.75 9.75 6.75a17 17 0 01-2.2 3.1M6.2 6.2A17 17 0 002.25 12s3.75 6.75 9.75 6.75a9.7 9.7 0 003.4-.6"/></svg>
                    </button>
                </div>
            </div>
            <div>
                <label for="gp-baru" class="block text-[13px] font-medium text-[#172B36] mb-1.5">Password baru</label>
                <div class="relative">
                    <input id="gp-baru" type="password" name="password" autocomplete="new-password"
                           placeholder="Minimal 8 karakter" class="w-full h-9 pl-3 pr-10 text-[13px] border border-[#D5DEE3] rounded-lg outline-none focus:ring-2 focus:ring-[#146C94] placeholder:text-[#6B7680]">
                    <button type="button" data-target="gp-baru" aria-label="Tampilkan password" aria-pressed="false"
                            class="gp-toggle absolute right-1.5 top-1/2 -translate-y-1/2 flex h-7 w-7 items-center justify-center rounded-md text-[#64748B] transition hover:bg-[#F1F5F9] hover:text-[#172B36] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                        <svg class="h-4 w-4 gp-eye" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75 9.75 6.75 9.75 6.75-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="h-4 w-4 gp-eye-off hidden" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a2 2 0 002.8 2.8M9.9 5.4A9.8 9.8 0 0112 5.25c6 0 9.75 6.75 9.75 6.75a17 17 0 01-2.2 3.1M6.2 6.2A17 17 0 002.25 12s3.75 6.75 9.75 6.75a9.7 9.7 0 003.4-.6"/></svg>
                    </button>
                </div>
            </div>
            <div>
                <label for="gp-konfirmasi" class="block text-[13px] font-medium text-[#172B36] mb-1.5">Konfirmasi password baru</label>
                <div class="relative">
                    <input id="gp-konfirmasi" type="password" name="password_confirmation" autocomplete="new-password"
                           placeholder="Ulangi password baru" class="w-full h-9 pl-3 pr-10 text-[13px] border border-[#D5DEE3] rounded-lg outline-none focus:ring-2 focus:ring-[#146C94] placeholder:text-[#6B7680]">
                    <button type="button" data-target="gp-konfirmasi" aria-label="Tampilkan password" aria-pressed="false"
                            class="gp-toggle absolute right-1.5 top-1/2 -translate-y-1/2 flex h-7 w-7 items-center justify-center rounded-md text-[#64748B] transition hover:bg-[#F1F5F9] hover:text-[#172B36] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]">
                        <svg class="h-4 w-4 gp-eye" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75 9.75 6.75 9.75 6.75-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="h-4 w-4 gp-eye-off hidden" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a2 2 0 002.8 2.8M9.9 5.4A9.8 9.8 0 0112 5.25c6 0 9.75 6.75 9.75 6.75a17 17 0 01-2.2 3.1M6.2 6.2A17 17 0 002.25 12s3.75 6.75 9.75 6.75a9.7 9.7 0 003.4-.6"/></svg>
                    </button>
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 pt-1">
                <button type="submit" class="h-9 rounded-lg bg-[#146C94] px-4 text-[13px] font-medium text-white hover:bg-[#0F5678]">Simpan password</button>
            </div>
        </form>
    </div>
    <script>
        document.querySelectorAll('.gp-toggle').forEach(function (button) {
            button.addEventListener('click', function () {
                const input = document.getElementById(button.dataset.target);
                if (! input) return;

                const wasVisible = input.type === 'text';
                input.type = wasVisible ? 'password' : 'text';
                button.setAttribute('aria-pressed', wasVisible ? 'false' : 'true');
                button.setAttribute('aria-label', wasVisible ? 'Tampilkan password' : 'Sembunyikan password');
                button.querySelector('.gp-eye').classList.toggle('hidden', ! wasVisible);
                button.querySelector('.gp-eye-off').classList.toggle('hidden', wasVisible);
            });
        });
    </script>
</body>
</html>
