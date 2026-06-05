<x-admin-layout :title="__('Asset Reports')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Reports')]]">
    <x-admin.page-header :title="__('Fixed Asset Reports')" />
    <x-admin.card class="mb-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="report" class="erp-select">
                <option value="register" @selected($report === 'register')>{{ __('Asset Register') }}</option>
                <option value="valuation" @selected($report === 'valuation')>{{ __('Asset Valuation') }}</option>
                <option value="depreciation_schedule" @selected($report === 'depreciation_schedule')>{{ __('Depreciation Schedule') }}</option>
                <option value="fully_depreciated" @selected($report === 'fully_depreciated')>{{ __('Fully Depreciated') }}</option>
                <option value="near_end_of_life" @selected($report === 'near_end_of_life')>{{ __('Near End of Life') }}</option>
            </select>
            <button type="submit" class="erp-btn-secondary">{{ __('Generate') }}</button>
        </form>
    </x-admin.card>
    <x-admin.card>
        <div class="overflow-x-auto">
            @if ($report === 'valuation')
                <table class="erp-table w-full text-sm">
                    <thead><tr><th>{{ __('Asset') }}</th><th>{{ __('Cost') }}</th><th>{{ __('NBV') }}</th><th>{{ __('Monthly Depr.') }}</th></tr></thead>
                    <tbody>
                        @foreach ($data as $row)
                            <tr>
                                <td>{{ $row['asset']->asset_number }}</td>
                                <td>{{ number_format($row['profile']['acquisition_cost'], 2) }}</td>
                                <td>{{ number_format($row['profile']['net_book_value'], 2) }}</td>
                                <td>{{ number_format($row['profile']['monthly_depreciation'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @elseif ($report === 'depreciation_schedule')
                <table class="erp-table w-full text-sm">
                    <thead><tr><th>{{ __('Period') }}</th><th>{{ __('Asset') }}</th><th>{{ __('Amount') }}</th><th>{{ __('NBV After') }}</th></tr></thead>
                    <tbody>
                        @foreach ($data as $entry)
                            <tr>
                                <td>{{ $entry->period_date?->format('Y-m') }}</td>
                                <td>{{ $entry->asset?->asset_number }}</td>
                                <td>{{ number_format($entry->depreciation_amount, 2) }}</td>
                                <td>{{ number_format($entry->net_book_value_after, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <table class="erp-table w-full text-sm">
                    <thead><tr><th>{{ __('Asset') }}</th><th>{{ __('Category') }}</th><th>{{ __('Cost') }}</th><th>{{ __('NBV') }}</th></tr></thead>
                    <tbody>
                        @foreach ($data as $asset)
                            <tr>
                                <td><a href="{{ route('admin.assets.finance.profile', $asset) }}" class="erp-link">{{ $asset->asset_number }}</a></td>
                                <td>{{ $asset->category?->name }}</td>
                                <td>{{ number_format($asset->acquisition_cost, 2) }}</td>
                                <td>{{ number_format($asset->netBookValue(), 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </x-admin.card>
</x-admin-layout>
