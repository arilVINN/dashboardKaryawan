@php
    $isCompact = $compact ?? false;
    $cellPadding = $isCompact ? 'px-4 py-3' : 'px-6 py-4';

    $dummyDivisi = [
        [
            'id' => 1,
            'kode' => 'DIV-IT-01',
            'nama' => 'Teknologi Informasi & Komunikasi',
            'status' => 'Aktif', 
            
        ],
        [
            'id' => 2,
            'kode' => 'DIV-HR-02',
            'nama' => 'Human Capital Management (HRD)',
            'status' => 'Aktif',
        ],
        [
            'id' => 3,
            'kode' => 'DIV-FIN-03',
            'nama' => 'Finance & Accounting',
            'status' => 'Aktif',
        ],
        [
            'id' => 4,
            'kode' => 'DIV-MKT-04',
            'nama' => 'Pemasaran & Kemitraan',
            'status' => 'Non-aktif',
        ],
    ];
@endphp


<div class="flex flex-col gap-3 w-full mt-2">
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold text-slate-800">Manajemen Divisi</h2>
        <button onclick="bukaModalDivisi()" class="text-sm px-4 py-2 bg-[#0097B2] text-white rounded-lg font-medium hover:bg-[#008199] transition">
            Tambah
        </button>
    </div>
    
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
        <div class="{{ $isCompact ? 'overflow-hidden' : 'overflow-x-auto' }}">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Kode Divisi</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Nama Divisi</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap">Status</th>
                        <th class="{{ $cellPadding }} font-medium whitespace-nowrap text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($dummyDivisi as $divisi)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="{{ $cellPadding }} font-semibold text-slate-800 whitespace-nowrap">
                                {{ $divisi['kode'] }}
                            </td>
                            <td class="{{ $cellPadding }} text-slate-700">
                                {{ $divisi['nama'] }}
                            </td>

                            <td class="{{ $cellPadding }} whitespace-nowrap">
                                @if($divisi['status'] == 'Aktif')
                                    <span class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">
                                        {{ $divisi['status'] }}
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-500 rounded-full text-xs font-bold">
                                        {{ $divisi['status'] }}
                                    </span>
                                @endif
                            </td>

                            {{-- Kolom Aksi --}}
                            <td class="{{ $cellPadding }} whitespace-nowrap text-center ml-10 text-xs">
                                <a href="{{ url('/hrd/detailDivisi/' . $divisi['id']) }}" 
                                   class="text-[#0097B2] hover:text-[#008199] font-medium hover:underline mr-3">
                                    Selengkapnya
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalTambahDivisi" class="opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center transition-opacity duration-300">
    

    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupModalDivisi()"></div>


    <div id="modalBox" class="transform scale-95 translate-y-4 relative bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden transition-all duration-300">
        
        <div class="flex items-center justify-between p-4 border-b border-dashed border-gray-300">
            <div class="flex items-center gap-3">
                <div class="bg-cyan-100 p-1.5 rounded text-cyan-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900">Tambah Divisi</h3>
            </div>
            
            <button onclick="tutupModalDivisi()" class="text-slate-400 hover:text-red-500 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form action="#" method="POST" class="p-5">
            @csrf 
            
            <div class="mb-6">
                <label class="block text-xs font-bold text-slate-500 mb-2">1. NAMA DIVISI</label>
                <input type="text" name="nama_divisi" class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-[#004A65] focus:border-[#004A65] outline-none text-sm placeholder-gray-400" placeholder="BODY" required>
            </div>

            <div class="flex justify-between items-center mt-2">
                <button type="button" onclick="tutupModalDivisi()" class="px-5 py-1.5 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">
                    Batal
                </button>
                
                <button type="submit" class="px-4 py-1.5 bg-[#004A65] text-white text-sm font-medium rounded-md hover:bg-[#003347] transition flex items-center gap-1">
                    Tambah
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>


<script>
    const modalDivisi = document.getElementById('modalTambahDivisi');
    const modalBox = document.getElementById('modalBox');

    function bukaModalDivisi() {
        modalDivisi.classList.remove('opacity-0', 'pointer-events-none');
        
        modalBox.classList.remove('scale-95', 'translate-y-4');
        modalBox.classList.add('scale-100', 'translate-y-0');
    }

    function tutupModalDivisi() {
        modalDivisi.classList.add('opacity-0', 'pointer-events-none');
        
        modalBox.classList.remove('scale-100', 'translate-y-0');
        modalBox.classList.add('scale-95', 'translate-y-4');
    }
</script>