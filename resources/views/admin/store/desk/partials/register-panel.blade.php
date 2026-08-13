@php
    use App\Support\Inventory\StoreDeskViews;
@endphp

<x-admin.page-header :title="$registerTitle" :description="$registerDescription ?? null">
    <x-slot name="actions">
        @if ($registerCanCreate ?? false)
            <a
                href="{{ $registerCreateUrl }}"
                class="erp-btn-primary"
                @if ($registerCreateModal ?? false) data-erp-modal-open @endif
            >{{ $registerCreateLabel }}</a>
        @endif
    </x-slot>
</x-admin.page-header>

@switch ($activeStoreView)
    @case(StoreDeskViews::PRODUCTS)
        @include('admin.store.desk.partials.products-panel')
        @break
    @case(StoreDeskViews::BALANCES)
        @include('admin.inventory.store.partials.balances-content')
        @break
    @case(StoreDeskViews::MOVEMENTS)
        @include('admin.inventory.movements.partials.table')
        @break
    @case(StoreDeskViews::ALERTS)
        @include('admin.inventory.alerts.partials.content')
        @break
    @case(StoreDeskViews::RECEIPTS)
        @include('admin.inventory.receipts.partials.table', ['fromStoreDesk' => true])
        @break
    @case(StoreDeskViews::ISSUES)
        @include('admin.inventory.issues.partials.table', ['fromStoreDesk' => true])
        @break
    @case(StoreDeskViews::TRANSFERS)
        @include('admin.inventory.transfers.partials.table', ['fromStoreDesk' => true])
        @break
    @case(StoreDeskViews::ADJUSTMENTS)
        @include('admin.inventory.adjustments.partials.table', ['fromStoreDesk' => true])
        @break
@endswitch
