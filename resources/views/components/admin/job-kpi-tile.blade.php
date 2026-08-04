@props([
    'label',
    'value',
    'theme' => 'slate',
    'emphasis' => false,
])

<div {{ $attributes->class([
    'job-360-kpi-tile',
    'job-360-kpi-tile--'.$theme,
    'job-360-kpi-tile--emphasis' => $emphasis,
]) }}>
    <div class="job-360-kpi-tile__label">{{ $label }}</div>
    <div class="job-360-kpi-tile__value">{{ $value }}</div>
</div>
