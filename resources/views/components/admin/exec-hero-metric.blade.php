@props([
    'label',
    'value',
    'hint' => null,
    'subtext' => null,
    'href' => null,
    'empty' => false,
])

@if ($href)
    <a href="{{ $href }}" data-turbo-frame="erp-main" data-turbo-action="advance" {{ $attributes->merge(['class' => 'exec-hero-metric exec-hero-metric--link']) }}>
@else
    <div {{ $attributes->merge(['class' => 'exec-hero-metric']) }}>
@endif
    <span class="exec-hero-metric__label">{{ $label }}</span>
    <span class="exec-hero-metric__value @if($empty) exec-hero-metric__value--empty @endif">{{ $value }}</span>
    @if ($subtext)
        <span class="exec-hero-metric__subtext">{{ $subtext }}</span>
    @endif
    @if ($hint)
        <span class="exec-hero-metric__hint">{{ $hint }}</span>
    @endif
@if ($href)
    </a>
@else
    </div>
@endif
