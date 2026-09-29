@props([
    'action' => '#',
    'logo' => asset('gambar/silindo.png'),
    'bgCity' => asset('gambar/bglogin.png'),
])

<div
    class="relative z-10 w-full max-w-4xl bg-white rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2 min-h-[540px]">

    <div
        class="relative bg-gradient-to-br from-[#2E4D70] from-0% via-[#19A7CE] via-50% to-[#AFD3E2] to-100% p-8 flex flex-col justify-between overflow-hidden">

        <div class="relative z-10 flex items-center gap-3.5 scale-110 origin-left">
            <img src="{{ $logo }}" alt="Logo Silindo"
                class="w-14 h-14 object-contain drop-shadow bg-gradient-to-r from-[#2E4D70] to-[#AFD3E2] rounded-full p-1 shrink-0">
            <div class="text-white">
                <h2 class="text-xl font-bold leading-tight tracking-wide">PT SILINDO</h2>
                <p class="text-xs font-bold text-[#23404d]">PT Sinergi Ilmiah Indonesia</p>
            </div>
        </div>

        <div class="absolute inset-0 bg-gradient-to-l from-white/40 via-white/10 to-transparent pointer-events-none z-1"></div>

        <div
            class="absolute -right-0 top-24 w-10 h-[50%] rotate-40 pointer-events-none z-2 
                bg-gradient-to-b from-white/50 to-transparent">
        </div>
        <div
            class="absolute -right-0 -top-70 w-10 h-[150%] rotate-40 pointer-events-none z-2 
                bg-gradient-to-b from-white/50 to-transparent">
        </div>

        <div class="absolute inset-x-0 bottom-0 h-50 w-full pointer-events-none">
            <img src="{{ $bgCity }}" alt="City Illustration"
                class="w-full h-full object-cover object-bottom opacity-80">
        </div>
    </div>

    <div class="p-8 sm:p-12 flex flex-col justify-center bg-white">

        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Login Form</h1>
            <p class="text-xs font-semibold text-slate-700 mt-1">PT Silindo</p>
            <p class="text-[11px] text-slate-500 mt-2 leading-relaxed">
                Selamat datang di dashboard workflow PT Silindo,<br>
                Silahkan melakukan login
            </p>
        </div>

        <form action="{{ $action }}" method="POST" class="space-y-5 max-w-sm mx-auto w-full">
            @csrf
            <div>
                <label class="block text-sm font-bold text-slate-900 mb-1.5">Username</label>
                <input type="text" name="username" placeholder="Masukan username anda" required
                    class="w-full px-4 py-2.5 border border-slate-300 rounded-full text-xs text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0097B2] focus:border-transparent transition">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-900 mb-1.5">Password</label>
                <input type="password" name="password" placeholder="Masukan Password anda" required
                    class="w-full px-4 py-2.5 border border-slate-300 rounded-full text-xs text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0097B2] focus:border-transparent transition">
            </div>

            <div class="pt-4 flex justify-center">
                <button type="submit"
                    class="w-36 py-2.5 bg-[#0097B2] hover:bg-[#008199] text-white text-xs font-semibold rounded-lg shadow-sm hover:shadow transition">
                    Login
                </button>
            </div>
        </form>

    </div>

</div>
