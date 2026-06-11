@props([
    'slotKey' => null,
    'slot_key' => null,
    'src' => null,
    'fallbackKey' => null,
    'fallback_key' => null,
    'fallback' => 'default',
    'alt' => '',
    'class' => '',
    'width' => null,
    'height' => null,
    'sizes' => null,
    'loading' => 'lazy',
])

@php
    $resolver = app(\App\Services\Website\WebsiteMediaResolver::class);
    $slot = $slot_key ?? $slotKey;
    $fallbackSlot = $fallback_key ?? $fallbackKey ?? $fallback ?? 'default';

    if ($slot) {
        $resolved = $resolver->resolvePath((string) $slot);
        $resolvedAlt = $alt !== '' ? $alt : $resolver->resolveAlt((string) $slot);
    } elseif ($src !== null && $src !== '') {
        $resolved = $resolver->resolveSource((string) $src, (string) $fallbackSlot);
        $resolvedAlt = $alt !== '' ? $alt : $resolver->resolveAltForSource((string) $src, '', '');
    } else {
        $resolved = $resolver->resolvePath((string) $fallbackSlot);
        $resolvedAlt = $alt;
    }

    $fallbackUrl = $resolver->resolvePath((string) $fallbackSlot);
    $loadingAttr = in_array($loading, ['eager', 'lazy'], true) ? $loading : 'lazy';
@endphp

<img
    src="{{ $resolved }}"
    alt="{{ $resolvedAlt }}"
    @if ($width) width="{{ $width }}" @endif
    @if ($height) height="{{ $height }}" @endif
    @if ($sizes) sizes="{{ $sizes }}" @endif
    loading="{{ $loadingAttr }}"
    decoding="async"
    @if ($slot) data-website-media-slot="{{ $slot }}" @endif
    data-public-media-image
    {{ $attributes->merge(['class' => trim($class)]) }}
    onerror="if(!this.dataset.fallbackApplied){this.dataset.fallbackApplied='1';this.src='{{ $fallbackUrl }}';}"
>
