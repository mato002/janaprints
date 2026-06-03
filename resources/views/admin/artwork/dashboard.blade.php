<x-admin-layout :title="__('Artwork')" :breadcrumbs="[['label' => __('Artwork')]]">
    <x-admin.page-header :title="__('Artwork')" :description="__('Design requests, versions, and approvals.')" />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ([
            ['label' => __('Open Requests'), 'value' => $stats['open'], 'icon' => 'inbox'],
            ['label' => __('In Design'), 'value' => $stats['in_design'], 'icon' => 'pencil'],
            ['label' => __('Awaiting Approval'), 'value' => $stats['awaiting_approval'], 'icon' => 'clock'],
            ['label' => __('Approved'), 'value' => $stats['approved'], 'icon' => 'check-circle'],
            ['label' => __('Revision Requests'), 'value' => $stats['revision_requests'], 'icon' => 'refresh'],
        ] as $card)
            <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon']" />
        @endforeach
    </div>

    <x-admin.card class="mt-6">
        <x-admin.quick-actions :items="[]">
            @can('create', App\Models\Artwork\ArtworkRequest::class)
                <a href="{{ route('admin.artwork.create') }}" class="erp-btn-primary">{{ __('New request') }}</a>
            @endcan
            <a href="{{ route('admin.artwork.index') }}" class="erp-btn-secondary">{{ __('All requests') }}</a>
        </x-admin.quick-actions>
    </x-admin.card>
</x-admin-layout>
