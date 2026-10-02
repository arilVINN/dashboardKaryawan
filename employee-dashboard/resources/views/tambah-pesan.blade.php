<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Pesan - PT SILINDO</title>

    @vite('resources/css/app.css')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=PT+Sans:wght@400;700&display=swap"
        rel="stylesheet">
</head>

<body class="m-0 min-h-screen bg-white font-sans text-[#131B2E]">

    <div class="flex min-h-screen w-full">

        {{-- ================= SIDEBAR ================= --}}
        <aside class="w-64 bg-gradient-l text-slate-300 flex flex-col h-screen border-r border-whiite-30% ">

            <div class="bg-gradient-to-b from-[#044564] from-50% to-[#19A7CE] min-h-screen">

                <div class="flex items-center justify-between p-4 bg-white rounded-bl-xl">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('gambar/silindo.png') }}"
                            alt="Logo"
                            class="w-12 h-12 object-contain shrink-0">

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
                        <svg class="w-8 h-8"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2.5"
                                d="M4 6h16M4 12h16M4 18h16">
                            </path>
                        </svg>
                    </button>
                </div>

                <nav class="flex-1 p-4 pt-8 space-y-3 text-sm">

                    {{-- Dashboard --}}
                    <a href="/"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-white/40 hover:text-white transition">

                        <svg class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10 20v-6h4v6h5v-8h3L12 3L2 12h3v8">
                            </path>
                        </svg>

                        Dashboard
                    </a>

                    {{-- Tugas --}}
                    <a href="/tugas-revisi"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-white/40 hover:text-white transition">

                        <svg class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 5h8m-8 7h8m-8 7h8M3 17l2 2l4-4" />

                            <rect width="6"
                                height="6"
                                x="3"
                                y="4"
                                rx="1">
                            </rect>
                        </svg>

                        Tugas
                    </a>

                    {{-- Pesan --}}
                    <a href="/manajemen-pesan"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg bg-white/40 text-white font-medium transition">

                        <svg class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1.9-2-2-2m-3 12H7c-.55 0-1-.45-1-1s.45-1 1-1h10c.55 0 1-.45-1-1m0-3H7c-.55 0-1-.45-1-1s.45-1-1-1h10c.55 0 1 .45 1 1s-.45 1-1 1">
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
                            <p class="text-sm font-medium text-white">
                                Nama Pengguna
                            </p>

                            <p class="text-xs text-white-100 capitalize">
                                Role: Kadiv / HRD
                            </p>
                        </div>

                    </div>

                    <form action="#" method="POST">
                        @csrf

                        <button type="submit"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2 rounded-lg outline-1 bg-white text-red-400 hover:bg-red-500 hover:text-white text-sm font-medium transition">

                            <svg class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                </path>

                            </svg>

                            Keluar
                        </button>

                    </form>

                </div>

            </div>
        </aside>


        {{-- ================= MAIN CONTENT ================= --}}
        <main class="flex-1 min-w-0 min-h-screen bg-white">

            {{-- HEADER --}}
            <header class="h-[79px] w-full flex items-center px-8 gap-6 shadow-[0_4px_4px_rgba(0,0,0,0.20)]">

                {{-- Search --}}
                <div class="flex-1 max-w-[693px] mx-auto">

                    <div class="h-[51px] border border-[rgba(148,163,184,0.5)] rounded-lg flex items-center px-4">

                        <svg class="w-5 h-5 text-[#64748B] shrink-0"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z">
                            </path>

                        </svg>

                        <input type="text"
                            placeholder="Cari..."
                            class="w-full ml-3 outline-none border-none text-sm text-[#283044] placeholder:text-[#64748B]">

                    </div>
                </div>


                {{-- Notification --}}
                <button class="w-10 h-10 shrink-0 flex items-center justify-center rounded-full hover:bg-gray-100">

                    <svg class="w-6 h-6 text-[#283044]"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24">

                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0">
                        </path>

                    </svg>

                </button>


                {{-- Profile --}}
                <button class="w-10 h-10 shrink-0 rounded-full bg-[#19A7CE] flex items-center justify-center text-white font-bold">
                    S
                </button>

            </header>


            {{-- ================= CONTENT ================= --}}
            <section class="px-8 lg:px-10 py-8">

                {{-- Title --}}
                <h1 class="text-[28px] leading-[36px] font-bold text-[#131B2E]">
                    Tambah Pesan
                </h1>


                {{-- Form --}}
                <form action="#" method="POST" class="mt-8 max-w-[1072px]">

                    @csrf

                    {{-- Kepada --}}
                    <div class="max-w-[420px]">

                        <label for="penerima"
                            class="block mb-2 text-[14px] leading-[22px] font-bold text-[#283044]">
                            Untuk
                        </label>

                        <select id="penerima"
                            name="penerima"
                            class="w-full h-[38px] px-3 border border-[rgba(148,163,184,0.5)] rounded-md outline-none bg-white text-[14px] text-[#283044] focus:border-[#19A7CE]">

                            <option value="">
                                Pilih penerima
                            </option>

                            <option value="panji">
                                Panji
                            </option>

                            <option value="samuel">
                                Samuel Exlesiano Sigalingging
                            </option>

                        </select>

                    </div>


                    {{-- Pesan --}}
                    <div class="mt-6 max-w-[420px]">

                        <label for="pesan"
                            class="block mb-2 text-[14px] leading-[22px] font-bold text-[#283044]">
                            Pesan
                        </label>

                        <textarea id="pesan"
                            name="pesan"
                            rows="5"
                            placeholder="Ketik pesan anda"
                            class="w-full px-3 py-2 border border-[rgba(148,163,184,0.5)] rounded-md outline-none resize-none text-[14px] leading-[22px] text-[#283044] placeholder:text-[#64748B] focus:border-[#19A7CE]"></textarea>

                    </div>


                    {{-- Link --}}
                    <div class="mt-6 max-w-[662px]">

                        <label for="link"
                            class="block mb-2 text-[14px] leading-[22px] font-bold text-[#283044]">
                            Link
                        </label>

                        <input id="link"
                            name="link"
                            type="url"
                            placeholder="Masukkan link jika diperlukan"
                            class="w-full h-[42px] px-3 border border-[rgba(148,163,184,0.5)] rounded-md outline-none text-[14px] text-[#283044] placeholder:text-[#64748B] focus:border-[#19A7CE]">

                    </div>


                    {{-- Buttons --}}
                    <div class="flex flex-col-reverse sm:flex-row gap-4 mt-8">

                        {{-- Batal --}}
                        <a href="/manajemen-pesan"
                            class="w-full sm:w-[148px] h-[36px] rounded-md border border-[#19A7CE] text-[#19A7CE] flex items-center justify-center text-sm font-bold hover:bg-[#19A7CE] hover:text-white transition">

                            Batal

                        </a>


                        {{-- Kirim --}}
                        <button type="submit"
                            class="w-full sm:w-[148px] h-[36px] rounded-md bg-[#19A7CE] text-white text-sm font-bold hover:bg-[#146C94] transition">

                            Kirim

                        </button>

                    </div>

                </form>

            </section>

        </main>

    </div>

</body>

</html>