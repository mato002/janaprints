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
            <button type="submit" class="erp-btn-primary">{{ __('Save changes') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
