<x-admin-layout :title="__('Intelligence Settings')" :breadcrumbs="[
    ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
    ['label' => __('Inventory Intelligence'), 'url' => route('admin.inventory.intelligence.overview')],
    ['label' => __('Settings')],
]">
    <x-admin.page-header :title="__('Intelligence Settings')" :description="__('Read-only view of velocity intelligence thresholds (config/inventory_intelligence.php).')" />
    @include('admin.inventory.intelligence.partials.nav')

    <x-admin.card>
        <dl class="grid grid-cols-1 gap-4 md:grid-cols-2 text-sm">
            @foreach ([
                __('Analysis windows (days)') => implode(', ', $config['windows'] ?? []),
                __('Dead stock threshold (days)') => $config['dead_stock_days'] ?? '—',
                __('Critical days to depletion') => $config['critical_days_to_depletion'] ?? '—',
                __('High days to depletion') => $config['high_days_to_depletion'] ?? '—',
                __('Medium days to depletion') => $config['medium_days_to_depletion'] ?? '—',
                __('New item grace (days)') => $config['new_item_grace_days'] ?? '—',
                __('Fast-moving daily threshold') => $config['fast_moving_daily_threshold'] ?? '—',
                __('Slow-moving daily threshold') => $config['slow_moving_daily_threshold'] ?? '—',
                __('Default reorder cover (days)') => $config['default_reorder_cover_days'] ?? '—',
                __('Default snapshot window (days)') => $config['default_snapshot_window'] ?? '—',
            ] as $label => $value)
                <div>
                    <dt class="text-xs font-medium text-slate-500">{{ $label }}</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    </x-admin.card>
</x-admin-layout>
