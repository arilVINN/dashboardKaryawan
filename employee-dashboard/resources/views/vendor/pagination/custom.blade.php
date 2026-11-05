@if ($paginator->hasPages())
    <nav aria-label="Navigasi halaman" data-polish-pagination class="flex items-center justify-center gap-1.5 py-2">
        @if ($paginator->lastPage() > 5)
            @if ($paginator->onFirstPage())
                <span class="px-3 py-1.5 text-sm text-slate-300 bg-white border border-slate-200 rounded-md select-none">&laquo;</span>
            @else
                <button type="button" wire:click="gotoPage(1, '{{ $paginator->getPageName() }}')"
                    wire:loading.attr="disabled"
                    class="px-3 py-1.5 text-sm text-slate-600 bg-white border border-slate-200 rounded-md hover:border-[#004A65] hover:text-[#004A65] transition disabled:opacity-50"
                    aria-label="Halaman pertama">&laquo;</button>
            @endif
        @endif

        @if ($paginator->onFirstPage())
            <span class="px-3 py-1.5 text-sm text-slate-300 bg-white border border-slate-200 rounded-md select-none">&lsaquo;</span>
        @else
            <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')"
                wire:loading.attr="disabled"
                class="px-3 py-1.5 text-sm text-slate-600 bg-white border border-slate-200 rounded-md hover:border-[#004A65] hover:text-[#004A65] transition disabled:opacity-50"
                aria-label="Halaman sebelumnya">&lsaquo;</button>
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
                        <button type="button" wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}"
                            wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                            wire:loading.attr="disabled"
                            class="px-3 py-1.5 text-sm text-slate-600 bg-white border border-slate-200 rounded-md hover:border-[#004A65] hover:text-[#004A65] transition disabled:opacity-50"
                            aria-label="Ke halaman {{ $page }}">{{ $page }}</button>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')"
                wire:loading.attr="disabled"
                class="px-3 py-1.5 text-sm text-slate-600 bg-white border border-slate-200 rounded-md hover:border-[#004A65] hover:text-[#004A65] transition disabled:opacity-50"
                aria-label="Halaman berikutnya">&rsaquo;</button>
        @else
            <span class="px-3 py-1.5 text-sm text-slate-300 bg-white border border-slate-200 rounded-md select-none">&rsaquo;</span>
        @endif

        @if ($paginator->lastPage() > 5)
            @if ($paginator->currentPage() === $paginator->lastPage())
                <span class="px-3 py-1.5 text-sm text-slate-300 bg-white border border-slate-200 rounded-md select-none">&raquo;</span>
            @else
                <button type="button" wire:click="gotoPage({{ $paginator->lastPage() }}, '{{ $paginator->getPageName() }}')"
                    wire:loading.attr="disabled"
                    class="px-3 py-1.5 text-sm text-slate-600 bg-white border border-slate-200 rounded-md hover:border-[#004A65] hover:text-[#004A65] transition disabled:opacity-50"
                    aria-label="Halaman terakhir">&raquo;</button>
            @endif
        @endif
    </nav>
@endif
