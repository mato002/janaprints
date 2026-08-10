@props(['priority'])

@php
    $value = $priority instanceof \App\Enums\ArtworkPriority
        ? $priority->value
        : strtolower((string) $priority);

    [$variant, $label] = match ($value) {
        'low' => ['neutral', __('Low')],
        'normal' => ['neutral', __('Normal')],
        'high' => ['warning', __('High')],
        'urgent' => ['danger', __('Urgent')],
        default => ['neutral', str($value)->replace('_', ' ')->title()],
    };
@endphp

<x-admin.status-badge :variant="$variant">{{ $label }}</x-admin.status-badge>
