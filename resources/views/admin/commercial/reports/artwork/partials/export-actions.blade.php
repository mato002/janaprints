@props(['can_export', 'filters'])

<x-admin.export-dropdown
    :post-action="route('commercial.reports.artwork.export', $filters)"
    :can-export="$can_export"
/>
