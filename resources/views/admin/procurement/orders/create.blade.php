<x-admin-layout :title="__('Create Purchase Order')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Procurement'), 'url' => route('admin.procurement.dashboard')], ['label' => __('Purchase Orders'), 'url' => route('admin.procurement.orders.index')], ['label' => __('Create')]]">
    <x-admin.page-header :title="__('Create purchase order')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.procurement.orders.store') }}" class="space-y-6">
            @csrf
            <div class="erp-form-grid">
                <div>
                    <x-input-label for="vendor_id" :value="__('Vendor')" />
                    <select id="vendor_id" name="vendor_id" class="erp-select mt-1 w-full" required>
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor->id }}">{{ $vendor->vendor_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="order_date" :value="__('Order date')" />
                    <x-text-input id="order_date" name="order_date" type="date" class="mt-1 block w-full" value="{{ old('order_date', now()->toDateString()) }}" required />
                </div>
                <div>
                    <x-input-label for="expected_delivery_date" :value="__('Expected delivery')" />
                    <x-text-input id="expected_delivery_date" name="expected_delivery_date" type="date" class="mt-1 block w-full" :value="old('expected_delivery_date')" />
                </div>
            </div>
            @include('admin.procurement.partials.line-items-form', ['items' => $items, 'mode' => 'order'])
            <x-primary-button>{{ __('Save order') }}</x-primary-button>
        </form>
    </x-admin.card>
</x-admin-layout>
