@php
    $breadcrumbs = [
        ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
        ['label' => __('Inventory Control'), 'url' => route('admin.workspaces.supply-chain.section', ['section' => 'inventory-control'])],
        ['label' => __('Stock Count'), 'url' => route('admin.inventory.stock-counts.index')],
        ['label' => __('Create')],
    ];
@endphp
<x-admin-layout :title="__('New stock count')" :breadcrumbs="$breadcrumbs">
    <x-admin.page-header :title="__('New stock count')" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.inventory.stock-counts.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="erp-label">{{ __('Warehouse') }}</label>
                <select name="warehouse_id" class="erp-input w-full" required>
                    <option value="">{{ __('Select warehouse') }}</option>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Count type') }}</label>
                <select name="count_type" class="erp-input w-full" required>
                    @foreach ($countTypes as $type)
                        <option value="{{ $type->value }}" @selected(old('count_type') === $type->value)>{{ ucfirst($type->value) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Count date') }}</label>
                <input type="date" name="count_date" value="{{ old('count_date', now()->toDateString()) }}" class="erp-input w-full" required>
            </div>
            <div>
                <label class="erp-label">{{ __('Notes') }}</label>
                <textarea name="notes" class="erp-input w-full" rows="3">{{ old('notes') }}</textarea>
            </div>
            <div id="partial-items" class="hidden">
                <label class="erp-label">{{ __('Items (partial count)') }}</label>
                <select name="item_ids[]" class="erp-input w-full" multiple size="8">
                    @foreach ($items as $item)
                        <option value="{{ $item->id }}">{{ $item->item_name }} ({{ $item->sku }})</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="erp-btn-primary">{{ __('Create count') }}</button>
        </form>
    </x-admin.card>
    <script>
        document.querySelector('[name=count_type]')?.addEventListener('change', function () {
            document.getElementById('partial-items').classList.toggle('hidden', this.value !== 'partial');
        });
    </script>
</x-admin-layout>
