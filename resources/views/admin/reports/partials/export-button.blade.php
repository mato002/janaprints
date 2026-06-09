@props([
    'can_export' => false,
    'export_route' => null,
    'export_query' => null,
    'export_route_params' => [],
    'format_in_path' => false,
    'post_action' => null,
    'post_fields' => [],
])

<x-admin.export-dropdown
    :export-route="$can_export ? $export_route : null"
    :export-query="$export_query ?? request()->query()"
    :export-route-params="$export_route_params"
    :format-in-path="$format_in_path"
    :post-action="$can_export ? $post_action : null"
    :post-fields="$post_fields"
    :can-export="$can_export"
/>
