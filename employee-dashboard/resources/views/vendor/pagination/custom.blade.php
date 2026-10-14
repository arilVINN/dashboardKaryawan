@if ($paginator->hasPages())
    <nav aria-label="Navigasi halaman" data-polish-pagination class="flex flex-wrap items-center justify-center gap-1.5">
        @if ($paginator->onFirstPage())
            <span class="inline-flex h-9 items-center rounded-lg border border-slate-200 px-3 text-sm text-slate-300 select-none">Sebelumnya</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                class="inline-flex h-9 items-center rounded-lg border border-slate-200 px-3 text-sm text-slate-600 hover:border-[#004A65] hover:text-[#004A65] transition">Sebelumnya</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-sm text-slate-400 select-none">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-[#004A65] border border-[#004A65] text-sm font-semibold text-white">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-sm text-slate-600 hover:border-[#004A65] hover:text-[#004A65] transition">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                class="inline-flex h-9 items-center rounded-lg border border-slate-200 px-3 text-sm text-slate-600 hover:border-[#004A65] hover:text-[#004A65] transition">Berikutnya</a>
        @else
            <span class="inline-flex h-9 items-center rounded-lg border border-slate-200 px-3 text-sm text-slate-300 select-none">Berikutnya</span>
        @endif
    </nav>
@endif
