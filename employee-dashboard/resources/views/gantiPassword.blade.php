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
                <input id="gp-lama" type="password" name="password_lama" autocomplete="current-password"
                       placeholder="Password sementara" class="w-full h-9 px-3 text-[13px] border border-[#D5DEE3] rounded-lg outline-none focus:ring-2 focus:ring-[#146C94] placeholder:text-[#6B7680]">
            </div>
            <div>
                <label for="gp-baru" class="block text-[13px] font-medium text-[#172B36] mb-1.5">Password baru</label>
                <input id="gp-baru" type="password" name="password" autocomplete="new-password"
                       placeholder="Minimal 8 karakter" class="w-full h-9 px-3 text-[13px] border border-[#D5DEE3] rounded-lg outline-none focus:ring-2 focus:ring-[#146C94] placeholder:text-[#6B7680]">
            </div>
            <div>
                <label for="gp-konfirmasi" class="block text-[13px] font-medium text-[#172B36] mb-1.5">Konfirmasi password baru</label>
                <input id="gp-konfirmasi" type="password" name="password_confirmation" autocomplete="new-password"
                       placeholder="Ulangi password baru" class="w-full h-9 px-3 text-[13px] border border-[#D5DEE3] rounded-lg outline-none focus:ring-2 focus:ring-[#146C94] placeholder:text-[#6B7680]">
            </div>
            <div class="flex items-center justify-end gap-2 pt-1">
                <button type="submit" class="h-9 rounded-lg bg-[#146C94] px-4 text-[13px] font-medium text-white hover:bg-[#0F5678]">Simpan password</button>
            </div>
        </form>
    </div>
</body>
</html>
