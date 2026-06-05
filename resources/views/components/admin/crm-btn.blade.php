@props([
    'variant' => 'outline',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
    $erpVariant = match ($variant) {
        'primary' => 'primary',
        'outline', 'secondary' => 'secondary',
        'ghost' => 'ghost',
        'danger' => 'danger',
        default => 'secondary',
    };
    $crmVariant = match ($variant) {
        'primary' => 'primary',
        'outline', 'secondary' => 'outline',
        'ghost' => 'ghost',
        'danger' => 'danger',
        default => 'outline',
    };
    $classes = collect([
        'erp-btn',
        'crm-360__btn',
        'erp-btn--'.$erpVariant,
        'crm-360__btn--'.$crmVariant,
    ]);
    if ($size === 'sm') {
        $classes->push('erp-btn--sm', 'crm-360__btn--sm');
    }
    if ($size === 'xs') {
        $classes->push('erp-btn--xs');
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
