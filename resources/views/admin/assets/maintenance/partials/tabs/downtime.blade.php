<x-admin.data-table
    :search-placeholder="__('Search downtime…')"
    export-filename="asset-downtime"
>
    <x-slot name="head">
        <tr>
            <th scope="col">{{ __('Asset') }}</th>
            <th scope="col">{{ __('Work order') }}</th>
            <th scope="col">{{ __('Start') }}</th>
            <th scope="col">{{ __('End') }}</th>
            <th scope="col">{{ __('Duration') }}</th>
            <th scope="col">{{ __('Impact') }}</th>
        </tr>
    </x-slot>
    <x-slot name="body">
        @forelse ($records as $record)
            @php
                $search = strtolower(($record->asset?->asset_name ?? '').' '.($record->workOrder?->work_order_no ?? '').' '.($record->impact_level->value ?? ''));
            @endphp
            <tr x-show="rowVisible(@js($search))">
                <td class="font-medium">{{ $record->asset?->asset_name }}</td>
                <td>{{ $record->workOrder?->work_order_no ?? '—' }}</td>
                <td class="whitespace-nowrap">{{ $record->start_time?->format('Y-m-d H:i') }}</td>
                <td class="whitespace-nowrap">{{ $record->end_time?->format('Y-m-d H:i') ?? __('Active') }}</td>
                <td>{{ $record->duration_minutes }} min ({{ $record->durationHours() }}h)</td>
                <td>{{ $record->impact_level->label() }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6">
                    <x-admin.empty-state icon="clock" :title="__('No downtime records yet')" :description="__('Downtime is recorded automatically when maintenance work takes assets offline.')" />
                </td>
            </tr>
        @endforelse
    </x-slot>
    <x-slot name="footer"><x-admin.table-pagination :paginator="$records" /></x-slot>
</x-admin.data-table>
