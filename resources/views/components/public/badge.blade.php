@props(['variant' => 'magenta'])

@php
    $classes = match ($variant) {
        'magenta' => 'public-badge--magenta',
        'orange' => 'public-badge--orange',
        'cyan' => 'public-badge--cyan',
        'navy' => 'public-badge--navy',
        'light' => 'public-badge--light',
        default => 'public-badge--magenta',
    };
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
