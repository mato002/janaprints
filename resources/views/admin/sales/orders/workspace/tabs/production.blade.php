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
                    <button type="submit" class="erp-btn-primary">{{ __('Send to production') }}</button>
                </form>
            @endcan
        @endif
    </article>
</div>

<details class="so-360__collapse so-360__collapse--block mt-4" open>
    <summary>{{ __('Line items & production specifications') }}</summary>
    <div class="so-360__collapse-body">
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
