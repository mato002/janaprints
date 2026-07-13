<x-admin-layout :title="__('Payroll groups')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Compensation'), 'url' => route('admin.hr.compensation.dashboard')], ['label' => __('Payroll groups')]]">
    <x-admin.page-header
        :title="__('Payroll groups')"
        :description="__('Groups used to organize employees for payroll runs (e.g. Main, Casual, Contract). Add new groups here or use the + button on salary forms.')"
    >
        <x-slot name="actions">
            @can('create', App\Models\Hr\EmployeeCompensation::class)
                <button
                    type="button"
                    class="erp-btn-primary"
                    onclick="window.erpLookupManager.open(@js(route('admin.payroll-groups.quick-create')), { title: @js(__('Create payroll group')), onSuccess: () => window.location.reload() })"
                >{{ __('Add payroll group') }}</button>
            @endcan
        </x-slot>
    </x-admin.page-header>

<x-admin.data-table :searchable="false" :exportable="false">
        <x-slot name="head">
            <tr>
                <th>{{ __('Group') }}</th>
                <th>{{ __('Code') }}</th>
                <th>{{ __('Status') }}</th>
                <th class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($groups as $group)
                <tr>
                    <td class="font-medium">{{ $group->name }}</td>
                    <td class="font-mono text-xs text-slate-500">{{ $group->code }}</td>
                    <td>
                        <x-admin.status-badge :variant="$group->is_active ? 'success' : 'neutral'">
                            {{ $group->is_active ? __('Active') : __('Inactive') }}
                        </x-admin.status-badge>
                    </td>
                    <td class="erp-table-actions-col">
                        @can('create', App\Models\Hr\EmployeeCompensation::class)
                            <x-admin.table-row-actions>
                                @if ($group->is_active)
                                    <x-admin.table-row-action
                                        method="PATCH"
                                        :action="route('admin.hr.compensation.payroll-groups.deactivate', $group)"
                                        :confirm="__('Deactivate this payroll group?')"
                                    >{{ __('Deactivate') }}</x-admin.table-row-action>
                                @else
                                    <x-admin.table-row-action
                                        method="PATCH"
                                        :action="route('admin.hr.compensation.payroll-groups.reactivate', $group)"
                                    >{{ __('Reactivate') }}</x-admin.table-row-action>
                                @endif
                            </x-admin.table-row-actions>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="4"><x-admin.empty-state :title="__('No payroll groups yet')" /></td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>
</x-admin-layout>
