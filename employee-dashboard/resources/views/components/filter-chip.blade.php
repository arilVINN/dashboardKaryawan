@props(['name', 'label', 'removeUrl'])

<span data-filter-chip="{{ $name }}"
    class="inline-flex items-center gap-1.5 pl-2.5 pr-1.5 py-1 rounded-full bg-cyan-50 text-cyan-700 text-xs font-semibold border border-cyan-100">
    {{ $label }}
    <a href="{{ $removeUrl }}" data-remove-href="{{ $removeUrl }}" title="Hapus filter"
        class="inline-flex items-center justify-center w-4 h-4 rounded-full text-cyan-500 hover:bg-cyan-200 hover:text-cyan-900">&times;</a>
</span>
