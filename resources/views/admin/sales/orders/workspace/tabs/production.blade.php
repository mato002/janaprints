<article class="so-360__card mb-4" id="production-destination">
    <div class="so-360__card-head">
        <h2 class="so-360__card-title">{{ __('Where this order is going') }}</h2>
    </div>
    @can('updateProductionSetup', $salesOrder)
        <form method="POST" action="{{ route('admin.sales-orders.production-setup.update', $salesOrder) }}" class="space-y-3">
            @csrf
            @method('PATCH')
            @include('admin.sales.orders.partials.production-destination-picker', [
                'value' => old('production_destination', $salesOrder->production_destination?->value),
                'required' => true,
            ])
            <button type="submit" class="erp-btn-secondary">{{ __('Save destination') }}</button>
        </form>
    @else
        <p class="text-sm font-semibold text-slate-900">{{ $salesOrder->production_destination?->label() ?? __('Not set') }}</p>
        <p class="mt-1 text-xs text-slate-500">{{ $salesOrder->production_destination?->hint() }}</p>
    @endcan
</article>

<div class="so-360__grid so-360__grid--two">
    <article class="so-360__card">
        <h2 class="so-360__card-title">{{ __('Production product') }}</h2>
        @if ($salesOrder->inventoryItem)
            <p class="mb-3 text-sm">
                <span class="font-semibold text-slate-900">{{ $salesOrder->inventoryItem->item_name }}</span>
                <span class="text-slate-500">({{ $salesOrder->inventoryItem->sku }})</span>
            </p>
        @else
            <p class="mb-3 text-sm text-amber-700">{{ __('No catalogue product linked yet. Link a finished-good inventory item so production and material requirements can run.') }}</p>
        @endif

        @can('updateProductionSetup', $salesOrder)
            <form method="POST" action="{{ route('admin.sales-orders.production-setup.update', $salesOrder) }}" class="flex flex-wrap items-end gap-2">
                @csrf
                @method('PATCH')
                <div class="min-w-[16rem] flex-1">
                    <label class="erp-label">{{ __('Catalogue item') }}</label>
                    <select name="inventory_item_id" class="erp-input w-full" required>
                        <option value="">{{ __('Select product') }}</option>
                        @foreach ($catalogueItems as $item)
                            <option value="{{ $item->id }}" @selected($salesOrder->inventory_item_id == $item->id)>
                                {{ $item->item_name }} ({{ $item->sku }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="erp-btn-secondary">{{ $salesOrder->inventoryItem ? __('Update product') : __('Link product') }}</button>
            </form>
        @endcan
    </article>

    <article class="so-360__card">
        <h2 class="so-360__card-title">{{ __('Production links') }}</h2>
        <dl class="so-360__dl so-360__dl--compact">
            <div>
                <dt>{{ __('Artwork') }}</dt>
                <dd>
                    @if ($salesOrder->artworkRequest)
                        <a href="{{ route('admin.artwork.show', $salesOrder->artworkRequest) }}" class="so-360__link" data-turbo-frame="erp-main">
                            {{ $salesOrder->artworkRequest->request_number }}
                        </a>
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div>
                <dt>{{ __('Linked job card') }}</dt>
                <dd>
                    @if ($salesOrder->jobCard)
                        <a href="{{ route('admin.production.job-cards.show', $salesOrder->jobCard) }}" class="so-360__link font-mono" data-turbo-frame="erp-main">
                            {{ $salesOrder->jobCard->job_card_number }}
                        </a>
                    @else
                        {{ __('Not created') }}
                    @endif
                </dd>
            </div>
        </dl>

        @if (($workflow['can_release'] ?? false))
            @can('production', $salesOrder)
                <form method="POST" action="{{ route('admin.sales-orders.release-to-production', $salesOrder) }}" class="mt-4">
                    @csrf
                    <button type="submit" class="erp-btn-primary">{{ $salesOrder->production_destination?->sendToLabel() ?? __('Send to production') }}</button>
                </form>
            @endcan
        @endif
    </article>
</div>

<div class="mt-4 flex flex-wrap items-center justify-between gap-2">
    <p class="text-sm text-slate-600">{{ __('Print recipes for each line live on the Specifications tab.') }}</p>
    <div class="flex flex-wrap items-center gap-2">
        <button type="button" class="erp-btn-secondary text-xs" @click="setTab('specifications')">{{ __('Open specifications') }}</button>
        <a
            href="{{ route('admin.sales-orders.specifications.print', $salesOrder) }}"
            class="erp-btn-secondary text-xs"
            target="_blank"
            rel="noopener"
            data-turbo="false"
        >{{ __('Print specifications') }}</a>
    </div>
</div>

<details class="so-360__collapse so-360__collapse--block mt-3">
    <summary>{{ __('How production will make each line') }}</summary>
    <div class="so-360__collapse-body">
        <p class="mb-3 text-sm text-slate-600">{{ __('A production specification is the print recipe — size, paper, ink, finishing — copied onto the job card when you send this order to production. It is not the sales price.') }}</p>
        @forelse ($salesOrder->items as $item)
            @include('admin.sales.orders.partials.item-specification', [
                'salesOrder' => $salesOrder,
                'item' => $item,
                'itemSpecifications' => $itemSpecifications ?? [],
            ])
        @empty
            <p class="text-sm text-slate-500">{{ __('No line items.') }}</p>
        @endforelse
    </div>
</details>
