<p>{{ __('Dear :customer,', ['customer' => $receipt['customer_name']]) }}</p>

<p>{{ __('Thank you for your payment. Please find your receipt details below.') }}</p>
{{-- TODO(DOC-6): Replace with professional HTML document template matching documents/receipt/public.blade.php --}}

<ul>
    <li><strong>{{ __('Receipt number') }}:</strong> {{ $receipt['receipt_number'] }}</li>
    <li><strong>{{ __('Date') }}:</strong> {{ $receipt['payment_date'] }}</li>
    <li><strong>{{ __('Amount') }}:</strong> {{ number_format($receipt['amount'], 2) }} {{ $receipt['currency'] }}</li>
    <li><strong>{{ __('Payment method') }}:</strong> {{ $receipt['payment_method'] }}</li>
    <li><strong>{{ __('Balance remaining') }}:</strong> {{ number_format($receipt['balance_remaining'], 2) }} {{ $receipt['currency'] }}</li>
</ul>

@if (! empty($receipt['invoices_settled']))
    <p><strong>{{ __('Invoices settled') }}</strong></p>
    <ul>
        @foreach ($receipt['invoices_settled'] as $row)
            <li>{{ $row['invoice_number'] }} — {{ number_format($row['amount_applied'], 2) }} ({{ __('balance') }}: {{ number_format($row['balance_remaining'], 2) }})</li>
        @endforeach
    </ul>
@endif

<p><a href="{{ $receipt['public_url'] }}">{{ __('View professional receipt online') }}</a></p>

<p class="muted">{{ __('This is an automated message from :company.', ['company' => $receipt['company_name']]) }}</p>
