<x-admin.data-table
    :search-placeholder="__('Search reconciliations…')"
    export-filename="asset-reconciliations"
>
    <x-slot name="head">
        <tr>
            <th scope="col">{{ __('No') }}</th>
            <th scope="col">{{ __('Date') }}</th>
            <th scope="col">{{ __('Register NBV') }}</th>
            <th scope="col">{{ __('GL NBV') }}</th>
            <th scope="col">{{ __('Variance') }}</th>
            <th scope="col">{{ __('Status') }}</th>
            <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
        </tr>
    </x-slot>
    <x-slot name="body">
        @forelse ($reconciliations as $record)
            @php
                $search = strtolower(($record->reconciliation_no ?? '').' '.$record->status->value);
            @endphp
            <tr x-show="rowVisible(@js($search))">
                <td class="font-medium">{{ $record->reconciliation_no }}</td>
                <td class="whitespace-nowrap">{{ $record->reconciliation_date?->format('Y-m-d') }}</td>
                <td class="tabular-nums">{{ number_format($record->register_nbv, 2) }}</td>
                <td class="tabular-nums">{{ number_format($record->gl_nbv, 2) }}</td>
                <td class="tabular-nums">{{ number_format($record->variance_nbv, 2) }}</td>
                <td><x-admin.status-badge :variant="$record->status->badgeVariant()">{{ $record->status->label() }}</x-admin.status-badge></td>
                <td class="erp-table-actions-col">
                    <x-admin.table-row-actions>
                        <x-admin.table-row-action :href="route('admin.assets.finance.reconciliation.show', $record)">{{ __('View') }}</x-admin.table-row-action>
                    </x-admin.table-row-actions>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7">
                    <x-admin.empty-state icon="clipboard-list" :title="__('No reconciliations yet')" :description="__('Run a reconciliation to compare register NBV with the GL.')" />
                </td>
            </tr>
        @endforelse
    </x-slot>
    <x-slot name="footer"><x-admin.table-pagination :paginator="$reconciliations" /></x-slot>
</x-admin.data-table>
