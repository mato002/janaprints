<turbo-frame id="erp-form-modal">
    <div class="erp-form-modal w-full" data-erp-form-modal-panel>
        <div class="erp-form-modal__header">
            <h2 id="erp-form-modal-title" class="erp-form-modal__title">{{ __('Unable to save') }}</h2>
            <button
                type="button"
                class="erp-form-modal__close"
                data-erp-form-modal-close
                aria-label="{{ __('Close') }}"
            >
                <x-admin.icon name="x-mark" class="h-5 w-5" />
            </button>
        </div>
        <div class="erp-form-modal__body">
            <x-admin.alert variant="danger" data-erp-validation-errors data-erp-modal-error>
                <p class="font-medium">{{ $message ?? __('Something went wrong while saving this form. Please try again or contact support if the problem continues.') }}</p>
                @if (! empty($detail))
                    <p class="mt-2 text-sm font-mono">{{ $detail }}</p>
                @endif
            </x-admin.alert>
            <button type="button" class="erp-btn-secondary mt-4" data-erp-form-modal-close>
                {{ __('Close') }}
            </button>
        </div>
    </div>
</turbo-frame>
