<x-admin-layout :title="__('Fast Movers')" :breadcrumbs="[
    ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
    ['label' => __('Inventory Intelligence'), 'url' => route('admin.inventory.intelligence.overview')],
    ['label' => __('Fast Movers')],
]">
    <x-admin.page-header :title="__('Fast Movers')" :description="__('Items above the fast-moving daily consumption threshold (:window-day window).', ['window' => $window])" />
    @include('admin.inventory.intelligence.partials.nav')
    @include('admin.inventory.intelligence.partials.snapshot-table', [
        'title' => __('Fast moving items'),
        'snapshots' => $snapshots,
        'empty' => __('No fast movers in the current snapshot window.'),
    ])
</x-admin-layout>
