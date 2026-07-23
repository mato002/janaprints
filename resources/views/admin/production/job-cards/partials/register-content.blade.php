@php
    use App\Services\Production\ProductionJobCardIndexService;
    use App\Support\Production\ProductionFloorDeskViews;

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
    $indexUrl = ProductionFloorDeskViews::registerIndexUrl();
@endphp

@if (! ($embeddedInFloor ?? false))
    <x-admin.page-header
        :title="__('Job Cards')"
        :description="__('Full production order register with filters and exports.')"
    >
        @if ($canCreate && $createUrl)
            <x-slot name="actions">
                <a href="{{ $createUrl }}" class="erp-btn-primary" data-erp-modal-open>{{ __('Create Job Card') }}</a>
            </x-slot>
        @endif
    </x-admin.page-header>
@else
    <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-sm font-semibold text-erp-primary">{{ __('Job register') }}</h2>
            <p class="text-xs text-slate-600">{{ __('Full production order register with filters and exports.') }}</p>
        </div>
        @if ($canCreate && $createUrl)
            <a href="{{ $createUrl }}" class="erp-btn-primary" data-erp-modal-open>{{ __('Create Job Card') }}</a>
        @endif
    </div>
@endif

<div
    x-data="jobCardsRegister(@js([
        'columns' => $registerColumns,
        'presets' => $savedViewPresets,
        'indexUrl' => $indexUrl,
    ]))"
>
    @include('admin.production.job-cards.register.filters', [
        'filters' => $filters,
        'filterOptions' => $filterOptions,
        'activeChips' => $activeChips,
        'statusTabs' => $statusTabs,
        'savedViewPresets' => $savedViewPresets,
        'registerColumns' => $registerColumns,
        'indexUrl' => $indexUrl,
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
