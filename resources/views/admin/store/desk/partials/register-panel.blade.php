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
