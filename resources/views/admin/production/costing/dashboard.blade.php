<x-admin-layout :title="__('Job Profitability')" :breadcrumbs="[['label' => __('Production'), 'url' => route('admin.production.dashboard')], ['label' => __('Costing')]]">
    <x-admin.page-header :title="__('Production Profitability')" />
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold">{{ __('Top profitable jobs') }}</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($stats['top_profitable'] as $sheet)
                    <li>
                        <a href="{{ route('admin.production.job-cards.costing', $sheet->jobCard) }}" class="erp-link">
                            {{ $sheet->jobCard->job_card_number }}
                        </a>
                        — {{ number_format($sheet->gross_profit, 2) }}
                    </li>
                @empty
                    <li class="text-slate-500">{{ __('No data yet.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>
        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold">{{ __('Loss-making jobs') }}</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($stats['loss_making'] as $sheet)
                    <li>
                        <a href="{{ route('admin.production.job-cards.costing', $sheet->jobCard) }}" class="erp-link">
                            {{ $sheet->jobCard->job_card_number }}
                        </a>
                        — {{ number_format($sheet->gross_profit, 2) }}
                    </li>
                @empty
                    <li class="text-slate-500">{{ __('No losses recorded.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>
    </div>
    <x-admin.card class="mt-6">
        <h3 class="mb-3 text-sm font-semibold">{{ __('Customer profitability') }}</h3>
        <table class="erp-table text-sm">
            <thead><tr><th>{{ __('Customer') }}</th><th>{{ __('Revenue') }}</th><th>{{ __('Cost') }}</th><th>{{ __('Profit') }}</th><th>{{ __('Margin %') }}</th></tr></thead>
            <tbody>
                @foreach ($stats['customer_profitability'] as $row)
                    <tr>
                        <td>{{ $row['customer_name'] }}</td>
                        <td>{{ number_format($row['revenue'], 2) }}</td>
                        <td>{{ number_format($row['cost'], 2) }}</td>
                        <td>{{ number_format($row['profit'], 2) }}</td>
                        <td>{{ $row['margin_percent'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>
</x-admin-layout>
