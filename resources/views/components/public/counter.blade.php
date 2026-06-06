@props([
    'value',
    'suffix' => '',
    'prefix' => '',
    'duration' => 1750,
])

@php
    $formatted = $prefix.number_format((int) $value).$suffix;
@endphp

<span
    data-counter="{{ (int) $value }}"
    @if ($suffix !== '') data-counter-suffix="{{ $suffix }}" @endif
    @if ($prefix !== '') data-counter-prefix="{{ $prefix }}" @endif
    data-counter-duration="{{ $duration }}"
    {{ $attributes }}
>{{ $formatted }}</span>
