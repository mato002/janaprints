<x-admin.modal-form :title="$salesOrder->order_number" maxWidth="4xl">
    <div class="space-y-4">
        <div class="flex flex-wrap items-center gap-2">
            <span class="erp-badge">{{ str_replace('_', ' ', $salesOrder->status->value) }}</span>
            <span class="text-sm text-slate-600">{{ $salesOrder->customer?->company_name ?? '—' }}</span>
        </div>

        <dl class="grid grid-cols-2 gap-3 text-sm">
            <div><dt class="text-slate-500">{{ __('Subtotal') }}</dt><dd class="font-mono">{{ number_format((float) $salesOrder->subtotal, 2) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Total') }}</dt><dd class="font-mono font-medium">{{ number_format((float) $salesOrder->total_amount, 2) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Required') }}</dt><dd>{{ $salesOrder->required_date?->format('d M Y') ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Fulfilment') }}</dt><dd>{{ $salesOrder->fulfilment_method?->label() ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Billing') }}</dt><dd>{{ $salesOrder->billing_type?->label() ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Job') }}</dt><dd>{{ $salesOrder->jobCard?->job_card_number ?? '—' }}</dd></div>
        </dl>

        @php $financial = app(\App\Support\Sales\SalesOrderFinancialStatusService::class)->snapshot($salesOrder); @endphp
        <p class="text-sm text-slate-600">
            {{ __('Payment status') }}: <span class="font-medium">{{ $financial['financial_status_label'] ?? '—' }}</span>
        </p>

        @if ($salesOrder->items->isNotEmpty())
            <div class="rounded-lg border border-erp-border bg-white p-4">
                <h3 class="mb-2 text-sm font-semibold text-slate-900">{{ __('Order lines') }}</h3>
                @foreach ($salesOrder->items as $item)
                    <div class="border-b border-slate-100 py-2 text-sm last:border-0">
                        <span class="font-medium">{{ $item->item_name }}</span>
                        <span class="text-slate-500"> — {{ number_format((float) $item->quantity, 2) }} × {{ number_format((float) $item->unit_price, 2) }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        @if (request('from') === 'sales-desk')
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.sales-orders.edit', [$salesOrder, 'from' => 'sales-desk']) }}" class="erp-btn-secondary text-sm" data-erp-modal-open>{{ __('Edit order') }}</a>
                @can('create', App\Models\Sales\CustomerPayment::class)
                    <a href="{{ route('admin.payments.create', ['from' => 'sales-desk', 'customer_id' => $salesOrder->customer_id, 'sales_order_id' => $salesOrder->id]) }}" class="erp-btn-secondary text-sm" data-erp-modal-open>{{ __('Record payment') }}</a>
                @endcan
                @can('create', App\Models\Sales\CustomerInvoice::class)
                    <a href="{{ route('admin.invoices.from-sales-order', [$salesOrder, 'from' => 'sales-desk']) }}" class="erp-btn-secondary text-sm" data-erp-modal-open>{{ __('Create invoice') }}</a>
                @endcan
            </div>
        @endif
    </div>
</x-admin.modal-form>
