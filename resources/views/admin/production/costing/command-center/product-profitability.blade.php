<x-admin.card :padding="false">
    <div class="border-b border-erp-border px-4 py-3">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Product / Service Profitability') }}</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th>{{ __('Product / Service Type') }}</th>
                    <th class="text-right">{{ __('Revenue') }}</th>
                    <th class="text-right">{{ __('Cost') }}</th>
                    <th class="text-right">{{ __('Profit') }}</th>
                    <th>{{ __('Margin %') }}</th>
                    <th class="text-right">{{ __('Jobs Count') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dashboard['product_profitability'] as $row)
                    <tr>
                        <td class="font-medium">{{ $row['label'] }}</td>
                        <td class="text-right tabular-nums">KES {{ number_format($row['revenue'], 0) }}</td>
                        <td class="text-right tabular-nums">KES {{ number_format($row['cost'], 0) }}</td>
                        <td class="text-right tabular-nums {{ $row['profit'] >= 0 ? 'text-emerald-700' : 'text-red-700' }}">KES {{ number_format($row['profit'], 0) }}</td>
                        <td class="min-w-[10rem]">
                            <div class="flex flex-col gap-1">
                                <x-admin.status-badge :variant="$row['margin_variant']">{{ number_format($row['margin_percent'], 1) }}%</x-admin.status-badge>
                                <x-admin.margin-bar :percent="$row['margin_percent']" :variant="$row['margin_variant']" />
                            </div>
                        </td>
                        <td class="text-right tabular-nums">{{ $row['job_count'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500">{{ __('No product profitability data in this scope.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin.card>
