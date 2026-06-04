<x-admin-layout :title="$receipt->receipt_number" :breadcrumbs="[['label' => __('Procurement')], ['label' => __('Goods Receipts'), 'url' => route('admin.procurement.receipts.index')], ['label' => $receipt->receipt_number]]">
    <x-admin.page-header :title="$receipt->receipt_number">
        <x-slot name="actions">
            @can('post', $receipt)
                <form method="POST" action="{{ route('admin.procurement.receipts.post', $receipt) }}">@csrf<button class="erp-btn-primary">{{ __('Post to inventory') }}</button></form>
            @endcan
        </x-slot>
    </x-admin.page-header>
    <x-admin.card>
        <dl class="mb-4 grid gap-3 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500">{{ __('Purchase order') }}</dt><dd>{{ $receipt->purchaseOrder?->po_number }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Warehouse') }}</dt><dd>{{ $receipt->warehouse?->name }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Status') }}</dt><dd>{{ str($receipt->status->value)->headline() }}</dd></div>
            @if ($receipt->stockReceipt)
                <div><dt class="text-slate-500">{{ __('Stock receipt') }}</dt><dd><a href="{{ route('admin.inventory.receipts.show', $receipt->stockReceipt) }}" class="text-erp-accent hover:underline">{{ $receipt->stockReceipt->receipt_number }}</a></dd></div>
            @endif
        </dl>
        <table class="erp-table text-sm">
            <thead><tr><th>{{ __('Item') }}</th><th>{{ __('Qty received') }}</th><th>{{ __('Unit cost') }}</th></tr></thead>
            <tbody>
                @foreach ($receipt->items as $item)
                    <tr><td>{{ $item->purchaseOrderItem?->description }}</td><td>{{ $item->quantity_received }}</td><td>{{ number_format($item->unit_cost, 2) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>
</x-admin-layout>
