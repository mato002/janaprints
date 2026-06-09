<x-admin-layout :title="__('Units of Measure')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Catalogue'), 'url' => route('admin.inventory.catalogue.dashboard')], ['label' => __('Units of Measure')]]">
    <x-admin.page-header :title="__('Units of Measure')" :description="__('Manage stock units, packaging definitions, and conversion factors.')">
        <x-slot name="actions">
            @if (auth()->user()?->can('catalogue.create'))
                <a href="{{ route('admin.inventory.catalogue.units.create') }}" class="erp-btn-primary">{{ __('New Unit') }}</a>
            @endif
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table :search-placeholder="__('Search units...')" export-filename="units-of-measure" :filterable="true">
        <x-slot name="filters">
            <form method="GET" x-data="erpIndexFilterForm()" @change="onFieldChange($event)" class="flex flex-wrap items-center gap-2">
                <x-admin.status-pills
                    :options="[['value' => 'all', 'label' => __('All')], ['value' => 'active', 'label' => __('Active')], ['value' => 'inactive', 'label' => __('Inactive')]]"
                    param="status"
                    :current="$status"
                />
            </form>
        </x-slot>
        <x-slot name="head">
            <tr>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Code') }}</th>
                <th>{{ __('Base Unit') }}</th>
                <th>{{ __('Conversion') }}</th>
                <th>{{ __('Items') }}</th>
                <th>{{ __('Status') }}</th>
                <th class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($units as $unit)
                <tr x-show="rowVisible(@js(strtolower($unit->code.' '.$unit->name.' '.($unit->baseUnit?->name ?? ''))))">
                    <td class="font-medium">{{ $unit->name }}</td>
                    <td class="font-mono text-xs">{{ $unit->code }}</td>
                    <td>{{ $unit->baseUnit?->name ?? __('Base') }}</td>
                    <td>{{ $unit->conversionLabel() }}</td>
                    <td class="tabular-nums">{{ $unit->items_count + $unit->categories_count }}</td>
                    <td><x-admin.enum-status-badge :status="$unit->is_active ? 'active' : 'inactive'" /></td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.inventory.catalogue.units.show', $unit)">{{ __('View') }}</x-admin.table-row-action>
                            @if (auth()->user()?->can('catalogue.edit'))
                                <x-admin.table-row-action :href="route('admin.inventory.catalogue.units.edit', $unit)">{{ __('Edit') }}</x-admin.table-row-action>
                            @endif
                            @if (auth()->user()?->can('catalogue.edit') && $unit->is_active)
                                <x-admin.table-row-action method="PATCH" :action="route('admin.inventory.catalogue.units.deactivate', $unit)" :confirm="__('Deactivate this unit?')">{{ __('Deactivate') }}</x-admin.table-row-action>
                            @endif
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><x-admin.empty-state icon="switch-horizontal" :title="__('No units of measure yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$units" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
