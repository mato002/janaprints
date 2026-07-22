@php
    $priorityKey = $priority ?? 'normal';
    $classes = match ($priorityKey) {
        'low' => 'production-floor-priority--normal',
        'normal' => 'production-floor-priority--normal',
        'high' => 'production-floor-priority--high',
        'urgent' => 'production-floor-priority--urgent',
        'critical' => 'production-floor-priority--critical',
        default => 'production-floor-priority--normal',
    };
    $icon = match ($priorityKey) {
        'high' => '🟡',
        'urgent' => '🟠',
        'critical' => '🔴',
        default => '🟢',
    };
@endphp
<span class="production-floor-priority {{ $classes }}" @if (! empty($priorityKey)) data-priority="{{ $priorityKey }}" @endif>
    <span class="production-floor-priority__icon" aria-hidden="true">{{ $icon }}</span>
    <span class="production-floor-priority__label">{{ $label ?? ucfirst($priorityKey) }}</span>
</span>
