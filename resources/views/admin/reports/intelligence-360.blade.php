<x-admin-layout :title="$title">
    <x-admin.page-header :title="$title" :description="$description">
        <x-slot name="actions">
            @include('admin.reports.partials.export-button', [
                'can_export' => $can_export,
                'export_route' => $export_route ?? null,
                'export_route_params' => $export_route_params ?? [],
                'format_in_path' => true,
            ])
        </x-slot>
    </x-admin.page-header>

    @include('admin.reports.partials.filters', [
        'filters' => $filters,
        'branches' => $branches,
        'warehouses' => $warehouses ?? null,
        'vendors' => $vendors ?? null,
    ])

    @include('admin.reports.partials.sections', ['sections' => $sections])
</x-admin-layout>
