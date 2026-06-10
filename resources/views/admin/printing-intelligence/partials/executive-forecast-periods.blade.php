<x-admin.card>
    <div class="overflow-x-auto mb-4">
        <table class="erp-table text-sm">
            <thead>
                <tr>
                    <th>{{ __('Period') }}</th>
                    <th>{{ __('Forecast') }}</th>
                    <th>{{ __('Range') }}</th>
                    <th>{{ __('Confidence') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach (['next_month' => __('Next month'), 'next_quarter' => __('Next quarter'), 'next_year' => __('Next year')] as $key => $label)
                    @php $row = $data[$key] ?? null; @endphp
                    @if ($row)
                        <tr>
                            <td>{{ $label }}</td>
                            <td>{{ number_format((float) ($row['forecast_value'] ?? 0), 2) }}</td>
                            <td>{{ number_format((float) ($row['lower_bound'] ?? 0), 2) }} – {{ number_format((float) ($row['upper_bound'] ?? 0), 2) }}</td>
                            <td>{{ ($row['confidence_score'] ?? null) !== null ? number_format((float) $row['confidence_score'], 1).'%' : '—' }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</x-admin.card>
