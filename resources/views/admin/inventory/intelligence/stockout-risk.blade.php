<x-admin-layout :title="__('Stockout Risk')" :breadcrumbs="[
    ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
    ['label' => __('Inventory Intelligence'), 'url' => route('admin.inventory.intelligence.overview')],
    ['label' => __('Stockout Risk')],
]">
    <x-admin.page-header :title="__('Stockout Risk')" :description="__('Items with medium, high, or critical depletion risk (:window-day window).', ['window' => $window])" />
    @include('admin.inventory.intelligence.partials.nav')
    @include('admin.inventory.intelligence.partials.snapshot-table', [
        'title' => __('Velocity stockout risks'),
        'snapshots' => $snapshots,
        'empty' => __('No stockout risk items in the current snapshot window.'),
    ])
</x-admin-layout>
