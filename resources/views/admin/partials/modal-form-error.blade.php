<turbo-frame id="erp-form-modal">
    <div class="erp-form-modal w-full" data-erp-form-modal-panel>
        <div class="erp-form-modal__header">
            <h2 id="erp-form-modal-title" class="erp-form-modal__title">
                {{ $presentation['category_label'] ?? __('System Errors') }}
            </h2>
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
            @include('admin.partials.modal-validation-alert', [
                'validationMessages' => $validationMessages ?? [],
                'validationPresentation' => $presentation ?? null,
            ])
            @include('admin.partials.governed-form-errors', [
                'presentation' => $presentation ?? null,
                'message' => $message ?? null,
                'detail' => $detail ?? null,
            ])
            <button type="button" class="erp-btn-secondary mt-4" data-erp-form-modal-close>
                {{ __('Close') }}
            </button>
        </div>
    </div>
</turbo-frame>
