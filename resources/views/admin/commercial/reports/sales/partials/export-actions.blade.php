@props(['can_export', 'filters'])

<x-admin.export-dropdown
    :post-action="route('commercial.reports.sales.export', $filters)"
    :can-export="$can_export"
/>
