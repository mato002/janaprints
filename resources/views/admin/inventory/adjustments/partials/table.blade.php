@php
    $fromStoreDesk = (bool) ($fromStoreDesk ?? false);
@endphp

<x-admin.data-table
    :search-placeholder="__('Search adjustments…')"
    export-route="admin.inventory.exports"
    :export-route-params="['listing' => 'adjustments']"
    :export-query="request()->query()"
    :format-in-path="true"
    export-filename="stock-adjustments"
>
    <x-slot name="head">
        <tr>
            <th scope="col">{{ __('Adjustment') }}</th>
            <th scope="col">{{ __('Status') }}</th>
            <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
        </tr>
    </x-slot>
    <x-slot name="body">
        @forelse ($adjustments as $a)
            <tr x-show="rowVisible(@js(strtolower($a->adjustment_number.' '.$a->status->value)))">
                <td class="font-medium">{{ $a->adjustment_number }}</td>
                <td><x-admin.enum-status-badge :status="$a->status->value" /></td>
                <td class="erp-table-actions-col">
                    <x-admin.table-row-actions>
                        <x-admin.table-row-action :href="route('admin.inventory.adjustments.show', $a)">{{ __('View') }}</x-admin.table-row-action>
                    </x-admin.table-row-actions>
                </td>
            </tr>
        @empty
            <tr><td colspan="3"><x-admin.empty-state icon="switch-horizontal" :title="__('No adjustments yet')" /></td></tr>
        @endforelse
    </x-slot>
    <x-slot name="footer"><x-admin.table-pagination :paginator="$adjustments" /></x-slot>
</x-admin.data-table>
