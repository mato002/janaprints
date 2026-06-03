<x-admin-layout :title="__('Quotations')" :breadcrumbs="[['label' => __('Sales')], ['label' => __('Quotations')]]">
    <x-admin.page-header :title="__('Quotations')" :description="__('Quotation pipeline and conversion metrics.')" />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ([
            ['label' => __('Draft Quotations'), 'value' => $stats['draft'], 'icon' => 'document-text'],
            ['label' => __('Pending Approval'), 'value' => $stats['pending_approval'], 'icon' => 'clock'],
            ['label' => __('Sent'), 'value' => $stats['sent'], 'icon' => 'paper-airplane'],
            ['label' => __('Accepted'), 'value' => $stats['accepted'], 'icon' => 'check-circle'],
            ['label' => __('Conversion Rate'), 'value' => $stats['conversion_rate'].'%', 'icon' => 'chart-pie'],
        ] as $card)
            <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon']" />
        @endforeach
    </div>

    <x-admin.card class="mt-6">
        <x-admin.quick-actions :items="[]">
            @can('create', App\Models\Sales\Quotation::class)
                <a href="{{ route('admin.quotations.create') }}" class="erp-btn-primary">{{ __('New quotation') }}</a>
            @endcan
            <a href="{{ route('admin.quotations.index') }}" class="erp-btn-secondary">{{ __('All quotations') }}</a>
        </x-admin.quick-actions>
    </x-admin.card>
</x-admin-layout>
