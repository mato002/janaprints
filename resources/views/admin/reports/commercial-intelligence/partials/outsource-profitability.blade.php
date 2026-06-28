<div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
    <x-admin.card>
        <div class="border-b border-erp-border px-4 py-3"><h3 class="text-sm font-semibold">{{ __('Outsourced Jobs') }}</h3></div>
        <div class="overflow-x-auto p-4">
            <table class="erp-table w-full text-sm">
                <thead><tr><th>{{ __('Job') }}</th><th>{{ __('Vendor') }}</th><th>{{ __('Revenue') }}</th><th>{{ __('Vendor cost') }}</th><th>{{ __('Margin') }}</th></tr></thead>
                <tbody>
                    @forelse ($data['jobs'] ?? [] as $row)
                        <tr>
                            <td>{{ $row['job_number'] ?? '—' }}</td>
                            <td>{{ $row['vendor_name'] ?? '—' }}</td>
                            <td class="font-mono">{{ number_format($row['customer_revenue'] ?? 0, 2) }}</td>
                            <td class="font-mono">{{ number_format($row['vendor_cost'] ?? 0, 2) }}</td>
                            <td>{{ number_format($row['estimated_margin_percent'] ?? 0, 1) }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-slate-500">{{ __('No outsourced jobs in scope.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
    <x-admin.card>
        <div class="border-b border-erp-border px-4 py-3"><h3 class="text-sm font-semibold">{{ __('By Vendor') }}</h3></div>
        <div class="overflow-x-auto p-4">
            <table class="erp-table w-full text-sm">
                <thead><tr><th>{{ __('Vendor') }}</th><th>{{ __('Jobs') }}</th><th>{{ __('Revenue') }}</th><th>{{ __('Cost') }}</th><th>{{ __('Profit') }}</th></tr></thead>
                <tbody>
                    @forelse ($data['vendors'] ?? [] as $row)
                        <tr>
                            <td>{{ $row['vendor_name'] ?? '—' }}</td>
                            <td>{{ $row['job_count'] ?? 0 }}</td>
                            <td class="font-mono">{{ number_format($row['customer_revenue'] ?? 0, 2) }}</td>
                            <td class="font-mono">{{ number_format($row['vendor_cost'] ?? 0, 2) }}</td>
                            <td class="font-mono">{{ number_format($row['estimated_profit'] ?? 0, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-slate-500">{{ __('No vendor data.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
</div>
