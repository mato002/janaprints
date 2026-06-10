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

<x-admin.card class="mb-4">
    <nav class="flex flex-wrap gap-2">
        @foreach ($nav as $link)
            <a href="{{ route($link['route']) }}"
               @class([
                   'rounded-md px-3 py-1.5 text-xs font-medium',
                   'bg-slate-900 text-white' => request()->routeIs($link['route']),
                   'bg-slate-100 text-slate-700 hover:bg-slate-200' => ! request()->routeIs($link['route']),
               ])>
                {{ $link['label'] }}
            </a>
        @endforeach
    </nav>
</x-admin.card>
