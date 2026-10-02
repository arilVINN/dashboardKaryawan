<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Tugas - PT SILINDO</title>

    @vite('resources/css/app.css')
</head>

<body class="bg-white text-[#283044]">

<div class="flex min-h-screen w-full">

    {{-- =========================================================
        SIDEBAR
        Jangan ubah struktur/class sesuai permintaan desain
    ========================================================== --}}
    <aside class="w-64 bg-gradient-l text-slate-300 flex flex-col h-screen border-r border-whiite-30%">

        <div class="bg-gradient-to-b from-[#044564] from-50% to-[#19A7CE] min-h-screen">

            <div class="flex items-center justify-between p-4 bg-white rounded-bl-xl">
                <div class="flex items-center gap-3">
                    <img
                        src="{{ asset('gambar/silindo.png') }}"
                        alt="Logo"
                        class="w-12 h-12 object-contain shrink-0"
                    >

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
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2.5"
                            d="M4 6h16M4 12h16M4 18h16"
                        ></path>
                    </svg>
                </button>
            </div>

            <nav class="flex-1 p-4 pt-8 space-y-3 text-sm">

                <a href="/"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-white/40 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 20v-6h4v6h5v-8h3L12 3L2 12h3v8z"
                        ></path>
                    </svg>
                    Dashboard
                </a>

                <a href="/manajemen-staff"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-white/40 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13 5h8m-8 7h8m-8 7h8M3 17l2 2l4-4"
                        />
                        <rect width="6" height="6" x="3" y="4" rx="1"></rect>
                    </svg>
                    Tugas
                </a>

                <a href="/manajemen-pesan"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-white/40 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2m-3 12H7c-.55 0-1-.45-1-1s.45-1 1-1h10c.55 0 1-.45 1-1s-.45-1-1-1m0-3H7c-.55 0-1-.45-1-1s.45-1 1-1h10c.55 0 1-.45 1-1s-.45-1-1-1"
                        ></path>
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

                    <button
                        type="submit"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2 rounded-lg outline-1 bg-white text-red-400 hover:bg-red-500 hover:text-white text-sm font-medium transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                            ></path>
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
        <header class="h-[79px] bg-white shadow-[0_4px_4px_rgba(0,0,0,0.20)] flex items-center">

            <div class="w-full px-8 flex items-center justify-between gap-6">

                {{-- Search --}}
                <div class="w-full max-w-[693px] h-[51px] border border-[rgba(148,163,184,0.5)] rounded-lg px-2 flex items-center gap-2">

                    <svg
                        class="w-[22px] h-[22px] text-[#64748B] shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m20 20-4-4"></path>
                    </svg>

                    <input
                        type="text"
                        placeholder="Cari"
                        class="w-full outline-none border-0 text-[14px] leading-[22px] text-[#283044] placeholder:text-[#64748B]"
                    >
                </div>

                {{-- Notification + Profile --}}
                <div class="flex items-center gap-6 shrink-0">

                    <button class="w-10 h-10 flex items-center justify-center">
                        <svg
                            class="w-[30px] h-[30px] text-[#283044]"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"
                            ></path>
                        </svg>
                    </button>

                    <div class="w-10 h-10 rounded-full bg-[#D9D9D9] flex items-center justify-center">
                        <svg
                            class="w-4 h-4 rotate-90 text-[#565E74]"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m9 18 6-6-6-6"
                            ></path>
                        </svg>
                    </div>

                </div>
            </div>
        </header>


        {{-- PAGE CONTENT --}}
        <section class="px-8 py-7">

            {{-- Title --}}
            <div class="flex items-center gap-3 mb-7">

                <svg
                    class="w-5 h-5 text-[#565E74]"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m9 18 6-6-6-6"
                    ></path>
                </svg>

                <h2 class="text-[24px] leading-8 font-bold text-black">
                    Manajement Tugas
                </h2>

            </div>


            {{-- Action Button --}}
            <div class="flex justify-end mb-5">

                <button
                    type="button"
                    onclick="openTaskModal()"
                    class="h-9 px-6 rounded-[9px] bg-[#0E9DC3] text-white text-[16px] leading-6 hover:bg-[#0b89aa] transition"
                >
                    Buat Tugas Baru
                </button>

            </div>


            {{-- TABLE --}}
            <div class="w-full overflow-x-auto">

                <div class="min-w-[800px]">

                    {{-- Table Header --}}
                    <div class="grid grid-cols-[40%_20%_22%_18%] h-[30px] bg-[#D9D9D9] text-[#565E74] text-[14px] leading-[22px]">

                        <div class="px-2.5 flex items-center gap-2">
                            <span>Nama Divisi</span>

                            <svg
                                class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-width="1.7"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m8 9 4-4 4 4M16 15l-4 4-4-4"
                                ></path>
                            </svg>
                        </div>

                        <div class="px-2.5 flex items-center">
                            Tugas
                        </div>

                        <div class="px-2.5 flex items-center gap-2">
                            <span>Keterangan</span>

                            <svg
                                class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-width="1.7"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m8 9 4-4 4 4M16 15l-4 4-4-4"
                                ></path>
                            </svg>
                        </div>

                        <div class="px-2.5 flex items-center">
                            Aksi
                        </div>

                    </div>


                    {{-- Table Body --}}
                    <div class="min-h-[500px] bg-white border-x border-b border-slate-100">

                        {{-- Empty State --}}
                        <div class="flex flex-col items-center justify-center min-h-[400px] text-center">

                            <div class="w-12 h-12 rounded-full bg-[#F2F6FA] flex items-center justify-center mb-3">

                                <svg
                                    class="w-6 h-6 text-[#64748B]"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-width="1.7"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"
                                    ></path>
                                </svg>

                            </div>

                            <p class="text-[14px] text-[#64748B]">
                                Belum ada tugas
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </section>

    </main>
