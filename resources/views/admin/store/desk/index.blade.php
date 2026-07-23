@php
    $operatorMode = (bool) ($operatorMode ?? false);
@endphp

<x-admin-layout
    :title="__('Store Desk')"
    :breadcrumbs="$operatorMode
        ? [['label' => __('Store Desk')]]
        : [
            ['label' => __('Supply Chain'), 'url' => $fullSupplyChainDeskUrl],
            ['label' => __('Store Desk')],
        ]"
>
    <div
        class="store-desk-shell"
        x-data="storeDeskLookup(@js([
            'searchUrl' => $searchUrl,
        ]))"
    >
        <div class="mb-3 flex flex-col gap-2 rounded-lg border border-erp-accent/25 bg-erp-accent/5 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-erp-primary">{{ __('Store desk') }}</p>
                <p class="text-xs text-slate-600">{{ __('Receive, issue, transfer, adjust, and verify stock from one transaction hub.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if (! $operatorMode)
                    <a href="{{ $fullSupplyChainDeskUrl }}" class="erp-btn-secondary text-xs" data-turbo-frame="erp-main">{{ __('Full Supply Chain desk') }}</a>
                @endif
            </div>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <ul class="list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @include('admin.store.desk.partials.summary-strip', ['workQueue' => $workQueue])
        @include('admin.store.desk.partials.work-queue', ['workQueue' => $workQueue])
        @include('admin.store.desk.partials.fast-actions', ['fastActions' => $fastActions])
        @include('admin.store.desk.partials.item-lookup')
        @include('admin.store.desk.partials.warehouse-snapshot', ['warehouseSnapshot' => $warehouseSnapshot])
        @include('admin.store.desk.partials.pipelines', [
            'receivingPipeline' => $receivingPipeline,
            'issuePipeline' => $issuePipeline,
        ])
        @include('admin.store.desk.partials.movement-feed', ['movementFeed' => $movementFeed])
        @include('admin.store.desk.partials.low-stock', [
            'lowStockItems' => $lowStockItems,
            'reorderAlertsUrl' => $reorderAlertsUrl,
        ])
        @include('admin.store.desk.partials.reorder-suggestions', [
            'reorderRecommendations' => $reorderRecommendations,
            'reorderAlertsUrl' => $reorderAlertsUrl,
        ])
    </div>
</x-admin-layout>
