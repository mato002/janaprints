@php $comp = $compensation['active']; @endphp
<x-admin.card class="mb-4">
    @if ($comp)
        <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div><dt class="text-xs text-slate-500">{{ __('Basic Salary') }}</dt><dd class="font-medium">{{ number_format($comp->basic_salary, 2) }} {{ $comp->currency }}</dd></div>
            <div><dt class="text-xs text-slate-500">{{ __('Gross') }}</dt><dd>{{ number_format($comp->grossComponents(), 2) }}</dd></div>
            <div><dt class="text-xs text-slate-500">{{ __('Payroll Group') }}</dt><dd>{{ $comp->payroll_group_label ?? '—' }}</dd></div>
        </dl>
    @else
        <x-admin.empty-state icon="currency-dollar" :title="__('No active compensation')" />
    @endif
</x-admin.card>

<div class="grid gap-4 lg:grid-cols-2">
    @include('admin.employees.tabs.allowances', [
        'employee' => $employee,
        'allowanceDefinitions' => $compensation['allowance_definitions'],
    ])
    @include('admin.employees.tabs.deductions', [
        'employee' => $employee,
        'deductionDefinitions' => $compensation['deduction_definitions'],
    ])
</div>

<div class="mt-4">
    @include('admin.employees.tabs.salary-history', [
        'compensationHistory' => $compensation['history'],
        'salaryChanges' => $compensation['salary_changes'],
    ])
</div>
