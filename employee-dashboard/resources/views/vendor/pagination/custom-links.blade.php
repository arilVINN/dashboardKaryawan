@if ($paginator->hasPages())
    <nav aria-label="Navigasi halaman" data-polish-pagination class="flex items-center justify-center gap-1.5 py-2">
        @if ($paginator->lastPage() > 5)
            @if ($paginator->onFirstPage())
                <span class="px-3 py-1.5 text-sm text-slate-300 bg-white border border-slate-200 rounded-md select-none">&laquo;</span>
            @else
                <a href="{{ $paginator->url(1) }}"
                    class="px-3 py-1.5 text-sm text-slate-600 bg-white border border-slate-200 rounded-md hover:border-[#004A65] hover:text-[#004A65] transition"
                    aria-label="Halaman pertama">&laquo;</a>
            @endif
        @endif

        @if ($paginator->onFirstPage())
            <span class="px-3 py-1.5 text-sm text-slate-300 bg-white border border-slate-200 rounded-md select-none">&lsaquo;</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
                class="px-3 py-1.5 text-sm text-slate-600 bg-white border border-slate-200 rounded-md hover:border-[#004A65] hover:text-[#004A65] transition"
                aria-label="Halaman sebelumnya">&lsaquo;</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-3 py-1.5 text-sm text-slate-400 bg-white select-none">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="px-3 py-1.5 text-sm font-semibold text-white bg-[#004A65] border border-[#004A65] rounded-md">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}"
                            class="px-3 py-1.5 text-sm text-slate-600 bg-white border border-slate-200 rounded-md hover:border-[#004A65] hover:text-[#004A65] transition"
                            aria-label="Ke halaman {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
                class="px-3 py-1.5 text-sm text-slate-600 bg-white border border-slate-200 rounded-md hover:border-[#004A65] hover:text-[#004A65] transition"
                aria-label="Halaman berikutnya">&rsaquo;</a>
        @else
            <span class="px-3 py-1.5 text-sm text-slate-300 bg-white border border-slate-200 rounded-md select-none">&rsaquo;</span>
        @endif

        @if ($paginator->lastPage() > 5)
            @if ($paginator->currentPage() === $paginator->lastPage())
                <span class="px-3 py-1.5 text-sm text-slate-300 bg-white border border-slate-200 rounded-md select-none">&raquo;</span>
            @else
                <a href="{{ $paginator->url($paginator->lastPage()) }}"
                    class="px-3 py-1.5 text-sm text-slate-600 bg-white border border-slate-200 rounded-md hover:border-[#004A65] hover:text-[#004A65] transition"
                    aria-label="Halaman terakhir">&raquo;</a>
            @endif
        @endif
    </nav>
@endif
