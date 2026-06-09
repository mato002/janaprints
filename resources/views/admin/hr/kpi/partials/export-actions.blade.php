@props(['can_export', 'filters'])

<x-admin.export-dropdown
    :post-action="route('admin.hr.kpi.export')"
    :post-fields="$filters"
    :can-export="$can_export"
/>
