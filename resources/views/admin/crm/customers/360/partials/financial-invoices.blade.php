<div class="overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-3 py-2 text-left">{{ __('Invoice') }}</th>
                <th class="px-3 py-2 text-left">{{ __('Date') }}</th>
                <th class="px-3 py-2 text-left">{{ __('Due') }}</th>
                <th class="px-3 py-2 text-right">{{ __('Total') }}</th>
                <th class="px-3 py-2 text-right">{{ __('Balance') }}</th>
                <th class="px-3 py-2 text-left">{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($invoices as $invoice)
                <tr>
                    <td class="px-3 py-2"><a href="{{ route('admin.invoices.show', $invoice) }}" class="font-mono text-erp-accent">{{ $invoice->invoice_number }}</a></td>
                    <td class="px-3 py-2">{{ $invoice->invoice_date->format('M j, Y') }}</td>
                    <td class="px-3 py-2">{{ $invoice->due_date?->format('M j, Y') ?? '—' }}</td>
                    <td class="px-3 py-2 text-right font-mono">{{ number_format($invoice->total_amount, 2) }}</td>
                    <td class="px-3 py-2 text-right font-mono">{{ number_format($invoice->balance_due, 2) }}</td>
                    <td class="px-3 py-2"><x-admin.status-badge :variant="match($invoice->status->value) { 'posted' => 'success', 'approved' => 'info', 'cancelled' => 'warning', default => 'neutral' }">{{ $invoice->status->label() }}</x-admin.status-badge></td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-3 py-6 text-center text-slate-500">{{ __('No invoices') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if (empty($compact) && method_exists($invoices, 'links'))
    <div class="mt-4"><x-admin.table-pagination :paginator="$invoices" /></div>
@endif
