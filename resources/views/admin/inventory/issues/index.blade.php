<x-admin-layout :title="__('Stock issues')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Store Desk'), 'url' => route('admin.store.desk')], ['label' => __('Issues')]]">
    @unless (\App\Support\Navigation\WorkspaceEmbed::inWorkspaceContext())
        @include('admin.store.desk.partials.desk-mode-nav', ['activeStoreView' => \App\Support\Inventory\StoreDeskViews::ISSUES])
    @endunless
    <x-admin.page-header :title="__('Stock issues')">
        <x-slot name="actions">
            @if (auth()->user()?->can('inventory.issue'))
                <a href="{{ route('admin.inventory.issues.create') }}" class="erp-btn-primary">{{ __('New stock issue') }}</a>
            @endif
        </x-slot>
    </x-admin.page-header>

    @include('admin.inventory.issues.partials.table')
</x-admin-layout>
