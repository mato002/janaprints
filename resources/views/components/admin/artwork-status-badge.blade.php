@props(['status'])

@php
    $value = $status instanceof \App\Enums\ArtworkRequestStatus
        ? $status->value
        : strtolower(str_replace([' ', '-'], '_', (string) $status));

    [$variant, $label, $dot] = match ($value) {
        'requested' => ['neutral', __('Requested'), false],
        'in_design' => ['info', __('In design'), true],
        'submitted' => ['indigo', __('Submitted'), false],
        'approved' => ['success', __('Approved'), false],
        'revision_requested' => ['warning', __('Revision requested'), false],
        'rejected' => ['danger', __('Rejected'), false],
        default => ['neutral', str($value)->replace('_', ' ')->title(), false],
    };
@endphp

<x-admin.status-badge :variant="$variant">
    @if ($dot)<span class="mr-1 opacity-80">●</span>@endif{{ $label }}
</x-admin.status-badge>
