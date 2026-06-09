@php
    $breadcrumbs = [
        ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
        ['label' => __('Inventory Control'), 'url' => route('admin.workspaces.supply-chain.section', ['section' => 'inventory-control'])],
        ['label' => __('Stock Count'), 'url' => route('admin.inventory.stock-counts.index')],
        ['label' => __('Create')],
    ];
    $fields = $formFields ?? [];
@endphp
<x-admin-layout :title="__('New stock count')" :breadcrumbs="$breadcrumbs">
    <x-admin.page-header :title="__('New stock count')" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.inventory.stock-counts.store') }}" class="space-y-4">
            @csrf
            @if (($fields['warehouse_id']['visible'] ?? true))
                <div>
                    <label class="erp-label">{{ $fields['warehouse_id']['label'] ?? __('Warehouse') }}</label>
                    <select name="warehouse_id" class="erp-input w-full" @required($fields['warehouse_id']['required'] ?? true) @disabled($fields['warehouse_id']['read_only'] ?? false)>
                        <option value="">{{ __('Select warehouse') }}</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if (($fields['count_type']['visible'] ?? true))
                <div>
                    <label class="erp-label">{{ $fields['count_type']['label'] ?? __('Count type') }}</label>
                    <select name="count_type" class="erp-input w-full" @required($fields['count_type']['required'] ?? true) @disabled($fields['count_type']['read_only'] ?? false)>
                        @foreach ($countTypes as $type)
                            <option value="{{ $type->value }}" @selected(old('count_type') === $type->value)>{{ ucfirst($type->value) }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if (($fields['count_date']['visible'] ?? true))
                <div>
                    <label class="erp-label">{{ $fields['count_date']['label'] ?? __('Count date') }}</label>
                    <input type="date" name="count_date" value="{{ old('count_date', now()->toDateString()) }}" class="erp-input w-full" @required($fields['count_date']['required'] ?? true) @readonly($fields['count_date']['read_only'] ?? false)>
                </div>
            @endif
            @if (($fields['notes']['visible'] ?? true))
                <div>
                    <label class="erp-label">{{ $fields['notes']['label'] ?? __('Notes') }}</label>
                    <textarea name="notes" class="erp-input w-full" rows="3" @required($fields['notes']['required'] ?? false) @readonly($fields['notes']['read_only'] ?? false)>{{ old('notes') }}</textarea>
                </div>
            @endif
            <div id="partial-items" class="hidden">
                <label class="erp-label">{{ __('Items (partial count)') }}</label>
                <select name="item_ids[]" class="erp-input w-full" multiple size="8">
                    @foreach ($items as $item)
                        <option value="{{ $item->id }}">{{ $item->item_name }} ({{ $item->sku }})</option>
                    @endforeach
                </select>
            </div>
            @include('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => null])
            <button type="submit" class="erp-btn-primary">{{ __('Create count') }}</button>
        </form>
    </x-admin.card>
    <script>
        document.querySelector('[name=count_type]')?.addEventListener('change', function () {
            document.getElementById('partial-items').classList.toggle('hidden', this.value !== 'partial');
        });
    </script>
</x-admin-layout>