</div>


{{-- =========================================================
    MODAL BUAT TUGAS BARU
========================================================== --}}
<div
    id="taskModal"
    class="fixed inset-0 z-50 hidden"
>

    {{-- Overlay --}}
    <div
        class="absolute inset-0 bg-[rgba(86,94,116,0.4)]"
        onclick="closeTaskModal()"
    ></div>


    {{-- Modal --}}
    <div class="relative mx-auto mt-[8vh] w-[min(469px,calc(100%-32px))] max-h-[84vh] overflow-y-auto bg-white rounded-xl shadow-[0_8px_16px_rgba(0,0,0,0.12)]">

        {{-- Modal Header --}}
        <div class="h-auto min-h-[72px] bg-[#F2F6FA] rounded-t-xl px-[30px] py-5 flex items-center justify-between">

            <div class="flex items-center gap-3">

                <div class="w-9 h-9 rounded-lg bg-[#AFD3E2] flex items-center justify-center">

                    <svg
                        class="w-[18px] h-[18px] text-[#146C94]"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"
                        ></path>
                        <path
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M14 2v6h6M12 18v-6m-3 3h6"
                        ></path>
                    </svg>

                </div>

                <h3 class="text-[20px] leading-7 font-bold text-black">
                    Buat Tugas Baru
                </h3>

            </div>

            <button
                type="button"
                onclick="closeTaskModal()"
                class="text-[#565E74] hover:text-black transition"
            >
                <svg
                    class="w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-width="2"
                        stroke-linecap="round"
                        d="M6 6l12 12M18 6 6 18"
                    ></path>
                </svg>
            </button>

        </div>


        {{-- Modal Form --}}
        <form class="px-7 pt-6 pb-5 space-y-5">

            {{-- Judul --}}
            <div>
                <label class="block mb-2 text-[16px] leading-6 font-bold text-[#565E74]">
                    JUDUL TUGAS
                </label>

                <input
                    type="text"
                    placeholder="Masukkan judul tugas"
                    class="w-full h-[37px] px-3 rounded-lg border border-[#CBD5E1] outline-none text-sm focus:border-[#19A7CE]"
                >
            </div>


            {{-- Tujuan --}}
            <div>
                <label class="block mb-2 text-[16px] leading-6 font-bold text-[#565E74]">
                    TUJUAN PENERIMA
                </label>

                <select
                    class="w-full h-[37px] px-3 rounded-lg border border-[#CBD5E1] outline-none text-sm text-[#64748B] focus:border-[#19A7CE]"
                >
                    <option value="">Pilih penerima</option>
                    <option>Divisi</option>
                    <option>Staff</option>
                </select>
            </div>


            {{-- Tanggal Tenggat --}}
            <div>
                <label class="block mb-2 text-[16px] leading-6 font-bold text-[#565E74]">
                    TANGGAL TENGGAT
                </label>

                <input
                    type="date"
                    class="w-full h-[42px] px-3 rounded-lg border border-[#CBD5E1] outline-none text-sm text-[#64748B] focus:border-[#19A7CE]"
                >
            </div>


            {{-- Deskripsi --}}
            <div>
                <label class="block mb-2 text-[16px] leading-6 font-bold text-[#565E74]">
                    DESKRIPSI
                </label>

                <textarea
                    rows="4"
                    placeholder="Tulis deskripsi tugas"
                    class="w-full h-[103px] px-3 py-2 rounded-lg border border-[#CBD5E1] outline-none resize-none text-sm focus:border-[#19A7CE]"
                ></textarea>
            </div>


            {{-- Lampiran --}}
            <div class="bg-[rgba(205,218,236,0.25)] rounded-lg p-5">

                <label class="block mb-4 text-[16px] leading-6 font-bold text-[#565E74]">
                    LAMPIRAN
                </label>

                <label
                    class="min-h-[105px] border border-dashed border-[#AFD3E2] bg-white rounded-lg flex flex-col items-center justify-center cursor-pointer hover:bg-[#F8FBFD] transition"
                >

                    <svg
                        class="w-6 h-6 text-[#146C94] mb-2"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 16V4m0 0L8 8m4-4 4 4M5 12v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6"
                        ></path>
                    </svg>

                    <span class="text-[16px] leading-6 font-bold text-[#565E74]">
                        Unggah File
                    </span>

                    <span class="text-[10px] leading-5 text-[#64748B]">
                        Mendukung File (Maks 5 MB)
                    </span>

                    <input type="file" class="hidden">

                </label>

            </div>


            {{-- Buttons --}}
            <div class="flex justify-end gap-3 pt-1">

                <button
                    type="button"
                    onclick="closeTaskModal()"
                    class="h-8 px-3 rounded-lg bg-[#E8E7E9] text-[#333335] text-[16px] leading-6 hover:bg-[#D9D9D9] transition"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="h-8 px-3 rounded-lg bg-[#004B6C] text-white text-[16px] leading-6 font-bold flex items-center gap-2 hover:bg-[#003B55] transition"
                >
                    Kirim Tugas

                    <svg
                        class="w-3 h-3"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M5 12h14m-5-5 5 5-5 5"
                        ></path>
                    </svg>

                </button>

            </div>

        </form>

    </div>
</div>


<script>
    function openTaskModal() {
        document.getElementById('taskModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeTaskModal() {
        document.getElementById('taskModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeTaskModal();
        }
    });
</script>

</body>
</html>