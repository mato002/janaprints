<x-admin-layout :title="__('Leave')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Leave')]]">
    <x-admin.page-header :title="__('Leave Management')" :description="__('Leave requests, balances, and workforce absence planning.')">
        <x-slot name="actions">
            @can('create', App\Models\Hr\LeaveRequest::class)
                <a href="{{ route('admin.hr.leave.create') }}" class="erp-btn-primary">{{ __('Apply for leave') }}</a>
            @endcan
            <a href="{{ route('admin.hr.leave.index') }}" class="erp-btn-secondary">{{ __('All requests') }}</a>
            <a href="{{ route('admin.hr.leave.calendar') }}" class="erp-btn-secondary">{{ __('Calendar') }}</a>
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach ([
            ['label' => __('Pending Approval'), 'value' => $stats['pending'], 'icon' => 'clock'],
            ['label' => __('Approved This Month'), 'value' => $stats['approved_this_month'], 'icon' => 'check-circle'],
            ['label' => __('On Leave Today'), 'value' => $stats['on_leave_today'], 'icon' => 'calendar'],
        ] as $card)
            <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon']" />
        @endforeach
    </div>

    <x-admin.card class="mt-6">
        <x-admin.quick-actions :items="[]">
            <a href="{{ route('admin.hr.leave.balances') }}" class="erp-btn-secondary">{{ __('Leave balances') }}</a>
            <a href="{{ route('admin.hr.leave.calendar', ['view' => 'month']) }}" class="erp-btn-secondary">{{ __('Monthly calendar') }}</a>
            <a href="{{ route('admin.hr.leave.calendar', ['view' => 'week']) }}" class="erp-btn-secondary">{{ __('Weekly calendar') }}</a>
        </x-admin.quick-actions>
    </x-admin.card>
</x-admin-layout>
