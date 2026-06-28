<x-layouts.client :title="__('Invoices')" :heading="__('Invoices')">
    <div class="client-table-wrap">
        <table class="client-table client-table--cards">
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
                        <td data-label="{{ __('Invoice') }}">{{ $invoice->invoice_number }}</td>
                        <td data-label="{{ __('Date') }}">{{ $invoice->invoice_date?->format('M j, Y') }}</td>
                        <td data-label="{{ __('Due date') }}">{{ $invoice->due_date?->format('M j, Y') ?: '—' }}</td>
                        <td data-label="{{ __('Total') }}">KES {{ number_format((float) $invoice->total_amount, 0) }}</td>
                        <td data-label="{{ __('Balance') }}">KES {{ number_format((float) $invoice->balance_due, 0) }}</td>
                        <td data-label="{{ __('Action') }}"><a href="{{ route('client.invoices.show', $invoice) }}" class="client-link">{{ __('Open') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="client-empty">{{ __('No invoices yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $invoices->links() }}
</x-layouts.client>
