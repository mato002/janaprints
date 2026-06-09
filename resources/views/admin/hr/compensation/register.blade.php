<x-admin-layout :title="__('Employee Compensation Register')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Compensation'), 'url' => route('admin.hr.compensation.dashboard')], ['label' => __('Register')]]">
    <x-admin.page-header :title="__('Employee Compensation Register')">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.compensation.dashboard') }}" class="erp-btn-secondary">{{ __('Dashboard') }}</a>
            @can('create', App\Models\Hr\EmployeeCompensation::class)
                <a href="{{ route('admin.hr.compensation.create') }}" class="erp-btn-primary">{{ __('Assign compensation') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <form method="GET" class="erp-card mb-4">
        <div class="grid gap-3 md:grid-cols-3">
            <div>
                <label class="erp-label">{{ __('Branch') }}</label>
                <select name="branch_id" class="erp-input w-full text-sm">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((int) ($filters['branch_id'] ?? 0) === $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Payroll Group') }}</label>
                <select name="payroll_group" class="erp-input w-full text-sm">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($payrollGroups as $group)
                        <option value="{{ $group->value }}" @selected(($filters['payroll_group'] ?? '') === $group->value)>{{ $group->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Coverage') }}</label>
                <select name="coverage" class="erp-input w-full text-sm">
                    <option value="">{{ __('All') }}</option>
                    <option value="missing" @selected(($filters['coverage'] ?? '') === 'missing')>{{ __('Missing compensation') }}</option>
                </select>
            </div>
        </div>
        <div class="mt-3 flex gap-2">
            <button type="submit" class="erp-btn-primary">{{ __('Filter') }}</button>
            <a href="{{ route('admin.hr.compensation.register') }}" class="erp-btn-secondary">{{ __('Reset') }}</a>
        </div>
    </form>

    <x-admin.data-table :search-placeholder="__('Search employees…')">
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Employee') }}</th>
                <th scope="col">{{ __('Basic Salary') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Gross') }}</th>
                <th scope="col" class="hidden lg:table-cell">{{ __('Payroll Group') }}</th>
                <th scope="col" class="hidden lg:table-cell">{{ __('Effective') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($employees as $employee)
                @php $comp = $employee->compensation; @endphp
                <tr>
                    <td>
                        <div class="font-medium text-erp-primary">{{ $employee->full_name }}</div>
                        <div class="font-mono text-[11px] text-slate-500">{{ $employee->employee_number }}</div>
                    </td>
                    <td>{{ $comp ? number_format($comp->basic_salary, 2) : '—' }}</td>
                    <td class="hidden md:table-cell">{{ $comp ? number_format($comp->grossComponents(), 2) : '—' }}</td>
                    <td class="hidden lg:table-cell">{{ $comp?->payroll_group?->label() ?? '—' }}</td>
                    <td class="hidden lg:table-cell">{{ $comp?->effective_from?->format('M j, Y') ?? '—' }}</td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.hr.employees.show', ['employee' => $employee, 'tab' => 'compensation'])">{{ __('Employee 360') }}</x-admin.table-row-action>
                            @can('create', App\Models\Hr\EmployeeCompensation::class)
                                <x-admin.table-row-action :href="route('admin.hr.compensation.edit', $employee)">{{ $comp ? __('Revise') : __('Assign') }}</x-admin.table-row-action>
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><x-admin.empty-state icon="currency-dollar" :title="__('No employees found')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$employees" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
