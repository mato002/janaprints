@props(['can_export' => false, 'export_url' => null])

@if ($can_export && $export_url)
    <a href="{{ $export_url }}" class="erp-btn-secondary" data-turbo="false">{{ __('Export CSV') }}</a>
@elseif ($can_export)
    <button type="button" class="erp-btn-secondary" disabled title="{{ __('Export will be available in a future release') }}">
        {{ __('Export') }}
    </button>
@else
    <button type="button" class="erp-btn-secondary opacity-60" disabled title="{{ __('You do not have permission to export reports') }}">
        {{ __('Export') }}
    </button>
@endif
