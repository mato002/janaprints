<x-admin-layout :title="$transfer->issue_number" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Store Management'), 'url' => route('admin.inventory.store.dashboard')], ['label' => __('Store Transfers'), 'url' => route('admin.inventory.transfers.index')], ['label' => $transfer->issue_number]]">
    <x-admin.page-header :title="$transfer->issue_number" :description="$transfer->warehouse?->name.' -> '.$transfer->toWarehouse?->name">
        <x-slot name="actions">
            <x-admin.enum-status-badge :status="$transfer->status->value" />
            @can('post', $transfer)
                <form method="POST" action="{{ route('admin.inventory.transfers.post', $transfer) }}">
                    @csrf
                    <button class="erp-btn-primary">{{ __('Post transfer') }}</button>
                </form>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.card>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <div class="text-xs font-medium uppercase text-slate-500">{{ __('From') }}</div>
                <div class="mt-1 text-sm">{{ $transfer->warehouse?->name }}</div>
            </div>
            <div>
                <div class="text-xs font-medium uppercase text-slate-500">{{ __('To') }}</div>
                <div class="mt-1 text-sm">{{ $transfer->toWarehouse?->name }}</div>
            </div>
            <div>
                <div class="text-xs font-medium uppercase text-slate-500">{{ __('Date') }}</div>
                <div class="mt-1 text-sm">{{ $transfer->issue_date?->format('Y-m-d') }}</div>
            </div>
        </div>
    </x-admin.card>

    <x-admin.data-table class="mt-6" :search-placeholder="__('Search lines...')" export-filename="store-transfer-lines">
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Item') }}</th>
                <th scope="col">{{ __('Quantity') }}</th>
                <th scope="col">{{ __('Unit cost') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @foreach ($transfer->items as $line)
                <tr x-show="rowVisible(@js(strtolower($line->inventoryItem?->sku.' '.$line->inventoryItem?->item_name)))">
                    <td>
                        <div class="font-medium">{{ $line->inventoryItem?->item_name }}</div>
                        <div class="font-mono text-[11px] text-slate-500">{{ $line->inventoryItem?->sku }}</div>
                    </td>
                    <td class="tabular-nums">{{ number_format((float) $line->quantity, 3) }}</td>
                    <td class="tabular-nums">{{ number_format((float) $line->unit_cost, 2) }}</td>
                </tr>
            @endforeach
        </x-slot>
    </x-admin.data-table>
</x-admin-layout>
