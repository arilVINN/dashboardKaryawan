@php
    $isCompact = $compact ?? false;
    $cellPadding = $isCompact ? 'px-4 py-3' : 'px-6 py-4';

    // Data dummy pesan
    $dummyPesan = [
        [
            'id' => 1,
            'isi' => 'Izin bertanya pak, mengenai modul laporan bulanan...',
            'pengirim' => 'Andi Pratama',
            'tanggal' => '05 Oct 2026',
            'status' => 'Belum Dibaca',
        ],
        [
            'id' => 2,
            'isi' => 'Draft UI/UX dashboard sudah dikirim via Google Drive.',
            'pengirim' => 'Rina Maharani',
            'tanggal' => '04 Oct 2026',
            'status' => 'Dibaca',
        ],
        [
            'id' => 3,
            'isi' => 'Dokumentasi REST API sudah diperbarui.',
            'pengirim' => 'Samuel Sigalingging',
            'tanggal' => '01 Oct 2026',
            'status' => 'Dibaca',
        ],
    ];
@endphp

<div class="flex flex-col gap-3 w-full mt-2">
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold text-slate-800">Pesan Masuk</h2>
        <button onclick="bukaModalPesan()" class="text-sm px-4 py-2 bg-[#0097B2] text-white rounded-lg font-medium hover:bg-[#008199] transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
            </svg>
            Kirim Pesan
        </button>
    </div>
    
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
        <div class="{{ $isCompact ? 'overflow-hidden' : 'overflow-x-auto' }}">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Isi Pesan</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">Pengirim</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">Tanggal</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($dummyPesan as $pesan)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="{{ $cellPadding }} font-semibold text-slate-800 truncate max-w-[200px]">
                                {{ $pesan['isi'] }}
                            </td>
                            
                            <td class="{{ $cellPadding }} text-slate-700 text-center whitespace-nowrap">
                                {{ $pesan['pengirim'] }}
                            </td>

                            <td class="{{ $cellPadding }} text-slate-500 text-center whitespace-nowrap">
                                {{ $pesan['tanggal'] }}
                            </td>

                            {{-- Kolom Aksi --}}
                            <td class="{{ $cellPadding }} whitespace-nowrap text-center text-xs">
                                <a href="{{ route('kadiv.detailPesan', $pesan['id']) }}" 
                                   class="px-4 py-1.5 text-[#0e9dc3] hover:text-[#06627b] transition font-medium inline-block">
                                    Lihat
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL KIRIM PESAN --}}
<div id="modalKirimPesan" class="opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center transition-opacity duration-300">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupModalPesan()"></div>

    <div id="modalBoxPesan" class="transform scale-95 translate-y-4 relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden transition-all duration-300">
        
        <div class="flex items-center justify-between p-4 border-b border-dashed border-gray-300">
            <div class="flex items-center gap-3">
                <div class="bg-cyan-100 p-1.5 rounded text-cyan-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900">Kirim Pesan ke Staff</h3>
            </div>
            
            <button onclick="tutupModalPesan()" class="text-slate-400 hover:text-red-500 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form action="#" method="POST" class="p-5 flex flex-col gap-4">
            @csrf 
            
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">PENERIMA</label>
                <select name="penerima_id" class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-[#004A65] focus:border-[#004A65] outline-none text-sm text-slate-700" required>
                    <option value="">-- Pilih Staff Penerima --</option>
                    <option value="1">Samuel Sigalingging</option>
                    <option value="2">Andi Pratama</option>
                    <option value="3">Rina Maharani</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">ISI PESAN</label>
                <textarea name="isi_pesan" rows="4" class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-[#004A65] focus:border-[#004A65] outline-none text-sm placeholder-gray-400" placeholder="Tuliskan pesan kamu di sini..." required></textarea>
            </div>

            <div class="flex justify-between items-center mt-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="tutupModalPesan()" class="px-5 py-1.5 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">
                    Batal
                </button>
                
                <button type="submit" class="px-4 py-1.5 bg-[#004A65] text-white text-sm font-medium rounded-md hover:bg-[#003347] transition flex items-center gap-1">
                    Kirim
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const modalPesan = document.getElementById('modalKirimPesan');
    const modalBoxPesan = document.getElementById('modalBoxPesan');

    function bukaModalPesan() {
        modalPesan.classList.remove('opacity-0', 'pointer-events-none');
        modalBoxPesan.classList.remove('scale-95', 'translate-y-4');
        modalBoxPesan.classList.add('scale-100', 'translate-y-0');
    }

    function tutupModalPesan() {
        modalPesan.classList.add('opacity-0', 'pointer-events-none');
        modalBoxPesan.classList.remove('scale-100', 'translate-y-0');
        modalBoxPesan.classList.add('scale-95', 'translate-y-4');
    }
</script>