@php
    $breadcrumbs = [
        ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
        ['label' => __('Inventory Control'), 'url' => route('admin.workspaces.supply-chain.section', ['section' => 'inventory-control'])],
        ['label' => __('Cycle Count'), 'url' => route('admin.inventory.cycle-counts.index')],
        ['label' => __('Edit schedule')],
    ];
@endphp
<x-admin-layout :title="__('Edit schedule')" :breadcrumbs="$breadcrumbs">
    <x-admin.page-header :title="__('Edit schedule')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.inventory.cycle-counts.update', $schedule) }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="erp-label">{{ __('Warehouse') }}</label>
                <select name="warehouse_id" class="erp-input w-full" required>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected($schedule->warehouse_id === $warehouse->id)>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Frequency') }}</label>
                <select name="frequency" class="erp-input w-full" required>
                    @foreach ($frequencies as $freq)
                        <option value="{{ $freq->value }}" @selected($schedule->frequency === $freq)>{{ ucfirst($freq->value) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Next count date') }}</label>
                <input type="date" name="next_count_date" value="{{ $schedule->next_count_date->format('Y-m-d') }}" class="erp-input w-full" required>
            </div>
            <div>
                <label class="erp-label">{{ __('Responsible user') }}</label>
                <select name="responsible_user_id" class="erp-input w-full" required>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected($schedule->responsible_user_id === $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="erp-btn-primary">{{ __('Save') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
