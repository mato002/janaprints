<div class="so-360__grid so-360__grid--two">
    <article class="so-360__card">
        <h2 class="so-360__card-title">{{ __('Customer & commercial') }}</h2>
        <dl class="so-360__dl so-360__dl--compact">
            <div>
                <dt>{{ __('Customer') }}</dt>
                <dd>
                    @if ($salesOrder->customer)
                        <a href="{{ route('admin.crm.customers.show', $salesOrder->customer) }}" class="so-360__link" data-turbo-frame="erp-main">
                            {{ $salesOrder->customer->company_name }}
                        </a>
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div>
                <dt>{{ __('Quotation') }}</dt>
                <dd>
                    @if ($salesOrder->quotation)
                        <a href="{{ route('admin.quotations.show', $salesOrder->quotation) }}" class="so-360__link" data-turbo-frame="erp-main">
                            {{ $salesOrder->quotation->quotation_number }}
                        </a>
                    @else
                        {{ $salesOrder->is_direct_order ? __('Direct order') : '—' }}
                    @endif
                </dd>
            </div>
            <div>
                <dt>{{ __('Created by') }}</dt>
                <dd>{{ $salesOrder->creator?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt>{{ __('Branch') }}</dt>
                <dd>{{ $salesOrder->branch?->name ?? '—' }}</dd>
            </div>
        </dl>
    </article>

    <article class="so-360__card">
        <h2 class="so-360__card-title">{{ __('Totals') }}</h2>
        <div class="so-360__kpi-row">
            <div class="so-360__kpi">
                <span class="so-360__kpi-label">{{ __('Subtotal') }}</span>
                <span class="so-360__kpi-value font-mono">{{ number_format($salesOrder->subtotal, 2) }}</span>
            </div>
            <div class="so-360__kpi">
                <span class="so-360__kpi-label">{{ __('Tax') }}</span>
                <span class="so-360__kpi-value font-mono">{{ number_format($salesOrder->tax_amount, 2) }}</span>
            </div>
            <div class="so-360__kpi so-360__kpi--emphasis">
                <span class="so-360__kpi-label">{{ __('Total') }}</span>
                <span class="so-360__kpi-value font-mono">{{ number_format($salesOrder->total_amount, 2) }}</span>
            </div>
        </div>
    </article>
</div>

<article class="so-360__card mt-4">
    <div class="so-360__card-head">
        <h2 class="so-360__card-title">{{ __('Line items') }}</h2>
        @can('update', $salesOrder)
            <a href="{{ route('admin.sales-orders.edit', $salesOrder) }}" class="so-360__text-btn">{{ __('Edit order') }}</a>
        @endcan
    </div>
    @forelse ($salesOrder->items as $item)
        @include('admin.sales.orders.partials.item-specification', [
            'salesOrder' => $salesOrder,
            'item' => $item,
            'itemSpecifications' => $itemSpecifications ?? [],
        ])
    @empty
        <p class="text-sm text-slate-500">{{ __('No line items.') }}</p>
    @endforelse
</article>
