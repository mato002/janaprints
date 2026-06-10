<x-admin-layout :title="__('Warehouse Velocity')" :breadcrumbs="[
    ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
    ['label' => __('Inventory Intelligence'), 'url' => route('admin.inventory.intelligence.overview')],
    ['label' => __('Warehouse Velocity')],
]">
    <x-admin.page-header :title="__('Warehouse Velocity')" :description="__('Outbound consumption aggregated by warehouse (:window-day window).', ['window' => $window])" />
    @include('admin.inventory.intelligence.partials.nav')

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Warehouse') }}</th>
                        <th>{{ __('Items tracked') }}</th>
                        <th>{{ __('Total outbound') }}</th>
                        <th>{{ __('Avg daily consumption') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td class="font-medium">{{ $row['warehouse'] }}</td>
                            <td class="tabular-nums">{{ $row['item_count'] }}</td>
                            <td class="tabular-nums">{{ number_format($row['total_outbound'], 3) }}</td>
                            <td class="tabular-nums">{{ number_format($row['avg_daily_consumption'], 4) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-slate-500">{{ __('No warehouse velocity snapshots yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
</x-admin-layout>
