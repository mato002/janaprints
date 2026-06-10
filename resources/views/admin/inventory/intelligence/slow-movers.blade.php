<x-admin-layout :title="__('Slow Movers')" :breadcrumbs="[
    ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
    ['label' => __('Inventory Intelligence'), 'url' => route('admin.inventory.intelligence.overview')],
    ['label' => __('Slow Movers')],
]">
    <x-admin.page-header :title="__('Slow Movers')" :description="__('Items with outbound activity below the slow-moving threshold (:window-day window).', ['window' => $window])" />
    @include('admin.inventory.intelligence.partials.nav')
    @include('admin.inventory.intelligence.partials.snapshot-table', [
        'title' => __('Slow moving items'),
        'snapshots' => $snapshots,
        'empty' => __('No slow movers in the current snapshot window.'),
    ])
</x-admin-layout>
