<x-admin-layout :title="__('Balance Sheet')">
    <x-admin.page-header :title="__('Balance Sheet')" :description="__('As of :date — posted journals only', ['date' => $report['as_of_date']])" />

    @include('admin.accounting.partials.as-of-toolbar', [
        'action' => route('admin.accounting.reports.balance-sheet'),
        'resetUrl' => route('admin.accounting.reports.balance-sheet'),
        'filters' => $filters,
        'periods' => $periods,
    ])

    <div class="mb-4 grid grid-cols-2 gap-3">
        <x-admin.kpi-widget :label="__('Total assets')" :value="number_format($report['total_assets'], 2)" />
        <x-admin.kpi-widget :label="__('Liabilities + equity')" :value="number_format($report['total_liabilities_and_equity'], 2)" />
    </div>

    @foreach ($report['sections'] as $section)
        <x-admin.card class="mb-4">
            <h3 class="font-semibold mb-3">{{ $section['label'] }} — {{ number_format($section['total'], 2) }}</h3>
            @if (count($section['accounts']) === 0)
                <p class="text-sm text-slate-500">{{ __('No balances in this section.') }}</p>
            @else
                <table class="w-full text-sm">
                    <tbody>
                        @foreach ($section['accounts'] as $account)
                            <tr class="border-t border-erp-border">
                                <td class="py-2 font-mono text-xs">{{ $account['account_code'] }} — {{ $account['account_name'] }}</td>
                                <td class="py-2 text-right font-medium">{{ number_format($account['balance'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </x-admin.card>
    @endforeach

    <p class="text-sm {{ $report['is_balanced'] ? 'text-emerald-600' : 'text-red-600' }}">
        {{ $report['is_balanced'] ? __('Balance sheet balances (assets = liabilities + equity).') : __('Warning: assets do not equal liabilities plus equity.') }}
    </p>
</x-admin-layout>
