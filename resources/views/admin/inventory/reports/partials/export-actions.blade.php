@props(['can_export', 'filters'])

<x-admin.export-dropdown
    :post-action="route('admin.inventory.reports.export', $filters)"
    :can-export="$can_export"
/>
