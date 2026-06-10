<x-admin.card>
    <div class="overflow-x-auto">
        <table class="erp-table text-sm">
            <thead>
                <tr>
                    <th>{{ __('Machine') }}</th>
                    <th>{{ __('Current util.') }}</th>
                    <th>{{ __('Forecast util.') }}</th>
                    <th>{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $row['machine_name'] ?? '—' }}</td>
                        <td>{{ number_format((float) ($row['current_utilization_percent'] ?? 0), 1) }}%</td>
                        <td>{{ ($row['forecast_utilization_percent'] ?? null) !== null ? number_format((float) $row['forecast_utilization_percent'], 1).'%' : '—' }}</td>
                        <td>
                            @if ($row['is_bottleneck'] ?? false)
                                <span class="erp-badge">{{ __('Bottleneck') }}</span>
                            @elseif ($row['is_underutilized'] ?? false)
                                <span class="erp-badge">{{ __('Underutilized') }}</span>
                            @else
                                <span class="erp-badge">{{ __('Normal') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-slate-500 py-6">{{ __('No machine capacity data yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin.card>
