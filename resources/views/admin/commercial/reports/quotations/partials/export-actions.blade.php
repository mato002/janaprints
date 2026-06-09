@props(['can_export', 'filters'])

<x-admin.export-dropdown
    :post-action="route('commercial.reports.quotations.export', $filters)"
    :can-export="$can_export"
/>
