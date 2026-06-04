@props([
    'src',
    'alt' => '',
    'fallback' => 'default',
    'class' => '',
    'width' => null,
    'height' => null,
])

@php
    $images = config('public-images');
    $resolved = $images[$src] ?? (str_starts_with((string) $src, 'http') ? $src : ($images['default'] ?? ''));
    $fallbackUrl = $images[$fallback] ?? $images['default'] ?? '';
@endphp

<img
    src="{{ $resolved }}"
    alt="{{ $alt }}"
    @if ($width) width="{{ $width }}" @endif
    @if ($height) height="{{ $height }}" @endif
    loading="lazy"
    decoding="async"
    data-public-media-image
    {{ $attributes->merge(['class' => trim($class)]) }}
    onerror="if(!this.dataset.fallbackApplied){this.dataset.fallbackApplied='1';this.src='{{ $fallbackUrl }}';}"
>
