@php
    use App\Enums\FulfilmentMethod;
    use App\Enums\InventoryStockRole;
    use App\Enums\SalesOrderBillingType;
    use App\Support\Navigation\WorkspaceEmbed;

    $deskFrame = WorkspaceEmbed::turboFrame();
    $specArtwork = $specification->activeArtworkVersion;
    $specProduct = $specification->inventoryItem;
    $specArtworkRequired = $specProduct && $specProduct->stock_role === InventoryStockRole::FinishedGood;
    $specArtworkMissing = $specArtworkRequired && ! $specArtwork;
@endphp

<div class="space-y-4">
    <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Locked context') }}</p>
                <p class="font-medium text-slate-900">{{ $customer->name }}</p>
                <p class="text-xs text-slate-600">
                    {{ $specification->name }}
                    · {{ $specification->specification_code }}
                    · {{ $specProduct?->item_name }}
                </p>
                <p class="mt-1 text-xs">
                    @if ($specArtwork)
                        <span class="text-emerald-700">&#10003; {{ __('Artwork') }}: {{ $specArtwork->versionLabel() }}</span>
                    @elseif ($specArtworkRequired)
                        <span class="text-amber-700">! {{ __('Artwork required but missing') }}</span>
                    @else
                        <span class="text-slate-400">{{ __('Artwork not required') }}</span>
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a
                    href="{{ WorkspaceEmbed::url(route('admin.sales.desk', ['customer' => $customer->getRouteKey(), 'step' => 2])) }}"
                    class="erp-btn-secondary text-xs"
                    data-turbo-frame="{{ $deskFrame }}"
                    data-turbo-action="advance"
                >{{ __('Change specification') }}</a>
                @if (($deskUrls['customer_360'] ?? null))
                    <a href="{{ $deskUrls['customer_360'] }}" class="erp-btn-secondary text-xs" data-turbo-frame="erp-main">{{ __('View Customer 360') }}</a>
                @endif
            </div>
        </div>
    </div>

    @if ($specArtworkMissing)
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <p class="font-medium">{{ __('Artwork needed before order') }}</p>
            <p class="mt-1 text-xs">{{ __('Upload artwork on the specification, then continue. Order creation stays blocked until artwork is present.') }}</p>
            <a
                class="erp-btn-secondary mt-2 inline-flex text-xs"
                href="{{ route('admin.crm.customers.print-specifications.edit', [$customer, $specification, 'from' => 'sales-desk']) }}"
                data-erp-modal-open
            >{{ __('Upload artwork') }}</a>
        </div>
    @else
        <form
            method="POST"
            action="{{ route('admin.sales-orders.store') }}"
            class="space-y-3"
            data-turbo="false"
            data-erp-desk-form
            data-erp-desk-success-message="{{ __('Order created.') }}"
            data-erp-desk-submitting-message="{{ __('Creating order…') }}"
        >
            @csrf
            <input type="hidden" name="from" value="sales-desk">
            <input type="hidden" name="entry_mode" value="direct">
            <input type="hidden" name="customer_id" value="{{ $customer->id }}">
            <input type="hidden" name="customer_print_specification_id" value="{{ $specification->id }}">

            @include('admin.sales.orders.partials.production-destination-picker', [
                'value' => old('production_destination'),
                'required' => true,
            ])

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <label class="erp-label" for="desk-order-qty">{{ __('Order quantity') }}</label>
                    <input id="desk-order-qty" type="number" name="quantity" class="erp-input w-full min-h-[2.75rem]" min="0.001" step="any" value="{{ old('quantity', $specification->default_quantity ?? 1) }}" required>
                    @if ($specification->default_quantity)
                        <p class="mt-1 text-xs text-slate-500">{{ __('Pre-filled from specification default — change only if this order differs.') }}</p>
                    @endif
                </div>
                <div>
                    <label class="erp-label" for="desk-order-price">{{ __('Order unit price') }}</label>
                    <input id="desk-order-price" type="number" name="unit_price" class="erp-input w-full min-h-[2.75rem]" min="0" step="0.01" value="{{ old('unit_price', $specification->default_unit_price) }}">
                    @if ($specification->default_unit_price !== null)
                        <p class="mt-1 text-xs text-slate-500">{{ __('Pre-filled from specification default — change only if this order differs.') }}</p>
                    @endif
                </div>
                <div>
                    <label class="erp-label" for="desk-order-date">{{ __('Required date') }}</label>
                    <input id="desk-order-date" type="date" name="required_date" class="erp-input w-full min-h-[2.75rem]" min="{{ now()->toDateString() }}" value="{{ old('required_date') }}">
                    <p class="mt-1 text-xs text-slate-500">{{ __('Cannot be earlier than today.') }}</p>
                </div>
                <div>
                    <label class="erp-label" for="desk-order-priority">{{ __('Priority') }}</label>
                    <select id="desk-order-priority" name="priority" class="erp-input w-full min-h-[2.75rem]">
                        @foreach ($orderPriorities as $priority)
                            <option value="{{ $priority->value }}" @selected(old('priority', 'normal') === $priority->value)>{{ ucfirst($priority->value) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label" for="desk-order-fulfilment">{{ __('Fulfilment') }}</label>
                    <select id="desk-order-fulfilment" name="fulfilment_method" class="erp-input w-full min-h-[2.75rem]">
                        @foreach (FulfilmentMethod::cases() as $method)
                            <option value="{{ $method->value }}" @selected(old('fulfilment_method') === $method->value)>{{ $method->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label" for="desk-order-billing">{{ __('Billing type') }}</label>
                    <select id="desk-order-billing" name="billing_type" class="erp-input w-full min-h-[2.75rem]">
                        <option value="">{{ __('Use customer default') }}</option>
                        @foreach (SalesOrderBillingType::cases() as $type)
                            <option value="{{ $type->value }}" @selected(old('billing_type') === $type->value)>{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="erp-label" for="desk-order-notes">{{ __('Notes') }}</label>
                    <textarea id="desk-order-notes" name="notes" class="erp-input w-full" rows="2">{{ old('notes') }}</textarea>
                </div>
                @if ($canSendToProduction ?? false)
                    <div class="sm:col-span-2">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="send_to_production" value="1" class="rounded border-erp-border" @checked(old('send_to_production'))>
                            {{ __('Send to production') }}
                        </label>
                        <p class="mt-1 text-xs text-slate-500">{{ __('Creates a production job card immediately. Leave unchecked to release from the next step.') }}</p>
                    </div>
                @endif
            </div>

            <div class="flex flex-wrap justify-end gap-2 pt-1">
                <button type="submit" class="erp-btn-primary">{{ __('Create order') }}</button>
            </div>
        </form>
    @endif
</div>
