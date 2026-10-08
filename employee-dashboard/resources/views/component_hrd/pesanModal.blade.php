    {{-- MODAL KIRIM PESAN --}}
    <div id="modalKirimPesanHrd"
         class="opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center transition-opacity duration-300">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupModalPesanHrd()"></div>

        <div id="modalBoxPesanHrd"
             class="transform scale-95 translate-y-4 relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden transition-all duration-300">
            <div class="flex items-center justify-between p-4 border-b border-dashed border-gray-300">
                <div class="flex items-center gap-3">
                    <div class="bg-cyan-100 p-1.5 rounded text-cyan-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Kirim Pesan</h3>
                </div>
                <button type="button" onclick="tutupModalPesanHrd()" class="text-slate-400 hover:text-red-500 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="formKirimPesanHrd" class="p-5 flex flex-col gap-4">
                <div>
                    <label for="penerimaPesanHrd" class="block text-xs font-bold text-slate-500 mb-1">PENERIMA</label>
                    <select name="penerima_id_user" id="penerimaPesanHrd"
                            class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-[#004A65] focus:border-[#004A65] outline-none text-sm text-slate-700"
                            required>
                        <option value="">Memuat penerima...</option>
                    </select>
                </div>

                <div>
                    <label for="isiPesanBaruHrd" class="block text-xs font-bold text-slate-500 mb-1">ISI PESAN</label>
                    <textarea name="deskripsi" id="isiPesanBaruHrd" rows="4"
                              class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-[#004A65] focus:border-[#004A65] outline-none text-sm placeholder-gray-400"
                              placeholder="Tuliskan pesan di sini..." required></textarea>
                </div>

                <div class="flex justify-between items-center mt-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="tutupModalPesanHrd()"
                            class="px-5 py-1.5 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-1.5 bg-[#004A65] text-white text-sm font-medium rounded-md hover:bg-[#003347] transition flex items-center gap-1">
                        Kirim
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const token = sessionStorage.getItem('staff_token');
            const modal = document.getElementById('modalKirimPesanHrd');
            const modalBox = document.getElementById('modalBoxPesanHrd');
            const form = document.getElementById('formKirimPesanHrd');
            const recipientSelect = document.getElementById('penerimaPesanHrd');

            async function loadRecipients() {
                const response = await fetch('/api/hrd/staff', {
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json'
                    }
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Gagal memuat daftar penerima.');

                recipientSelect.replaceChildren(new Option('-- Pilih Penerima --', ''));
                (result.data || []).forEach((employee) => {
                    const account = employee.user;
                    const role = account?.role?.nama_role?.toLowerCase();
                    if (!account || account.id_user === @json(auth()->user()->id_user) || !['staff', 'kadiv'].includes(role)) {
                        return;
                    }

                    const division = employee.divisi?.nama_divisi;
                    const label = [employee.nama, role.toUpperCase(), division].filter(Boolean).join(' - ');
                    recipientSelect.add(new Option(label, account.id_user));
                });
            }

            if (!token) {
                window.location.href = '/login';
                return;
            }

            loadRecipients().catch((error) => {
                console.error('Gagal memuat penerima pesan HRD:', error);
                recipientSelect.replaceChildren(new Option('Penerima gagal dimuat', ''));
                alert(error.message);
            });

            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                const description = document.getElementById('isiPesanBaruHrd').value.trim();
                if (!description) {
                    alert('Isi pesan wajib diisi.');
                    return;
                }

                const payload = {
                    penerima_id_user: recipientSelect.value,
                    tipe: 'pesan',
                    judul_pesan: description.slice(0, 200),
                    deskripsi: description
                };

                try {
                    const response = await fetch('/api/hrd/pesan', {
                        method: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + token,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.message || 'Pesan gagal dikirim.');

                    form.reset();
                    tutupModalPesanHrd();
                    window.location.reload();
                } catch (error) {
                    alert(error.message);
                }
            });

            window.bukaModalPesanHrd = function () {
                modal.classList.remove('opacity-0', 'pointer-events-none');
                modal.classList.add('opacity-100');
                modalBox.classList.remove('scale-95', 'translate-y-4');
                modalBox.classList.add('scale-100', 'translate-y-0');
            };

            window.tutupModalPesanHrd = function () {
                modal.classList.add('opacity-0', 'pointer-events-none');
                modal.classList.remove('opacity-100');
                modalBox.classList.remove('scale-100', 'translate-y-0');
                modalBox.classList.add('scale-95', 'translate-y-4');
            };
        })();
    </script>
</div>
