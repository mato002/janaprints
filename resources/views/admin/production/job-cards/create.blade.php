<x-admin-layout :title="__('New job card')" :breadcrumbs="[['label' => __('Production'), 'url' => route('admin.production.dashboard')], ['label' => __('New')]]">
    <x-admin.page-header :title="__('New job card')" :description="__('From confirmed sales order with approved artwork.')" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.production.job-cards.store') }}" class="space-y-4 max-w-xl">
            @csrf
            <div>
                <label class="erp-label">{{ __('Sales order') }}</label>
                <select name="sales_order_id" class="erp-input w-full" required>
                    <option value="">{{ __('Select sales order') }}</option>
                    @foreach ($eligibleOrders as $order)
                        <option value="{{ $order->id }}" @selected(old('sales_order_id') == $order->id)>
                            {{ $order->order_number }} — {{ $order->customer?->company_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Production type') }}</label>
                <select name="production_type" class="erp-input w-full" required>
                    @foreach ($productionTypes as $type)
                        <option value="{{ $type->value }}" @selected(old('production_type', 'mixed') === $type->value)>{{ ucfirst(str_replace('_', ' ', $type->value)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Priority') }}</label>
                <select name="priority" class="erp-input w-full" required>
                    @foreach ($priorities as $priority)
                        <option value="{{ $priority->value }}" @selected(old('priority', 'normal') === $priority->value)>{{ ucfirst($priority->value) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="erp-label">{{ __('Planned start') }}</label>
                    <input type="date" name="planned_start_date" class="erp-input w-full" value="{{ old('planned_start_date') }}">
                </div>
                <div>
                    <label class="erp-label">{{ __('Planned end') }}</label>
                    <input type="date" name="planned_end_date" class="erp-input w-full" value="{{ old('planned_end_date') }}">
                </div>
            </div>
            <button type="submit" class="erp-btn-primary" @disabled($eligibleOrders->isEmpty())>{{ __('Create job card') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
