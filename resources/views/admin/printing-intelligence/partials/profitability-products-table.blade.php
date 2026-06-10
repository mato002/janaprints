<div class="overflow-x-auto">
    <table class="erp-table text-sm">
        <thead>
            <tr>
                <th>{{ __('Product') }}</th>
                <th>{{ __('Revenue') }}</th>
                <th>{{ __('Cost') }}</th>
                <th>{{ __('Profit') }}</th>
                <th>{{ __('Margin') }}</th>
                <th>{{ __('Jobs') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['product_label'] ?? '—' }}</td>
                    <td>{{ number_format((float) ($row['revenue'] ?? 0), 2) }}</td>
                    <td>{{ number_format((float) ($row['cost'] ?? 0), 2) }}</td>
                    <td>{{ number_format((float) ($row['profit'] ?? 0), 2) }}</td>
                    <td>{{ ($row['margin_percent'] ?? null) !== null ? number_format((float) $row['margin_percent'], 1).'%' : '—' }}</td>
                    <td>{{ $row['job_count'] ?? 0 }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-slate-500 py-6">{{ __('No product profitability data yet.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
