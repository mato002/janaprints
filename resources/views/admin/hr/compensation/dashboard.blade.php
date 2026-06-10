<x-admin-layout :title="__('Compensation Center')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Compensation Center')]]">
    <x-admin.page-header :title="__('Compensation Center')" :description="__('Configure employee pay packages between workforce records and payroll processing.')">
        <x-slot name="actions">
            @can('create', App\Models\Hr\EmployeeCompensation::class)
                <a href="{{ route('admin.hr.compensation.create') }}" class="erp-btn-primary" data-erp-modal-open>{{ __('New compensation') }}</a>
            @endcan
            <a href="{{ route('admin.hr.compensation.register') }}" class="erp-btn-secondary">{{ __('Employee register') }}</a>
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['label' => __('Active Employees'), 'value' => $stats['active_employees'], 'icon' => 'identification'],
            ['label' => __('With Compensation'), 'value' => $stats['with_compensation'], 'icon' => 'check-circle'],
            ['label' => __('Missing Compensation'), 'value' => $stats['missing_compensation'], 'icon' => 'exclamation'],
            ['label' => __('Avg Gross Pay'), 'value' => number_format($stats['avg_gross'], 2), 'icon' => 'cash'],
        ] as $card)
            <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon']" />
        @endforeach
    </div>

    <x-admin.card class="mt-6">
        <x-admin.quick-actions :items="[]">
            <a href="{{ route('admin.hr.compensation.register') }}" class="erp-btn-secondary">{{ __('Employee compensation register') }}</a>
            <a href="{{ route('admin.hr.compensation.templates') }}" class="erp-btn-secondary">{{ __('Salary templates') }}</a>
            <a href="{{ route('admin.hr.compensation.allowances') }}" class="erp-btn-secondary">{{ __('Allowance library') }}</a>
            <a href="{{ route('admin.hr.compensation.deductions') }}" class="erp-btn-secondary">{{ __('Deduction library') }}</a>
            @can('hr.compensation.audit')
                <a href="{{ route('admin.hr.compensation.audit') }}" class="erp-btn-secondary">{{ __('Compensation audit log') }}</a>
            @endcan
        </x-admin.quick-actions>
    </x-admin.card>
</x-admin-layout>
