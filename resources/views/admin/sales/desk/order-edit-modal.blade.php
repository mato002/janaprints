<x-admin.modal-form :title="__('Edit order')" maxWidth="5xl">
    <form method="POST" action="{{ route('admin.sales-orders.update', $salesOrder) }}" class="space-y-4" data-erp-desk-form>
        @csrf
        @method('PUT')
        <input type="hidden" name="from" value="sales-desk">

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="erp-label">{{ __('Order date') }}</label>
                <input type="date" name="order_date" class="erp-input w-full" value="{{ old('order_date', $salesOrder->order_date->format('Y-m-d')) }}" required>
            </div>
            <div>
                <label class="erp-label">{{ __('Required date') }}</label>
                <input
                    type="date"
                    name="required_date"
                    class="erp-input w-full"
                    @if (! $salesOrder->required_date || $salesOrder->required_date->gte(today())) min="{{ now()->toDateString() }}" @endif
                    value="{{ old('required_date', $salesOrder->required_date?->format('Y-m-d')) }}"
                >
                <p class="mt-1 text-xs text-slate-500">{{ __('A new required date cannot be before today.') }}</p>
            </div>
            <div>
                <label class="erp-label">{{ __('Fulfilment') }}</label>
                <select name="fulfilment_method" class="erp-input w-full">
                    @foreach (\App\Enums\FulfilmentMethod::cases() as $method)
                        <option value="{{ $method->value }}" @selected(old('fulfilment_method', $salesOrder->fulfilment_method?->value ?? 'collection') === $method->value)>{{ $method->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Billing type') }}</label>
                <select name="billing_type" class="erp-input w-full">
                    @foreach (\App\Enums\SalesOrderBillingType::cases() as $type)
                        <option value="{{ $type->value }}" @selected(old('billing_type', $salesOrder->billing_type?->value) === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="erp-label">{{ __('Notes') }}</label>
                <textarea name="notes" class="erp-input w-full" rows="2">{{ old('notes', $salesOrder->notes) }}</textarea>
            </div>
        </div>

        <div class="space-y-3 rounded-lg border border-erp-border p-4">
            <div>
                <h3 class="font-medium">{{ __('Print specifications') }}</h3>
                <p class="mt-1 text-xs text-slate-500">{{ __('The Digital, Offset, or Outsource details captured when this order was created. Changes apply to this order only.') }}</p>
            </div>
            @include('admin.crm.customers.print-specifications.partials.job-fields', [
                'specification' => $jobFieldSpecification ?? null,
                'preselectedDestination' => old(
                    'production_destination',
                    $salesOrder->production_destination?->value
                        ?? optional($jobFieldSpecification ?? null)->production_destination?->value
                ),
                'lockDestination' => false,
                'customer' => $salesOrder->customer,
                'productionVendors' => $productionVendors ?? collect(),
                'idPrefix' => 'order-job-modal',
            ])
        </div>

        <x-admin.form-modal-actions>
            <button type="submit" class="erp-btn-primary">{{ __('Save order') }}</button>
        </x-admin.form-modal-actions>
    </form>
</x-admin.modal-form>
