@php
    use App\Enums\SalesOrderStatus;
@endphp

<div class="so-360__grid so-360__grid--two">
    <article class="so-360__card">
        <div class="so-360__card-head">
            <h2 class="so-360__card-title">{{ __('Invoicing') }}</h2>
            @can('create', App\Models\Sales\CustomerInvoice::class)
                @if ($salesOrder->remainingInvoiceTotal() > 0 && ! in_array($salesOrder->status, [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled], true))
                    <a href="{{ route('admin.invoices.from-sales-order', $salesOrder) }}" class="erp-btn-primary" data-erp-modal-open>{{ __('Create invoice') }}</a>
                @endif
            @endcan
        </div>

        @if (! empty($financial['billing_eligibility']['blockers']))
            <p class="mb-3 text-sm text-amber-700">{{ implode(' ', $financial['billing_eligibility']['blockers']) }}</p>
            <p class="mb-3 text-xs text-amber-700">{{ __('Use deposit or progress billing on the next screen if you need to invoice before fulfilment is complete.') }}</p>
        @endif

        <dl class="so-360__dl so-360__dl--compact">
            <div><dt>{{ __('Order total') }}</dt><dd class="font-mono">{{ number_format($salesOrder->total_amount, 2) }}</dd></div>
            <div><dt>{{ __('Invoiced') }}</dt><dd class="font-mono">{{ number_format($salesOrder->invoiced_total, 2) }}</dd></div>
            <div><dt>{{ __('Remaining') }}</dt><dd class="font-mono">{{ number_format($salesOrder->remainingInvoiceTotal(), 2) }}</dd></div>
        </dl>

        @if (! empty($financial['payment']))
            <dl class="so-360__dl so-360__dl--compact mt-3 border-t border-slate-100 pt-3">
                <div><dt>{{ __('Payment status') }}</dt><dd>{{ $financial['payment']['label'] }}</dd></div>
                <div><dt>{{ __('Paid') }}</dt><dd class="font-mono">{{ number_format($financial['payment']['amount_paid'], 2) }}</dd></div>
                <div><dt>{{ __('Outstanding') }}</dt><dd class="font-mono">{{ number_format($financial['payment']['amount_outstanding'], 2) }}</dd></div>
                <div><dt>{{ __('Fulfilment ready') }}</dt><dd>{{ ($financial['billing_eligibility']['fulfilment_ready'] ?? false) ? __('Yes') : __('No') }}</dd></div>
            </dl>
        @endif
    </article>

    <article class="so-360__card">
        <h2 class="so-360__card-title">{{ __('Billing & deposit') }}</h2>
        @if (! empty($financial['deposit']))
            <dl class="so-360__dl so-360__dl--compact">
                <div><dt>{{ __('Billing type') }}</dt><dd>{{ $financial['deposit']['billing_type'] }}</dd></div>
                <div><dt>{{ __('Required deposit') }}</dt><dd class="font-mono">{{ number_format($financial['deposit']['required'], 2) }}</dd></div>
                <div><dt>{{ __('Deposit invoiced') }}</dt><dd class="font-mono">{{ number_format($financial['deposit']['invoiced'], 2) }}</dd></div>
                <div><dt>{{ __('Deposit paid') }}</dt><dd class="font-mono">{{ number_format($financial['deposit']['paid'], 2) }}</dd></div>
            </dl>
        @else
            <p class="text-sm text-slate-500">{{ __('No deposit terms on this order.') }}</p>
        @endif

        @if ($salesOrder->invoices->isNotEmpty())
            <ul class="mt-4 space-y-1.5 text-sm">
                @foreach ($salesOrder->invoices as $inv)
                    <li class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 py-1.5 last:border-0">
                        <a href="{{ route('admin.invoices.show', $inv) }}" class="so-360__link font-mono" data-turbo-frame="erp-main">{{ $inv->invoice_number }}</a>
                        <span class="text-slate-600">{{ $inv->status->label() }} · <span class="font-mono">{{ number_format($inv->total_amount, 2) }}</span></span>
                    </li>
                @endforeach
            </ul>
        @endif
    </article>
</div>

@if (! empty($profitability))
    <details class="so-360__collapse so-360__collapse--block mt-4">
        <summary>{{ __('Estimated profitability breakdown') }}</summary>
        <div class="so-360__collapse-body">
            <dl class="so-360__dl so-360__dl--compact sm:!grid-cols-3 lg:!grid-cols-6">
                <div><dt>{{ __('Revenue') }}</dt><dd class="font-mono">{{ number_format($profitability['revenue'], 2) }}</dd></div>
                <div><dt>{{ __('Material cost') }}</dt><dd class="font-mono">{{ number_format($profitability['material_cost'], 2) }}</dd></div>
                <div><dt>{{ __('Waste cost') }}</dt><dd class="font-mono">{{ number_format($profitability['wastage_cost'], 2) }}</dd></div>
                <div><dt>{{ __('Outsource cost') }}</dt><dd class="font-mono">{{ number_format($profitability['outsource_cost'], 2) }}</dd></div>
                <div><dt>{{ __('Estimated profit') }}</dt><dd class="font-mono">{{ number_format($profitability['estimated_profit'], 2) }}</dd></div>
                <div><dt>{{ __('Margin') }}</dt><dd>{{ number_format($profitability['estimated_margin_percent'], 1) }}%</dd></div>
            </dl>
        </div>
    </details>
@endif
