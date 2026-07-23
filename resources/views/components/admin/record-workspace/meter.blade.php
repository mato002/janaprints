@props([
    'label',
    'value' => 0,
    'display' => null,
    'hint' => null,
])

@php
    $pct = max(0, min(100, (int) $value));
@endphp

<div class="rw-meter">
    <div class="rw-meter__head">
        <span class="rw-meter__label">{{ $label }}</span>
        <span class="rw-meter__value">{{ $display ?? $pct.'%' }}</span>
    </div>
    <div class="rw-meter__track" aria-hidden="true">
        <span class="rw-meter__fill" style="width: {{ $pct }}%"></span>
    </div>
    @if ($hint)
        <p class="rw-meter__hint">{{ $hint }}</p>
    @endif
</div>
