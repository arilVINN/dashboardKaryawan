@props(['status', 'label' => null, 'short' => false, 'variant' => 'default', 'dot' => false, 'size' => 'sm'])

@php
    $palette = $variant === 'content'
        ? \App\Support\StatusPalette::content($status)
        : \App\Support\StatusPalette::for($status);

    $text = $label ?? ($palette ? ($short ? ($palette['short'] ?? $palette['label']) : $palette['label']) : $status);
    $sizeClass = $size === 'md' ? 'text-xs' : 'text-[11px]';
@endphp

@if ($palette)
    <span
        {{ $attributes->merge(['class' => "inline-flex w-fit items-center gap-1.5 rounded-full border px-2 py-0.5 font-semibold {$sizeClass}"]) }}
        style="background-color: {{ $palette['bg'] }}; border-color: {{ $palette['border'] }}; color: {{ $palette['text'] }};"
        title="{{ $label ?? $palette['label'] }}">
        @if ($dot && isset($palette['dot']))
            <span class="h-[7px] w-[7px] shrink-0 rounded-full" style="background-color: {{ $palette['dot'] }};" aria-hidden="true"></span>
        @endif
        {{ $text }}
    </span>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex w-fit items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] text-[#172B36]']) }}>
        {{ $text }}
    </span>
@endif
