@php
    $nav = [
        ['label' => __('Overview'), 'route' => 'admin.inventory.intelligence.overview'],
        ['label' => __('Stockout Risk'), 'route' => 'admin.inventory.intelligence.stockout-risk'],
        ['label' => __('Dead Stock'), 'route' => 'admin.inventory.intelligence.dead-stock'],
        ['label' => __('Fast Movers'), 'route' => 'admin.inventory.intelligence.fast-movers'],
        ['label' => __('Slow Movers'), 'route' => 'admin.inventory.intelligence.slow-movers'],
        ['label' => __('Warehouse Velocity'), 'route' => 'admin.inventory.intelligence.warehouse-velocity'],
    ];

    if (auth()->user()?->can('inventory.intelligence.configure')) {
        $nav[] = ['label' => __('Settings'), 'route' => 'admin.inventory.intelligence.settings'];
    }
@endphp

<x-admin.workspace-nav :links="$nav" variant="compact" />
