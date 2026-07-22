<x-admin.data-table
    :search-placeholder="__('Search entries…')"
    export-filename="depreciation-entries"
>
    <x-slot name="head">
        <tr>
            <th scope="col">{{ __('Period') }}</th>
            <th scope="col">{{ __('Asset') }}</th>
            <th scope="col">{{ __('Amount') }}</th>
            <th scope="col">{{ __('Accumulated') }}</th>
            <th scope="col">{{ __('NBV') }}</th>
            <th scope="col">{{ __('Status') }}</th>
            <th scope="col">{{ __('Journal') }}</th>
        </tr>
    </x-slot>
    <x-slot name="body">
        @forelse ($entries as $entry)
            @php
                $search = strtolower(($entry->period_date?->format('Y-m') ?? '').' '.($entry->asset?->asset_number ?? '').' '.($entry->posting_status->value ?? '').' '.($entry->journal?->reference ?? ''));
            @endphp
            <tr x-show="rowVisible(@js($search))">
                <td>{{ $entry->period_date?->format('Y-m') }}</td>
                <td class="font-medium">{{ $entry->asset?->asset_number }}</td>
                <td class="tabular-nums">{{ number_format($entry->depreciation_amount, 2) }}</td>
                <td class="tabular-nums">{{ number_format($entry->accumulated_after, 2) }}</td>
                <td class="tabular-nums">{{ number_format($entry->net_book_value_after, 2) }}</td>
                <td>{{ $entry->posting_status->label() }}</td>
                <td>{{ $entry->journal?->reference ?? '—' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7">
                    <x-admin.empty-state icon="clipboard-list" :title="__('No depreciation entries yet')" :description="__('Entries appear after depreciation runs are posted.')" />
                </td>
            </tr>
        @endforelse
    </x-slot>
    <x-slot name="footer"><x-admin.table-pagination :paginator="$entries" /></x-slot>
</x-admin.data-table>
