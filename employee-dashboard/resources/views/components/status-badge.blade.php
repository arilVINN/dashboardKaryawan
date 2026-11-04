@props(['status', 'label' => null, 'short' => false])

@php
    $palette = \App\Support\StatusPalette::for($status);
    $text = $label ?? ($palette ? ($short ? $palette['short'] : $palette['label']) : $status);
@endphp

@if ($palette)
    <span
        {{ $attributes->merge(['class' => 'inline-flex w-fit items-center gap-1.5 rounded-full border px-2 py-0.5 text-[11px] text-[#172B36]']) }}
        style="background-color: {{ $palette['bg'] }}; border-color: {{ $palette['border'] }};"
        title="{{ $label ?? $palette['label'] }}">
        <span class="h-1.5 w-1.5 shrink-0 rounded-full" style="background-color: {{ $palette['dot'] }}"></span>
        {{ $text }}
    </span>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex w-fit items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] text-[#172B36]']) }}>
        {{ $text }}
    </span>
@endif
