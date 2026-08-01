@php
    use App\Support\Inventory\StoreDeskViews;
    use App\Support\Navigation\WorkspaceEmbed;

    $breadcrumbs = [
        ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
        ['label' => __('Store Desk'), 'url' => StoreDeskViews::deskUrl()],
        ['label' => __('Reorder Alerts')],
    ];
@endphp
<x-admin-layout :title="__('Reorder Alerts')" :breadcrumbs="$breadcrumbs">
    @unless (WorkspaceEmbed::inWorkspaceContext())
        @include('admin.store.desk.partials.desk-mode-nav', ['activeStoreView' => StoreDeskViews::ALERTS])
    @endunless
    <x-admin.page-header :title="__('Reorder Alerts')" :description="__('Actionable low-stock alerts with acknowledgement, resolution, and purchase request handoff.')" />
    @include('admin.inventory.alerts.partials.content')
</x-admin-layout>
