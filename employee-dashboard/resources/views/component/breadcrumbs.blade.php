@props([
    'parentText' => 'Kembali',
    'currentPage' => 'Detail'
])

<div class="flex-0 p-1 pt-5 justify-start ml-5">
    <nav aria-label="Breadcrumb" role="navigation">
        <ul class="flex flex-wrap items-center my-1">
            <!-- Level 1: Halaman Induk (Daftar Pesan) -->
            <li class="inline-flex items-center">
                <a href="{{ url()->previous() }}" class="font-medium text-gray-700 hover:text-[#0097B2] transition">
                    Kembali
                </a>
            </li>

            <!-- Level 2: Halaman yang sedang dibuka (Detail Pesan) -->
            <li class="flex items-center">
                <svg class="w-4 h-4 mx-2 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"></path>
                </svg>
                <span class="font-semibold text-slate-900">
                    Detail
                </span> 
            </li>
        </ul>
    </nav>
</div>