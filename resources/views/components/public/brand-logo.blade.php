@props([
    'size' => 'md',
])

@php
    $sizeClasses = [
        'sm' => 'h-8 w-8',
        'md' => 'h-10 w-10',
        'lg' => 'h-12 w-12',
    ];
@endphp

<img
    src="{{ url(config('site.local.logo')) }}"
    alt=""
    {{ $attributes->merge(['class' => ($sizeClasses[$size] ?? $sizeClasses['md']) . ' shrink-0 object-contain']) }}
    width="40"
    height="40"
    decoding="async"
    aria-hidden="true"
>
