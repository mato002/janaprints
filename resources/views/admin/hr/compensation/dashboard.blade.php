<x-admin-layout :title="__('Compensation')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Compensation')]]">
    <x-admin.page-header
        :title="__('Compensation')"
        :description="__('Overview of employee pay. Use the tabs above for salaries, payroll classes, and allowance/deduction libraries.')"
    >
        <x-slot name="actions">
            @can('create', App\Models\Hr\EmployeeCompensation::class)
                <a href="{{ route('admin.hr.compensation.create') }}" class="erp-btn-primary" data-erp-modal-open>{{ __('Assign salary') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['label' => __('Active employees'), 'value' => $stats['active_employees'], 'icon' => 'identification'],
            ['label' => __('With salary set'), 'value' => $stats['with_compensation'], 'icon' => 'check-circle'],
            ['label' => __('Missing salary'), 'value' => $stats['missing_compensation'], 'icon' => 'exclamation'],
            ['label' => __('Average gross'), 'value' => number_format($stats['avg_gross'], 2), 'icon' => 'cash'],
        ] as $card)
            <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon']" />
        @endforeach
    </div>

    @if (($stats['pending_approval'] ?? 0) > 0)
        <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            {{ trans_choice(':count salary change awaits approval.|:count salary changes await approval.', $stats['pending_approval'], ['count' => $stats['pending_approval']]) }}
            <a href="{{ route('admin.hr.compensation.register') }}" class="ml-1 font-medium text-erp-accent hover:underline">{{ __('Review in salary register') }}</a>
        </div>
    @endif

    <x-admin.card class="mt-6" :padding="false">
        <div class="flex items-center justify-between border-b border-erp-border px-4 py-3">
            <div>
                <h2 class="text-sm font-semibold text-erp-primary">{{ __('Employees without salary') }}</h2>
                <p class="mt-0.5 text-xs text-slate-500">{{ __('These employees cannot be included in payroll until a salary is assigned.') }}</p>
            </div>
            <a href="{{ route('admin.hr.compensation.register', ['coverage' => 'missing']) }}" class="shrink-0 text-sm font-medium text-erp-accent hover:underline">{{ __('View all') }}</a>
        </div>

        <x-admin.data-table :searchable="false" :exportable="false">
            <x-slot name="head">
                <tr>
                    <th>{{ __('Employee') }}</th>
                    <th class="hidden md:table-cell">{{ __('Branch') }}</th>
                    <th class="erp-table-actions-col">{{ __('Actions') }}</th>
                </tr>
            </x-slot>
            <x-slot name="body">
                @forelse ($missingEmployees as $employee)
                    <tr>
                        <td>
                            <div class="font-medium text-erp-primary">{{ $employee->full_name }}</div>
                            <div class="erp-ref-code">{{ $employee->employee_number }}</div>
                        </td>
                        <td class="hidden md:table-cell">{{ $employee->branch?->name ?? '—' }}</td>
                        <td class="erp-table-actions-col">
                            @can('create', App\Models\Hr\EmployeeCompensation::class)
                                <x-admin.table-row-actions>
                                    <x-admin.table-row-action :href="route('admin.hr.compensation.edit', $employee)">{{ __('Assign salary') }}</x-admin.table-row-action>
                                </x-admin.table-row-actions>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">
                            <x-admin.empty-state
                                icon="check-circle"
                                :title="__('All active employees have salaries')"
                                :description="__('You can revise pay or manage payroll classes from the tabs above.')"
                            />
                        </td>
                    </tr>
                @endforelse
            </x-slot>
        </x-admin.data-table>
    </x-admin.card>

    @can('hr.compensation.audit')
        <p class="mt-4 text-right text-xs text-slate-500">
            <a href="{{ route('admin.hr.compensation.audit') }}" class="text-erp-accent hover:underline">{{ __('Compensation audit log') }}</a>
        </p>
    @endcan
</x-admin-layout>
