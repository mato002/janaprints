@php
    $breadcrumbs = [
        ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
        ['label' => __('Inventory Control'), 'url' => route('admin.workspaces.supply-chain.section', ['section' => 'inventory-control'])],
        ['label' => __('Cycle Count'), 'url' => route('admin.inventory.cycle-counts.index')],
        ['label' => __('Create')],
    ];
@endphp
<x-admin-layout :title="__('New cycle count schedule')" :breadcrumbs="$breadcrumbs">
    <x-admin.page-header :title="__('New schedule')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.inventory.cycle-counts.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="erp-label">{{ __('Warehouse') }}</label>
                <select name="warehouse_id" class="erp-input w-full" required>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Frequency') }}</label>
                <select name="frequency" class="erp-input w-full" required>
                    @foreach ($frequencies as $freq)
                        <option value="{{ $freq->value }}">{{ ucfirst($freq->value) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Next count date') }}</label>
                <input type="date" name="next_count_date" value="{{ old('next_count_date', now()->toDateString()) }}" class="erp-input w-full" required>
            </div>
            <div>
                <label class="erp-label">{{ __('Category (optional)') }}</label>
                <select name="inventory_category_id" class="erp-input w-full">
                    <option value="">{{ __('All categories') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Responsible user') }}</label>
                <select name="responsible_user_id" class="erp-input w-full" required>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected($user->id === auth()->id())>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Notes') }}</label>
                <textarea name="notes" class="erp-input w-full" rows="2"></textarea>
            </div>
            <button type="submit" class="erp-btn-primary">{{ __('Create schedule') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
