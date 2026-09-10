@props([
    'specification' => null,
    'billingTypes' => [],
    'fulfilmentMethods' => [],
    'priorities' => [],
    'disabled' => false,
    'idPrefix' => 'spec',
    'showHeading' => true,
])

@php
    $spec = $specification;
    $billingTypes = $billingTypes ?: \App\Enums\SalesOrderBillingType::cases();
    $fulfilmentMethods = $fulfilmentMethods ?: \App\Enums\FulfilmentMethod::cases();
    $priorities = $priorities ?: \App\Enums\ProductionPriority::cases();
    $quantityId = $idPrefix.'-default-quantity';
    $priceId = $idPrefix.'-default-unit-price';
    $priorityId = $idPrefix.'-default-priority';
    $fulfilmentId = $idPrefix.'-default-fulfilment';
    $billingId = $idPrefix.'-default-billing';
    $notesId = $idPrefix.'-customer-instructions';
@endphp

@if ($showHeading)
    <section class="rounded-lg border border-erp-border p-4">
        <h3 class="mb-1 text-sm font-semibold text-slate-900">{{ __('Order details') }}</h3>
        <p class="mb-3 text-xs text-slate-500">{{ __('Copied onto the sales order when this specification is used. Change them here rather than on the order form.') }}</p>
@else
    <div>
        <p class="mb-3 text-xs text-slate-500">{{ __('Copied onto the sales order when this specification is used.') }}</p>
@endif
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="erp-label" for="{{ $quantityId }}">{{ __('Quantity') }}</label>
                <input
                    type="number"
                    step="0.001"
                    min="0"
                    id="{{ $quantityId }}"
                    name="default_quantity"
                    class="erp-input w-full"
                    value="{{ old('default_quantity', $spec?->default_quantity ?? '1') }}"
                    @disabled($disabled)
                >
            </div>
            <div>
                <label class="erp-label" for="{{ $priceId }}">{{ __('Unit price') }}</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    id="{{ $priceId }}"
                    name="default_unit_price"
                    class="erp-input w-full"
                    value="{{ old('default_unit_price', $spec?->default_unit_price) }}"
                    @disabled($disabled)
                >
            </div>
            <div>
                <label class="erp-label" for="{{ $priorityId }}">{{ __('Priority') }}</label>
                <select id="{{ $priorityId }}" name="default_priority" class="erp-input w-full" @disabled($disabled)>
                    @foreach ($priorities as $priority)
                        <option value="{{ $priority->value }}" @selected(old('default_priority', $spec?->default_priority?->value ?? 'normal') === $priority->value)>
                            {{ $priority->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label" for="{{ $fulfilmentId }}">{{ __('Fulfilment') }}</label>
                <select id="{{ $fulfilmentId }}" name="default_fulfilment_method" class="erp-input w-full" @disabled($disabled)>
                    <option value="">{{ __('—') }}</option>
                    @foreach ($fulfilmentMethods as $method)
                        <option value="{{ $method->value }}" @selected(old('default_fulfilment_method', $spec?->default_fulfilment_method?->value) === $method->value)>
                            {{ $method->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="erp-label" for="{{ $billingId }}">{{ __('Billing type') }}</label>
                <select id="{{ $billingId }}" name="default_billing_type" class="erp-input w-full" @disabled($disabled)>
                    <option value="">{{ __('Use customer default') }}</option>
                    @foreach ($billingTypes as $type)
                        <option value="{{ $type->value }}" @selected(old('default_billing_type', $spec?->default_billing_type?->value) === $type->value)>
                            {{ $type->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="erp-label" for="{{ $notesId }}">{{ __('Notes') }}</label>
                <textarea
                    id="{{ $notesId }}"
                    name="customer_instructions"
                    class="erp-input w-full"
                    rows="2"
                    @disabled($disabled)
                >{{ old('customer_instructions', $spec?->customer_instructions) }}</textarea>
            </div>
        </div>
@if ($showHeading)
    </section>
@else
    </div>
@endif
