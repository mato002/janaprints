@php
    $breadcrumbs = [
        ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
        ['label' => __('Inventory Control'), 'url' => route('admin.workspaces.supply-chain.section', ['section' => 'inventory-control'])],
        ['label' => __('Inventory Reconciliation')],
    ];
@endphp
<x-admin-layout :title="__('Inventory Reconciliation')" :breadcrumbs="$breadcrumbs">
    <x-admin.page-header :title="__('Inventory reconciliation')" />

    <x-admin.card class="mb-6">
        <h3 class="font-medium mb-3">{{ __('Pending variances') }}</h3>
        <x-admin.data-table :searchable="true" export-filename="pending-reconciliations">
            <x-slot name="head">
                <tr><th>{{ __('Reconciliation') }}</th><th>{{ __('Count') }}</th><th>{{ __('Warehouse') }}</th><th>{{ __('Status') }}</th><th class="erp-table-actions-col">{{ __('Actions') }}</th></tr>
            </x-slot>
            <x-slot name="body">
                @forelse ($pending as $rec)
                    <tr>
                        <td>{{ $rec->reconciliation_number }}</td>
                        <td>{{ $rec->stockCount?->count_number }}</td>
                        <td>{{ $rec->stockCount?->warehouse?->name }}</td>
                        <td><x-admin.enum-status-badge :status="$rec->status->value" /></td>
                        <td class="erp-table-actions-col">
                            <x-admin.table-row-actions>
                                <x-admin.table-row-action :href="route('admin.inventory.reconciliations.show', $rec)">{{ __('View') }}</x-admin.table-row-action>
                                @can('approve', $rec)
                                    <x-admin.table-row-action method="POST" :action="route('admin.inventory.reconciliations.approve', $rec)">{{ __('Approve') }}</x-admin.table-row-action>
                                @endcan
                            </x-admin.table-row-actions>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-admin.empty-state :title="__('No pending reconciliations')" /></td></tr>
                @endforelse
            </x-slot>
            <x-slot name="footer"><x-admin.table-pagination :paginator="$pending" page-name="pending_page" /></x-slot>
        </x-admin.data-table>
    </x-admin.card>

    <x-admin.card class="mb-6">
        <h3 class="font-medium mb-3">{{ __('Approved variances') }}</h3>
        <x-admin.data-table :searchable="true" export-filename="approved-reconciliations">
            <x-slot name="head">
                <tr><th>{{ __('Reconciliation') }}</th><th>{{ __('Count') }}</th><th>{{ __('Warehouse') }}</th><th class="erp-table-actions-col">{{ __('Actions') }}</th></tr>
            </x-slot>
            <x-slot name="body">
                @forelse ($approved as $rec)
                    <tr>
                        <td>{{ $rec->reconciliation_number }}</td>
                        <td>{{ $rec->stockCount?->count_number }}</td>
                        <td>{{ $rec->stockCount?->warehouse?->name }}</td>
                        <td class="erp-table-actions-col">
                            <x-admin.table-row-actions>
                                <x-admin.table-row-action :href="route('admin.inventory.reconciliations.show', $rec)">{{ __('View') }}</x-admin.table-row-action>
                                @can('post', $rec)
                                    <x-admin.table-row-action method="POST" :action="route('admin.inventory.reconciliations.post', $rec)">{{ __('Post') }}</x-admin.table-row-action>
                                @endcan
                            </x-admin.table-row-actions>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><x-admin.empty-state :title="__('No approved reconciliations')" /></td></tr>
                @endforelse
            </x-slot>
            <x-slot name="footer"><x-admin.table-pagination :paginator="$approved" page-name="approved_page" /></x-slot>
        </x-admin.data-table>
    </x-admin.card>

    <x-admin.card>
        <h3 class="font-medium mb-3">{{ __('Posted reconciliations') }}</h3>
        <x-admin.data-table :searchable="true" export-filename="posted-reconciliations">
            <x-slot name="head">
                <tr><th>{{ __('Reconciliation') }}</th><th>{{ __('Count') }}</th><th>{{ __('Posted by') }}</th><th>{{ __('Posted at') }}</th><th class="erp-table-actions-col">{{ __('Actions') }}</th></tr>
            </x-slot>
            <x-slot name="body">
                @forelse ($posted as $rec)
                    <tr>
                        <td>{{ $rec->reconciliation_number }}</td>
                        <td>{{ $rec->stockCount?->count_number }}</td>
                        <td>{{ $rec->poster?->name }}</td>
                        <td>{{ $rec->posted_at?->format('Y-m-d H:i') }}</td>
                        <td class="erp-table-actions-col">
                            <x-admin.table-row-actions>
                                <x-admin.table-row-action :href="route('admin.inventory.reconciliations.show', $rec)">{{ __('View') }}</x-admin.table-row-action>
                                <x-admin.table-row-action :href="route('admin.inventory.reconciliations.show', $rec).'#audit'">{{ __('Audit History') }}</x-admin.table-row-action>
                            </x-admin.table-row-actions>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-admin.empty-state :title="__('No posted reconciliations')" /></td></tr>
                @endforelse
            </x-slot>
            <x-slot name="footer"><x-admin.table-pagination :paginator="$posted" page-name="posted_page" /></x-slot>
        </x-admin.data-table>
    </x-admin.card>
</x-admin-layout>
