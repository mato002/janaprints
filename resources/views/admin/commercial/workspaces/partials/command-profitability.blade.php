@php($profitability = $profitability ?? [])
@if ($profitability['available'] ?? false)
    <section class="mb-6">
        <div class="mb-3 flex items-center justify-between gap-3">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Profitability Intelligence') }}</h2>
            <a href="{{ $profitability['report_url'] }}" class="text-xs font-medium text-erp-accent">{{ __('Full report') }}</a>
        </div>
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            @foreach ([
                ['title' => __('Top customers'), 'rows' => $profitability['top_customers'] ?? [], 'name' => 'customer_name'],
                ['title' => __('Top products'), 'rows' => $profitability['top_products'] ?? [], 'name' => 'label'],
                ['title' => __('Top salespersons'), 'rows' => $profitability['top_salespersons'] ?? [], 'name' => 'salesperson_name'],
            ] as $panel)
                <x-admin.card :padding="false">
                    <div class="border-b border-erp-border px-4 py-3 text-sm font-semibold">{{ $panel['title'] }}</div>
                    <table class="erp-table erp-table--grid text-sm">
                        <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Profit') }}</th><th>{{ __('Margin') }}</th></tr></thead>
                        <tbody>
                            @forelse ($panel['rows'] as $row)
                                <tr>
                                    <td>{{ $row[$panel['name']] }}</td>
                                    <td>{{ number_format($row['profit'], 2) }}</td>
                                    <td>{{ $row['margin_percent'] }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-4 text-center text-slate-500">{{ __('No data yet') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </x-admin.card>
            @endforeach
        </div>
    </section>
@endif
