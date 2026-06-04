<x-admin-layout :title="__('Receive Goods')" :breadcrumbs="[['label' => __('Procurement')], ['label' => $order->po_number, 'url' => route('admin.procurement.orders.show', $order)], ['label' => __('Receive')]]">
    <x-admin.page-header :title="__('Receive goods')" :description="$order->po_number" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.procurement.orders.receive.store', $order) }}" class="space-y-6">
            @csrf
            <div class="erp-form-grid">
                <div>
                    <x-input-label for="warehouse_id" :value="__('Warehouse')" />
                    <select id="warehouse_id" name="warehouse_id" class="erp-select mt-1 w-full" required>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="receipt_date" :value="__('Receipt date')" />
                    <x-text-input id="receipt_date" name="receipt_date" type="date" class="mt-1 block w-full" value="{{ now()->toDateString() }}" required />
                </div>
            </div>
            <table class="erp-table text-sm">
                <thead><tr><th>{{ __('Item') }}</th><th>{{ __('Remaining') }}</th><th>{{ __('Receive qty') }}</th></tr></thead>
                <tbody>
                    @foreach ($order->items as $index => $item)
                        @if ($item->remainingQuantity() > 0)
                            <tr>
                                <td>{{ $item->description }}<input type="hidden" name="items[{{ $index }}][purchase_order_item_id]" value="{{ $item->id }}"></td>
                                <td>{{ $item->remainingQuantity() }}</td>
                                <td><input type="number" step="0.001" min="0.001" max="{{ $item->remainingQuantity() }}" name="items[{{ $index }}][quantity_received]" value="{{ $item->remainingQuantity() }}" class="erp-input w-full" required></td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
            <x-primary-button>{{ __('Create goods receipt') }}</x-primary-button>
        </form>
    </x-admin.card>
</x-admin-layout>
