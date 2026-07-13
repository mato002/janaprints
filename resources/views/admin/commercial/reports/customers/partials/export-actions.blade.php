@props(['can_export', 'filters'])

<x-admin.export-dropdown
    :post-action="route('admin.commercial.reports.customers.export', $filters)"
    :can-export="$can_export"
/>
