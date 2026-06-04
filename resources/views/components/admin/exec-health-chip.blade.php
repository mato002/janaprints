@props([
    'label',
    'value',
    'href' => null,
])

@if ($href)
    <a href="{{ $href }}" data-turbo-frame="erp-main" {{ $attributes->merge(['class' => 'exec-health-chip exec-health-chip--link']) }}>
@else
    <span {{ $attributes->merge(['class' => 'exec-health-chip']) }}>
@endif
    <span class="exec-health-chip__label">{{ $label }}</span>
    <span class="exec-health-chip__value">{{ $value }}</span>
@if ($href)
    </a>
@else
    </span>
@endif
