<x-admin-layout :title="__('CRM')" :breadcrumbs="[['label' => __('CRM')]]">
    <x-admin.page-header :title="__('CRM')" :description="__('Customer relationships, leads, and follow-ups.')" />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ([
            ['label' => __('Total Customers'), 'value' => $stats['total_customers'], 'icon' => 'user-circle'],
            ['label' => __('Active Customers'), 'value' => $stats['active_customers'], 'icon' => 'users'],
            ['label' => __('Leads'), 'value' => $stats['leads'], 'icon' => 'sparkles'],
            ['label' => __('Open Opportunities'), 'value' => $stats['open_opportunities'], 'icon' => 'chart-pie'],
            ['label' => __('Follow Ups Due Today'), 'value' => $stats['follow_ups_due_today'], 'icon' => 'clock'],
        ] as $card)
            <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon']" />
        @endforeach
    </div>

    <x-admin.card class="mt-6">
        <x-admin.quick-actions
            :items="[]"
        >
            @can('create', App\Models\Crm\Customer::class)
                <a href="{{ route('admin.crm.customers.create') }}" class="erp-btn-primary">{{ __('New customer') }}</a>
            @endcan
            @can('create', App\Models\Crm\Lead::class)
                <a href="{{ route('admin.crm.leads.create') }}" class="erp-btn-secondary">{{ __('New lead') }}</a>
            @endcan
            <a href="{{ route('admin.crm.customers.index') }}" class="erp-btn-secondary">{{ __('All customers') }}</a>
            <a href="{{ route('admin.crm.leads.index') }}" class="erp-btn-secondary">{{ __('All leads') }}</a>
        </x-admin.quick-actions>
    </x-admin.card>
</x-admin-layout>
