<x-admin.card>
    <div class="overflow-x-auto p-4">
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th>{{ __('Branch') }}</th>
                    <th>{{ __('Revenue') }}</th>
                    <th>{{ __('Collections') }}</th>
                    <th>{{ __('Production cost') }}</th>
                    <th>{{ __('Estimated profit') }}</th>
                    <th>{{ __('Margin') }}</th>
                    <th>{{ __('Jobs') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($branches as $row)
                    <tr>
                        <td>{{ $row['branch_name'] ?? '—' }}</td>
                        <td class="font-mono">{{ number_format($row['revenue'] ?? 0, 2) }}</td>
                        <td class="font-mono">{{ number_format($row['collections'] ?? 0, 2) }}</td>
                        <td class="font-mono">{{ number_format($row['production_cost'] ?? 0, 2) }}</td>
                        <td class="font-mono">{{ number_format($row['estimated_profit'] ?? 0, 2) }}</td>
                        <td>{{ number_format($row['margin_percent'] ?? 0, 1) }}%</td>
                        <td>{{ $row['jobs_count'] ?? 0 }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-slate-500">{{ __('No branch profitability data in this scope.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin.card>
