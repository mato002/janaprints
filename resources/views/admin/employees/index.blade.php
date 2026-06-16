<x-admin-layout :title="__('Employees')" :breadcrumbs="[['label' => __('Organization')], ['label' => __('Employees')]]">
    <x-admin.workspace-content-header :title="__('Employees')">
        <x-slot:actions>
            @can('create', App\Models\Employee::class)
                <a href="{{ route('admin.employees.create') }}" class="erp-btn-primary" data-erp-modal-open>{{ __('Create employee') }}</a>
            @endcan
        </x-slot:actions>
    </x-admin.workspace-content-header>

    <x-admin.data-table
        :search-placeholder="__('Search employees…')"
        export-route="admin.employees.export"
        :export-query="request()->query()"
        :format-in-path="true"
        export-filename="employees"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Employee') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Login email') }}</th>
                <th scope="col" class="hidden lg:table-cell">{{ __('Role') }}</th>
                <th scope="col" class="hidden lg:table-cell">{{ __('Activation') }}</th>
                <th scope="col" class="hidden sm:table-cell">{{ __('Branch') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($employees as $employee)
                @php
                    $rowActivationStatus = $activationManagement->activationDisplayStatus($employee);
                    $assignedRoles = $employee->user?->roles->pluck('name')->all() ?? [];
                    $roleLabel = filled($assignedRoles)
                        ? implode(', ', $assignedRoles)
                        : ($employee->activation_role
                            ? $employee->activation_role.' ('.__('pending').')'
                            : '—');
                    $rowSearch = strtolower($employee->employee_number.' '.$employee->full_name.' '.$employee->branch->name.' '.($employee->email ?? '').' '.$roleLabel);
                @endphp
                <tr x-show="rowVisible(@js($rowSearch))">
                    <td>
                        <div class="font-medium text-erp-primary">{{ $employee->full_name }}</div>
                        <div class="font-mono text-[11px] text-slate-500">{{ $employee->employee_number }}</div>
                    </td>
                    <td class="hidden md:table-cell text-sm text-slate-600">{{ $employee->email ?: '—' }}</td>
                    <td class="hidden lg:table-cell text-sm text-slate-600">{{ $roleLabel }}</td>
                    <td class="hidden lg:table-cell text-sm text-slate-600">{{ ucfirst($rowActivationStatus) }}</td>
                    <td class="hidden sm:table-cell">{{ $employee->branch->name }}</td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            @can('update', $employee)
                                <x-admin.table-row-action :href="route('admin.employees.edit', $employee)">{{ __('Edit') }}</x-admin.table-row-action>
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><x-admin.empty-state icon="identification" :title="__('No employees yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$employees" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
