<x-admin-layout :title="$document['documentNumber']" :breadcrumbs="[['label' => __('Quotations'), 'url' => route('admin.quotations.index')], ['label' => $quotation->quotation_number, 'url' => route('admin.quotations.show', $quotation)], ['label' => __('Document')]]">
    <x-admin.page-header class="jp-doc-print-hide" :title="$document['title']" :description="$document['documentNumber']">
        <a href="{{ route('admin.quotations.show', $quotation) }}" class="erp-btn-secondary">{{ __('Back to quotation') }}</a>
    </x-admin.page-header>

    <div class="jp-doc-actions mb-4 flex flex-wrap gap-2 print:hidden">
        <button type="button" onclick="window.print()" class="erp-btn-primary">{{ __('Print') }}</button>
        <a href="{{ route('admin.quotations.document.pdf', $quotation) }}" class="erp-btn-secondary" data-turbo="false">{{ __('Download PDF') }}</a>
    </div>

    <x-admin.card class="mx-auto max-w-4xl print:shadow-none print:border-0" id="quotation-document">
        @include('documents.partials.styles')
        @include('documents.partials.print-styles')
        <div class="jp-doc p-6">
            @include('documents.quotation.content', ['document' => $document])
        </div>
    </x-admin.card>
</x-admin-layout>
