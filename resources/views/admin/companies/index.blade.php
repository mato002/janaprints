<x-admin-layout :title="__('Companies')" :breadcrumbs="[['label' => __('Organization')], ['label' => __('Companies')]]">
    <x-admin.workspace-content-header :title="__('Companies')" :description="__('Legal entities in your ERP tenant.')">
        <x-slot:actions>
            @can('create', App\Models\Company::class)
                <a href="{{ route('admin.companies.create') }}" class="erp-btn-primary erp-btn--sm">{{ __('Create company') }}</a>
            @endcan
        </x-slot:actions>
    </x-admin.workspace-content-header>

    <x-admin.data-table
        :search-placeholder="__('Search companies…')"
        export-route="admin.companies.export"
        :export-query="request()->query()"
        :format-in-path="true"
        export-filename="companies"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Name') }}</th>
                <th scope="col">{{ __('Code') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($companies as $company)
                <tr x-show="rowVisible(@js(strtolower($company->name.' '.$company->code)), @js($company->is_active ? 'active' : 'inactive'))">
                    <td class="font-medium text-erp-primary">{{ $company->name }}</td>
                    <td class="font-mono text-xs text-slate-500">{{ $company->code }}</td>
                    <td>
                        <x-admin.status-badge :variant="$company->is_active ? 'success' : 'danger'">
                            {{ $company->is_active ? __('Active') : __('Inactive') }}
                        </x-admin.status-badge>
                    </td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            @can('update', $company)
                                <x-admin.table-row-action :href="route('admin.companies.edit', $company)">{{ __('Edit') }}</x-admin.table-row-action>
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4"><x-admin.empty-state icon="building" :title="__('No companies yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$companies" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
