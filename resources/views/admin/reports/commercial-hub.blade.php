<x-admin-layout :title="$title">
    @if ($empty_hub ?? false)
        <x-admin.page-header :title="$title" :description="$description" />
        <x-admin.card>
            <x-admin.empty-state
                icon="document-text"
                :title="__('No commercial reports available')"
                :description="__('Your role does not include departmental commercial report permissions. Use Commercial 360 or Commercial Intelligence tabs for summary analytics.')"
            />
        </x-admin.card>
    @else
    <x-admin.page-header :title="$title" :description="$description">
        <x-slot name="actions">
            @include('admin.commercial.reports.'.$report_view_key.'.partials.export-actions', [
                'can_export' => $can_export,
                'filters' => $filters,
                'export_route' => $export_route ?? null,
            ])
        </x-slot>
    </x-admin.page-header>

    @include('admin.commercial.reports.partials.export-status')

    @if (! empty($report_options))
        @php
            $hubTurboFrame = \App\Support\Navigation\WorkspaceEmbed::turboFrame();
            $hubEmbedded = \App\Support\Navigation\WorkspaceEmbed::inWorkspaceContext();
        @endphp
        <x-admin.card :padding="false" class="mb-4">
            <form
                method="get"
                action="{{ $filter_action }}"
                class="flex items-center gap-2 px-4 py-3"
                @if ($hubTurboFrame) data-turbo-frame="{{ $hubTurboFrame }}" @endif
                data-turbo-action="advance"
            >
                @if ($hubEmbedded)
                    <input type="hidden" name="embedded" value="1">
                @endif
                <label for="commercial-report-type" class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Report type') }}</label>
                @include('admin.commercial.reports.partials.report-type-select', [
                    'report_options' => $report_options,
                    'report_key' => $report_key,
                ])
            </form>
        </x-admin.card>
    @endif

    @switch($report_key)
        @case('sales')
            @include('admin.commercial.reports.sales.partials.filters', [
                'filters' => $filters,
                'branches' => $branches,
                'customers' => $customers,
                'salespersons' => $salespersons,
                'report_options' => $report_options,
                'report_key' => $report_key,
                'filter_action' => $filter_action,
                'filter_reset_url' => $filter_reset_url,
            ])
            @break
        @case('quotations')
            @include('admin.commercial.reports.quotations.partials.filters', [
                'filters' => $filters,
                'branches' => $branches,
                'customers' => $customers,
                'salespersons' => $salespersons,
                'report_options' => $report_options,
                'report_key' => $report_key,
                'filter_action' => $filter_action,
                'filter_reset_url' => $filter_reset_url,
            ])
            @break
        @case('sales_orders')
            @include('admin.commercial.reports.sales-orders.partials.filters', [
                'filters' => $filters,
                'branches' => $branches,
                'customers' => $customers,
                'salespersons' => $salespersons,
                'report_options' => $report_options,
                'report_key' => $report_key,
                'filter_action' => $filter_action,
                'filter_reset_url' => $filter_reset_url,
            ])
            @break
        @case('customers')
            @include('admin.commercial.reports.customers.partials.filters', [
                'filters' => $filters,
                'branches' => $branches,
                'salespersons' => $salespersons,
                'report_options' => $report_options,
                'report_key' => $report_key,
                'filter_action' => $filter_action,
                'filter_reset_url' => $filter_reset_url,
            ])
            @break
        @case('artwork')
            @include('admin.commercial.reports.artwork.partials.filters', [
                'filters' => $filters,
                'branches' => $branches,
                'customers' => $customers,
                'designers' => $designers,
                'report_options' => $report_options,
                'report_key' => $report_key,
                'filter_action' => $filter_action,
                'filter_reset_url' => $filter_reset_url,
            ])
            @break
        @case('conversion')
            @include('admin.commercial.reports.conversion.partials.filters', [
                'filters' => $filters,
                'branches' => $branches,
                'lead_sources' => $lead_sources,
                'salespersons' => $salespersons,
                'report_options' => $report_options,
                'report_key' => $report_key,
                'filter_action' => $filter_action,
                'filter_reset_url' => $filter_reset_url,
            ])
            @break
    @endswitch

    @include('admin.commercial.reports.'.$report_view_key.'.partials.kpi-strip', ['kpis' => $kpis])

    @include('admin.commercial.reports.'.$report_view_key.'.partials.tabs', [
        'tabs' => $tabs,
        'active_tab' => $active_tab,
        'filters' => $filters,
        'index_route' => $index_route ?? null,
    ])

    @switch($report_key)
        @case('sales')
            @include('admin.commercial.reports.sales.partials.tab-content', [
                'tab_data' => $tab_data,
                'active_tab' => $active_tab,
                'tabs' => $tabs,
                'filters' => $filters,
            ])
            @break
        @case('quotations')
            @include('admin.commercial.reports.quotations.partials.tab-content', [
                'tab_data' => $tab_data,
                'active_tab' => $active_tab,
                'tabs' => $tabs,
                'filters' => $filters,
            ])
            @break
        @case('sales_orders')
            @include('admin.commercial.reports.sales-orders.partials.tab-content', [
                'tab_data' => $tab_data,
                'active_tab' => $active_tab,
                'tabs' => $tabs,
                'filters' => $filters,
            ])
            @break
        @case('customers')
            @include('admin.commercial.reports.customers.partials.tab-content', [
                'tab_data' => $tab_data,
                'active_tab' => $active_tab,
                'tabs' => $tabs,
                'filters' => $filters,
            ])
            @break
        @case('artwork')
            @include('admin.commercial.reports.artwork.partials.tab-content', [
                'tab_data' => $tab_data,
                'active_tab' => $active_tab,
                'tabs' => $tabs,
                'filters' => $filters,
            ])
            @break
        @case('conversion')
            @include('admin.commercial.reports.conversion.partials.tab-content', [
                'tab_data' => $tab_data,
                'active_tab' => $active_tab,
                'tabs' => $tabs,
                'filters' => $filters,
                'has_production_pipeline' => $has_production_pipeline ?? false,
                'has_dispatch_pipeline' => $has_dispatch_pipeline ?? false,
            ])
            @break
    @endswitch
    @endif
</x-admin-layout>
