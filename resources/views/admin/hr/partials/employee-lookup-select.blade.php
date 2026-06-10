@props([
    'employees',
    'value' => null,
    'required' => true,
    'selectClass' => 'erp-select mt-1 w-full',
])

@php
    $employeeOptions = collect($employees)->map(fn ($employee) => [
        'id' => $employee->id,
        'name' => trim("{$employee->first_name} {$employee->last_name}")." ({$employee->employee_number})",
    ]);
@endphp

<x-admin.lookup-select
    name="employee_id"
    :label="__('Employee')"
    :options="$employeeOptions"
    :value="old('employee_id', $value)"
    :required="$required"
    create-route="admin.employees.quick-create"
    refresh-route="admin.lookups.employees"
    permission="employees.manage"
    :modal-title="__('Create employee')"
    option-label-key="name"
    :select-class="$selectClass"
    {{ $attributes }}
/>
