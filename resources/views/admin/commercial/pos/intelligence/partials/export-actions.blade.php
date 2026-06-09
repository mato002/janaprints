@props(['can_export', 'filters'])

<x-admin.export-dropdown
    :post-action="route('commercial.pos.reports.export', $filters)"
    :can-export="$can_export"
/>
