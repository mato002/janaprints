@props(['can_export', 'filters'])

<x-admin.export-dropdown
    :post-action="route('admin.procurement.supplier-performance.export', $filters)"
    :can-export="$can_export"
/>
