@props([
    'listing',
    'exportQuery' => null,
])

<x-admin.export-dropdown
    export-route="admin.accounting.exports"
    :export-route-params="['listing' => $listing]"
    :export-query="$exportQuery ?? request()->query()"
    :format-in-path="true"
/>
