<x-admin.card class="mx-auto max-w-lg print:shadow-none" id="payment-receipt">
    @include('admin.sales.payments.partials.receipt-content')
</x-admin.card>

@if ($printActions ?? false)
    <div class="mt-4 flex flex-wrap justify-center gap-2 print:hidden">
        <button type="button" onclick="window.print()" class="erp-btn-primary">{{ __('Print receipt') }}</button>
        @can('downloadReceiptPdf', $payment)
            <a href="{{ route('admin.payments.receipt.pdf', $payment) }}" class="erp-btn-secondary">{{ __('Download PDF') }}</a>
        @endcan
        @can('emailReceipt', $payment)
            <form method="POST" action="{{ route('admin.payments.receipt.email', $payment) }}">@csrf
                <button type="submit" class="erp-btn-secondary">{{ __('Email receipt') }}</button>
            </form>
        @endcan
        @can('smsReceipt', $payment)
            <form method="POST" action="{{ route('admin.payments.receipt.sms', $payment) }}">@csrf
                <button type="submit" class="erp-btn-secondary">{{ __('SMS link') }}</button>
            </form>
        @endcan
        <a href="{{ route('admin.payments.show', $payment) }}" class="erp-btn-secondary">{{ __('Back to payment') }}</a>
    </div>
@endif
