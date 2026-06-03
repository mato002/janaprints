<x-admin-layout :title="__('Departments')" :breadcrumbs="[['label' => __('Organization')], ['label' => __('Departments')]]">
    <x-admin.page-header :title="__('Departments')">
        <x-slot name="actions">
            @can('create', App\Models\Department::class)
                <a href="{{ route('admin.departments.create') }}" class="erp-btn-primary">{{ __('Create department') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table>
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Company') }}</th>
                <th scope="col">{{ __('Name') }}</th>
                <th scope="col">{{ __('Code') }}</th>
                <th scope="col" class="text-right">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($departments as $department)
                <tr x-show="matches(@js($department->company->name.' '.$department->name.' '.$department->code))">
                    <td>{{ $department->company->name }}</td>
                    <td class="font-medium text-erp-primary">{{ $department->name }}</td>
                    <td class="font-mono text-xs text-slate-500">{{ $department->code }}</td>
                    <td class="text-right">
                        @can('update', $department)
                            <a href="{{ route('admin.departments.edit', $department) }}" class="font-medium text-erp-accent hover:underline">{{ __('Edit') }}</a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="4"><x-admin.empty-state icon="view-grid" :title="__('No departments yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer">{{ $departments->links() }}</x-slot>
    </x-admin.data-table>
</x-admin-layout>
