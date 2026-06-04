@props(['status'])

@php
    $normalized = strtolower(str_replace([' ', '-'], '_', (string) $status));
    $variant = match (true) {
        in_array($normalized, ['active', 'approved', 'completed', 'posted', 'sent', 'open'], true) => 'success',
        in_array($normalized, ['pending', 'pending_approval', 'submitted', 'in_review', 'viewed'], true) => 'warning',
        in_array($normalized, ['draft'], true) => 'draft',
        in_array($normalized, ['rejected', 'cancelled', 'inactive', 'closed_lost'], true) => 'danger',
        in_array($normalized, ['in_production', 'printing', 'scheduled', 'queued'], true) => 'in_production',
        default => 'neutral',
    };
    $label = str($status)->replace('_', ' ')->title();
@endphp

<x-admin.status-badge :variant="$variant">{{ $label }}</x-admin.status-badge>
