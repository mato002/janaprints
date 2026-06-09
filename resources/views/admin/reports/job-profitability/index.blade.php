<x-admin-layout :title="__('Job Profitability')" :breadcrumbs="[['label' => __('Reports & Intelligence'), 'url' => route('admin.reports.executive')], ['label' => __('Job Profitability')]]">
    <x-admin.page-header :title="__('Job Profitability')" :description="__('Profitability intelligence from production job costing truth.')" />

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="url()->current()" :reset-url="url()->current()">
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('From') }}">
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('To') }}">
        </x-admin.index-toolbar>
    </x-admin.card>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-admin.card>
            <h2 class="text-sm font-semibold mb-3">{{ __('Top 20 most profitable jobs') }}</h2>
            <table class="erp-table erp-table--grid text-sm">
                <thead><tr><th>{{ __('Job') }}</th><th>{{ __('Revenue') }}</th><th>{{ __('Cost') }}</th><th>{{ __('Profit') }}</th><th>{{ __('Margin') }}</th></tr></thead>
                <tbody>
                    @forelse ($topProfitable as $sheet)
                        <tr>
                            <td><a href="{{ route('admin.production.job-cards.costing', $sheet->jobCard) }}">{{ $sheet->jobCard?->job_card_number }}</a></td>
                            <td>{{ number_format($sheet->revenue, 2) }}</td>
                            <td>{{ number_format($sheet->total_cost, 2) }}</td>
                            <td>{{ number_format($sheet->gross_profit, 2) }}</td>
                            <td>{{ $sheet->gross_margin_percent }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">{{ __('No data') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-admin.card>

        <x-admin.card>
            <h2 class="text-sm font-semibold mb-3">{{ __('Top 20 least profitable jobs') }}</h2>
            <table class="erp-table erp-table--grid text-sm">
                <thead><tr><th>{{ __('Job') }}</th><th>{{ __('Revenue') }}</th><th>{{ __('Cost') }}</th><th>{{ __('Profit') }}</th><th>{{ __('Margin') }}</th></tr></thead>
                <tbody>
                    @forelse ($leastProfitable as $sheet)
                        <tr>
                            <td><a href="{{ route('admin.production.job-cards.costing', $sheet->jobCard) }}">{{ $sheet->jobCard?->job_card_number }}</a></td>
                            <td>{{ number_format($sheet->revenue, 2) }}</td>
                            <td>{{ number_format($sheet->total_cost, 2) }}</td>
                            <td>{{ number_format($sheet->gross_profit, 2) }}</td>
                            <td>{{ $sheet->gross_margin_percent }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">{{ __('No data') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-admin.card>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        @foreach ([
            ['title' => __('Customer profitability'), 'rows' => $customerProfitability, 'name' => 'customer_name'],
            ['title' => __('Product profitability'), 'rows' => $productProfitability, 'name' => 'label'],
            ['title' => __('Salesperson profitability'), 'rows' => $salespersonProfitability, 'name' => 'salesperson_name'],
        ] as $panel)
            <x-admin.card>
                <h2 class="text-sm font-semibold mb-3">{{ $panel['title'] }}</h2>
                <table class="erp-table erp-table--grid text-sm">
                    <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Revenue') }}</th><th>{{ __('Profit') }}</th><th>{{ __('Margin') }}</th></tr></thead>
                    <tbody>
                        @foreach ($panel['rows'] as $row)
                            <tr>
                                <td>{{ $row[$panel['name']] }}</td>
                                <td>{{ number_format($row['revenue'], 2) }}</td>
                                <td>{{ number_format($row['profit'], 2) }}</td>
                                <td>{{ $row['margin_percent'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-admin.card>
        @endforeach
    </div>
</x-admin-layout>
