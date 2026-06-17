@props([
    'name' => 'payroll_group',
    'value' => null,
    'groups' => collect(),
    'label' => null,
    'required' => true,
    'selectClass' => 'erp-input w-full',
    'scopeCompanyField' => null,
])

<x-admin.lookup-select
    :name="$name"
    :label="$label ?? __('Payroll group')"
    :options="$groups"
    option-value-key="code"
    option-label-key="name"
    :value="$value"
    :required="$required"
    :empty-option="false"
    :select-class="$selectClass"
    create-route="admin.payroll-groups.quick-create"
    refresh-route="admin.lookups.payroll_groups"
    permission="hr.compensation.create"
    :modal-title="__('Create payroll group')"
    :scope-company-field="$scopeCompanyField"
/>
