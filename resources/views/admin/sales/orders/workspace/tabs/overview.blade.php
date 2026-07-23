@php
    $firstItem = $salesOrder->items->first();
    $itemCount = $salesOrder->items->count();
    $productLabel = $salesOrder->inventoryItem?->item_name
        ?? $firstItem?->item_name
        ?? __('No product linked');
@endphp

{{-- Section 1: high-value operational summary --}}
<section class="so-360__section so-360__section--primary">
    <div class="so-360__grid so-360__grid--summary">
        <article class="so-360__card so-360__card--hero">
            <h2 class="so-360__card-title">{{ __('Order summary') }}</h2>
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
                    <dt>{{ __('Status') }}</dt>
                    <dd class="capitalize">{{ str_replace('_', ' ', $salesOrder->status->value) }}</dd>
                </div>
                <div>
                    <dt>{{ __('Total') }}</dt>
                    <dd class="font-mono text-base font-semibold text-slate-900">{{ number_format($salesOrder->total_amount, 2) }}</dd>
                </div>
                <div>
                    <dt>{{ __('Order date') }}</dt>
                    <dd>{{ optional($salesOrder->order_date)->format('M j, Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt>{{ __('Required date') }}</dt>
                    <dd>{{ optional($salesOrder->required_date)->format('M j, Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt>{{ __('Priority') }}</dt>
                    <dd class="capitalize">{{ $salesOrder->priority?->value ? str_replace('_', ' ', $salesOrder->priority->value) : '—' }}</dd>
                </div>
            </dl>
        </article>

        <article class="so-360__card">
            <h2 class="so-360__card-title">{{ __('Workflow progress') }}</h2>
            <ol class="so-360__mini-pipeline">
                @foreach ($workflow['pipeline'] as $step)
                    <li @class([
                        'so-360__mini-step',
                        'so-360__mini-step--complete' => $step['state'] === 'complete',
                        'so-360__mini-step--current' => $step['state'] === 'current',
                        'so-360__mini-step--paused' => $step['state'] === 'paused',
                        'so-360__mini-step--cancelled' => $step['state'] === 'cancelled',
                        'so-360__mini-step--upcoming' => in_array($step['state'], ['upcoming'], true),
                    ])>{{ $step['label'] }}</li>
                @endforeach
            </ol>
            @if ($workflow['hint'] ?? null)
                <p class="mt-3 text-xs text-slate-600">{{ $workflow['hint'] }}</p>
            @endif
        </article>
    </div>
</section>

{{-- Section 2: commercial + production --}}
<section class="so-360__section">
    <div class="so-360__grid so-360__grid--two">
        <article class="so-360__card">
            <div class="so-360__card-head">
                <h2 class="so-360__card-title">{{ __('Commercial summary') }}</h2>
                <button type="button" class="so-360__text-btn" @click="setTab('commercial')">{{ __('Open') }}</button>
            </div>
            <dl class="so-360__dl so-360__dl--compact">
                <div>
                    <dt>{{ __('Line items') }}</dt>
                    <dd>{{ $itemCount }}</dd>
                </div>
                <div>
                    <dt>{{ __('Subtotal') }}</dt>
                    <dd class="font-mono">{{ number_format($salesOrder->subtotal, 2) }}</dd>
                </div>
                <div>
                    <dt>{{ __('Tax') }}</dt>
                    <dd class="font-mono">{{ number_format($salesOrder->tax_amount, 2) }}</dd>
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
            </dl>
        </article>

        <article class="so-360__card">
            <div class="so-360__card-head">
                <h2 class="so-360__card-title">{{ __('Production summary') }}</h2>
                <button type="button" class="so-360__text-btn" @click="setTab('production')">{{ __('Open') }}</button>
            </div>
            <dl class="so-360__dl so-360__dl--compact">
                <div>
                    <dt>{{ __('Product') }}</dt>
                    <dd>{{ $productLabel }}</dd>
                </div>
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
                <div>
                    <dt>{{ __('Catalogue SKU') }}</dt>
                    <dd class="font-mono">{{ $salesOrder->inventoryItem?->sku ?? '—' }}</dd>
                </div>
            </dl>
        </article>
    </div>
</section>

{{-- Section 3: financial + billing --}}
<section class="so-360__section">
    <div class="so-360__grid so-360__grid--two">
        <article class="so-360__card">
            <div class="so-360__card-head">
                <h2 class="so-360__card-title">{{ __('Financial summary') }}</h2>
                <button type="button" class="so-360__text-btn" @click="setTab('financial')">{{ __('Details') }}</button>
            </div>
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
            @if (! empty($profitability))
                <details class="so-360__collapse mt-3">
                    <summary>{{ __('Estimated profitability') }}</summary>
                    <dl class="so-360__dl so-360__dl--compact mt-2">
                        <div><dt>{{ __('Profit') }}</dt><dd class="font-mono">{{ number_format($profitability['estimated_profit'], 2) }}</dd></div>
                        <div><dt>{{ __('Margin') }}</dt><dd>{{ number_format($profitability['estimated_margin_percent'], 1) }}%</dd></div>
                        <div><dt>{{ __('Material') }}</dt><dd class="font-mono">{{ number_format($profitability['material_cost'], 2) }}</dd></div>
                        <div><dt>{{ __('Waste') }}</dt><dd class="font-mono">{{ number_format($profitability['wastage_cost'], 2) }}</dd></div>
                    </dl>
                </details>
            @endif
        </article>

        <article class="so-360__card">
            <div class="so-360__card-head">
                <h2 class="so-360__card-title">{{ __('Billing summary') }}</h2>
                <button type="button" class="so-360__text-btn" @click="setTab('financial')">{{ __('Invoices') }}</button>
            </div>
            <dl class="so-360__dl so-360__dl--compact">
                <div>
                    <dt>{{ __('Invoiced') }}</dt>
                    <dd class="font-mono">{{ number_format($salesOrder->invoiced_total, 2) }}</dd>
                </div>
                <div>
                    <dt>{{ __('Remaining') }}</dt>
                    <dd class="font-mono">{{ number_format($salesOrder->remainingInvoiceTotal(), 2) }}</dd>
                </div>
                @if (! empty($financial['payment']))
                    <div>
                        <dt>{{ __('Paid') }}</dt>
                        <dd class="font-mono">{{ number_format($financial['payment']['amount_paid'], 2) }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Outstanding') }}</dt>
                        <dd class="font-mono">{{ number_format($financial['payment']['amount_outstanding'], 2) }}</dd>
                    </div>
                @endif
                @if (! empty($financial['deposit']))
                    <div>
                        <dt>{{ __('Billing type') }}</dt>
                        <dd>{{ $financial['deposit']['billing_type'] }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Deposit paid') }}</dt>
                        <dd class="font-mono">{{ number_format($financial['deposit']['paid'], 2) }}</dd>
                    </div>
                @endif
            </dl>
        </article>
    </div>
</section>

{{-- Collapsible advanced --}}
<details class="so-360__collapse so-360__collapse--block">
    <summary>{{ __('Traceability details') }}</summary>
    <div class="so-360__collapse-body">
        <dl class="so-360__dl so-360__dl--compact">
            <div>
                <dt>{{ __('Quotation') }}</dt>
                <dd>{{ $salesOrder->quotation?->quotation_number ?? '—' }}</dd>
            </div>
            <div>
                <dt>{{ __('Artwork') }}</dt>
                <dd>{{ $salesOrder->artworkRequest?->request_number ?? '—' }}</dd>
            </div>
            <div>
                <dt>{{ __('Job card') }}</dt>
                <dd>
                    @if ($salesOrder->jobCard)
                        <a href="{{ route('admin.production.job-cards.show', $salesOrder->jobCard) }}" class="so-360__link" data-turbo-frame="erp-main">
                            {{ $salesOrder->jobCard->job_card_number }}
                        </a>
                    @else
                        —
                    @endif
                </dd>
            </div>
            @if ($salesOrder->conversion)
                <div class="sm:col-span-2">
                    <dt>{{ __('Converted') }}</dt>
                    <dd>
                        {{ $salesOrder->conversion->created_at?->format('Y-m-d H:i') }}
                        — {{ $salesOrder->conversion->converter?->name }}
                        ({{ __('Quotation rev') }} {{ $salesOrder->conversion->quotation_revision_number }},
                        {{ __('Artwork v') }}{{ $salesOrder->conversion->artwork_version_number }})
                    </dd>
                </div>
            @endif
        </dl>
    </div>
</details>
