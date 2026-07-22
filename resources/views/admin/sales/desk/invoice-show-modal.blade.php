<x-admin.modal-form :title="$invoice->invoice_number" maxWidth="2xl">
    <div class="space-y-4">
        <dl class="grid grid-cols-2 gap-3 text-sm">
            <div><dt class="text-slate-500">{{ __('Customer') }}</dt><dd class="font-medium">{{ $invoice->customer?->company_name ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Status') }}</dt><dd>{{ str_replace('_', ' ', $invoice->status->value) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Total') }}</dt><dd class="font-mono font-medium">{{ number_format((float) $invoice->total_amount, 2) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Balance due') }}</dt><dd class="font-mono">{{ number_format((float) $invoice->balance_due, 2) }}</dd></div>
        </dl>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.invoices.document', $invoice) }}" class="erp-btn-primary text-sm" target="_blank" rel="noopener">{{ __('Print invoice') }}</a>
            @if ($invoice->salesOrder)
                <a href="{{ route('admin.sales.desk', ['customer' => $invoice->customer?->getRouteKey(), 'order' => $invoice->salesOrder->getRouteKey(), 'step' => 4]) }}" class="erp-btn-secondary text-sm" data-turbo-frame="_top">{{ __('Back to desk') }}</a>
            @endif
        </div>
    </div>
</x-admin.modal-form>
