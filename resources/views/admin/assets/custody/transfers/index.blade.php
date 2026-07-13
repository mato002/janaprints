<x-admin-layout :title="__('Branch Transfers')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Branch Transfers')]]">
    <x-admin.page-header :title="__('Branch Asset Transfers')" :description="__('Branch-to-branch transfers with approval and acceptance.')">
        <x-slot name="actions">
            @can('create', \App\Models\Assets\AssetBranchTransfer::class)
                <a href="{{ route('admin.assets.custody.transfers.create') }}" class="erp-btn-primary">{{ __('New transfer') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="url()->current()" :reset-url="url()->current()">
            <select name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                <option value="">{{ __('All statuses') }}</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.data-table
        :search-placeholder="__('Search transfers…')"
        export-filename="asset-transfers"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Transfer no') }}</th>
                <th scope="col">{{ __('Asset') }}</th>
                <th scope="col">{{ __('From branch') }}</th>
                <th scope="col">{{ __('To branch') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col">{{ __('Requested') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($transfers as $transfer)
                @php
                    $search = strtolower(($transfer->transfer_no ?? '').' '.($transfer->asset?->asset_name ?? '').' '.($transfer->fromBranch?->name ?? '').' '.($transfer->toBranch?->name ?? '').' '.$transfer->status->value);
                @endphp
                <tr x-show="rowVisible(@js($search))">
                    <td class="font-mono font-medium">{{ $transfer->transfer_no }}</td>
                    <td>{{ $transfer->asset?->asset_name }}</td>
                    <td>{{ $transfer->fromBranch?->name }}</td>
                    <td>{{ $transfer->toBranch?->name }}</td>
                    <td><x-admin.status-badge :variant="$transfer->status->badgeVariant()">{{ $transfer->status->label() }}</x-admin.status-badge></td>
                    <td class="whitespace-nowrap">{{ $transfer->requested_at?->format('Y-m-d H:i') }}</td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.assets.custody.transfers.show', $transfer)">{{ __('View') }}</x-admin.table-row-action>
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <x-admin.empty-state icon="truck" :title="__('No transfers yet')" :description="__('Create a branch transfer to move an asset between locations.')" />
                    </td>
                </tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$transfers" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
