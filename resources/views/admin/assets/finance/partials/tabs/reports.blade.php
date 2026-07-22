@php
    $reportsAction = $hubUrl . '?' . http_build_query(array_merge(request()->except('page'), ['tab' => 'reports']));
    $reportsReset = $hubUrl . '?tab=reports';
@endphp

<x-admin.card :padding="false" class="mb-4">
    <x-admin.index-toolbar :action="$reportsAction" :reset-url="$reportsReset">
        <input type="hidden" name="tab" value="reports">
        <select name="report" class="erp-toolbar-select min-w-[12rem]" aria-label="{{ __('Report') }}">
            <option value="register" @selected($report === 'register')>{{ __('Asset Register') }}</option>
            <option value="valuation" @selected($report === 'valuation')>{{ __('Asset Valuation') }}</option>
            <option value="depreciation_schedule" @selected($report === 'depreciation_schedule')>{{ __('Depreciation Report') }}</option>
            <option value="maintenance" @selected($report === 'maintenance')>{{ __('Maintenance Report') }}</option>
            <option value="custody" @selected($report === 'custody')>{{ __('Custody Report') }}</option>
            <option value="warranty_expiry" @selected($report === 'warranty_expiry')>{{ __('Warranty Expiry') }}</option>
            <option value="replacement" @selected($report === 'replacement')>{{ __('Replacement Candidates') }}</option>
            <option value="fully_depreciated" @selected($report === 'fully_depreciated')>{{ __('Fully Depreciated') }}</option>
            <option value="near_end_of_life" @selected($report === 'near_end_of_life')>{{ __('Near End of Life') }}</option>
        </select>
        @if (in_array($report, ['depreciation_schedule', 'maintenance'], true))
            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('From date') }}">
            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('To date') }}">
        @endif
    </x-admin.index-toolbar>
</x-admin.card>

<x-admin.card>
    <div class="overflow-x-auto">
        @if ($report === 'valuation')
            <table class="erp-table w-full text-sm">
                <thead><tr><th>{{ __('Asset') }}</th><th>{{ __('Cost') }}</th><th>{{ __('NBV') }}</th><th>{{ __('Monthly Depr.') }}</th></tr></thead>
                <tbody>
                    @forelse ($data as $row)
                        <tr>
                            <td>{{ $row['asset']->asset_number }}</td>
                            <td>{{ number_format($row['profile']['acquisition_cost'], 2) }}</td>
                            <td>{{ number_format($row['profile']['net_book_value'], 2) }}</td>
                            <td>{{ number_format($row['profile']['monthly_depreciation'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-slate-500">{{ __('No assets match the selected filters.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        @elseif ($report === 'depreciation_schedule')
            <table class="erp-table w-full text-sm">
                <thead><tr><th>{{ __('Period') }}</th><th>{{ __('Asset') }}</th><th>{{ __('Amount') }}</th><th>{{ __('NBV After') }}</th></tr></thead>
                <tbody>
                    @forelse ($data as $entry)
                        <tr>
                            <td>{{ $entry->period_date?->format('Y-m') }}</td>
                            <td>{{ $entry->asset?->asset_number }}</td>
                            <td>{{ number_format($entry->depreciation_amount, 2) }}</td>
                            <td>{{ number_format($entry->net_book_value_after, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-slate-500">{{ __('No depreciation entries match the selected filters.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        @elseif ($report === 'maintenance')
            <table class="erp-table w-full text-sm">
                <thead><tr><th>{{ __('Work Order') }}</th><th>{{ __('Asset') }}</th><th>{{ __('Type') }}</th><th>{{ __('Status') }}</th><th>{{ __('Opened') }}</th></tr></thead>
                <tbody>
                    @forelse ($data as $order)
                        <tr>
                            <td>{{ $order->work_order_no }}</td>
                            <td>{{ $order->asset?->asset_number }}</td>
                            <td>{{ $order->maintenance_type?->label() ?? $order->maintenance_type }}</td>
                            <td>{{ $order->status?->label() ?? $order->status }}</td>
                            <td>{{ $order->opened_at?->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-slate-500">{{ __('No maintenance work orders match the selected filters.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        @elseif ($report === 'custody')
            <table class="erp-table w-full text-sm">
                <thead><tr><th>{{ __('Asset') }}</th><th>{{ __('Branch') }}</th><th>{{ __('Custodian') }}</th><th>{{ __('Condition') }}</th></tr></thead>
                <tbody>
                    @forelse ($data as $asset)
                        <tr>
                            <td>{{ $asset->asset_number }}</td>
                            <td>{{ $asset->branch?->name }}</td>
                            <td>{{ $asset->assignedUser?->name ?? $asset->assignedEmployee?->full_name ?? '—' }}</td>
                            <td>{{ $asset->current_condition?->label() ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-slate-500">{{ __('No assets match the selected filters.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        @elseif ($report === 'warranty_expiry')
            <table class="erp-table w-full text-sm">
                <thead><tr><th>{{ __('Asset') }}</th><th>{{ __('Vendor') }}</th><th>{{ __('Warranty End') }}</th><th>{{ __('Reference') }}</th></tr></thead>
                <tbody>
                    @forelse ($data as $warranty)
                        <tr>
                            <td>{{ $warranty->asset?->asset_number }}</td>
                            <td>{{ $warranty->vendor?->vendor_name ?? '—' }}</td>
                            <td>{{ $warranty->warranty_end?->format('Y-m-d') }}</td>
                            <td>{{ $warranty->reference_number ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-slate-500">{{ __('No warranties expiring in the next 90 days.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        @elseif ($report === 'replacement')
            <table class="erp-table w-full text-sm">
                <thead><tr><th>{{ __('Asset') }}</th><th>{{ __('Priority') }}</th><th>{{ __('Health') }}</th><th>{{ __('Reasons') }}</th></tr></thead>
                <tbody>
                    @forelse ($data as $row)
                        <tr>
                            <td>{{ $row['asset']->asset_number }} — {{ $row['asset']->asset_name }}</td>
                            <td><x-admin.status-badge :variant="$row['priority'] === 'high' ? 'danger' : ($row['priority'] === 'medium' ? 'warning' : 'neutral')">{{ ucfirst($row['priority']) }}</x-admin.status-badge></td>
                            <td>{{ $row['health_score'] ?? '—' }}</td>
                            <td>{{ implode(', ', $row['reasons']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-slate-500">{{ __('No replacement candidates identified.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        @else
            <table class="erp-table w-full text-sm">
                <thead><tr><th>{{ __('Asset') }}</th><th>{{ __('Category') }}</th><th>{{ __('Cost') }}</th><th>{{ __('NBV') }}</th></tr></thead>
                <tbody>
                    @forelse ($data as $asset)
                        <tr>
                            <td><a href="{{ route('admin.assets.finance.profile', $asset) }}" class="erp-link">{{ $asset->asset_number }}</a></td>
                            <td>{{ $asset->category?->name }}</td>
                            <td>{{ number_format($asset->acquisition_cost, 2) }}</td>
                            <td>{{ number_format($asset->netBookValue(), 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-slate-500">{{ __('No assets match the selected filters.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    </div>
</x-admin.card>
