@if ($orderPresentation)
    <x-admin.card>
        <h3 class="mb-2 text-sm font-semibold text-slate-900">{{ __('Order actions') }}</h3>
        <div class="flex flex-wrap gap-2">
            @if ($orderPresentation['edit_url'] ?? null)
                <a href="{{ $orderPresentation['edit_url'] }}" class="erp-btn-secondary text-xs" data-erp-modal-open>{{ __('Edit order') }}</a>
            @endif
            @if ($orderPresentation['payment_url'] ?? null)
                <a href="{{ $orderPresentation['payment_url'] }}" class="erp-btn-secondary text-xs" data-erp-modal-open>{{ __('Record payment') }}</a>
            @endif
            @if ($orderPresentation['invoice_url'] ?? null)
                <a href="{{ $orderPresentation['invoice_url'] }}" class="erp-btn-secondary text-xs" data-erp-modal-open>{{ __('Create invoice') }}</a>
            @endif
            @if ($orderPresentation['latest_invoice']['document_url'] ?? null)
                <a href="{{ $orderPresentation['latest_invoice']['document_url'] }}" class="erp-btn-secondary text-xs" target="_blank" rel="noopener">{{ __('Print invoice') }}</a>
            @endif
        </div>

        @if ($orderPresentation['financial'] ?? null)
            <p class="mt-3 text-xs text-slate-600">
                {{ __('Payment status') }}:
                <span class="font-medium">{{ $orderPresentation['financial']['financial_status_label'] ?? '—' }}</span>
                @if (! empty($orderPresentation['financial']['deposit']['required_amount']))
                    · {{ __('Deposit due') }}: {{ number_format((float) $orderPresentation['financial']['deposit']['required_amount'], 2) }}
                @endif
            </p>
        @endif
    </x-admin.card>
@endif
