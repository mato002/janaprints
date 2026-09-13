@php
    $from = request('from');
    $collection = $invoice->collectionStatus();
    $linkedOrders = $invoice->salesOrders->isNotEmpty()
        ? $invoice->salesOrders
        : collect([$invoice->salesOrder])->filter();
    $postedPayments = $invoice->paymentAllocations
        ->map(fn ($allocation) => $allocation->payment)
        ->filter(fn ($payment) => $payment && $payment->status === App\Enums\CustomerPaymentStatus::Posted)
        ->unique('id');
@endphp

<x-admin.modal-form :title="$invoice->invoice_number" maxWidth="3xl">
    <div class="space-y-5">
        <div class="flex flex-wrap items-center gap-2">
            <x-admin.status-badge :variant="$collection->badgeVariant()">{{ $collection->label() }}</x-admin.status-badge>
            <x-admin.status-badge :variant="match($invoice->status) {
                App\Enums\CustomerInvoiceStatus::Draft => 'neutral',
                App\Enums\CustomerInvoiceStatus::Approved => 'info',
                App\Enums\CustomerInvoiceStatus::Posted => 'success',
                App\Enums\CustomerInvoiceStatus::Cancelled => 'warning',
            }">{{ $invoice->status->label() }}</x-admin.status-badge>
            <span class="erp-badge">{{ $invoice->invoice_type->label() }}</span>
        </div>

        <dl class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-3">
            <div>
                <dt class="text-slate-500">{{ __('Customer') }}</dt>
                <dd class="font-medium">{{ $invoice->customer?->company_name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">{{ __('Date') }}</dt>
                <dd>{{ $invoice->invoice_date?->format('d M Y') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">{{ __('Due date') }}</dt>
                <dd>{{ $invoice->due_date?->format('d M Y') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">{{ __('Total') }}</dt>
                <dd class="font-mono font-medium">{{ number_format((float) $invoice->total_amount, 2) }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">{{ __('Paid') }}</dt>
                <dd class="font-mono">{{ number_format((float) $invoice->amount_paid, 2) }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">{{ __('Balance due') }}</dt>
                <dd class="font-mono font-medium">{{ number_format((float) $invoice->balance_due, 2) }}</dd>
            </div>
        </dl>

        @if ($linkedOrders->isNotEmpty())
            <div>
                <h3 class="mb-2 text-sm font-medium text-slate-900">{{ __('Orders') }}</h3>
                <ul class="space-y-1 text-sm">
                    @foreach ($linkedOrders as $linkedOrder)
                        <li class="flex flex-wrap items-center gap-2">
                            <a
                                href="{{ route('admin.sales-orders.show', [$linkedOrder, 'from' => $from]) }}"
                                class="font-mono text-erp-accent hover:underline"
                                data-erp-modal-open
                            >{{ $linkedOrder->order_number }}</a>
                            @if ($linkedOrder->production_destination)
                                <span class="erp-badge">{{ $linkedOrder->production_destination->label() }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($invoice->lines->isNotEmpty())
            <div class="overflow-x-auto rounded-lg border border-erp-border">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-[11px] uppercase text-slate-400">
                            <th class="px-3 py-2">{{ __('Item') }}</th>
                            <th class="px-3 py-2">{{ __('Qty') }}</th>
                            <th class="px-3 py-2">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->lines as $line)
                            <tr class="border-t border-erp-border">
                                <td class="px-3 py-2">{{ $line->item_name }}</td>
                                <td class="px-3 py-2">{{ $line->quantity }}</td>
                                <td class="px-3 py-2 font-mono">{{ number_format((float) $line->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($postedPayments->isNotEmpty())
            <div>
                <h3 class="mb-2 text-sm font-medium text-slate-900">{{ __('Payments') }}</h3>
                <ul class="space-y-1 text-sm">
                    @foreach ($postedPayments as $payment)
                        <li class="flex flex-wrap items-center gap-2">
                            <span class="font-mono">{{ $payment->payment_number }}</span>
                            <span class="text-slate-500">{{ number_format((float) $payment->amount, 2) }}</span>
                            @can('viewReceipt', $payment)
                                <a href="{{ route('admin.payments.receipt', $payment) }}" class="text-xs text-erp-accent" target="_blank" rel="noopener">{{ __('Receipt') }}</a>
                            @endcan
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <x-admin.workflow-error />

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.invoices.document', $invoice) }}" class="erp-btn-secondary text-sm" target="_blank" rel="noopener">{{ __('Print invoice') }}</a>

            @can('approve', $invoice)
                <form method="POST" action="{{ route('admin.invoices.approve', $invoice) }}">
                    @csrf
                    @if ($from)
                        <input type="hidden" name="from" value="{{ $from }}">
                    @endif
                    <button type="submit" class="erp-btn-primary text-sm">{{ __('Approve') }}</button>
                </form>
            @endcan

            @can('post', $invoice)
                <form method="POST" action="{{ route('admin.invoices.post', $invoice) }}">
                    @csrf
                    @if ($from)
                        <input type="hidden" name="from" value="{{ $from }}">
                    @endif
                    <button type="submit" class="erp-btn-primary text-sm">{{ __('Post to AR') }}</button>
                </form>
            @endcan

            @if ($invoice->status === App\Enums\CustomerInvoiceStatus::Posted && $invoice->balance_due > 0)
                @can('create', App\Models\Sales\CustomerPayment::class)
                    <a
                        href="{{ route('admin.payments.create', array_filter([
                            'customer_id' => $invoice->customer_id,
                            'invoice_id' => $invoice->id,
                            'from' => $from ?: null,
                        ])) }}"
                        class="erp-btn-primary text-sm"
                        data-erp-modal-open
                    >{{ __('Record payment') }}</a>
                @endcan
            @endif

            @if ($from === 'sales-desk' && $invoice->salesOrder)
                <a href="{{ route('admin.sales.desk', ['customer' => $invoice->customer?->getRouteKey(), 'order' => $invoice->salesOrder->getRouteKey(), 'step' => 4]) }}" class="erp-btn-secondary text-sm" data-turbo-frame="erp-main">{{ __('Back to desk') }}</a>
            @endif
        </div>
    </div>
</x-admin.modal-form>
