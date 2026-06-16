<x-layouts.client :title="__('Invoices')" :heading="__('Invoices')">
    <div class="client-table-wrap">
        <table class="client-table">
            <thead>
                <tr>
                    <th>{{ __('Invoice') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Due date') }}</th>
                    <th>{{ __('Total') }}</th>
                    <th>{{ __('Balance') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->invoice_date?->format('M j, Y') }}</td>
                        <td>{{ $invoice->due_date?->format('M j, Y') ?: '—' }}</td>
                        <td>KES {{ number_format((float) $invoice->total_amount, 0) }}</td>
                        <td>KES {{ number_format((float) $invoice->balance_due, 0) }}</td>
                        <td><a href="{{ route('client.invoices.show', $invoice) }}" class="client-link">{{ __('Open') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="client-empty">{{ __('No invoices yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $invoices->links() }}
</x-layouts.client>
