@php
    $breadcrumbs = [
        ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
        ['label' => __('Inventory Control'), 'url' => route('admin.workspaces.supply-chain.section', ['section' => 'inventory-control'])],
        ['label' => __('Cycle Count'), 'url' => route('admin.inventory.cycle-counts.index')],
        ['label' => __('Create')],
    ];
    $fields = $formFields ?? [];
@endphp
<x-admin-layout :title="__('New cycle count schedule')" :breadcrumbs="$breadcrumbs">
    <x-admin.page-header :title="__('New schedule')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.inventory.cycle-counts.store') }}" class="space-y-4">
            @csrf
            @if (($fields['warehouse_id']['visible'] ?? true))
                <div>
                    <label class="erp-label">{{ $fields['warehouse_id']['label'] ?? __('Warehouse') }}</label>
                    <select name="warehouse_id" class="erp-input w-full" @required($fields['warehouse_id']['required'] ?? true) @disabled($fields['warehouse_id']['read_only'] ?? false)>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if (($fields['frequency']['visible'] ?? true))
                <div>
                    <label class="erp-label">{{ $fields['frequency']['label'] ?? __('Frequency') }}</label>
                    <select name="frequency" class="erp-input w-full" @required($fields['frequency']['required'] ?? true) @disabled($fields['frequency']['read_only'] ?? false)>
                        @foreach ($frequencies as $freq)
                            <option value="{{ $freq->value }}" @selected(old('frequency') === $freq->value)>{{ ucfirst($freq->value) }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if (($fields['next_count_date']['visible'] ?? true))
                <div>
                    <label class="erp-label">{{ $fields['next_count_date']['label'] ?? __('Next count date') }}</label>
                    <input type="date" name="next_count_date" value="{{ old('next_count_date', now()->toDateString()) }}" class="erp-input w-full" @required($fields['next_count_date']['required'] ?? true) @readonly($fields['next_count_date']['read_only'] ?? false)>
                </div>
            @endif
            @if (($fields['inventory_category_id']['visible'] ?? true))
                <div>
                    <label class="erp-label">{{ $fields['inventory_category_id']['label'] ?? __('Category (optional)') }}</label>
                    <select name="inventory_category_id" class="erp-input w-full" @required($fields['inventory_category_id']['required'] ?? false) @disabled($fields['inventory_category_id']['read_only'] ?? false)>
                        <option value="">{{ __('All categories') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('inventory_category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if (($fields['responsible_user_id']['visible'] ?? true))
                <div>
                    <label class="erp-label">{{ $fields['responsible_user_id']['label'] ?? __('Responsible user') }}</label>
                    <select name="responsible_user_id" class="erp-input w-full" @required($fields['responsible_user_id']['required'] ?? true) @disabled($fields['responsible_user_id']['read_only'] ?? false)>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(old('responsible_user_id', auth()->id()) == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if (($fields['notes']['visible'] ?? true))
                <div>
                    <label class="erp-label">{{ $fields['notes']['label'] ?? __('Notes') }}</label>
                    <textarea name="notes" class="erp-input w-full" rows="2" @required($fields['notes']['required'] ?? false) @readonly($fields['notes']['read_only'] ?? false)>{{ old('notes') }}</textarea>
                </div>
            @endif
            @include('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => null])
            <button type="submit" class="erp-btn-primary">{{ __('Create schedule') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
