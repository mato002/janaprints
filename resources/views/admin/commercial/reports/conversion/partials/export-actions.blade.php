@props(['can_export', 'filters'])

<x-admin.export-dropdown
    :post-action="route('admin.commercial.reports.conversion.export', $filters)"
    :can-export="$can_export"
/>
