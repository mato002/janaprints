<x-admin-layout :title="$document['documentNumber']" :breadcrumbs="[['label' => __('Payments'), 'url' => route('admin.payments.index')], ['label' => $payment->payment_number, 'url' => route('admin.payments.show', $payment)], ['label' => __('Receipt')]]">
    <x-admin.page-header class="jp-doc-print-hide" :title="$document['title']" :description="$document['documentNumber']">
        <a href="{{ route('admin.payments.show', $payment) }}" class="erp-btn-secondary">{{ __('Back to payment') }}</a>
    </x-admin.page-header>

    <div class="jp-doc-actions mb-4 flex flex-wrap justify-center gap-2 print:hidden">
        <button type="button" onclick="window.print()" class="erp-btn-primary">{{ __('Print receipt') }}</button>
        @can('downloadReceiptPdf', $payment)
            <x-documents.pdf-download-button
                :url="route('admin.payments.receipt.pdf', $payment)"
                :filename="$payment->receipt_number"
                class="erp-btn-secondary"
            />
        @endcan
        @can('emailReceipt', $payment)
            <x-documents.email-submit-form
                :action="route('admin.payments.receipt.email', $payment)"
                :label="__('Email receipt')"
                :submitting-label="__('Sending email…')"
                :submitting-message="filled($payment->customer?->email)
                    ? __('Sending receipt to :recipient…', ['recipient' => $payment->customer->email])
                    : __('Sending receipt…')"
            />
        @endcan
        @can('smsReceipt', $payment)
            <form method="POST" action="{{ route('admin.payments.receipt.sms', $payment) }}">@csrf
                <button type="submit" class="erp-btn-secondary">{{ __('SMS link') }}</button>
            </form>
        @endcan
    </div>

    <x-admin.card class="mx-auto max-w-4xl print:shadow-none print:border-0" id="payment-receipt">
        @include('documents.partials.styles')
        @include('documents.partials.print-styles')
        <div class="jp-doc p-6">
            @include('documents.receipt.content', ['document' => $document])
        </div>
    </x-admin.card>
</x-admin-layout>
