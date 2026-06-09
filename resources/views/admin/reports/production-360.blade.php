<x-admin-layout :title="$title">
    <x-admin.page-header :title="$title" :description="$description">
        <x-slot name="actions">
            @if (($read_only ?? true) === true)
                <span class="erp-badge bg-slate-100 text-slate-700">{{ __('Read-only intelligence') }}</span>
            @endif
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
    ])

    @include('admin.reports.partials.sections', ['sections' => $sections])
</x-admin-layout>
