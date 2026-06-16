@props(['status'])

@php
    $value = is_object($status) && property_exists($status, 'value') ? $status->value : (string) $status;
    $label = str($value)->headline();
    $tone = match (strtolower($value)) {
        'sent', 'viewed', 'submitted', 'in_progress', 'in progress' => 'info',
        'accepted', 'approved', 'posted', 'delivered', 'closed', 'paid' => 'success',
        'rejected', 'cancelled', 'declined', 'overdue' => 'danger',
        'revision_requested', 'revision requested', 'pending', 'pending_approval', 'pending approval' => 'warning',
        default => 'neutral',
    };
@endphp

<span {{ $attributes->merge(['class' => 'client-badge client-badge--'.$tone]) }}>{{ $label }}</span>
