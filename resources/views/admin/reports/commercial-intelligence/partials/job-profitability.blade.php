<x-admin.card>
    <div class="overflow-x-auto">
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th>{{ __('Job') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Revenue') }}</th>
                    <th>{{ __('Material') }}</th>
                    <th>{{ __('Waste') }}</th>
                    <th>{{ __('Outsource') }}</th>
                    <th>{{ __('Profit') }}</th>
                    <th>{{ __('Margin') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jobs ?? [] as $row)
                    <tr>
                        <td>{{ $row['job_number'] ?? '—' }}</td>
                        <td>{{ $row['customer_name'] ?? '—' }}</td>
                        <td class="font-mono">{{ number_format($row['revenue'] ?? 0, 2) }}</td>
                        <td class="font-mono">{{ number_format($row['material_cost'] ?? 0, 2) }}</td>
                        <td class="font-mono">{{ number_format($row['wastage_cost'] ?? 0, 2) }}</td>
                        <td class="font-mono">{{ number_format($row['outsource_cost'] ?? 0, 2) }}</td>
                        <td class="font-mono">{{ number_format($row['estimated_profit'] ?? 0, 2) }}</td>
                        <td>{{ number_format($row['estimated_margin_percent'] ?? 0, 1) }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-8 text-center text-slate-500">{{ __('No completed job profitability data in this scope.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($jobs instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
        <div class="border-t border-erp-border px-4 py-3">{{ $jobs->links() }}</div>
    @endif
</x-admin.card>
