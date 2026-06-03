<x-admin-layout :title="__('Employees')" :breadcrumbs="[['label' => __('Organization')], ['label' => __('Employees')]]">
    <x-admin.page-header :title="__('Employees')">
        <x-slot name="actions">
            @can('create', App\Models\Employee::class)
                <a href="{{ route('admin.employees.create') }}" class="erp-btn-primary">{{ __('Create employee') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table>
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Number') }}</th>
                <th scope="col">{{ __('Name') }}</th>
                <th scope="col" class="hidden sm:table-cell">{{ __('Branch') }}</th>
                <th scope="col" class="text-right">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($employees as $employee)
                <tr x-show="matches(@js($employee->employee_number.' '.$employee->full_name.' '.$employee->branch->name))">
                    <td class="font-mono text-xs text-slate-500">{{ $employee->employee_number }}</td>
                    <td class="font-medium text-erp-primary">{{ $employee->full_name }}</td>
                    <td class="hidden sm:table-cell">{{ $employee->branch->name }}</td>
                    <td class="text-right">
                        @can('update', $employee)
                            <a href="{{ route('admin.employees.edit', $employee) }}" class="font-medium text-erp-accent hover:underline">{{ __('Edit') }}</a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="4"><x-admin.empty-state icon="identification" :title="__('No employees yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer">{{ $employees->links() }}</x-slot>
    </x-admin.data-table>
</x-admin-layout>
