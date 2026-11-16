@props([
    'column',
    'label',
    'padding' => 'px-6 py-4',
    'default' => null,
])

@php
    // Effective current sort (falls back to the list's default column).
    $currentSort = request('sort', $default);
    $currentDir = request('dir', 'asc');
    $isActive = $currentSort === $column;
    $nextDir = $isActive && $currentDir === 'asc' ? 'desc' : 'asc';

    // Preserve active filters; clicking a header resets pagination.
    $query = request()->query();
    $query['sort'] = $column;
    $query['dir'] = $nextDir;
    unset($query['page']);
    $href = url()->current() . '?' . http_build_query($query);
@endphp

<th class="{{ $padding }} font-medium whitespace-nowrap">
    <a href="{{ $href }}" data-sort="{{ $column }}" data-dir="{{ $nextDir }}"
        class="inline-flex items-center gap-1 hover:text-[#004A65]">
        {{ $label }}
        @if ($isActive && $currentDir === 'asc')
            <span class="text-[#004A65]" aria-hidden="true">&#9650;</span>
        @elseif ($isActive)
            <span class="text-[#004A65]" aria-hidden="true">&#9660;</span>
        @else
            <span class="text-slate-300" aria-hidden="true">&#8597;</span>
        @endif
    </a>
</th>
