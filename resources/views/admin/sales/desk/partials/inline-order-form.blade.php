@php
    use App\Enums\FulfilmentMethod;
    use App\Enums\InventoryStockRole;
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
            x-data="{
                productionDestination: @js(old('production_destination', $specification->production_destination?->value ?? '')),
            }"
        >
            @csrf
            <input type="hidden" name="from" value="sales-desk">
            <input type="hidden" name="entry_mode" value="direct">
            <input type="hidden" name="customer_id" value="{{ $customer->id }}">
            <input type="hidden" name="customer_print_specification_id" value="{{ $specification->id }}">

            @include('admin.sales.orders.partials.production-destination-picker', [
                'alpineModel' => 'productionDestination',
                'value' => old('production_destination', $specification->production_destination?->value),
                'required' => true,
            ])
            <p class="text-xs text-slate-500">{{ __('Quantity, price, priority, fulfilment, billing, and notes come from the specification.') }}</p>

            <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('From specification') }}</p>
                <p class="mt-1">
                    {{ $specification->default_quantity ?? 1 }}
                    <span class="text-slate-400">{{ __('qty') }}</span>
                    <span class="text-slate-400">·</span>
                    {{ $specification->default_unit_price !== null ? number_format((float) $specification->default_unit_price, 2) : '0' }}
                    <span class="text-slate-400">{{ __('unit price') }}</span>
                    <span class="text-slate-400">·</span>
                    {{ $specification->default_priority?->label() ?? __('Normal') }}
                    @if ($specification->default_fulfilment_method)
                        <span class="text-slate-400">·</span>
                        {{ $specification->default_fulfilment_method->label() }}
                    @endif
                    @if ($specification->default_billing_type)
                        <span class="text-slate-400">·</span>
                        {{ $specification->default_billing_type->label() }}
                    @endif
                </p>
            </div>

            <input type="hidden" name="quantity" value="{{ old('quantity', $specification->default_quantity ?? 1) }}">
            <input type="hidden" name="unit_price" value="{{ old('unit_price', $specification->default_unit_price ?? 0) }}">
            <input type="hidden" name="required_date" value="{{ old('required_date', now()->toDateString()) }}">
            <input type="hidden" name="priority" value="{{ old('priority', $specification->default_priority?->value ?? 'normal') }}">
            <input type="hidden" name="fulfilment_method" value="{{ old('fulfilment_method', $specification->default_fulfilment_method?->value ?? FulfilmentMethod::Collection->value) }}">
            <input type="hidden" name="billing_type" value="{{ old('billing_type', $specification->default_billing_type?->value) }}">
            <input type="hidden" name="notes" value="{{ old('notes', $specification->customer_instructions) }}">

            <div class="flex flex-wrap justify-end gap-2 pt-1">
                <button type="submit" class="erp-btn-primary">{{ __('Create order') }}</button>
            </div>
        </form>
    @endif
</div>
