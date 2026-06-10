<div class="overflow-x-auto">
    <table class="erp-table text-sm">
        <thead>
            <tr>
                <th>{{ __('Machine') }}</th>
                <th>{{ __('Jobs') }}</th>
                <th>{{ __('Revenue') }}</th>
                <th>{{ __('Cost') }}</th>
                <th>{{ __('Profit') }}</th>
                <th>{{ __('Margin') }}</th>
                <th>{{ __('Utilization') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['machine_name'] ?? '—' }}</td>
                    <td>{{ $row['jobs_processed'] ?? 0 }}</td>
                    <td>{{ number_format((float) ($row['revenue'] ?? 0), 2) }}</td>
                    <td>{{ number_format((float) ($row['cost'] ?? 0), 2) }}</td>
                    <td>{{ number_format((float) ($row['profit'] ?? 0), 2) }}</td>
                    <td>{{ ($row['margin_percent'] ?? null) !== null ? number_format((float) $row['margin_percent'], 1).'%' : '—' }}</td>
                    <td>{{ ($row['utilization_percent'] ?? null) !== null ? number_format((float) $row['utilization_percent'], 1).'%' : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-slate-500 py-6">{{ __('No machine profitability data yet.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
