<x-admin.modal-form
    :title="__('Edit quotation')"
    :breadcrumbs="[['label' => __('Quotations'), 'url' => route('admin.quotations.show', $quotation)], ['label' => __('Edit')]]"
    maxWidth="5xl"
>
    <x-admin.form-shell :action="route('admin.quotations.update', $quotation)" method="PUT" class="space-y-6">
        @php($fields = $formFields ?? [])
        <p class="text-sm text-amber-700">{{ __('Saving creates revision :n → :next', ['n' => $quotation->revision_number, 'next' => $quotation->revision_number + 1]) }}</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @if(($fields['customer_id']['visible'] ?? true))
            <div>
                <label class="erp-label">{{ __('Customer') }}</label>
                <select name="customer_id" class="erp-input" @required($fields['customer_id']['required'] ?? true) @disabled($fields['customer_id']['read_only'] ?? false)>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}" @selected($quotation->customer_id == $c->id)>{{ $c->company_name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            @if(($fields['quotation_date']['visible'] ?? true))
            <div>
                <label class="erp-label">{{ __('Quotation date') }}</label>
                <input type="date" name="quotation_date" class="erp-input" value="{{ old('quotation_date', $quotation->quotation_date->format('Y-m-d')) }}" @required($fields['quotation_date']['required'] ?? true) @readonly($fields['quotation_date']['read_only'] ?? false)>
            </div>
            @endif
            @if(($fields['valid_until']['visible'] ?? true))
            <div>
                <label class="erp-label">{{ __('Valid until') }}</label>
                <input type="date" name="valid_until" class="erp-input" value="{{ old('valid_until', $quotation->valid_until?->format('Y-m-d')) }}" @required($fields['valid_until']['required'] ?? false) @readonly($fields['valid_until']['read_only'] ?? false)>
            </div>
            @endif
            @if(($fields['currency']['visible'] ?? true))
            <div>
                <label class="erp-label">{{ __('Currency') }}</label>
                <input type="text" name="currency" class="erp-input" value="{{ old('currency', $quotation->currency) }}" maxlength="3" @required($fields['currency']['required'] ?? true) @readonly($fields['currency']['read_only'] ?? false)>
            </div>
            @endif
        </div>
        @include('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => $quotation])
        @include('admin.sales.quotations.partials.items-form', ['quotation' => $quotation])
        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Save & new revision') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
