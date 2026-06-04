<x-admin.card :padding="false">
    <div class="border-b border-erp-border px-4 py-3">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Top Profitable Jobs') }}</h2>
        <p class="mt-0.5 text-xs text-slate-500">{{ __('Top 10 by gross profit in selected scope') }}</p>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th>{{ __('Rank') }}</th>
                    <th>{{ __('Job Card') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th class="text-right">{{ __('Revenue') }}</th>
                    <th class="text-right">{{ __('Cost') }}</th>
                    <th class="text-right">{{ __('Profit') }}</th>
                    <th>{{ __('Margin %') }}</th>
                    <th class="erp-table-actions-col">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dashboard['top_profitable'] as $row)
                    <tr>
                        <td class="tabular-nums font-medium">{{ $row['rank'] }}</td>
                        <td class="font-mono">{{ $row['job_card_number'] }}</td>
                        <td>{{ $row['customer_name'] }}</td>
                        <td class="text-right tabular-nums">KES {{ number_format($row['revenue'], 0) }}</td>
                        <td class="text-right tabular-nums">KES {{ number_format($row['cost'], 0) }}</td>
                        <td class="text-right tabular-nums font-medium text-emerald-700">KES {{ number_format($row['profit'], 0) }}</td>
                        <td>
                            <div class="flex flex-col gap-1">
                                <x-admin.status-badge :variant="$row['margin_variant']">{{ number_format($row['margin_percent'], 1) }}%</x-admin.status-badge>
                                <x-admin.margin-bar :percent="$row['margin_percent']" :variant="$row['margin_variant']" />
                            </div>
                        </td>
                        <td class="erp-table-actions-col">
                            @if ($row['job_360_url'])
                                <a href="{{ $row['job_360_url'] }}" class="text-xs font-medium text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ __('Open Job 360') }}</a>
                            @elseif ($row['costing_url'])
                                <a href="{{ $row['costing_url'] }}" class="text-xs font-medium text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ __('View costing') }}</a>
                            @else
                                <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-500">{{ __('No profitable jobs found yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin.card>
