<x-admin-layout :title="$unit->name" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Catalogue'), 'url' => route('admin.inventory.catalogue.dashboard')], ['label' => __('Units of Measure'), 'url' => route('admin.inventory.catalogue.units.index')], ['label' => $unit->name]]">
    <x-admin.page-header :title="$unit->name">
        <x-admin.enum-status-badge :status="$unit->is_active ? 'active' : 'inactive'" />
        @if (auth()->user()?->can('catalogue.edit'))
            <a href="{{ route('admin.inventory.catalogue.units.edit', $unit) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>
        @endif
    </x-admin.page-header>

    <x-admin.card>
        <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2 mb-6">
            <div><dt class="text-slate-500">{{ __('Code') }}</dt><dd class="font-mono">{{ $unit->code }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Conversion') }}</dt><dd>{{ $unit->conversionLabel() }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Base unit') }}</dt><dd>{{ $unit->baseUnit?->name ?? __('Base unit') }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Usage count') }}</dt><dd>{{ $unit->usageCount() }}</dd></div>
        </dl>

        <h2 class="text-sm font-semibold text-slate-900 mb-3">{{ __('Items using this UOM') }}</h2>
        @if ($items->isEmpty())
            <x-admin.empty-state icon="cube" :title="__('No inventory items use this unit yet')" />
        @else
            <table class="erp-table erp-table--grid text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Item') }}</th>
                        <th>{{ __('SKU') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th class="erp-table-actions-col">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td>{{ $item->item_name }}</td>
                            <td class="font-mono text-xs">{{ $item->sku }}</td>
                            <td>{{ $item->category?->name }}</td>
                            <td class="erp-table-actions-col">
                                <x-admin.table-row-actions>
                                    <x-admin.table-row-action :href="route('admin.inventory.items.show', $item)">{{ __('View') }}</x-admin.table-row-action>
                                </x-admin.table-row-actions>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-admin.card>
</x-admin-layout>
