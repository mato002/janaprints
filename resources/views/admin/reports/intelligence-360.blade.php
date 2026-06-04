<x-admin-layout :title="$title">
    <x-admin.page-header :title="$title" :description="$description">
        <x-slot name="actions">
            @include('admin.reports.partials.export-button', ['can_export' => $can_export])
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
