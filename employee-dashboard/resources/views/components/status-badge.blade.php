@props(['status', 'label' => null, 'short' => false, 'variant' => 'default', 'dot' => false, 'size' => 'sm', 'solid' => false, 'palette' => null])

@php
    $resolvedPalette = is_array($palette) ? $palette : match (true) {
        $solid && $variant === 'content' => \App\Support\StatusPalette::contentSolid($status),
        $variant === 'content' => \App\Support\StatusPalette::content($status),
        default => \App\Support\StatusPalette::for($status),
    };

    $text = $label ?? ($resolvedPalette ? ($short ? ($resolvedPalette['short'] ?? $resolvedPalette['label']) : $resolvedPalette['label']) : $status);
    $sizeClass = $size === 'md' ? 'text-xs' : 'text-[11px]';
@endphp

@if ($resolvedPalette)
    <span
        {{ $attributes->merge(['class' => "inline-flex w-fit items-center gap-1.5 rounded-full border px-2 py-0.5 font-semibold {$sizeClass}"]) }}
        style="background-color: {{ $resolvedPalette['bg'] }}; border-color: {{ $resolvedPalette['border'] }}; color: {{ $resolvedPalette['text'] }};"
        title="{{ $label ?? $resolvedPalette['label'] }}">
        @if ($dot && isset($resolvedPalette['dot']))
            <span class="h-1.5 w-1.5 shrink-0 rounded-full" style="background-color: {{ $resolvedPalette['dot'] }};" aria-hidden="true"></span>
        @endif
        {{ $text }}
    </span>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex w-fit items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] text-[#172B36]']) }}>
        {{ $text }}
    </span>
@endif
