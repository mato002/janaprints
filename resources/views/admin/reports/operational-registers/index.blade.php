@php
    use App\Support\Navigation\WorkspaceEmbed;

    $turboFrame = WorkspaceEmbed::turboFrame();
@endphp

<x-admin-layout :title="$title">
    <x-admin.page-header :title="$title" :description="$description">
        <x-slot name="actions">
            @if ($print_url)
                <a href="{{ $print_url }}" target="_blank" rel="noopener" class="erp-btn-secondary text-sm">{{ __('Print') }}</a>
            @endif
            <x-admin.export-dropdown
                export-route="admin.reports.operational-registers.export"
                :export-query="request()->query()"
                :can-export="$can_export"
            />
        </x-slot>
    </x-admin.page-header>

    @include('admin.reports.operational-registers.partials.kpi-strip', [
        'kpis' => $executive_kpis,
    ])

    @include('admin.reports.operational-registers.partials.filters', [
        'filters' => $filters,
        'branches' => $branches,
        'presets' => $presets,
        'turbo_frame' => $turboFrame,
    ])

    @include('admin.reports.operational-registers.partials.register-nav', [
        'registers' => $registers,
        'active_register' => $active_register,
        'filters' => $filters,
        'turbo_frame' => $turboFrame,
    ])

    @include('admin.reports.operational-registers.partials.register-content', [
        'tab_data' => $tab_data,
        'active_register_label' => $active_register_label,
    ])
</x-admin-layout>
