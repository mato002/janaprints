<x-admin-layout :title="__('Attributes')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Catalogue'), 'url' => route('admin.inventory.catalogue.dashboard')], ['label' => __('Attributes')]]">
    <x-admin.page-header :title="__('Attributes')"><x-slot name="actions">@if (auth()->user()?->can('catalogue.create'))<a href="{{ route('admin.inventory.catalogue.attributes.create') }}" class="erp-btn-primary">{{ __('New Attribute') }}</a>@endif</x-slot></x-admin.page-header>
    <x-admin.data-table :search-placeholder="__('Search attributes...')" export-filename="catalogue-attributes">
        <x-slot name="head"><tr><th>{{ __('Attribute') }}</th><th>{{ __('Category') }}</th><th>{{ __('Type') }}</th><th>{{ __('Options') }}</th><th class="erp-table-actions-col">{{ __('Actions') }}</th></tr></x-slot>
        <x-slot name="body">
            @forelse ($attributes as $attribute)
                <tr x-show="rowVisible(@js(strtolower($attribute->code.' '.$attribute->name.' '.($attribute->category?->name ?? ''))))">
                    <td><div class="font-medium">{{ $attribute->name }}</div><div class="font-mono text-[11px] text-slate-500">{{ $attribute->code }}</div></td>
                    <td>{{ $attribute->category?->name ?? __('Reusable') }}</td>
                    <td>{{ str($attribute->data_type)->headline() }}</td>
                    <td class="text-xs">{{ $attribute->options->pluck('label')->join(', ') ?: __('-') }}</td>
                    <td class="erp-table-actions-col"><x-admin.table-row-actions>@if (auth()->user()?->can('catalogue.edit'))<x-admin.table-row-action :href="route('admin.inventory.catalogue.attributes.edit', $attribute)">{{ __('Edit') }}</x-admin.table-row-action>@endif @if (auth()->user()?->can('catalogue.delete'))<x-admin.table-row-action method="DELETE" :action="route('admin.inventory.catalogue.attributes.destroy', $attribute)" variant="danger" :confirm="__('Remove this attribute?')">{{ __('Remove') }}</x-admin.table-row-action>@endif</x-admin.table-row-actions></td>
                </tr>
            @empty
                <tr><td colspan="5"><x-admin.empty-state icon="sliders" :title="__('No attributes yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$attributes" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
