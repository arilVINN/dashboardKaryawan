@props([
    'event',
    'showDate' => true,
    'lines' => 1,
    'openable' => true,
    'suffix' => '',
])

@php
    $domId = 'content-event-'.$event['id'].($suffix !== '' ? '-'.$suffix : '');
@endphp

<button id="{{ $domId }}"
    {{ $attributes->merge([
        'type' => 'button',
        'class' => 'flex w-full cursor-pointer flex-col gap-1 rounded-lg border border-[#DDE4EA] bg-white p-2 text-left shadow-[0_1px_2px_rgba(15,23,42,0.05)] transition hover:border-[#C3CFD9] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94]',
    ]) }}
    @if ($openable) wire:click="openEvent({{ $event['id'] }})" @endif
    aria-label="Buka event {{ $event['title'] }}">
    <span class="text-[13px] font-semibold leading-snug text-[#172B36] {{ $lines > 1 ? 'line-clamp-2' : 'block truncate' }}">
        {{ $event['title'] }}
    </span>

    @if ($showDate)
        <span class="text-[12px] leading-tight text-[#4B5563]">{{ $event['date_label'] }}</span>
    @endif

    @if (! empty($event['time_label']))
        <span class="text-[12px] leading-tight text-[#4B5563]">{{ $event['time_label'] }}</span>
    @endif

    <span class="mt-0.5 flex flex-col items-start gap-1">
        <x-content-type-tag :name="$event['type_name']" :fill="$event['type_fill']" :border="$event['type_border']" />
        <x-status-badge variant="content" dot :status="$event['status']" />
    </span>
</button>
