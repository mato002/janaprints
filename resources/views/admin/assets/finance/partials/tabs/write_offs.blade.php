<x-admin.data-table
    :search-placeholder="__('Search write-offs…')"
    export-filename="asset-write-offs"
>
    <x-slot name="head">
        <tr>
            <th scope="col">{{ __('No') }}</th>
            <th scope="col">{{ __('Asset') }}</th>
            <th scope="col">{{ __('Reason') }}</th>
            <th scope="col">{{ __('NBV') }}</th>
            <th scope="col">{{ __('Status') }}</th>
            <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
        </tr>
    </x-slot>
    <x-slot name="body">
        @forelse ($writeOffs as $writeOff)
            @php
                $search = strtolower(($writeOff->writeoff_no ?? '').' '.($writeOff->asset?->asset_number ?? '').' '.($writeOff->reason->value ?? '').' '.$writeOff->status->value);
            @endphp
            <tr x-show="rowVisible(@js($search))">
                <td class="font-mono font-medium">{{ $writeOff->writeoff_no }}</td>
                <td>{{ $writeOff->asset?->asset_number }}</td>
                <td>{{ $writeOff->reason->label() }}</td>
                <td class="tabular-nums">{{ number_format($writeOff->nbv_at_writeoff, 2) }}</td>
                <td><x-admin.status-badge :variant="$writeOff->status->badgeVariant()">{{ $writeOff->status->label() }}</x-admin.status-badge></td>
                <td class="erp-table-actions-col">
                    <x-admin.table-row-actions>
                        @if ($writeOff->status === \App\Enums\AssetWriteOffStatus::PendingApproval)
                            <x-admin.table-row-action method="POST" :action="route('admin.assets.finance.write-offs.approve', $writeOff)">{{ __('Approve') }}</x-admin.table-row-action>
                        @endif
                        @if ($writeOff->status === \App\Enums\AssetWriteOffStatus::Approved)
                            <x-admin.table-row-action method="POST" :action="route('admin.assets.finance.write-offs.post', $writeOff)">{{ __('Post') }}</x-admin.table-row-action>
                        @endif
                    </x-admin.table-row-actions>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6">
                    <x-admin.empty-state icon="clipboard-list" :title="__('No write-offs yet')" :description="__('Create a write-off when an asset is retired from the register.')" />
                </td>
            </tr>
        @endforelse
    </x-slot>
    <x-slot name="footer"><x-admin.table-pagination :paginator="$writeOffs" /></x-slot>
</x-admin.data-table>
