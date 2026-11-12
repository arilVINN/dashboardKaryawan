@props([
    'name',
    'fill' => '#F1F5F9',
    'border' => '#CBD5E1',
    'quiet' => false,
])

@php
    // Cards use the form pill's border colour as their background so both
    // palettes match; forms keep the lighter fill.
    $background = $quiet ? $border : $fill;
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex h-[22px] w-fit items-center rounded-md border px-2 text-[11px] font-semibold']) }}
    style="background-color: {{ $background }}; border-color: {{ $border }}; color: #172B36;">
    {{ $name }}
</span>
