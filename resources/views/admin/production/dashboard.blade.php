<x-admin-layout :title="__('Production')" :breadcrumbs="[['label' => __('Production')]]">
    <x-admin.page-header :title="__('Production')" :description="__('Job cards, queues, and quality control.')" />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ([
            ['label' => __('Open Job Cards'), 'value' => $stats['open'], 'icon' => 'inbox'],
            ['label' => __('Jobs In Production'), 'value' => $stats['in_production'], 'icon' => 'cog'],
            ['label' => __('Jobs Awaiting QC'), 'value' => $stats['awaiting_qc'], 'icon' => 'clipboard-check'],
            ['label' => __('Completed Today'), 'value' => $stats['completed_today'], 'icon' => 'check-circle'],
            ['label' => __('Delayed Jobs'), 'value' => $stats['delayed'], 'icon' => 'exclamation'],
        ] as $card)
            <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon']" />
        @endforeach
    </div>

    <x-admin.card class="mt-6">
        <x-admin.quick-actions :items="[]">
            @can('create', App\Models\Production\ProductionJobCard::class)
                <a href="{{ route('admin.production.job-cards.create') }}" class="erp-btn-primary">{{ __('New job card') }}</a>
            @endcan
            <a href="{{ route('admin.production.job-cards.index') }}" class="erp-btn-secondary">{{ __('All job cards') }}</a>
        </x-admin.quick-actions>
    </x-admin.card>
</x-admin-layout>
