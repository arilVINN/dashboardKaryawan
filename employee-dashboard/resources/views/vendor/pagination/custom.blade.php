@if ($paginator->hasPages())
    <nav aria-label="Navigasi halaman" data-polish-pagination class="flex items-center justify-center gap-1.5 py-2">
        @if ($paginator->onFirstPage())
            <span class="px-3 py-1.5 text-sm text-slate-300 border border-slate-200 rounded-md select-none">&laquo;</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1.5 text-sm text-slate-600 border border-slate-200 rounded-md hover:border-[#004A65] hover:text-[#004A65] transition">&laquo;</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-3 py-1.5 text-sm text-slate-400 select-none">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="px-3 py-1.5 text-sm font-semibold text-white bg-[#004A65] border border-[#004A65] rounded-md">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="px-3 py-1.5 text-sm text-slate-600 border border-slate-200 rounded-md hover:border-[#004A65] hover:text-[#004A65] transition">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-1.5 text-sm text-slate-600 border border-slate-200 rounded-md hover:border-[#004A65] hover:text-[#004A65] transition">&raquo;</a>
        @else
            <span class="px-3 py-1.5 text-sm text-slate-300 border border-slate-200 rounded-md select-none">&raquo;</span>
        @endif
    </nav>
@endif
