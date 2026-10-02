<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manajement Pesan - PT SILINDO</title>

    @vite(['resources/css/app.css'])
</head>

<body class="bg-white font-sans text-[#283044]">

    <div class="flex min-h-screen">

        {{-- =========================================================
            SIDEBAR
            JANGAN DIUBAH
        ========================================================== --}}
        <aside class="w-64 bg-gradient-l text-slate-300 flex flex-col h-screen border-r border-whiite-30% ">

            <div class="bg-gradient-to-b from-[#044564] from-50% to-[#19A7CE] min-h-screen">

                <div class="flex items-center justify-between p-4 bg-white rounded-bl-xl">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('gambar/silindo.png') }}" alt="Logo" class="w-12 h-12 object-contain shrink-0">

                        <div class="flex flex-col">
                            <h1 class="text-xl font-bold text-[#2A4B6A] leading-tight">
                                PT SILINDO
                            </h1>
                            <p class="text-[11px] font-small text-[#1CA4BA] tracking-tight leading-tight">
                                PT SINERGI ILMIAH INDONESIA
                            </p>
                        </div>
                    </div>

                    <button class="p-2 text-[#2A4B6A] hover:bg-gray-100 rounded-md">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M4 6h16M4 12h16M4 18h16">
                            </path>
                        </svg>
                    </button>
                </div>

                <nav class="flex-1 p-4 pt-8 space-y-3 text-sm">
                    <a href="#"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg bg-white/40 text-white font-medium transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 20v-6h4v6h5v-8h3L12 3L2 12h3v8z">
                            </path>
                        </svg>
                        Dashboard
                    </a>

                    <a href="#"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-white/40 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 5h8m-8 7h8m-8 7h8M3 17l2 2l4-4"/>
                            <rect width="6" height="6" x="3" y="4" rx="1">
                            </path>
                        </svg>
                        Tugas
                    </a>

                    <a href="#"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-white/40 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                stroke-linejoin="round" stroke-width="2"
                                d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2m-3 12H7c-.55 0-1-.45-1-1s.45-1 1-1h10c.55 0 1-.45 1-1s-.45-1-1-1m0-3H7c-.55 0-1-.45-1-1s.45-1 1-1h10c.55 0 1-.45 1-1s-.45-1-1-1">
                            </path>
                        </svg>
                        Pesan
                    </a>
                </nav>

                <div class="p-4 border-t border-white mt-auto">

                    <div class="flex items-center gap-3 mb-3 px-2 bottom-0 mt-80">
                        <div class="w-9 h-9 rounded-full bg-blue-500 flex items-center justify-center font-bold text-white">
                            S
                        </div>

                        <div class="overflow-hidden">
                            <p class="text-sm font-medium text-white">Nama Pengguna</p>
                            <p class="text-xs text-white-100 capitalize">Role: Kadiv / HRD</p>
                        </div>
                    </div>

                    <form action="#" method="POST">
                        @csrf

                        <button type="submit"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2 rounded-lg outline-1 bg-white text-red-400 hover:bg-red-500 hover:text-white text-sm font-medium transition">

                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                </path>
                            </svg>

                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </aside>


        {{-- =========================================================
            MAIN CONTENT
        ========================================================== --}}
        <main class="flex-1 min-w-0 bg-white">

            {{-- HEADER --}}
            <header class="h-[79px] bg-white shadow-[0_4px_4px_rgba(0,0,0,0.20)] flex items-center px-8 lg:px-10 xl:px-12 gap-6">

                {{-- Search --}}
                <div class="flex-1 max-w-[693px] h-[51px] border border-[rgba(148,163,184,0.5)] rounded-lg px-3 flex items-center gap-2">

                    <svg class="w-[22px] h-[22px] shrink-0 text-[#64748B]"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z">
                        </path>
                    </svg>

                    <span class="text-sm leading-[22px] text-[#64748B]">
                        Cari
                    </span>
                </div>

                {{-- Header right --}}
                <div class="ml-auto flex items-center gap-6">

                    {{-- Notification --}}
                    <button class="w-10 h-10 flex items-center justify-center">
                        <svg class="w-[30px] h-[30px] text-[#283044]"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4">
                            </path>
                        </svg>
                    </button>

                    {{-- Profile --}}
                    <div class="w-10 h-10 rounded-full bg-[#D9D9D9] flex items-center justify-center">
                        <svg class="w-4 h-4 text-[#565E74]"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="m7 10 5 5 5-5">
                            </path>
                        </svg>
                    </div>

                </div>
            </header>


            {{-- CONTENT --}}
            <section class="px-8 lg:px-10 xl:px-12 py-8">

                {{-- Judul + tombol --}}
                <div class="flex items-center justify-between mb-8">

                    <h1 class="text-2xl leading-8 font-bold text-black">
                        Manajement Pesan
                    </h1>

                    <button
                        type="button"
                        id="openModal"
                        class="bg-[#0E9DC3] hover:bg-[#0C8EAF] text-white rounded-[9px] h-9 px-4 flex items-center justify-center text-base transition">

                        Aksi
                    </button>
                </div>


                {{-- TABLE --}}
                <div class="border border-[#E2E2E3] rounded-lg overflow-hidden">

                    {{-- Table Header --}}
                    <div class="grid grid-cols-[40%_20%_22%_18%] min-h-[30px] bg-[#D9D9D9] items-center">

                        <div class="px-3 flex items-center gap-2 text-sm leading-[22px] text-[#565E74]">
                            <span>Nama Divisi</span>

                            <svg class="w-3 h-3"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="m7 10 5-5 5 5M7 14l5 5 5-5">
                                </path>
                            </svg>
                        </div>

                        <div class="px-3 text-center text-sm leading-[22px] text-[#565E74]">
                            Tugas
                        </div>

                        <div class="px-3 flex items-center justify-end gap-2 text-sm leading-[22px] text-[#565E74]">
                            <span>Keterangan</span>

                            <svg class="w-3 h-3"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="m7 10 5-5 5 5M7 14l5 5 5-5">
                                </path>
                            </svg>
                        </div>

                        <div class="px-3 text-center text-sm leading-[22px] text-[#565E74]">
                        </div>
                    </div>


                    {{-- Table Body --}}
                    <div class="min-h-[300px] bg-white">

                        {{-- Contoh baris --}}
                        <div class="grid grid-cols-[40%_20%_22%_18%] min-h-[60px] border-b border-[#E2E2E3] items-center">

                            <div class="px-3 text-sm text-[#283044]">
                                Divisi Teknologi Informasi
                            </div>

                            <div class="px-3 text-sm text-[#283044] text-center">
                                Monitoring Sistem
                            </div>

                            <div class="px-3 text-sm text-[#565E74] text-right">
                                Belum dibaca
                            </div>

                            <div class="px-3 flex justify-center">
                                <button
                                    type="button"
                                    class="text-[#0E9DC3] hover:underline text-sm font-medium">
                                    Lihat
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

            </section>
        </main>
    </div>


    {{-- =========================================================
        MODAL OVERLAY
    ========================================================== --}}
    <div
        id="messageModal"
        class="hidden fixed inset-0 z-50 bg-[rgba(86,94,116,0.4)] flex items-center justify-center p-4">

        {{-- MODAL --}}
        <div class="bg-white rounded-xl w-full max-w-[469px] max-h-[calc(100vh-40px)] overflow-y-auto shadow-[0_8px_16px_rgba(0,0,0,0.12)]">

            {{-- Modal Header --}}
            <div class="bg-[#F2F6FA] rounded-t-lg px-[30px] py-5 flex items-center gap-4">

                <div class="bg-[#AFD3E2] rounded-lg p-2 flex items-center justify-center">

                    <svg class="w-[18px] h-[18px] text-[#283044]"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z">
                        </path>
                    </svg>

                </div>

                <h2 class="flex-1 text-xl leading-7 font-bold text-black">
                    Tulis Pesan Baru
                </h2>

                <button
                    type="button"
                    id="closeModal"
                    class="w-6 h-6 flex items-center justify-center text-[#565E74] hover:text-black">

                    <svg class="w-6 h-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 6l12 12M18 6 6 18">
                        </path>
                    </svg>
                </button>

            </div>


            {{-- Modal Body --}}
            <div class="px-[30px] pt-0 pb-0">

                <div class="py-7 flex flex-col gap-[22px]">

                    {{-- Tujuan Penerima --}}
                    <div class="flex flex-col">

                        <label class="pb-[6px] text-base leading-6 font-bold text-[#565E74]">
                            TUJUAN PENERIMA
                        </label>

                        <input
                            type="text"
                            placeholder="Body"
                            class="w-full h-[37px] rounded-lg border border-[#CBD5E1] px-[11px] text-sm text-[#283044] placeholder:text-[#94A3B8] outline-none focus:border-[#19A7CE]">

                    </div>


                    {{-- BODY tambahan sesuai Figma --}}
                    <div>

                        <input
                            type="text"
                            placeholder="Body"
                            class="w-full h-[37px] rounded-lg border border-[#CBD5E1] px-[11px] text-sm text-[#283044] placeholder:text-[#94A3B8] outline-none focus:border-[#19A7CE]">

                    </div>


                    {{-- Subjek --}}
                    <div class="flex flex-col">

                        <label class="pb-[6px] text-base leading-6 font-bold text-[#565E74]">
                            SUBJEK PESAN
                        </label>

                        <input
                            type="text"
                            placeholder="Body"
                            class="w-full h-[37px] rounded-lg border border-[#CBD5E1] px-[11px] text-sm text-[#283044] placeholder:text-[#94A3B8] outline-none focus:border-[#19A7CE]">

                    </div>


                    {{-- Isi Pesan --}}
                    <div class="flex flex-col">

                        <label class="pb-[6px] text-base leading-6 font-bold text-[#565E74]">
                            ISI PESAN
                        </label>

                        <textarea
                            rows="3"
                            placeholder="Body"
                            class="w-full h-[87px] resize-none rounded-lg border border-[#CBD5E1] px-[11px] py-2 text-sm text-[#283044] placeholder:text-[#94A3B8] outline-none focus:border-[#19A7CE]"></textarea>

                    </div>


                    {{-- Lampiran --}}
                    <div class="bg-[rgba(205,218,236,0.25)] rounded-lg p-5 flex flex-col gap-4">

                        <div class="flex items-start justify-between gap-3">

                            <label class="text-base leading-6 font-bold text-[#565E74]">
                                LAMPIRAN
                            </label>

                            <button
                                type="button"
                                class="h-8 bg-[#F6F1F1] border border-[rgba(148,163,184,0.5)] rounded-xl px-3 flex items-center gap-2 text-sm font-bold text-[#283044]">

                                <svg class="w-3.5 h-3.5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z">
                                    </path>
                                    <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M14 2v6h6">
                                    </path>
                                </svg>

                                Unggah Berkas

                                <svg class="w-[18px] h-[18px]"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="m6 9 6 6 6-6">
                                    </path>
                                </svg>
                            </button>

                        </div>


                        {{-- Upload Area --}}
                        <label
                            class="bg-white rounded-lg border border-dashed border-[#AFD3E2] px-3 py-5 flex flex-col items-center justify-center cursor-pointer hover:bg-[#F8FBFD]">

                            <svg class="w-6 h-6 text-[#565E74] mb-1"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 3v12m0-12 4 4m-4-4L8 7M5 13v5a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3v-5">
                                </path>
                            </svg>

                            <span class="text-base leading-6 font-bold text-[#565E74]">
                                Unggah File
                            </span>

                            <span class="text-[10px] leading-5 text-[#131B2E]">
                                Mendukung File (Maks 5 MB)
                            </span>

                            <input type="file" class="hidden">
                        </label>

                    </div>

                </div>
            </div>


            {{-- Modal Footer --}}
            <div class="px-[30px] pb-[30px] flex items-center justify-between">

                <button
                    type="button"
                    id="cancelModal"
                    class="h-8 bg-[#E8E7E9] rounded-lg px-3 flex items-center justify-center text-base leading-6 text-[#333335] hover:bg-[#D9D9D9] transition">

                    Batal
                </button>

                <button
                    type="button"
                    class="h-8 bg-[#004B6C] rounded-lg px-3 flex items-center justify-center gap-2 text-base leading-6 font-bold text-white hover:bg-[#003D58] transition">

                    Kirim Pesan

                    <svg class="w-3 h-3"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="m5 12 14-7-7 14-2-6-5-1Z">
                        </path>
                    </svg>

                </button>

            </div>

        </div>
    </div>


    {{-- =========================================================
        MODAL SCRIPT
    ========================================================== --}}
    <script>
        const openModal = document.getElementById('openModal');
        const messageModal = document.getElementById('messageModal');
        const closeModal = document.getElementById('closeModal');
        const cancelModal = document.getElementById('cancelModal');

        openModal.addEventListener('click', () => {
            messageModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        });

        closeModal.addEventListener('click', () => {
            messageModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        });

        cancelModal.addEventListener('click', () => {
            messageModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        });

        messageModal.addEventListener('click', (event) => {
            if (event.target === messageModal) {
                messageModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        });
    </script>

</body>
</html>
```
