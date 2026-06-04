<x-admin-layout :title="__('Segments')" :breadcrumbs="[['label' => __('CRM')], ['label' => __('Segments')]]">
    <x-admin.page-header :title="__('Customer segments')" :description="__('Group customers for campaigns and pricing.')">
        <x-slot name="actions">
            @can('create', App\Models\Crm\Customer::class)
                <a href="{{ route('admin.crm.segments.create') }}" class="erp-btn-primary">{{ __('Create segment') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table :search-placeholder="__('Search segments…')" export-filename="segments">
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Name') }}</th>
                <th scope="col">{{ __('Code') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($segments as $segment)
                <tr x-show="rowVisible(@js(strtolower($segment->name.' '.$segment->code)))">
                    <td class="font-medium text-erp-primary">{{ $segment->name }}</td>
                    <td class="font-mono text-xs text-slate-500">{{ $segment->code }}</td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            @can('update', $segment)
                                <x-admin.table-row-action :href="route('admin.crm.segments.edit', $segment)">{{ __('Edit') }}</x-admin.table-row-action>
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">
                        <x-admin.empty-state icon="tag" :title="__('No segments yet')" :description="__('Create segments to organize your customer base.')">
                            <x-slot name="action">
                                @can('create', App\Models\Crm\Customer::class)
                                    <a href="{{ route('admin.crm.segments.create') }}" class="erp-btn-primary">{{ __('Create segment') }}</a>
                                @endcan
                            </x-slot>
                        </x-admin.empty-state>
                    </td>
                </tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$segments" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
