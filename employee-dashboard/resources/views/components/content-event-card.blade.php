@props([
    'event',
    'showDate' => true,
    'lines' => 1,
    'openable' => true,
    'suffix' => '',
])

@php
    $domId = 'content-event-'.$event['id'].($suffix !== '' ? '-'.$suffix : '');

    $status = strtolower(trim((string) ($event['status'] ?? '')));
    $palette = \App\Support\StatusPalette::content($status);
    $isRevisi = in_array($status, ['revisi', 'menunggu'], true);

    if ($isRevisi) {
        // Revisi uses a clearly grey card (not white) with dark text so it
        // stands apart from both the white Plan card and the coloured blocks.
        $cardStyle = 'background-color: #E2E8F0; border-color: #000000;';
    } elseif ($status === 'plan') {
        $cardStyle = 'background-color: #FFFFFF; border-color: #94A3B8;';
    } elseif ($palette) {
        $cardStyle = 'background-color: '.$palette['bg'].'; border-color: '.$palette['border'].';';
    } else {
        $cardStyle = '';
    }
@endphp

<button id="{{ $domId }}"
    {{ $attributes->merge([
        'type' => 'button',
        'class' => 'flex w-full cursor-pointer flex-col gap-1 rounded-lg border border-[#DDE4EA] bg-white p-2 text-left shadow-[0_1px_2px_rgba(15,23,42,0.05)] transition hover:border-[#C3CFD9] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]',
    ]) }}
    @if ($cardStyle !== '') style="{{ $cardStyle }}" @endif
    data-status="{{ $event['status'] }}"
    @if ($openable) wire:click="openEvent({{ $event['id'] }})" @endif
    aria-label="Buka event {{ $event['title'] }}">
    <span class="text-[13px] font-semibold leading-snug text-[#172B36] {{ $lines > 1 ? 'line-clamp-2' : 'block truncate' }}">
        {{ $event['title'] }}
    </span>

    @if ($showDate)
        <span class="text-[12px] leading-tight text-[#4B5563]">{{ $event['date_label'] }}</span>
    @endif

    <span class="mt-auto flex items-end justify-between gap-2 pt-1">
        <span class="flex min-w-0 flex-col items-start gap-1">
            <span class="flex flex-wrap items-center gap-1">
                <x-content-type-tag :name="$event['type_name']" :fill="$event['type_fill']" :border="$event['type_border']" :text="$event['type_text']" quiet />
                @if (! empty($event['pillar_name']))
                    <x-content-pillar-pill :name="$event['pillar_name']" :color="$event['pillar_color']" :text="$event['pillar_text']" />
                @endif
            </span>
            <x-status-badge variant="content" dot solid :status="$event['status']" />
        </span>

        @if (! empty($event['time_label']))
            <span class="shrink-0 pb-0.5 text-[11px] font-medium leading-none text-[#4B5563]"
                style="font-variant-numeric: tabular-nums;">{{ $event['time_label'] }}</span>
        @endif
    </span>
</button>
