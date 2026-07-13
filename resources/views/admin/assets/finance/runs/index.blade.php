<x-admin-layout :title="__('Depreciation Runs')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Depreciation Runs')]]">
    <x-admin.page-header :title="__('Depreciation Runs')">
        <x-slot name="actions">
            @can('run', \App\Models\Assets\DepreciationRun::class)
                <a href="{{ route('admin.assets.finance.runs.create') }}" class="erp-btn-primary">{{ __('New run') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table
        :search-placeholder="__('Search runs…')"
        export-filename="depreciation-runs"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Run no') }}</th>
                <th scope="col">{{ __('Period') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col">{{ __('Total') }}</th>
                <th scope="col">{{ __('Assets') }}</th>
                <th scope="col">{{ __('Run date') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($runs as $run)
                @php
                    $search = strtolower(($run->run_number ?? '').' '.($run->period ?? '').' '.$run->status->value);
                @endphp
                <tr x-show="rowVisible(@js($search))">
                    <td class="font-mono font-medium">{{ $run->run_number }}</td>
                    <td>{{ $run->period }}</td>
                    <td><x-admin.status-badge :variant="$run->status->badgeVariant()">{{ $run->status->label() }}</x-admin.status-badge></td>
                    <td class="tabular-nums">{{ number_format($run->total_depreciation, 2) }}</td>
                    <td>{{ $run->assets_processed }}</td>
                    <td class="whitespace-nowrap">{{ $run->run_date?->format('Y-m-d') }}</td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.assets.finance.runs.show', $run)">{{ __('View') }}</x-admin.table-row-action>
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <x-admin.empty-state icon="clipboard-list" :title="__('No depreciation runs yet')" :description="__('Start a depreciation run to post period depreciation.')" />
                    </td>
                </tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$runs" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
