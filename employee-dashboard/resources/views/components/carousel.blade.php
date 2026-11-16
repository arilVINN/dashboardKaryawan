@php
    $carousels = $carousels ?? collect();
    $userRole = strtolower(auth()->user()?->role?->nama_role ?? '');
    $isAuthorized = in_array($userRole, ['hrd', 'kadiv']);
    $currentUserId = auth()->user()?->id_user;
    $storeRoute = $userRole === 'hrd' ? 'hrd.carousel.store' : 'carousel.store';
    $destroyRoute = $userRole === 'hrd' ? 'hrd.carousel.destroy' : 'carousel.destroy';
@endphp

<!-- CSS Swiper -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<style>
    .swiper-pagination-bullet {
        width: 24px !important;
        height: 6px !important;
        border-radius: 9999px !important;
        background: #cbd5e1 !important;
        opacity: 1 !important;
        transition: all 0.3s ease;
    }

    .swiper-pagination-bullet-active {
        background: #0284c7 !important;
        width: 32px !important;
    }
</style>

<!-- Alert Sukses / Error -->
@if (session('success'))
    <div
        class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-xl flex items-center justify-between">
        <span>{{ session('success') }}</span>
        <button type="button" onclick="this.parentElement.remove()"
            class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
    </div>
@endif

