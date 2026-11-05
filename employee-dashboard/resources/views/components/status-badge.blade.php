@props(['status', 'label' => null, 'short' => false])

@php
    $palette = \App\Support\StatusPalette::for($status);
    $text = $label ?? ($palette ? ($short ? $palette['short'] : $palette['label']) : $status);
@endphp

@if ($palette)
    <span
        {{ $attributes->merge(['class' => 'inline-flex w-fit items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold']) }}
        style="background-color: {{ $palette['bg'] }}; border-color: {{ $palette['border'] }}; color: {{ $palette['text'] }};"
        title="{{ $label ?? $palette['label'] }}">
        {{ $text }}
    </span>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex w-fit items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] text-[#172B36]']) }}>
        {{ $text }}
    </span>
@endif
