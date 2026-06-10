<x-admin-layout :title="__('Departments')" :breadcrumbs="[['label' => __('Organization')], ['label' => __('Departments')]]">
    <x-admin.workspace-content-header :title="__('Departments')">
        <x-slot:actions>
            @can('create', App\Models\Department::class)
                <a href="{{ route('admin.departments.create') }}" class="erp-btn-primary erp-btn--sm">{{ __('Create department') }}</a>
            @endcan
        </x-slot:actions>
    </x-admin.workspace-content-header>

    <x-admin.data-table
        :search-placeholder="__('Search departments…')"
        export-route="admin.departments.export"
        :export-query="request()->query()"
        :format-in-path="true"
        export-filename="departments"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Department') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Company') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($departments as $department)
                <tr x-show="rowVisible(@js(strtolower($department->company->name.' '.$department->name.' '.$department->code)))">
                    <td>
                        <div class="font-medium text-erp-primary">{{ $department->name }}</div>
                        <div class="font-mono text-[11px] text-slate-500">{{ $department->code }}</div>
                    </td>
                    <td class="hidden md:table-cell">{{ $department->company->name }}</td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            @can('update', $department)
                                <x-admin.table-row-action :href="route('admin.departments.edit', $department)">{{ __('Edit') }}</x-admin.table-row-action>
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3"><x-admin.empty-state icon="view-grid" :title="__('No departments yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$departments" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
