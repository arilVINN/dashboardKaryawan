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
                class="w-14 h-14 object-contain drop-shadow bg-white rounded-full p-1 shrink-0">
            <div class="text-white">
                <h2 class="text-xl font-bold leading-tight tracking-wide">PT SILINDO</h2>
                <p class="text-xs font-bold text-[#23404d]">PT Sinergi Ilmiah Indonesia</p>
            </div>
        </div>

        <div class="absolute inset-0 bg-gradient-to-l from-white/40 via-white/10 to-transparent pointer-events-none z-1">
        </div>

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

        <form id="form-login" method="POST" action="{{ $action }}" class="space-y-5 max-w-sm mx-auto w-full">
            @csrf
            <div>
                <label class="block text-sm font-bold text-slate-900 mb-1.5">Username</label>
                <input type="text" name="username" placeholder="Masukan username anda" required
                    class="w-full px-4 py-2.5 border border-slate-300 rounded-full text-xs text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0097B2] focus:border-transparent transition">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-900 mb-1.5">Password</label>
                <!-- Wrapper relative untuk memposisikan ikon mata di dalam input -->
                <div class="relative w-full">
                    <input type="password" name="password" id="password" placeholder="Masukan Password anda" required
                        class="w-full px-4 py-2.5 pr-10 border border-slate-300 rounded-full text-xs text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0097B2] focus:border-transparent transition">

                    <!-- Tombol Ikon Mata -->
                    <button type="button" onclick="togglePassword()"
                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                        <!-- Icon Mata Tertutup (Default) -->
                        <svg id="eye-slash" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88">
                            </path>
                        </svg>

                        <!-- Icon Mata Terbuka (Hidden by default) -->
                        <svg id="eye-open" class="w-4 h-4 hidden" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>

            @php
                $initialLoginError = session('error') ?? (session('forbidden') ?? $errors->first());
            @endphp
            <p id="login-error" class="text-xs text-red-500 text-center {{ $initialLoginError ? '' : 'hidden' }}">
                {{ $initialLoginError }}
            </p>

            <div class="pt-4 flex justify-center">
                <button type="submit" id="btn-login"
                    class="w-36 py-2.5 bg-[#0097B2] hover:bg-[#008199] text-white text-xs font-semibold rounded-lg shadow-sm hover:shadow transition">
                    Login
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    document.getElementById('form-login').addEventListener('submit', async function(e) {
        e.preventDefault();

        const errorEl = document.getElementById('login-error');
        const btnLogin = document.getElementById('btn-login');

        errorEl.classList.add('hidden');
        btnLogin.disabled = true;
        btnLogin.textContent = 'Memproses...';

        try {
            const formData = new FormData(this);
            const response = await fetch('/login', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': formData.get('_token'),
                },
                body: JSON.stringify({
                    _token: formData.get('_token'),
                    username: formData.get('username'),
                    password: formData.get('password'),
                })
            });

            if (response.status === 419) {
                errorEl.textContent = 'Sesi kadaluarsa. Memuat ulang halaman...';
                errorEl.classList.remove('hidden');
                setTimeout(() => window.location.reload(), 1500);
                return;
            }

            const data = await response.json();

            if (response.ok && data.access_token) {
                localStorage.setItem('staff_token', data.access_token);
                sessionStorage.setItem('staff_token', data.access_token);

                // Arahkan berdasarkan role
                const role = (data.role || '').toLowerCase();
                if (role === 'kadiv') {
                    window.location.href = '/kadiv/dashboard';
                } else if (role === 'hrd') {
                    window.location.href = '/hrd/dashboard';
                } else {
                    window.location.href = '/';
                }
            } else {
                errorEl.textContent = data.message || 'Login gagal, periksa kembali username dan password.';
                errorEl.classList.remove('hidden');
            }
        } catch (error) {
            errorEl.textContent = 'Terjadi kesalahan jaringan.';
            errorEl.classList.remove('hidden');
        }

        btnLogin.disabled = false;
        btnLogin.textContent = 'Login';
    });


    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const eyeSlash = document.getElementById('eye-slash');
        const eyeOpen = document.getElementById('eye-open');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeSlash.classList.add('hidden');
            eyeOpen.classList.remove('hidden');
        } else {
            passwordInput.type = 'password';
            eyeSlash.classList.remove('hidden');
            eyeOpen.classList.add('hidden');
        }
    }
</script>
