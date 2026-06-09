@props([
    'size' => 'md',
    'full' => false,
    'header' => false,
])

@php
    $markSizeClasses = [
        'sm' => 'h-8 w-8',
        'md' => 'h-10 w-10',
        'lg' => 'h-12 w-12',
    ];

    $fullSizeClasses = [
        'sm' => 'h-8 w-auto max-w-[150px]',
        'md' => 'h-10 w-auto max-w-[190px] sm:max-w-[210px]',
        'lg' => 'h-14 w-auto max-w-[240px] sm:max-w-[260px]',
    ];

    if ($header) {
        $classList = 'public-header__logo shrink-0 object-contain object-left';
    } elseif ($full) {
        $classList = ($fullSizeClasses[$size] ?? $fullSizeClasses['md']) . ' shrink-0 object-contain object-left';
    } else {
        $classList = ($markSizeClasses[$size] ?? $markSizeClasses['md']) . ' shrink-0 object-contain';
    }
@endphp

<img
    src="{{ $brandingLogoUrl }}"
    alt="{{ $full || $header ? config('site.name') : '' }}"
    {{ $attributes->merge(['class' => $classList]) }}
    @if ($full || $header)
        width="280"
        height="132"
    @else
        width="40"
        height="40"
    @endif
    decoding="async"
    @if (! $full && ! $header)
        aria-hidden="true"
    @endif
>
