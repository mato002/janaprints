<x-layouts.client :title="__('Payments')" :heading="__('Payments')">
    <div class="client-table-wrap">
        <table class="client-table">
            <thead>
                <tr>
                    <th>{{ __('Receipt') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Method') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $row)
                    @php($payment = $row['payment'])
                    <tr>
                        <td>{{ $payment->receipt_number ?: $payment->payment_number }}</td>
                        <td>{{ $payment->payment_date?->format('M j, Y') }}</td>
                        <td>{{ $payment->payment_method->label() }}</td>
                        <td>KES {{ number_format((float) $payment->amount, 0) }}</td>
                        <td><a href="{{ $row['receipt_url'] }}" class="client-link" target="_blank" rel="noopener">{{ __('View receipt') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="client-empty">{{ __('No payments recorded yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $payments->links() }}
</x-layouts.client>
