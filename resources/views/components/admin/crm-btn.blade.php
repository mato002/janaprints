@props([
    'variant' => 'outline',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
    $classes = collect([
        'crm-360__btn',
        'crm-360__btn--' . $variant,
    ]);
    if ($size === 'sm') {
        $classes->push('crm-360__btn--sm');
    }
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes->join(' ')]) }}>
        @isset($icon){{ $icon }}@endisset
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes->join(' ')]) }}>
        @isset($icon){{ $icon }}@endisset
        {{ $slot }}
    </button>
@endif
