<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terjadi kendala - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md rounded-xl border border-[#E2E8EC] bg-white p-6 shadow-sm">
        <h1 class="text-[16px] font-semibold text-[#172B36]">Terjadi kendala pada sistem</h1>
        <p class="mt-2 text-[13px] text-[#4B5563]">Harap coba lagi. Jika masalah berlanjut, hubungi administrator.</p>
        <a href="{{ url('/') }}"
           class="mt-5 inline-flex h-9 items-center rounded-lg bg-[#146C94] px-4 text-[13px] font-medium text-white hover:bg-[#0F5678]">
            Kembali ke beranda
        </a>
    </div>
</body>
</html>
