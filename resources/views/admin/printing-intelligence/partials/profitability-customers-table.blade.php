<div class="overflow-x-auto">
    <table class="erp-table text-sm">
        <thead>
            <tr>
                <th>{{ __('Customer') }}</th>
                <th>{{ __('Revenue') }}</th>
                <th>{{ __('Profit') }}</th>
                <th>{{ __('Margin') }}</th>
                <th>{{ __('Profit / job') }}</th>
                <th>{{ __('Jobs') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['customer_name'] ?? '—' }}</td>
                    <td>{{ number_format((float) ($row['revenue'] ?? 0), 2) }}</td>
                    <td>{{ number_format((float) ($row['profit'] ?? 0), 2) }}</td>
                    <td>{{ ($row['margin_percent'] ?? null) !== null ? number_format((float) $row['margin_percent'], 1).'%' : '—' }}</td>
                    <td>{{ ($row['profit_per_job'] ?? null) !== null ? number_format((float) $row['profit_per_job'], 2) : '—' }}</td>
                    <td>{{ $row['jobs_completed'] ?? 0 }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-slate-500 py-6">{{ __('No customer profitability data yet.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
