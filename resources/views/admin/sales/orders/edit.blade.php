<x-admin.modal-form
    :title="__('Edit sales order')"
    :breadcrumbs="[
        ['label' => __('Sales Orders'), 'url' => route('admin.sales-orders.dashboard')],
        ['label' => $salesOrder->order_number, 'url' => route('admin.sales-orders.show', $salesOrder)],
        ['label' => __('Edit')],
    ]"
    maxWidth="5xl"
>
    <form method="POST" action="{{ route('admin.sales-orders.update', $salesOrder) }}" class="space-y-4">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                <label class="erp-label">{{ __('Fulfilment method') }}</label>
                <select name="fulfilment_method" class="erp-input w-full">
                    @foreach (\App\Enums\FulfilmentMethod::cases() as $method)
                        <option value="{{ $method->value }}" @selected(old('fulfilment_method', $salesOrder->fulfilment_method?->value ?? 'collection') === $method->value)>
                            {{ $method->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Billing type') }}</label>
                <select name="billing_type" class="erp-input w-full">
                    @foreach (\App\Enums\SalesOrderBillingType::cases() as $type)
                        <option value="{{ $type->value }}" @selected(old('billing_type', $salesOrder->billing_type?->value ?? 'net_30') === $type->value)>
                            {{ $type->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Payment terms (days)') }}</label>
                <input type="number" name="payment_terms_days" class="erp-input w-full" min="0" max="365"
                    value="{{ old('payment_terms_days', $salesOrder->payment_terms_days ?? 30) }}">
            </div>
        </div>
        <div>
            <label class="erp-label">{{ __('Notes') }}</label>
            <textarea name="notes" class="erp-input w-full" rows="2">{{ old('notes', $salesOrder->notes) }}</textarea>
        </div>

        <div class="space-y-3 rounded-lg border border-erp-border p-4">
            <div>
                <h3 class="font-medium">{{ __('Print specifications') }}</h3>
                <p class="mt-1 text-xs text-slate-500">{{ __('The Digital, Offset, or Outsource details captured when this order was created. Changes apply to this order only.') }}</p>
                @if ($salesOrder->customerPrintSpecification)
                    <p class="mt-2 text-sm text-slate-700">
                        <span class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Linked specification') }}</span>
                        <span class="mt-0.5 block font-medium">
                            {{ $salesOrder->customerPrintSpecification->name }}
                            @if ($salesOrder->customerPrintSpecification->specification_code)
                                <span class="font-mono text-xs text-slate-500">({{ $salesOrder->customerPrintSpecification->specification_code }})</span>
                            @endif
                        </span>
                    </p>
                @endif
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
                'idPrefix' => 'order-job',
            ])
        </div>

        <div class="rounded-lg border border-erp-border p-4 space-y-4" x-data="{ useArtwork: @js((bool) old('uses_existing_artwork', $salesOrder->uses_existing_artwork)) }">
            <h3 class="font-medium">{{ __('Production product') }}</h3>
            <div>
                <label class="erp-label">{{ __('Catalogue item') }}</label>
                <select name="inventory_item_id" class="erp-input w-full">
                    <option value="">{{ __('—') }}</option>
                    @foreach ($catalogueItems ?? [] as $item)
                        <option value="{{ $item->id }}" @selected(old('inventory_item_id', $salesOrder->inventory_item_id) == $item->id)>{{ $item->item_name }} ({{ $item->sku }})</option>
                    @endforeach
                </select>
            </div>

            <h3 class="font-medium">{{ __('Artwork') }}</h3>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="uses_existing_artwork" value="1" x-model="useArtwork" @checked(old('uses_existing_artwork', $salesOrder->uses_existing_artwork))>
                <span>{{ __('Use existing artwork from customer library?') }}</span>
            </label>
            <div x-show="useArtwork" x-cloak>
                @include('admin.sales.quotations.partials.artwork-picker-field', [
                    'scopedCustomerId' => $salesOrder->customer_id,
                    'value' => old('customer_artwork_id', $salesOrder->customer_artwork_id),
                ])
                @if ($salesOrder->artwork_confirmed_at)
                    <p class="mt-1 text-xs text-slate-500">{{ __('Confirmed') }} {{ $salesOrder->artwork_confirmed_at->format('Y-m-d H:i') }}</p>
                @endif
            </div>
        </div>

        <h3 class="font-medium">{{ __('Line items') }}</h3>
        @include('admin.sales.orders.partials.items-form', ['salesOrder' => $salesOrder])

        <x-admin.form-modal-actions class="erp-form-modal__actions--sticky">
            <button type="submit" class="erp-btn-primary">{{ __('Save changes') }}</button>
        </x-admin.form-modal-actions>
    </form>
</x-admin.modal-form>
