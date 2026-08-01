@php
    use App\Support\Inventory\StoreDeskViews;
    use App\Support\Navigation\WorkspaceEmbed;

    $operatorMode = (bool) ($operatorMode ?? false);
    $activeStoreView = StoreDeskViews::normalize($activeStoreView ?? request('view'));
    $isPanel = StoreDeskViews::isPanelView($activeStoreView);
    $title = $isPanel ? ($registerTitle ?? __('Store Desk')) : __('Store Desk');
@endphp

<x-admin-layout
    :title="$title"
    :breadcrumbs="$operatorMode
        ? [['label' => __('Store Desk')]]
        : (
            $isPanel
                ? [
                    ['label' => __('Supply Chain'), 'url' => $fullSupplyChainDeskUrl],
                    ['label' => __('Store Desk'), 'url' => StoreDeskViews::deskUrl()],
                    ['label' => $registerTitle ?? __('Register')],
                ]
                : [
                    ['label' => __('Supply Chain'), 'url' => $fullSupplyChainDeskUrl],
                    ['label' => __('Store Desk')],
                ]
        )"
>
    <div
        @class([
            'store-desk-command' => ! $isPanel,
            'store-desk-register' => $isPanel,
        ])
        @if (! $isPanel)
            x-data="storeDeskLookup(@js(['searchUrl' => $searchUrl]))"
        @endif
    >
        @unless (WorkspaceEmbed::inWorkspaceContext())
            @include('admin.store.desk.partials.desk-mode-nav', ['activeStoreView' => $activeStoreView])
        @endunless

        @if (session('status'))
            <div class="mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-3 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800">
                <ul class="list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($isPanel)
            @include('admin.store.desk.partials.register-panel')
        @else
            @include('admin.store.desk.partials.item-lookup')

            @include('admin.store.desk.partials.summary-strip', ['workQueue' => $workQueue])

            @include('admin.store.desk.partials.work-queue', ['workQueue' => $workQueue])

            <div class="store-desk-command__split mb-3 grid gap-3 lg:grid-cols-5">
                <div class="lg:col-span-3">
                    @include('admin.store.desk.partials.needs-attention', [
                        'needsAttention' => $workQueue['needs_attention'] ?? [],
                        'lowStockItems' => $lowStockItems,
                        'reorderAlertsUrl' => $reorderAlertsUrl,
                    ])
                </div>
                <div class="lg:col-span-2">
                    @include('admin.store.desk.partials.fast-actions', ['fastActions' => $fastActions])
                </div>
            </div>

            @include('admin.store.desk.partials.warehouse-snapshot', ['warehouseSnapshot' => $warehouseSnapshot])

            <div class="store-desk-command__split mb-3 grid gap-3 lg:grid-cols-2">
                @include('admin.store.desk.partials.movement-feed', ['movementFeed' => $movementFeed])
                @include('admin.store.desk.partials.pipelines', [
                    'receivingPipeline' => $receivingPipeline,
                ])
            </div>
        @endif
    </div>
</x-admin-layout>
