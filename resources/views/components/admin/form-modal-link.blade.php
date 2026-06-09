@props([
    'href',
    'variant' => 'primary',
])

@php
    $classes = match ($variant) {
        'secondary' => 'erp-btn-secondary',
        default => 'erp-btn-primary',
    };
@endphp

<a
    href="{{ $href }}"
    data-erp-modal-open
    {{ $attributes->merge(['class' => $classes]) }}
>
    {{ $slot }}
</a>
