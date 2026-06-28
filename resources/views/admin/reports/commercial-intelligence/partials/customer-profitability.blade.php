<div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
    <x-admin.card>
        <div class="border-b border-erp-border px-4 py-3"><h3 class="text-sm font-semibold">{{ __('Top Customers by Profit') }}</h3></div>
        <div class="overflow-x-auto p-4">
            <table class="erp-table w-full text-sm">
                <thead><tr><th>{{ __('Customer') }}</th><th>{{ __('Revenue') }}</th><th>{{ __('Cost') }}</th><th>{{ __('Profit') }}</th><th>{{ __('Margin') }}</th></tr></thead>
                <tbody>
                    @forelse ($data['top_customers'] ?? [] as $row)
                        <tr>
                            <td>{{ $row['customer_name'] ?? '—' }}</td>
                            <td class="font-mono">{{ number_format($row['revenue'] ?? 0, 2) }}</td>
                            <td class="font-mono">{{ number_format($row['total_cost'] ?? 0, 2) }}</td>
                            <td class="font-mono">{{ number_format($row['profit'] ?? 0, 2) }}</td>
                            <td>{{ number_format($row['margin_percent'] ?? 0, 1) }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-slate-500">{{ __('No data.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
    <x-admin.card>
        <div class="border-b border-erp-border px-4 py-3"><h3 class="text-sm font-semibold">{{ __('Most Active Customers') }}</h3></div>
        <div class="overflow-x-auto p-4">
            <table class="erp-table w-full text-sm">
                <thead><tr><th>{{ __('Customer') }}</th><th>{{ __('Orders') }}</th><th>{{ __('Revenue') }}</th></tr></thead>
                <tbody>
                    @forelse ($data['most_active'] ?? [] as $row)
                        <tr>
                            <td>{{ $row['customer_name'] ?? '—' }}</td>
                            <td>{{ $row['order_count'] ?? 0 }}</td>
                            <td class="font-mono">{{ number_format($row['revenue'] ?? 0, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-6 text-center text-slate-500">{{ __('No data.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
</div>
