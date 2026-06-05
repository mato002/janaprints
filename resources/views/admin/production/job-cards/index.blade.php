@php
    use App\Services\Production\ProductionJobCardIndexService;

    $indexService = app(ProductionJobCardIndexService::class);
    $jobCards = $job_cards ?? null;
    $filters = $filters ?? [];
    $filterOptions = $filter_options ?? [];
    $activeChips = $active_filter_chips ?? [];
    $statusTabs = $status_tabs ?? [];
    $savedViewPresets = $saved_view_presets ?? [];
    $registerColumns = $register_columns ?? [];
    $bulkActions = $bulk_actions ?? [];
    $hasActiveFilters = $has_active_filters ?? false;
    $canCreate = $can_create ?? false;
    $createUrl = $create_url ?? null;
    $salesOrdersUrl = $sales_orders_url ?? null;
@endphp

<x-admin-layout
    :title="__('Job cards')"
    :breadcrumbs="[
        ['label' => __('Production'), 'url' => route('admin.workspaces.production')],
        ['label' => __('Job cards')],
    ]"
>
    <x-admin.page-header
        :title="__('Job Cards')"
        :description="__('Production order execution register.')"
    >
        @if ($canCreate && $createUrl)
            <x-slot name="actions">
                <a href="{{ $createUrl }}" class="erp-btn-primary" data-turbo-frame="erp-main">{{ __('Create Job Card') }}</a>
            </x-slot>
        @endif
    </x-admin.page-header>

    <div
        x-data="jobCardsRegister(@js([
            'columns' => $registerColumns,
            'presets' => $savedViewPresets,
            'indexUrl' => route('admin.production.job-cards.index'),
        ]))"
    >
        @include('admin.production.job-cards.register.filters', [
            'filters' => $filters,
            'filterOptions' => $filterOptions,
            'activeChips' => $activeChips,
            'statusTabs' => $statusTabs,
            'savedViewPresets' => $savedViewPresets,
            'registerColumns' => $registerColumns,
        ])

        @include('admin.production.job-cards.register.table', [
            'jobCards' => $jobCards,
            'indexService' => $indexService,
            'filters' => $filters,
            'bulkActions' => $bulkActions,
            'registerColumns' => $registerColumns,
            'hasActiveFilters' => $hasActiveFilters,
            'canCreate' => $canCreate,
            'createUrl' => $createUrl,
            'salesOrdersUrl' => $salesOrdersUrl,
        ])
    </div>
</x-admin-layout>
