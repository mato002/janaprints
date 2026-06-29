<x-admin-layout :title="__('Bills of Materials')" :breadcrumbs="[['label' => __('Production'), 'url' => route('admin.workspaces.production')], ['label' => __('Bills of Materials')]]">
    <x-admin.page-header :title="__('Bills of Materials')">
        <x-slot name="actions">
            @if (auth()->user()?->can('production.bom.create'))
                <a href="{{ route('admin.production.boms.create') }}" class="erp-btn-primary">{{ __('New BOM') }}</a>
            @endif
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table :search-placeholder="__('Search BOMs...')" export-filename="production-boms">
        <x-slot name="head">
            <tr>
                <th>{{ __('Finished product') }}</th>
                <th>{{ __('BOM name') }}</th>
                <th>{{ __('Components') }}</th>
                <th>{{ __('Status') }}</th>
                <th class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($boms as $bom)
                <tr x-show="rowVisible(@js(strtolower(($bom->finishedItem?->sku ?? '').' '.($bom->finishedItem?->item_name ?? '').' '.$bom->name)))">
                    <td>
                        <div class="font-medium">{{ $bom->finishedItem?->item_name ?? '—' }}</div>
                        <div class="font-mono text-[11px] text-slate-500">{{ $bom->finishedItem?->sku }}</div>
                    </td>
                    <td>{{ $bom->name }} <span class="text-slate-500">v{{ $bom->version }}</span></td>
                    <td class="tabular-nums">{{ $bom->lines_count }}</td>
                    <td>
                        <x-admin.status-badge :tone="$bom->is_active ? 'green' : 'slate'">
                            {{ $bom->is_active ? __('Active') : __('Inactive') }}
                        </x-admin.status-badge>
                    </td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            @if (auth()->user()?->can('production.bom.edit'))
                                <x-admin.table-row-action :href="route('admin.production.boms.edit', $bom)">{{ __('Edit') }}</x-admin.table-row-action>
                            @endif
                            @if (auth()->user()?->can('production.bom.edit'))
                                <x-admin.table-row-action method="DELETE" :action="route('admin.production.boms.destroy', $bom)" variant="danger" :confirm="__('Remove this BOM?')">{{ __('Remove') }}</x-admin.table-row-action>
                            @endif
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-admin.empty-state icon="collection" :title="__('No BOMs yet')" :description="__('Define raw material recipes for finished products.')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$boms" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
