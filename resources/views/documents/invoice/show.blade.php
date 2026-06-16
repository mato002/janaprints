<x-admin-layout :title="$document['documentNumber']" :breadcrumbs="[['label' => __('Invoices'), 'url' => route('admin.invoices.index')], ['label' => $invoice->invoice_number, 'url' => route('admin.invoices.show', $invoice)], ['label' => __('Document')]]">
    <x-admin.page-header class="jp-doc-print-hide" :title="$document['title']" :description="$document['documentNumber']">
        <a href="{{ route('admin.invoices.show', $invoice) }}" class="erp-btn-secondary">{{ __('Back to invoice') }}</a>
    </x-admin.page-header>

    <div class="jp-doc-actions mb-4 flex flex-wrap gap-2 print:hidden">
        <button type="button" onclick="window.print()" class="erp-btn-primary">{{ __('Print') }}</button>
        <x-documents.pdf-download-button
            :url="route('admin.invoices.document.pdf', $invoice)"
            :filename="$invoice->invoice_number"
            class="erp-btn-secondary"
        />
    </div>

    <x-admin.card class="mx-auto max-w-4xl print:shadow-none print:border-0" id="invoice-document">
        @include('documents.partials.styles')
        @include('documents.partials.print-styles')
        <div class="jp-doc p-6">
            @include('documents.invoice.content', ['document' => $document])
        </div>
    </x-admin.card>
</x-admin-layout>
