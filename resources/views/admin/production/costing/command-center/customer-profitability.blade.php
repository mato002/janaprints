<div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
    <x-admin.card :padding="false">
        <div class="border-b border-erp-border px-4 py-3">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Most Profitable Customers') }}</h2>
            <p class="mt-0.5 text-xs text-slate-500">{{ __('Top 10 by gross profit') }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Customer') }}</th>
                        <th class="text-right">{{ __('Revenue') }}</th>
                        <th class="text-right">{{ __('Cost') }}</th>
                        <th class="text-right">{{ __('Profit') }}</th>
                        <th>{{ __('Margin %') }}</th>
                        <th class="text-right">{{ __('Jobs') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dashboard['top_customers'] as $row)
                        <tr>
                            <td>
                                @if ($row['customer_url'])
                                    <a href="{{ $row['customer_url'] }}" class="font-medium text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ $row['customer_name'] }}</a>
                                @else
                                    {{ $row['customer_name'] }}
                                @endif
                            </td>
                            <td class="text-right tabular-nums">KES {{ number_format($row['revenue'], 0) }}</td>
                            <td class="text-right tabular-nums">KES {{ number_format($row['cost'], 0) }}</td>
                            <td class="text-right tabular-nums text-emerald-700">KES {{ number_format($row['profit'], 0) }}</td>
                            <td><x-admin.status-badge :variant="$row['margin_variant']">{{ number_format($row['margin_percent'], 1) }}%</x-admin.status-badge></td>
                            <td class="text-right tabular-nums">{{ $row['job_count'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">{{ __('No customer profitability data yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>

    <x-admin.card :padding="false">
        <div class="border-b border-erp-border px-4 py-3">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-amber-700">{{ __('Low-Margin / Loss Customers') }}</h2>
            <p class="mt-0.5 text-xs text-slate-500">{{ __('Top 10 requiring account review') }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Customer') }}</th>
                        <th class="text-right">{{ __('Revenue') }}</th>
                        <th class="text-right">{{ __('Cost') }}</th>
                        <th class="text-right">{{ __('Profit/Loss') }}</th>
                        <th>{{ __('Margin %') }}</th>
                        <th class="text-right">{{ __('Jobs') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dashboard['low_margin_customers'] as $row)
                        <tr>
                            <td>
                                @if ($row['customer_url'])
                                    <a href="{{ $row['customer_url'] }}" class="font-medium text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ $row['customer_name'] }}</a>
                                @else
                                    {{ $row['customer_name'] }}
                                @endif
                            </td>
                            <td class="text-right tabular-nums">KES {{ number_format($row['revenue'], 0) }}</td>
                            <td class="text-right tabular-nums">KES {{ number_format($row['cost'], 0) }}</td>
                            <td class="text-right tabular-nums {{ $row['profit'] >= 0 ? 'text-amber-700' : 'text-red-700' }}">KES {{ number_format($row['profit'], 0) }}</td>
                            <td><x-admin.status-badge :variant="$row['margin_variant']">{{ number_format($row['margin_percent'], 1) }}%</x-admin.status-badge></td>
                            <td class="text-right tabular-nums">{{ $row['job_count'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">{{ __('No low-margin or loss customers in this scope.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
</div>
