@php
    $fromStoreDesk = (bool) ($fromStoreDesk ?? request('from') === 'store-desk');
    $breadcrumbs = $fromStoreDesk
        ? [
            ['label' => __('Store Desk'), 'url' => route('admin.store.desk')],
            ['label' => $receipt->receipt_number],
        ]
        : [
            ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
            ['label' => __('Store Management'), 'url' => route('admin.inventory.store.dashboard')],
            ['label' => __('Goods Receiving'), 'url' => route('admin.inventory.receipts.index')],
            ['label' => $receipt->receipt_number],
        ];
@endphp

<x-admin-layout :title="$receipt->receipt_number" :breadcrumbs="$breadcrumbs">
    <x-admin.page-header :title="$receipt->receipt_number">
        <span class="erp-badge">{{ $receipt->status->value }}</span>
        @can('post', $receipt)
            <form method="POST" action="{{ route('admin.inventory.receipts.post', $receipt) }}">
                @csrf
                @if ($fromStoreDesk)
                    <input type="hidden" name="from" value="store-desk">
                @endif
                <button class="erp-btn-primary">{{ __('Post receipt') }}</button>
            </form>
        @endcan
        @if ($fromStoreDesk)
            <a href="{{ route('admin.store.desk') }}" class="erp-btn-secondary" data-turbo-frame="erp-main">{{ __('Back to Store Desk') }}</a>
        @endif
    </x-admin.page-header>
    <x-admin.card>
        <p class="mb-4 text-sm">{{ $receipt->warehouse?->name }} — {{ $receipt->receipt_date->format('Y-m-d') }}</p>
        @foreach ($receipt->items as $line)
            <div class="py-1 text-sm">{{ $line->inventoryItem?->item_name }}: {{ $line->quantity }} @ {{ $line->unit_cost }}</div>
        @endforeach
    </x-admin.card>
</x-admin-layout>
