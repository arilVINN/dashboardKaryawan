@props([
    'event',
    'suffix' => '',
])

@php
    $domId = 'content-event-'.$event['id'].($suffix !== '' ? '-'.$suffix : '');

    $status = strtolower(trim((string) ($event['status'] ?? '')));
    $palette = \App\Support\StatusPalette::content($status);
    $isRevisi = in_array($status, ['revisi', 'menunggu'], true);

    if ($isRevisi) {
        // Revisi keeps the dark grey card treatment so it stands apart.
        $rowStyle = 'background-color: #4B5563; border-color: #000000;';
        $titleStyle = 'color: #F9FAFB;';
        $metaStyle = 'color: #E5E7EB;';
    } elseif ($palette) {
        $rowStyle = 'background-color: '.$palette['bg'].'; border-color: '.$palette['border'].';';
        $titleStyle = 'color: #172B36;';
        $metaStyle = 'color: #4B5563;';
    } else {
        $rowStyle = 'background-color: #FFFFFF; border-color: #DDE4EA;';
        $titleStyle = 'color: #172B36;';
        $metaStyle = 'color: #4B5563;';
    }
@endphp

<button id="{{ $domId }}"
    {{ $attributes->merge([
        'type' => 'button',
        'class' => 'content-event-row flex w-full cursor-pointer flex-col gap-1.5 rounded-lg border px-2.5 py-2 text-left transition hover:border-[#C3CFD9] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]',
    ]) }}
    style="{{ $rowStyle }}"
    data-status="{{ $event['status'] }}"
    wire:click="openEvent({{ $event['id'] }})"
    aria-label="Buka event {{ $event['title'] }}">
    <span class="flex min-w-0 items-center gap-2">
        <span class="min-w-0 flex-1 truncate text-[13px] font-semibold leading-snug" style="{{ $titleStyle }}">{{ $event['title'] }}</span>
        @if (! empty($event['time_label']))
            <span class="shrink-0 text-[11px] font-medium leading-tight" style="{{ $metaStyle }} font-variant-numeric: tabular-nums;">{{ $event['time_label'] }}</span>
        @endif
    </span>

    <span class="flex flex-wrap items-center gap-1.5">
        <x-content-type-tag :name="$event['type_name']" :fill="$event['type_fill']" :border="$event['type_border']" quiet />
        <x-status-badge variant="content" dot solid :status="$event['status']" />
    </span>
</button>
