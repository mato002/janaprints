<x-admin.card>
    <div class="overflow-x-auto">
        <table class="erp-table text-sm">
            <thead>
                <tr>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Growth') }}</th>
                    <th>{{ __('Revenue share') }}</th>
                    <th>{{ __('Profit share') }}</th>
                    <th>{{ __('Trend') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $row['customer_name'] ?? '—' }}</td>
                        <td>{{ ($row['growth_percent'] ?? null) !== null ? number_format((float) $row['growth_percent'], 1).'%' : '—' }}</td>
                        <td>{{ ($row['revenue_contribution_percent'] ?? null) !== null ? number_format((float) $row['revenue_contribution_percent'], 1).'%' : '—' }}</td>
                        <td>{{ ($row['profit_contribution_percent'] ?? null) !== null ? number_format((float) $row['profit_contribution_percent'], 1).'%' : '—' }}</td>
                        <td><span class="erp-badge">{{ ucfirst($row['trend'] ?? 'stable') }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-slate-500 py-6">{{ __('No customer trend data yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin.card>