@if (session('error'))
    <div
        class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm rounded-xl flex items-center justify-between">
        <span>{{ session('error') }}</span>
        <button type="button" onclick="this.parentElement.remove()"
            class="text-rose-500 hover:text-rose-700 font-bold">&times;</button>
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm rounded-xl">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- CONTAINER UTAMA CAROUSEL -->
<div class="w-full my-6">

    <div class="flex justify-between items-center mb-4">
        <div>
            <h2 class="text-base font-bold text-slate-800">Informasi & Kegiatan Divisi</h2>

        </div>

        @if ($isAuthorized)
            <button type="button" onclick="openCarouselModal()"
                class="px-4 py-2 bg-[#0284c7] hover:bg-sky-700 text-white text-xs md:text-sm font-semibold rounded-xl shadow-sm transition-all flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                </svg>
                <span>Tambah Konten</span>
            </button>
        @endif
    </div>

    @if ($carousels->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-center text-slate-400">
            <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.5"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
            </svg>
            <p class="text-sm font-medium text-slate-600">Belum ada konten kegiatan atau pengumuman.</p>
            @if ($isAuthorized)
                <p class="text-xs text-slate-400 mt-1">Klik tombol <span class="font-semibold text-sky-600">"Tambah
                        Konten"</span> untuk menambahkan banner kegiatan baru.</p>
            @endif
        </div>
    @else
        <!-- SWIPER CAROUSEL -->
        <div class="swiper myKadivSwiper pb-10">
            <div class="swiper-wrapper">
                @foreach ($carousels as $item)
                    @php
                        $bgColors = match (strtolower($item->category ?? '')) {
                            'event' => ['bg' => 'from-emerald-600 to-emerald-800', 'badge' => 'bg-emerald-900'],
                            'quotes' => ['bg' => 'from-blue-500 to-blue-700', 'badge' => 'bg-blue-900'],
                            'pengumuman' => ['bg' => 'from-rose-600 to-rose-800', 'badge' => 'bg-rose-900'],
                            default => ['bg' => 'from-sky-600 to-sky-800', 'badge' => 'bg-sky-500'],
                        };
                        $creatorName = $item->user?->karyawan?->nama ?? 'Pengguna tidak diketahui';
                        $divisionName = $item->divisi?->nama_divisi ?? 'Seluruh divisi';
                    @endphp
                    <div class="swiper-slide">
                        <div class="relative h-52 rounded-2xl overflow-hidden shadow-md group">

                            @if ($item->image)
                                <img src="{{ asset('storage/' . $item->image) }}"
                                    class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                    alt="{{ $item->title }}"
                                    onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex';">
                                {{-- Fallback jika gambar gagal load --}}
                                <div
                                    class="absolute inset-0 bg-gradient-to-br {{ $bgColors['bg'] }} hidden items-center justify-center">
                                    <svg class="w-14 h-14 text-white/30" fill="none" stroke="currentColor"
                                        stroke-width="1" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" />
                                    </svg>
                                </div>
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent">
                                </div>
                            @else
                                <div
                                    class="absolute inset-0 bg-gradient-to-br {{ $bgColors['bg'] }} flex flex-col justify-center items-center px-6 text-center">
                                    {{-- Icon dekoratif --}}
                                    @if (strtolower($item->category ?? '') === 'quotes')
                                        <svg class="w-8 h-8 text-white/30 mb-3" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
                                        </svg>
                                    @elseif(strtolower($item->category ?? '') === 'pengumuman')
                                        <svg class="w-8 h-8 text-white/30 mb-3" fill="none" stroke="currentColor"
                                            stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                                        </svg>
                                    @else
                                        <svg class="w-8 h-8 text-white/30 mb-3" fill="none" stroke="currentColor"
                                            stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                        </svg>
                                    @endif

                                    <h3 class="text-white text-base font-bold leading-snug line-clamp-3">
                                        {{ $item->title }}</h3>
                                    <p
                                        class="text-white/70 text-xs mt-2 font-medium flex items-center gap-1 justify-center">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                        {{ $item->date ? \Carbon\Carbon::parse($item->date)->locale('id')->isoFormat('D MMMM Y') : '-' }}
                                    </p>
                                    <p class="text-white/80 text-xs mt-2 font-medium">
                                        Dibuat oleh {{ $creatorName }} · {{ $divisionName }}
                                    </p>
                                </div>
                            @endif

                            {{-- Badge Kategori --}}
                            <div class="absolute top-3 left-3 z-10">
                                <span
                                    class="px-3 py-1 text-xs font-semibold {{ $bgColors['badge'] }} text-white rounded-full shadow-sm">{{ $item->category }}</span>
                            </div>

                            {{-- Tombol Hapus (hover) --}}
                            @if ($userRole === 'hrd' || ($userRole === 'kadiv' && $item->user_id_user === $currentUserId))
                                <div
                                    class="absolute top-3 right-3 z-10 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                    <form action="{{ route($destroyRoute, $item->id) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus konten ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="p-2 bg-white/90 hover:bg-white text-rose-600 rounded-full shadow transition cursor-pointer"
                                            title="Hapus Konten">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0">
                                                </path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            @endif

                            {{-- Judul + tanggal di bawah (hanya untuk card IMAGE) --}}
                            @if ($item->image)
                                <div class="absolute inset-x-0 bottom-0 z-10 p-5">
                                    <p class="text-xs text-slate-300 mb-1 font-medium flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                        <span>{{ $item->date ? \Carbon\Carbon::parse($item->date)->locale('id')->isoFormat('D MMMM Y') : '-' }}</span>
                                    </p>
                                    <h3 class="text-white text-base font-bold leading-snug line-clamp-2">
                                        {{ $item->title }}</h3>
                                    <p class="text-xs text-white/80 mb-1 font-medium">
                                        Dibuat oleh {{ $creatorName }} · {{ $divisionName }}
                                    </p>
                                </div>
                            @endif

                        </div>
                    </div>
                @endforeach
            </div>
            <!-- Indikator Pagination -->
            <div class="swiper-pagination"></div>
        </div>
    @endif
</div>

<!-- MODAL POP-UP FORM TAMBAH KONTEN -->
@if ($isAuthorized)
    <div id="carouselModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden">
        <div
            class="bg-white w-full max-w-lg rounded-2xl shadow-xl overflow-hidden transform transition-all m-4 border border-slate-100">

            <!-- Header Modal -->
            <div class="flex justify-between items-center px-6 py-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-800">Tambah Konten / Kegiatan Baru</h3>
                <button type="button" onclick="closeCarouselModal()"
                    class="text-slate-400 hover:text-slate-600 p-1 rounded-lg cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Form Modal -->
            <form action="{{ route($storeRoute) }}" method="POST" enctype="multipart/form-data"
                class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                @csrf

                <!-- Pilihan Kategori -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kategori Konten</label>
                    <select name="category"
                        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500"
                        required>
                        <option value="Event">Event / Kegiatan</option>
                        <option value="Quotes">Quotes Pagi / Motivasi</option>
                        <option value="Pengumuman">Pengumuman Penting</option>
                    </select>
                </div>

                <!-- Judul / Keterangan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Keterangan/Isi</label>
                    <input type="text" name="title"
                        placeholder="Contoh: Event A/ Selamat pagi/ Pengumuman penting"
                        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500"
                        required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal</label>
                    <input type="date" name="date" value="{{ date('Y-m-d') }}"
                        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500"
                        required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Upload Foto / Gambar Background
                        <span class="ml-1 font-normal text-slate-400 normal-case">(Opsional · maks. 5 MB)</span>
                    </label>
                    <div
                        class="border-2 border-dashed border-slate-300 rounded-xl p-4 text-center hover:bg-slate-50 transition cursor-pointer">
                        <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/jpg"
                            class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 cursor-pointer">
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Kosongkan jika konten berupa teks/quotes saja.</p>
                </div>

                <!-- Footer Modal / Tombol Aksi -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeCarouselModal()"
                        class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-semibold rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2 bg-[#0284c7] hover:bg-sky-700 text-white text-sm font-semibold rounded-xl transition shadow cursor-pointer">
                        Simpan Konten
                    </button>
                </div>
            </form>

        </div>
    </div>
@endif

<!-- SCRIPT SWIPER & MODAL -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.querySelector('.myKadivSwiper')) {
            new Swiper(".myKadivSwiper", {
                slidesPerView: 1,
                spaceBetween: 16,
                slidesPerGroup: 1,
                loop: {{ $carousels->count() > 3 ? 'true' : 'false' }},
                autoplay: {
                    delay: 3500,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                },
                breakpoints: {
                    640: {
                        slidesPerView: 2
                    },
                    1024: {
                        slidesPerView: 3
                    }
                }
            });
        }
    });

    function openCarouselModal() {
        const modal = document.getElementById('carouselModal');
        if (modal) modal.classList.remove('hidden');
    }

    function closeCarouselModal() {
        const modal = document.getElementById('carouselModal');
        if (modal) modal.classList.add('hidden');
    }
</script>
