<x-admin-layout :title="__('Payroll')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Payroll')]]">
    <x-admin.page-header :title="__('Payroll')" :description="__('Payroll processing from attendance and leave through to payslips and accounting.')">
        <x-slot name="actions">
            @can('create', App\Models\Hr\PayrollRun::class)
                <a href="{{ route('admin.hr.payroll.create') }}" class="erp-btn-primary" data-erp-modal-open>{{ __('New payroll run') }}</a>
            @endcan
            <a href="{{ route('admin.hr.payroll.index') }}" class="erp-btn-secondary">{{ __('All runs') }}</a>
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach ([
            ['label' => __('Pending Approval'), 'value' => $stats['pending_approval'], 'icon' => 'clock'],
            ['label' => __('Posted This Year'), 'value' => $stats['posted_this_year'], 'icon' => 'check-circle'],
            ['label' => __('Last Net Payroll'), 'value' => number_format($stats['last_net_total'], 2), 'icon' => 'cash'],
        ] as $card)
            <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon']" />
        @endforeach
    </div>
</x-admin-layout>
