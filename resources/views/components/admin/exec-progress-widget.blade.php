@props([
    'label',
    'value',
    'percent' => null,
    'variant' => 'default',
])

@php
    $barClass = match ($variant) {
        'success' => 'exec-progress__bar--success',
        'warning' => 'exec-progress__bar--warning',
        'danger' => 'exec-progress__bar--danger',
        default => '',
    };
    $pct = $percent !== null ? min(100, max(0, (int) $percent)) : null;
@endphp

<div {{ $attributes->merge(['class' => 'exec-progress-widget']) }}>
    <div class="exec-progress-widget__head">
        <span class="exec-progress-widget__label">{{ $label }}</span>
        <span class="exec-progress-widget__value">{{ $value }}</span>
    </div>
    @if ($pct !== null)
        <div class="exec-progress__track" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
            <div class="exec-progress__bar {{ $barClass }}" style="width: {{ $pct }}%"></div>
        </div>
    @endif
</div>
