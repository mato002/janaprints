@props(['can_export', 'filters'])

<div class="flex flex-wrap items-center gap-2">
    <x-admin.export-dropdown
        :post-action="route('admin.reports.hr.export')"
        :post-fields="$filters"
        :can-export="$can_export"
    />

    @if ($can_export)
        <a href="{{ route('admin.reports.hr.print', $filters) }}" target="_blank" class="erp-btn-secondary text-xs">
            {{ __('Print') }}
        </a>
    @endif
</div>
