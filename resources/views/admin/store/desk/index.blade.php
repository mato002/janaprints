@php
    $operatorMode = (bool) ($operatorMode ?? false);
    $health = $workQueue['health'] ?? ['percent' => 100, 'label' => __('Healthy'), 'tone' => 'emerald', 'detail' => ''];
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
        class="store-desk-command"
        x-data="storeDeskLookup(@js([
            'searchUrl' => $searchUrl,
        ]))"
    >
        @unless (\App\Support\Navigation\WorkspaceEmbed::inWorkspaceContext())
            @include('admin.store.desk.partials.desk-mode-nav', ['activeStoreView' => \App\Support\Inventory\StoreDeskViews::DESK])
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

        {{-- 1. Search first — clerks look up before they browse --}}
        @include('admin.store.desk.partials.item-lookup')

        {{-- 2. Store Health + KPIs --}}
        @include('admin.store.desk.partials.summary-strip', ['workQueue' => $workQueue])

        {{-- 3. Needs Attention + Quick Actions --}}
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

        {{-- 4. Warehouse Snapshot (compact) --}}
        @include('admin.store.desk.partials.warehouse-snapshot', ['warehouseSnapshot' => $warehouseSnapshot])

        {{-- 5. Activity + Procurement --}}
        <div class="store-desk-command__split mb-3 grid gap-3 lg:grid-cols-2">
            @include('admin.store.desk.partials.movement-feed', ['movementFeed' => $movementFeed])
            @include('admin.store.desk.partials.pipelines', [
                'receivingPipeline' => $receivingPipeline,
            ])
        </div>
    </div>
</x-admin-layout>
