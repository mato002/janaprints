<x-admin.page-header :title="$employee->full_name" :description="$employee->employee_number.' · '.$employee->branch?->name">
    <x-slot name="actions">
        <a href="{{ route('admin.employees.index') }}" class="erp-btn-secondary">{{ __('All employees') }}</a>
        @can('update', $employee)
            <a href="{{ route('admin.employees.edit', $employee) }}" class="erp-btn-secondary">{{ __('Edit profile') }}</a>
        @endcan
        @can('create', App\Models\Hr\EmployeeCompensation::class)
            <a href="{{ route('admin.hr.compensation.edit', $employee) }}" class="erp-btn-primary">{{ __('Manage compensation') }}</a>
        @endcan
    </x-slot>
</x-admin.page-header>
