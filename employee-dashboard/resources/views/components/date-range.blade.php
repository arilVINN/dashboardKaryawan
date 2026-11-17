@props(['start', 'end'])

<span {{ $attributes }}>{{ \App\Helpers\FormatTanggal::rentang($start, $end) }}</span>
