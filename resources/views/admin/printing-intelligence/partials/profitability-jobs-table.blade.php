<div class="overflow-x-auto">
    <table class="erp-table text-sm">
        <thead>
            <tr>
                <th>{{ __('Job') }}</th>
                <th>{{ __('Customer') }}</th>
                <th>{{ __('Revenue') }}</th>
                <th>{{ __('Cost') }}</th>
                <th>{{ __('Profit') }}</th>
                <th>{{ __('Margin') }}</th>
                <th>{{ __('Class') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['job_card_number'] ?? '—' }}</td>
                    <td>{{ $row['customer_name'] ?? '—' }}</td>
                    <td>{{ number_format((float) ($row['revenue'] ?? 0), 2) }}</td>
                    <td>{{ number_format((float) ($row['total_cost'] ?? 0), 2) }}</td>
                    <td>{{ number_format((float) ($row['gross_profit'] ?? 0), 2) }}</td>
                    <td>{{ ($row['gross_margin_percent'] ?? null) !== null ? number_format((float) $row['gross_margin_percent'], 1).'%' : '—' }}</td>
                    <td><span class="erp-badge">{{ ucfirst(str_replace('_', ' ', $row['profitability_class'] ?? 'unknown')) }}</span></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-slate-500 py-6">{{ __('No job profitability snapshots yet.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
