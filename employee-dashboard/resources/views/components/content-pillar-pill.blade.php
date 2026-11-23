@props([
    'name',
    'color' => '#94A3B8',
    'text' => '#FFFFFF',
])

@if (trim((string) $name) !== '')
    <span {{ $attributes->merge(['class' => 'inline-flex h-[22px] w-fit items-center rounded-md border px-2 text-[11px] font-semibold']) }}
        style="background-color: {{ $color }}; border-color: {{ $color }}; color: {{ $text }};">
        {{ $name }}
    </span>
@endif
