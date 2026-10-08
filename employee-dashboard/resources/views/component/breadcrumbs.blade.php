@props([
    'parentText' => 'Kembali',
    'parentUrl' => null,
    'currentPage' => 'Detail',
])

@php
    // Prefer an explicit, relevant parent page over browser history.
    $backUrl = $parentUrl ?: url()->previous();
@endphp

<div class="px-8 pt-4">
    <nav aria-label="Breadcrumb" role="navigation">
        <ul class="flex flex-wrap items-center gap-1 text-sm">
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
                <span class="font-semibold text-slate-900" data-breadcrumb-current="{{ $currentPage }}">
                    {{ $currentPage }}
                </span>
            </li>
        </ul>
    </nav>
</div>
