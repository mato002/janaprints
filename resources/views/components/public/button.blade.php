@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
    $classes = match ($variant) {
        'primary' => 'public-btn--primary public-btn--motion',
        'gradient' => 'public-btn--gradient public-btn--motion',
        'outline', 'secondary' => 'public-btn--secondary public-btn--motion-secondary',
        'accent' => 'public-btn--accent public-btn--motion',
        'ghost' => 'public-btn--ghost',
        'ghost-dark' => 'public-btn--ghost-dark public-btn--motion-secondary',
        'outline-light' => 'public-btn--outline-light public-btn--motion-secondary',
        default => 'public-btn--primary public-btn--motion',
    };

    $sizeClass = match ($size) {
        'lg' => 'public-btn--lg',
        'sm' => 'public-btn--sm',
        default => '',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => trim("$classes $sizeClass")]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => trim("$classes $sizeClass")]) }}>
        {{ $slot }}
    </button>
@endif
