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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16">
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
                        d="M13 5h8m-8 7h8m-8 7h8M3 17l2 2l4-4"/><rect width="6" height="6" x="3" y="4" rx="1">
                    </path>
                </svg>
                Tugas
            </a>

            <a href="#"
                class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-white/40 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2m-3 12H7c-.55 0-1-.45-1-1s.45-1 1-1h10c.55 0 1 .45 1 1s-.45 1-1 1m0-3H7c-.55 0-1-.45-1-1s.45-1 1-1h10c.55 0 1 .45 1 1s-.45 1-1 1m0-3H7c-.55 0-1-.45-1-1s.45-1 1-1h10c.55 0 1 .45 1 1s-.45 1-1 1">
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                        </path>
                    </svg>
                    Keluar
                </button>
            </form>
        </div>
    </div>
</aside>
