<x-admin.modal-form
    :title="__('Create quotation')"
    :breadcrumbs="[['label' => __('Quotations'), 'url' => route('admin.quotations.index')], ['label' => __('Create')]]"
    maxWidth="5xl"
>
    <x-admin.form-shell :action="route('admin.quotations.store')" class="space-y-6">
        @if (request('from') === 'sales-desk')
            <input type="hidden" name="from" value="sales-desk">
        @endif
        @php($fields = $formFields ?? [])
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @if(($fields['customer_id']['visible'] ?? true))
            <x-admin.lookup-select
                name="customer_id"
                :label="__('Customer')"
                :options="$customers"
                :value="old('customer_id', $presetCustomerId ?? null)"
                :required="($fields['customer_id']['required'] ?? true)"
                :readonly="($fields['customer_id']['read_only'] ?? false)"
                create-route="admin.crm.customers.quick-create"
                refresh-route="admin.lookups.customers"
                permission="crm.customers.create"
                :modal-title="__('Create customer')"
                option-label-key="company_name"
                option-value-key="id"
                select-class="erp-input w-full"
                :empty-option="false"
            />
            @endif
            @if(($fields['lead_id']['visible'] ?? true))
            <x-admin.lookup-select
                name="lead_id"
                :label="__('Lead (optional)')"
                :options="$leads"
                :value="old('lead_id', $presetLeadId ?? null)"
                :required="($fields['lead_id']['required'] ?? false)"
                :readonly="($fields['lead_id']['read_only'] ?? false)"
                create-route="admin.crm.leads.quick-create"
                refresh-route="admin.lookups.leads"
                permission="crm.leads.create"
                :modal-title="__('Create lead')"
                option-label-key="lead_name"
                option-value-key="id"
                select-class="erp-input w-full"
                :placeholder="__('None')"
            />
            @endif
            @include('admin.sales.quotations.partials.artwork-picker-field')
            @if(($fields['quotation_date']['visible'] ?? true))
            <div>
                <label class="erp-label">{{ __('Quotation date') }}</label>
                <input type="date" name="quotation_date" class="erp-input" value="{{ old('quotation_date', now()->toDateString()) }}" @required($fields['quotation_date']['required'] ?? true) @readonly($fields['quotation_date']['read_only'] ?? false)>
            </div>
            @endif
            @if(($fields['valid_until']['visible'] ?? true))
            <div>
                <label class="erp-label">{{ __('Valid until') }}</label>
                <input type="date" name="valid_until" class="erp-input" value="{{ old('valid_until', $fields['valid_until']['default'] ?? '') }}" @required($fields['valid_until']['required'] ?? false) @readonly($fields['valid_until']['read_only'] ?? false)>
            </div>
            @endif
            @if(($fields['currency']['visible'] ?? true))
            <div>
                <label class="erp-label">{{ $fields['currency']['label'] ?? __('Currency') }}</label>
                <input type="text" name="currency" class="erp-input" value="{{ old('currency', $fields['currency']['default'] ?? 'KES') }}" maxlength="3" @required($fields['currency']['required'] ?? true) @readonly($fields['currency']['read_only'] ?? false)>
            </div>
            @endif
            @if(($fields['notes']['visible'] ?? true))
            <div class="md:col-span-2">
                <label class="erp-label">{{ $fields['notes']['label'] ?? __('Notes') }}</label>
                <textarea name="notes" class="erp-input w-full" rows="3" @required($fields['notes']['required'] ?? false) @readonly($fields['notes']['read_only'] ?? false)>{{ old('notes') }}</textarea>
            </div>
            @endif
        </div>
        @include('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => null])
        <div>
            <h3 class="font-medium text-slate-800 mb-3">{{ __('Line items') }}</h3>
            @include('admin.sales.quotations.partials.items-form')
        </div>
        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Create quotation') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
