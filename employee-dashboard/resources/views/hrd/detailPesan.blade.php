<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesan HRD - PT Silindo</title>

    @vite('resources/css/app.css')
</head>

<body class="bg-white flex h-screen overflow-hidden">

    @include('component_hrd.sidebar')

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        @include('component.topbar')

        <main class="flex-1 overflow-y-auto px-10 py-8">
            <h1 class="text-2xl font-bold text-slate-900 mb-6">Pesan</h1>

            <div class="space-y-4 bg-white p-5 rounded-3xl w-full drop-shadow-2xl">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 leading-snug">lorem ipsum</h2>
                    <p class="text-sm font-semibold text-slate-900 mt-1">tenggat : 21 sep 2026, 16.00</p>
                    <p class="text-sm font-semibold text-slate-900">status : on going</p>
                </div>

                <div class="text-sm text-slate-800 leading-relaxed text-justify pt-2">
                    <p>
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque pharetra ut lectus vel luctus.
                        Aenean pellentesque sapien placerat justo tincidunt, sit amet laoreet lectus dapibus. Etiam
                        fermentum erat faucibus, auctor nisi vitae, aliquet quam. Cras eget lacus et mauris gravida
                        aliquet. Proin auctor arcu nec dapibus accumsan. Quisque nec mauris leo. Pellentesque eu
                        pellentesque arcu, ac varius diam. Phasellus a libero sem. Pellentesque placerat at odio eu
                        tempor. Ut non eros tortor. Aenean tincidunt sit amet risus vel imperdiet. Vestibulum posuere
                        facilisis urna, quis pulvinar nisl porttitor ut. Ut sollicitudin ullamcorper eros.
                    </p>
                </div>

                <hr class="border-t border-slate-300 my-6">

                <div>
                    <button type="button" id="btn-toggle-balas"
                        class="px-6 py-2.5 bg-[#0097B2] hover:bg-[#008199] text-white text-sm font-semibold rounded-lg shadow-sm transition">
                        Balas Pesan
                    </button>
                </div>

            </div>
            <div id="form-balasan" class="hidden pt-2 space-y-4 max-w-xl transition-all">
                <h2 class="text-xl font-bold text-slate-900">Balasan</h2>

                <form action="#" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-1.5">Pesan</label>
                        <input type="text" name="pesan_balasan" placeholder="Tulis balasan pesan..."
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                    </div>

                    <div class="flex items-center gap-4 pt-1">
                        <button type="reset"
                            class="px-7 py-2 bg-[#d32f2f] hover:bg-[#b71c1c] text-white text-sm font-medium rounded-lg shadow-sm transition">
                            reset
                        </button>

                        <button type="submit"
                            class="px-7 py-2 bg-[#0097B2] hover:bg-[#008199] text-white text-sm font-medium rounded-lg shadow-sm transition">
                            submit
                        </button>
                    </div>
                </form>
            </div>
        </main>

    </div>

    <script>
        const btnToggle = document.getElementById('btn-toggle-balas');
        const formBalasan = document.getElementById('form-balasan');
        const btnBatal = document.getElementById('btn-batal');

        btnToggle.addEventListener('click', () => {
            formBalasan.classList.remove('hidden');
            btnToggle.classList.add('hidden');
        });

    </script>

</body>

</html>
