@props(['can_export' => false])

@if ($can_export)
    <button type="button" class="erp-btn-secondary" disabled title="{{ __('Export will be available in a future release') }}">
        {{ __('Export') }}
    </button>
@else
    <button type="button" class="erp-btn-secondary opacity-60" disabled title="{{ __('You do not have permission to export reports') }}">
        {{ __('Export') }}
    </button>
@endif
