<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $carousel->title }} - Informasi & Kegiatan Divisi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">

    {{-- Sidebar sesuai role user yang login --}}
    @if ($userRole === 'hrd')
        @include('component_hrd.sidebar')
    @elseif ($userRole === 'kadiv')
        @include('component_kadiv.sidebar')
    @elseif (in_array($userRole, ['direktur', 'direksi', 'superadmin'], true))
        @include('component_direktur.sidebar')
    @else
        @include('component.sidebar')
    @endif

    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        @include('component.topbar')

        <main class="flex-1 overflow-y-auto px-6 sm:px-8 pt-6 pb-8">
            <div class="w-full">

                {{-- Breadcrumb --}}
                @include('component.breadcrumbs', [
                    'parentText' => 'Informasi & Kegiatan Divisi',
                    'parentUrl'  => $dashboardUrl,
                    'currentPage'=> 'Detail Kegiatan',
                    'inline'     => true,
                ])

                {{-- Alert Flash --}}
                @if (session('success'))
                    <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-xl flex items-center justify-between">
                        <span>{{ session('success') }}</span>
                        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold ml-2">&times;</button>
                    </div>
                @endif

                {{-- Card Konten --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 md:p-8 w-full">

                    @php
                        $badgeClasses = match (strtolower($carousel->category ?? '')) {
                            'event'       => 'bg-sky-100 text-sky-700',
                            'quotes'      => 'bg-blue-100 text-blue-700',
                            'pengumuman'  => 'bg-rose-100 text-rose-700',
                            default       => 'bg-slate-100 text-slate-700',
                        };
                        $formattedDate = $carousel->date
                            ? \Carbon\Carbon::parse($carousel->date)->locale('id')->isoFormat('D MMMM Y')
                            : ($carousel->created_at ? $carousel->created_at->locale('id')->isoFormat('D MMMM Y') : '-');
                    @endphp

                    {{-- Layout 2 kolom: gambar kiri, konten kanan --}}
                    <div class="flex flex-col md:flex-row gap-6">

                        {{-- Kolom Kiri: Gambar --}}
                        @if ($carousel->image)
                            <div class="md:w-80 lg:w-96 shrink-0">
                                <div class="rounded-xl overflow-hidden bg-slate-100 w-full h-56 md:h-full md:min-h-[220px]">
                                    <img src="{{ asset('storage/' . $carousel->image) }}"
                                         alt="{{ $carousel->title }}"
                                         class="w-full h-full object-cover">
                                </div>
                            </div>
                        @endif

                        {{-- Kolom Kanan: Badge, Judul, Meta, Deskripsi --}}
                        <div class="flex-1 min-w-0">

                            {{-- Badge --}}
                            <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full {{ $badgeClasses }} mb-3">
                                {{ $carousel->category }}
                            </span>

                            {{-- Judul --}}
                            <h1 class="text-xl font-semibold leading-snug text-[#172B36] mb-1">
                                {{ $carousel->title }}
                            </h1>

                            {{-- Meta: tanggal • pembuat • divisi --}}
                            <p class="text-sm text-[#4B5563] mb-4">
                                {{ $formattedDate }} &nbsp;•&nbsp; {{ $creatorName }} &nbsp;•&nbsp; {{ $divisionName }}
                            </p>

                            {{-- Deskripsi --}}
                            <p class="text-sm font-normal leading-[1.6] text-[#172B36]">
                                {!! nl2br(e($description)) !!}
                            </p>

                        </div>
                    </div>

                    {{-- Like Button & Watermark (bawah, full width) --}}
                    <div class="mt-6 pt-5 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                        <div class="flex items-center gap-3">
                            <form id="like-form" action="{{ route('carousel.like', $carousel->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="button"
                                    id="btn-like-toggle"
                                    onclick="handleToggleLike({{ $carousel->id }})"
                                    class="p-1.5 rounded-xl transition duration-150 transform active:scale-90 cursor-pointer flex items-center justify-center focus:outline-none"
                                    title="{{ $isLiked ? 'Batal menyukai' : 'Sukai kegiatan ini' }}">
                                    <svg id="heart-icon"
                                        class="w-8 h-8 transition-colors duration-200 {{ $isLiked ? 'text-rose-500 fill-rose-500' : 'text-rose-400 fill-none' }}"
                                        stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                    </svg>
                                </button>
                            </form>

                            <div>
                                <p id="like-count-display" class="text-sm font-semibold text-slate-800">
                                    {{ $totalLikes }} orang menyukai
                                </p>
                                <p class="text-xs text-slate-400">Klik love untuk sukai Konten ini.</p>
                            </div>
                        </div>

                        <span class="text-xs text-slate-400 sm:text-right">Informasi & Kegiatan Divisi</span>

                    </div>

                </div>
            </div>
        </main>
    </div>

    <script>
        let isLiking = false;
        let isCurrentlyLiked = {{ $isLiked ? 'true' : 'false' }};
        let currentLikesCount = {{ $totalLikes }};

        async function handleToggleLike(carouselId) {
            if (isLiking) return;
            isLiking = true;

            const nextLiked = !isCurrentlyLiked;
            const nextCount = nextLiked ? currentLikesCount + 1 : Math.max(0, currentLikesCount - 1);
            updateLikeUi(nextLiked, nextCount);

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch(`/carousel/${carouselId}/like`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data && typeof data.liked !== 'undefined') {
                        isCurrentlyLiked = data.liked;
                        currentLikesCount = data.total_likes;
                        updateLikeUi(isCurrentlyLiked, currentLikesCount);
                    }
                } else {
                    updateLikeUi(isCurrentlyLiked, currentLikesCount);
                    document.getElementById('like-form').submit();
                }
            } catch (err) {
                updateLikeUi(isCurrentlyLiked, currentLikesCount);
                document.getElementById('like-form').submit();
            } finally {
                isLiking = false;
            }
        }

        function updateLikeUi(liked, count) {
            const heart = document.getElementById('heart-icon');
            const btn   = document.getElementById('btn-like-toggle');
            if (liked) {
                heart.classList.remove('text-rose-400', 'fill-none');
                heart.classList.add('text-rose-500', 'fill-rose-500');
                btn.setAttribute('title', 'Batal menyukai');
            } else {
                heart.classList.remove('text-rose-500', 'fill-rose-500');
                heart.classList.add('text-rose-400', 'fill-none');
                btn.setAttribute('title', 'Sukai kegiatan ini');
            }
            document.getElementById('like-count-display').textContent = `${count} orang menyukai ini`;
        }
    </script>

</body>

</html>
