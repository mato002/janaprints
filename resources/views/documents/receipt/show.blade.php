<x-admin-layout :title="$document['documentNumber']" :breadcrumbs="[['label' => __('Payments'), 'url' => route('admin.payments.index')], ['label' => $payment->payment_number, 'url' => route('admin.payments.show', $payment)], ['label' => __('Receipt')]]">
    <x-admin.page-header :title="$document['title']" :description="$document['documentNumber']">
        <a href="{{ route('admin.payments.show', $payment) }}" class="erp-btn-secondary">{{ __('Back to payment') }}</a>
    </x-admin.page-header>

    <div class="jp-doc-actions mb-4 flex flex-wrap justify-center gap-2 print:hidden">
        <button type="button" onclick="window.print()" class="erp-btn-primary">{{ __('Print receipt') }}</button>
        @can('downloadReceiptPdf', $payment)
            <a href="{{ route('admin.payments.receipt.pdf', $payment) }}" class="erp-btn-secondary" data-turbo="false">{{ __('Download PDF') }}</a>
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
    </div>

    <x-admin.card class="mx-auto max-w-4xl print:shadow-none print:border-0" id="payment-receipt">
        @include('documents.partials.styles')
        <div class="jp-doc p-6">
            @include('documents.receipt.content', ['document' => $document])
        </div>
    </x-admin.card>
</x-admin-layout>
