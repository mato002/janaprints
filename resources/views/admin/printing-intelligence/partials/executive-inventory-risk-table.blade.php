<x-admin.card>
    <div class="overflow-x-auto">
        <table class="erp-table text-sm">
            <thead>
                <tr>
                    <th>{{ __('Category') }}</th>
                    <th>{{ __('Items tracked') }}</th>
                    <th>{{ __('Days to risk') }}</th>
                    <th>{{ __('Risk class') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $row['label'] ?? '—' }}</td>
                        <td>{{ $row['items_tracked'] ?? 0 }}</td>
                        <td>{{ ($row['days_to_risk'] ?? null) !== null ? number_format((float) $row['days_to_risk'], 1) : '—' }}</td>
                        <td><span class="erp-badge">{{ ucfirst($row['risk_class'] ?? 'unknown') }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-slate-500 py-6">{{ __('No inventory velocity data available.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin.card>
