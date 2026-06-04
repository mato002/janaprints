<x-admin-layout :title="__('Store permissions')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Store Management'), 'url' => route('admin.inventory.store.dashboard')], ['label' => __('Permissions')]]">
    <x-admin.page-header :title="__('Store permissions')" :description="__('Role coverage for store operations.')" />

    <x-admin.data-table :search-placeholder="__('Search roles...')" export-filename="store-permissions">
        <x-slot name="filters">
            <label class="block max-w-xs text-xs font-medium text-slate-600">
                {{ __('Store access') }}
                <select class="erp-select mt-1" x-model="filterValues.store_access">
                    <option value="all">{{ __('All') }}</option>
                    <option value="yes">{{ __('Has store permissions') }}</option>
                    <option value="no">{{ __('No store permissions') }}</option>
                </select>
            </label>
        </x-slot>
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Role') }}</th>
                @foreach ($permissions as $label)
                    <th scope="col">{{ $label }}</th>
                @endforeach
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @foreach ($roles as $role)
                @php
                    $rolePermissions = $role->permissions->pluck('name');
                    $hasStoreAccess = $rolePermissions->intersect(array_keys($permissions))->isNotEmpty() ? 'yes' : 'no';
                @endphp
                <tr x-show="rowVisible(@js(strtolower($role->name)), null, @js(['store_access' => $hasStoreAccess]), {{ $loop->iteration }})">
                    <td class="font-medium">{{ $role->name }}</td>
                    @foreach (array_keys($permissions) as $permission)
                        <td>
                            <x-admin.status-badge :variant="$rolePermissions->contains($permission) ? 'success' : 'neutral'">
                                {{ $rolePermissions->contains($permission) ? __('Yes') : __('No') }}
                            </x-admin.status-badge>
                        </td>
                    @endforeach
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.roles.show', $role)">{{ __('View role') }}</x-admin.table-row-action>
                            @can('update', $role)
                                <x-admin.table-row-action :href="route('admin.roles.permissions.edit', $role)">{{ __('Edit permissions') }}</x-admin.table-row-action>
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @endforeach
        </x-slot>
    </x-admin.data-table>
</x-admin-layout>
