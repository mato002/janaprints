<x-admin-layout :title="__('Edit Purchase Order')" :breadcrumbs="[['label' => __('Procurement')], ['label' => $order->po_number]]">
    <x-admin.page-header :title="__('Edit purchase order')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.procurement.orders.update', $order) }}" class="space-y-6">
            @csrf @method('PUT')
            <div class="erp-form-grid">
                <div>
                    <x-input-label for="vendor_id" :value="__('Vendor')" />
                    <select id="vendor_id" name="vendor_id" class="erp-select mt-1 w-full" required>
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor->id }}" @selected($order->vendor_id === $vendor->id)>{{ $vendor->vendor_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="order_date" :value="__('Order date')" />
                    <x-text-input id="order_date" name="order_date" type="date" class="mt-1 block w-full" :value="old('order_date', $order->order_date?->format('Y-m-d'))" required />
                </div>
            </div>
            @include('admin.procurement.partials.line-items-form', ['items' => $items, 'mode' => 'order', 'existing' => $order->items])
            <x-primary-button>{{ __('Save changes') }}</x-primary-button>
        </form>
    </x-admin.card>
</x-admin-layout>
