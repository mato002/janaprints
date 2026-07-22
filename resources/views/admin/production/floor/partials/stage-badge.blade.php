@php
    $stageKey = $stage ?? '';
    $classes = match ($stageKey) {
        'waiting' => 'production-floor-stage--waiting',
        'on_press' => 'production-floor-stage--printing',
        'at_vendor' => 'production-floor-stage--vendor',
        'finishing' => 'production-floor-stage--finishing',
        'qc' => 'production-floor-stage--qc',
        'ready' => 'production-floor-stage--dispatch',
        'out' => 'production-floor-stage--completed',
        'on_hold' => 'production-floor-stage--hold',
        default => 'production-floor-stage--waiting',
    };
@endphp
<span class="production-floor-stage {{ $classes }}" @if (! empty($stageKey)) data-stage="{{ $stageKey }}" @endif>
    {{ $label ?? '—' }}
</span>
