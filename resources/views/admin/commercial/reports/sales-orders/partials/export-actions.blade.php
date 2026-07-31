@props(['can_export', 'filters', 'export_route' => null])

<x-admin.export-dropdown
    :post-action="route($export_route ?? 'admin.commercial.reports.sales_orders.export', $filters)"
    :can-export="$can_export"
/>
