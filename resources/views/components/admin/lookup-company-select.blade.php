@props([
    'companies',
    'value' => null,
    'selectClass' => 'erp-select mt-1',
    'required' => true,
])

@if (auth()->user()->hasRole('Super Admin'))
    <x-admin.lookup-select
        name="company_id"
        :label="__('Company')"
        :options="$companies"
        :value="old('company_id', $value ?? $companies->first()?->id)"
        :required="$required"
        create-route="admin.companies.quick-create"
        refresh-route="admin.lookups.companies"
        permission="companies.manage"
        :modal-title="__('Create company')"
        option-label-key="name"
        option-value-key="id"
        :select-class="$selectClass"
        :empty-option="false"
    />
@else
    <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
@endif
