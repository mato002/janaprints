<x-admin.modal-form :title="__('Payment receipt')" maxWidth="2xl">
    <div class="space-y-4">
        <div class="rounded-lg border border-erp-border p-4 text-sm">
            @include('documents.receipt.content', ['document' => $document])
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.payments.receipt.pdf', $payment) }}" class="erp-btn-primary text-sm" target="_blank" rel="noopener">{{ __('Print receipt') }}</a>
            @if ($deskReturnUrl ?? null)
                <a href="{{ $deskReturnUrl }}" class="erp-btn-secondary text-sm" data-turbo-frame="_top">{{ __('Back to desk') }}</a>
            @endif
        </div>
    </div>
</x-admin.modal-form>
