<x-admin-layout :title="__('Employees')" :breadcrumbs="[['label' => __('Organization')], ['label' => __('Employees')]]">
    <x-admin.workspace-content-header :title="__('Employees')">
        <x-slot:actions>
            @can('email', App\Models\Employee::class)
                <a
                    href="{{ url()->route('admin.employees.email.compose', ['all' => 1]) }}"
                    class="erp-btn-secondary"
                    data-erp-modal-open
                >{{ __('Email all staff') }}</a>
            @endcan
            @can('create', App\Models\Employee::class)
                <a href="{{ route('admin.employees.create') }}" class="erp-btn-primary" data-erp-modal-open>{{ __('Create employee') }}</a>
            @endcan
        </x-slot:actions>
    </x-admin.workspace-content-header>

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="route('admin.employees.index')" :reset-url="route('admin.employees.index')">
            <select name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                <option value="active" @selected(($filters['active'] ?? true) === true)>{{ __('Active employees') }}</option>
                <option value="inactive" @selected(($filters['active'] ?? true) === false)>{{ __('Inactive only') }}</option>
                <option value="all" @selected(($filters['active'] ?? true) === null)>{{ __('All employees') }}</option>
            </select>
            @if ($branches->isNotEmpty())
                <select name="branch_id" class="erp-toolbar-select" aria-label="{{ __('Branch') }}">
                    <option value="">{{ __('All branches') }}</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((int) ($filters['branch_id'] ?? 0) === $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            @endif
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.data-table
        :search-placeholder="__('Search employees…')"
        export-route="admin.employees.export"
        :export-query="request()->query()"
        :format-in-path="true"
        export-filename="employees"
        :selectable="auth()->user()->can('email', App\Models\Employee::class)"
    >
        @can('email', App\Models\Employee::class)
            <x-slot name="bulk">
                <button
                    type="button"
                    class="erp-btn-secondary py-1 text-xs"
                    @click="if (selected.size === 0) { window.showErpSweetAlert?.(@js(__('Select at least one employee.')), 'warning'); return; } const url = @js(url()->route('admin.employees.email.compose')).concat('?', [...selected].map((id) => 'employees[]=' + encodeURIComponent(id)).join('&')); window.erpModalManager?.openModal(url)"
                >
                    {{ __('Email selected') }}
                </button>
            </x-slot>
        @endcan

        <x-slot name="head">
            <tr>
                @can('email', App\Models\Employee::class)
                    <th scope="col" class="w-10 erp-table-checkbox-col">
                        <input
                            type="checkbox"
                            class="rounded border-slate-300"
                            aria-label="{{ __('Select all') }}"
                            @change="toggleAll($event)"
                        >
                    </th>
                @endcan
                <th scope="col">{{ __('Employee') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Login email') }}</th>
                <th scope="col" class="hidden lg:table-cell">{{ __('Basic salary') }}</th>
                <th scope="col" class="hidden lg:table-cell">{{ __('Role') }}</th>
                <th scope="col" class="hidden lg:table-cell">{{ __('Activation') }}</th>
                <th scope="col" class="hidden sm:table-cell">{{ __('Branch') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($employees as $employee)
                @php
                    $showUrl = route('admin.hr.employees.show', $employee);
                    $rowActivationStatus = $activationManagement->activationDisplayStatus($employee);
                    $assignedRoles = $employee->user?->roles->pluck('name')->all() ?? [];
                    $roleLabel = filled($assignedRoles)
                        ? implode(', ', $assignedRoles)
                        : ($employee->activation_role
                            ? $employee->activation_role.' ('.__('pending').')'
                            : '—');
                    $rowSearch = strtolower($employee->employee_number.' '.$employee->full_name.' '.$employee->branch->name.' '.($employee->email ?? '').' '.$roleLabel);
                @endphp
                <tr
                    data-row-id="{{ $employee->id }}"
                    data-href="{{ $showUrl }}"
                    data-turbo-frame="erp-main"
                    role="link"
                    tabindex="0"
                    aria-label="{{ __('Open :name', ['name' => $employee->full_name]) }}"
                    class="cursor-pointer"
                    x-show="rowVisible(@js($rowSearch))"
                    @click="if (!$event.target.closest('[data-erp-row-actions], .erp-table-checkbox-col, a, button, input, label')) { window.erpVisitUrl?.($el.dataset.href); }"
                    @keydown.enter.prevent="if (!$event.target.closest('[data-erp-row-actions], .erp-table-checkbox-col, a, button, input, label')) { window.erpVisitUrl?.($el.dataset.href); }"
                >
                    @can('email', App\Models\Employee::class)
                        <td class="erp-table-checkbox-col" @click.stop>
                            @if ($employee->email)
                                <input
                                    type="checkbox"
                                    class="row-select rounded border-slate-300"
                                    value="{{ $employee->id }}"
                                    data-export-row
                                    @change="toggleRow(@js((string) $employee->id), $event)"
                                >
                            @endif
                        </td>
                    @endcan
                    <td>
                        <div class="font-medium text-erp-primary">{{ $employee->full_name }}</div>
                        <div class="erp-ref-code">{{ $employee->employee_number }}</div>
                    </td>
                    <td class="hidden md:table-cell text-sm text-slate-600">{{ $employee->email ?: '—' }}</td>
                    <td class="hidden lg:table-cell text-sm text-slate-600">
                        @if ($employee->compensation)
                            {{ number_format($employee->compensation->basic_salary, 2) }}
                        @else
                            <span class="text-amber-700">{{ __('Not set') }}</span>
                        @endif
                    </td>
                    <td class="hidden lg:table-cell text-sm text-slate-600">{{ $roleLabel }}</td>
                    <td class="hidden lg:table-cell text-sm text-slate-600">{{ ucfirst($rowActivationStatus) }}</td>
                    <td class="hidden sm:table-cell">{{ $employee->branch->name }}</td>
                    <td class="erp-table-actions-col" @click.stop>
                        <x-admin.table-row-actions>
                            @can('view', $employee)
                                <x-admin.table-row-action
                                    :href="$showUrl"
                                    data-turbo-frame="erp-main"
                                    data-turbo-action="advance"
                                >{{ __('View 360') }}</x-admin.table-row-action>
                            @endcan
                            @can('update', $employee)
                                <x-admin.table-row-action :href="route('admin.employees.edit', $employee)">{{ __('Edit') }}</x-admin.table-row-action>
                            @endcan
                            @can('viewAny', App\Models\Hr\EmployeeCompensation::class)
                                <x-admin.table-row-action
                                    :href="url()->route('admin.hr.compensation.edit', $employee)"
                                    data-turbo-frame="erp-main"
                                    data-turbo-action="advance"
                                >{{ $employee->compensation ? __('Salary') : __('Set salary') }}</x-admin.table-row-action>
                            @endcan
                            @can('email', App\Models\Employee::class)
                                @if ($employee->email)
                                    <x-admin.table-row-action
                                        :href="url()->route('admin.employees.email.compose', ['employees' => [$employee->id]])"
                                        data-erp-modal-open
                                    >{{ __('Email') }}</x-admin.table-row-action>
                                @endif
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ auth()->user()->can('email', App\Models\Employee::class) ? 8 : 7 }}"><x-admin.empty-state icon="identification" :title="__('No employees yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$employees" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
