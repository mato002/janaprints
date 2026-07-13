<x-admin-layout :title="__('Handovers')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Handovers')]]">
    <x-admin.page-header :title="__('Asset Handovers')" :description="__('Formal transfer evidence between employees and branches.')">
        <x-slot name="actions">
            @can('create', \App\Models\Assets\AssetHandover::class)
                <a href="{{ route('admin.assets.custody.handovers.create') }}" class="erp-btn-primary">{{ __('New handover') }}</a>
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
        :search-placeholder="__('Search handovers…')"
        export-filename="asset-handovers"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Handover no') }}</th>
                <th scope="col">{{ __('Asset') }}</th>
                <th scope="col">{{ __('From') }}</th>
                <th scope="col">{{ __('To') }}</th>
                <th scope="col">{{ __('Date') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($handovers as $handover)
                @php
                    $from = $handover->fromEmployee?->full_name ?? $handover->fromBranch?->name ?? '';
                    $to = $handover->toEmployee?->full_name ?? $handover->toBranch?->name ?? '';
                    $search = strtolower(($handover->handover_no ?? '').' '.($handover->asset?->asset_name ?? '').' '.$from.' '.$to.' '.$handover->status->value);
                @endphp
                <tr x-show="rowVisible(@js($search))">
                    <td class="font-mono font-medium">{{ $handover->handover_no }}</td>
                    <td>{{ $handover->asset?->asset_name }}</td>
                    <td>{{ $from !== '' ? $from : '—' }}</td>
                    <td>{{ $to !== '' ? $to : '—' }}</td>
                    <td class="whitespace-nowrap">{{ $handover->handover_date?->format('Y-m-d') }}</td>
                    <td><x-admin.status-badge :variant="$handover->status->badgeVariant()">{{ $handover->status->label() }}</x-admin.status-badge></td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.assets.custody.handovers.show', $handover)">{{ __('View') }}</x-admin.table-row-action>
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <x-admin.empty-state icon="clipboard-list" :title="__('No handovers yet')" :description="__('Create a handover to formally transfer custody evidence.')" />
                    </td>
                </tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$handovers" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
