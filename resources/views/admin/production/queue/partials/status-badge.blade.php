@php
    $variant = $variant ?? 'neutral';
    $badgeClass = match ($variant) {
        'success' => 'erp-badge--success',
        'danger' => 'erp-badge--danger',
        'warning' => 'erp-badge--warning',
        'info' => 'erp-badge--info',
        'draft' => 'erp-badge--draft',
        default => 'erp-badge--neutral',
    };
@endphp

<span class="erp-badge {{ $badgeClass }} text-xs whitespace-nowrap">{{ $label }}</span>
