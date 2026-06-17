<x-admin-layout :title="__('Sales Orders')" :breadcrumbs="[['label' => __('Sales')], ['label' => __('Sales Orders')]]">
    <x-admin.page-header :title="__('Sales Orders')" :description="__('Order pipeline from draft through delivery.')" />

    <div class="workspace-kpi-grid grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        @foreach ([
            ['label' => __('Draft Orders'), 'value' => $stats['draft'], 'icon' => 'document-text'],
            ['label' => __('Confirmed Orders'), 'value' => $stats['confirmed'], 'icon' => 'check'],
            ['label' => __('Ready For Production'), 'value' => $stats['ready_for_production'], 'icon' => 'clipboard-check'],
            ['label' => __('Orders In Production'), 'value' => $stats['in_production'], 'icon' => 'cog'],
            ['label' => __('Completed Orders'), 'value' => $stats['completed'], 'icon' => 'badge-check'],
            ['label' => __('Delivered Orders'), 'value' => $stats['delivered'], 'icon' => 'truck'],
        ] as $card)
            <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon']" />
        @endforeach
    </div>

    <x-admin.card class="mt-6">
        <x-admin.quick-actions :items="[]">
            @can('create', App\Models\Sales\SalesOrder::class)
                <a href="{{ route('admin.sales-orders.create') }}" class="erp-btn-primary">{{ __('New from quotation') }}</a>
            @endcan
            <a href="{{ route('admin.sales-orders.index') }}" class="erp-btn-secondary">{{ __('All orders') }}</a>
        </x-admin.quick-actions>
    </x-admin.card>
</x-admin-layout>
