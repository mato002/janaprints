<x-admin-layout
    :title="__('Maintenance')"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Maintenance')],
    ]"
>
    <x-admin.page-header
        :title="__('Maintenance Operations')"
        :description="match ($activeTab) {
            'work-orders' => __('Preventive, corrective, and emergency maintenance work orders.'),
            'plans' => __('Preventive maintenance schedules and upcoming due dates.'),
            'calendar' => __('Scheduled maintenance across month, week, and overdue views.'),
            'downtime' => __('Asset downtime records with duration and impact.'),
            'technicians' => __('Internal and vendor maintenance technicians.'),
            default => __('Work orders, downtime, and preventive maintenance overview.'),
        }"
    >
        <x-slot name="actions">
            @if ($activeTab === 'work-orders')
                @can('create', \App\Models\Assets\MaintenanceWorkOrder::class)
                    <a href="{{ route('admin.assets.maintenance.work-orders.create') }}" class="erp-btn-primary" data-erp-modal-open>{{ __('New work order') }}</a>
                @endcan
            @elseif ($activeTab === 'plans')
                @can('create', \App\Models\Assets\MaintenancePlan::class)
                    <a href="{{ route('admin.assets.maintenance.plans.create') }}" class="erp-btn-primary" data-erp-modal-open>{{ __('New plan') }}</a>
                @endcan
            @elseif ($activeTab === 'overview')
                @can('create', \App\Models\Assets\MaintenanceWorkOrder::class)
                    <a href="{{ route('admin.assets.maintenance.work-orders.create') }}" class="erp-btn-primary" data-erp-modal-open>{{ __('New work order') }}</a>
                @endcan
            @endif
        </x-slot>
    </x-admin.page-header>

    @include('admin.assets.maintenance.partials.tabs-nav')

    @include('admin.assets.maintenance.partials.tabs.' . match ($activeTab) {
        'work-orders' => 'work_orders',
        default => str_replace('-', '_', $activeTab),
    })
</x-admin-layout>
