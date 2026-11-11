@props([
    'name',
    'fill' => '#F1F5F9',
    'border' => '#CBD5E1',
])

<span {{ $attributes->merge(['class' => 'inline-flex h-[22px] w-fit items-center rounded-md border px-2 text-[11px] font-semibold']) }}
    style="background-color: {{ $fill }}; border-color: {{ $border }}; color: #172B36;">
    {{ $name }}
</span>
