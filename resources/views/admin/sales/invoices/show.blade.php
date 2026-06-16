<x-admin-layout :title="$invoice->invoice_number" :breadcrumbs="[['label' => __('Invoices'), 'url' => route('admin.invoices.index')], ['label' => $invoice->invoice_number]]">
    <x-admin.page-header :title="$invoice->invoice_number" :description="$invoice->customer?->company_name">
        <x-slot:actions>
            <x-admin.status-badge :variant="match($invoice->status) {
                App\Enums\CustomerInvoiceStatus::Draft => 'neutral',
                App\Enums\CustomerInvoiceStatus::Approved => 'info',
                App\Enums\CustomerInvoiceStatus::Posted => 'success',
                App\Enums\CustomerInvoiceStatus::Cancelled => 'warning',
            }">{{ $invoice->status->label() }}</x-admin.status-badge>
            <span class="erp-badge">{{ $invoice->invoice_type->label() }}</span>
            @can('view', $invoice)
                <a href="{{ route('admin.invoices.document', $invoice) }}" class="erp-btn-secondary">{{ __('View document') }}</a>
                <x-documents.pdf-download-button
                    :url="route('admin.invoices.document.pdf', $invoice)"
                    :filename="$invoice->invoice_number"
                    class="erp-btn-secondary"
                />
            @endcan
            @can('update', $invoice)
                <a href="{{ route('admin.invoices.edit', $invoice) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <x-admin.kpi-widget :label="__('Subtotal')" :value="number_format($invoice->subtotal, 2)" icon="currency-dollar" />
        <x-admin.kpi-widget :label="__('Tax')" :value="number_format($invoice->tax_amount, 2)" icon="receipt-tax" />
        <x-admin.kpi-widget :label="__('Total')" :value="number_format($invoice->total_amount, 2)" icon="calculator" />
        @if ($invoice->billing_percent)
            <x-admin.kpi-widget :label="__('Progress')" :value="$invoice->billing_percent.'%'" icon="chart-bar" />
        @endif
    </div>

    <x-admin.card class="mb-4">
        <div class="flex flex-wrap gap-2">
            @can('approve', $invoice)
                <form method="POST" action="{{ route('admin.invoices.approve', $invoice) }}">@csrf
                    <button type="submit" class="erp-btn-primary">{{ __('Approve') }}</button></form>
            @endcan
            @can('post', $invoice)
                <form method="POST" action="{{ route('admin.invoices.post', $invoice) }}">@csrf
                    <button type="submit" class="erp-btn-primary">{{ __('Post to AR') }}</button></form>
            @endcan
            @can('creditNote', $invoice)
                <form method="POST" action="{{ route('admin.invoices.credit-note.store', $invoice) }}">@csrf
                    <button type="submit" class="erp-btn-secondary">{{ __('Credit note') }}</button></form>
            @endcan
            @can('cancel', $invoice)
                <form method="POST" action="{{ route('admin.invoices.cancel', $invoice) }}">@csrf
                    <button type="submit" class="erp-btn-secondary text-red-600">{{ __('Cancel') }}</button></form>
            @endcan
            @if ($invoice->status === App\Enums\CustomerInvoiceStatus::Posted && $invoice->balance_due > 0)
                @can('create', App\Models\Sales\CustomerPayment::class)
                    <a href="{{ route('admin.payments.create', ['customer_id' => $invoice->customer_id, 'invoice_id' => $invoice->id]) }}" class="erp-btn-primary">{{ __('Record payment') }}</a>
                @endcan
            @endif
        </div>
    </x-admin.card>

    @if ($invoice->status === App\Enums\CustomerInvoiceStatus::Posted)
        <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
            <x-admin.kpi-widget :label="__('Paid')" :value="number_format($invoice->amount_paid, 2)" icon="currency-dollar" />
            <x-admin.kpi-widget :label="__('Balance due')" :value="number_format($invoice->balance_due, 2)" icon="scale" />
        </div>
    @endif

    @php
        $postedPayments = $invoice->paymentAllocations
            ->map(fn ($allocation) => $allocation->payment)
            ->filter(fn ($payment) => $payment && $payment->status === App\Enums\CustomerPaymentStatus::Posted)
            ->unique('id');
    @endphp
    @if ($postedPayments->isNotEmpty())
        <x-admin.card class="mb-4">
            <h3 class="font-medium mb-3">{{ __('Payment receipts') }}</h3>
            <ul class="text-sm space-y-1">
                @foreach ($postedPayments as $payment)
                    <li class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('admin.payments.show', $payment) }}" class="font-mono text-erp-accent">{{ $payment->payment_number }}</a>
                        <span class="text-slate-500">{{ number_format($payment->amount, 2) }}</span>
                        @can('viewReceipt', $payment)
                            <a href="{{ route('admin.payments.receipt', $payment) }}" class="text-erp-accent text-xs">{{ __('View receipt') }}</a>
                        @endcan
                    </li>
                @endforeach
            </ul>
        </x-admin.card>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('References') }}</h3>
            <dl class="text-sm space-y-2">
                @if ($invoice->salesOrder)
                    <div><dt class="text-slate-500">{{ __('Sales order') }}</dt>
                        <dd><a href="{{ route('admin.sales-orders.show', $invoice->salesOrder) }}" class="text-erp-accent">{{ $invoice->salesOrder->order_number }}</a></dd></div>
                @endif
                @if ($invoice->jobCard)
                    <div><dt class="text-slate-500">{{ __('Job card') }}</dt>
                        <dd><a href="{{ route('admin.production.job-cards.show', $invoice->jobCard) }}" class="text-erp-accent">{{ $invoice->jobCard->job_card_number }}</a></dd></div>
                @endif
                @if ($invoice->creditedInvoice)
                    <div><dt class="text-slate-500">{{ __('Credits') }}</dt>
                        <dd><a href="{{ route('admin.invoices.show', $invoice->creditedInvoice) }}" class="text-erp-accent">{{ $invoice->creditedInvoice->invoice_number }}</a></dd></div>
                @endif
                @if ($invoice->postedJournal)
                    <div><dt class="text-slate-500">{{ __('GL journal') }}</dt>
                        <dd><a href="{{ route('admin.accounting.journals.show', $invoice->postedJournal) }}" class="text-erp-accent">{{ $invoice->postedJournal->journal_number }}</a></dd></div>
                @endif
            </dl>
        </x-admin.card>
        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Tax summary') }}</h3>
            @forelse ($invoice->taxLines as $tax)
                <div class="flex justify-between text-sm py-1 border-b border-erp-border">
                    <span>{{ $tax->tax_name }}</span>
                    <span class="font-mono">{{ number_format($tax->tax_amount, 2) }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-500">{{ __('No tax lines') }}</p>
            @endforelse
        </x-admin.card>
    </div>

    <x-admin.card>
        <h3 class="font-medium mb-3">{{ __('Lines') }}</h3>
        <table class="w-full text-sm">
            <thead><tr class="text-left text-[11px] uppercase text-slate-400"><th>{{ __('Item') }}</th><th>{{ __('Qty') }}</th><th>{{ __('Price') }}</th><th>{{ __('Tax %') }}</th><th>{{ __('Total') }}</th></tr></thead>
            <tbody>
                @foreach ($invoice->lines as $line)
                    <tr class="border-t border-erp-border">
                        <td class="py-2">{{ $line->item_name }}</td>
                        <td class="py-2">{{ $line->quantity }}</td>
                        <td class="py-2 font-mono">{{ number_format($line->unit_price, 2) }}</td>
                        <td class="py-2">{{ $line->tax_rate }}%</td>
                        <td class="py-2 font-mono">{{ number_format($line->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>
</x-admin-layout>
