<x-admin-layout :title="__('Edit sales order')" :breadcrumbs="[['label' => __('Sales Orders'), 'url' => route('admin.sales-orders.dashboard')], ['label' => $salesOrder->order_number, 'url' => route('admin.sales-orders.show', $salesOrder)], ['label' => __('Edit')]]">
    <x-admin.page-header :title="$salesOrder->order_number" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.sales-orders.update', $salesOrder) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-xl">
                <div>
                    <label class="erp-label">{{ __('Order date') }}</label>
                    <input type="date" name="order_date" class="erp-input w-full" value="{{ old('order_date', $salesOrder->order_date->format('Y-m-d')) }}" required>
                </div>
                <div>
                    <label class="erp-label">{{ __('Required date') }}</label>
                    <input type="date" name="required_date" class="erp-input w-full" value="{{ old('required_date', $salesOrder->required_date?->format('Y-m-d')) }}">
                </div>
            </div>
            <div>
                <label class="erp-label">{{ __('Notes') }}</label>
                <textarea name="notes" class="erp-input w-full" rows="2">{{ old('notes', $salesOrder->notes) }}</textarea>
            </div>
            <h3 class="font-medium">{{ __('Line items') }}</h3>
            @include('admin.sales.orders.partials.items-form', ['salesOrder' => $salesOrder])
            <button type="submit" class="erp-btn-primary">{{ __('Save changes') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
