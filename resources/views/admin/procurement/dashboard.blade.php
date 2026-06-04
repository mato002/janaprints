<x-admin-layout :title="__('Procurement')" :breadcrumbs="[['label' => __('Procurement')]]">
    <x-admin.page-header :title="__('Procurement & Vendors')" />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ([
            ['label' => __('Active Vendors'), 'value' => $stats['active_vendors'], 'icon' => 'truck'],
            ['label' => __('Pending Purchase Requests'), 'value' => $stats['pending_requests'], 'icon' => 'clipboard-list'],
            ['label' => __('Pending Purchase Orders'), 'value' => $stats['pending_orders'], 'icon' => 'shopping-bag'],
            ['label' => __('Goods Awaiting Receipt'), 'value' => $stats['awaiting_receipt'], 'icon' => 'archive'],
            ['label' => __('Recent Receipts'), 'value' => $stats['recent_receipts'], 'icon' => 'switch-horizontal'],
        ] as $card)
            <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon']" />
        @endforeach
    </div>

    <x-admin.card class="mt-6 flex flex-wrap gap-2">
        @can('create', App\Models\Procurement\Vendor::class)
            <a href="{{ route('admin.procurement.vendors.create') }}" class="erp-btn-primary">{{ __('New vendor') }}</a>
        @endcan
        @can('create', App\Models\Procurement\PurchaseRequest::class)
            <a href="{{ route('admin.procurement.requests.create') }}" class="erp-btn-secondary">{{ __('Purchase request') }}</a>
        @endcan
        @can('create', App\Models\Procurement\PurchaseOrder::class)
            <a href="{{ route('admin.procurement.orders.create') }}" class="erp-btn-secondary">{{ __('Purchase order') }}</a>
        @endcan
    </x-admin.card>
</x-admin-layout>
