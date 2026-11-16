@props([
    'parentText' => null,
    'parentUrl' => null,
    'currentPage' => 'Detail',
    'currentUrl' => null,
])

@php
    // Prefer an explicit, relevant parent page over browser history.
    $backUrl = $parentUrl ?: url()->previous();
    $currentUrl = $currentUrl ?: url()->full();
    $inline = $inline ?? false;
    $showParent = filled($parentText);
@endphp

<div class="{{ $inline ? 'mb-4' : 'px-8 pt-4' }}">
    <nav aria-label="Breadcrumb" role="navigation">
        <ul class="flex flex-wrap items-center gap-1 text-sm">
            @if ($showParent)
                <li class="inline-flex items-center">
                    <a href="{{ $backUrl }}" data-breadcrumb-parent="{{ $parentText }}"
                        class="font-medium text-gray-700 hover:text-[#0097B2] transition">
                        {{ $parentText }}
                    </a>
                </li>

                <li class="flex items-center">
                    <svg class="w-4 h-4 mx-2 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"></path>
                    </svg>
                </li>
            @endif

            <li class="inline-flex items-center">
                <a href="{{ $currentUrl }}" data-breadcrumb-current="{{ $currentPage }}" aria-current="page"
                    class="font-semibold text-slate-900 hover:text-[#0097B2] transition">
                    {{ $currentPage }}
                </a>
            </li>
        </ul>
    </nav>
</div>
