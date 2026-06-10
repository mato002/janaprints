<x-admin.card>
    <div class="overflow-x-auto">
        <table class="erp-table text-sm">
            <thead>
                <tr>
                    <th>{{ __('Product') }}</th>
                    <th>{{ __('Recent jobs') }}</th>
                    <th>{{ __('Forecast jobs') }}</th>
                    <th>{{ __('Growth') }}</th>
                    <th>{{ __('Trend') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $row['product_label'] ?? '—' }}</td>
                        <td>{{ $row['recent_job_count'] ?? 0 }}</td>
                        <td>{{ ($row['forecast_job_count'] ?? null) !== null ? number_format((float) $row['forecast_job_count'], 1) : '—' }}</td>
                        <td>{{ ($row['growth_percent'] ?? null) !== null ? number_format((float) $row['growth_percent'], 1).'%' : '—' }}</td>
                        <td><span class="erp-badge">{{ ucfirst($row['trend'] ?? 'stable') }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-slate-500 py-6">{{ __('No demand forecast data yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin.card>
