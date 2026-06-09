@if ($tabData['restricted'] ?? false)
    <x-admin.empty-state icon="lock-closed" :title="__('Costing access required')" />
@else
    @php($sheet = $tabData['cost_sheet'])
    @php($tone = $tabData['variance_tone'] ?? 'green')
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4 mb-4">
        <x-admin.kpi-widget :label="__('Revenue')" :value="number_format($sheet->revenue, 2)" icon="cash" />
        <x-admin.kpi-widget :label="__('Total cost')" :value="number_format($sheet->total_cost, 2)" icon="receipt-tax" />
        <x-admin.kpi-widget :label="__('Gross profit')" :value="number_format($sheet->gross_profit, 2)" icon="trending-up" />
        <x-admin.kpi-widget :label="__('Margin')" :value="$sheet->gross_margin_percent.'%'" icon="chart-pie" />
    </div>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-6 mb-4 text-sm">
        @foreach ([
            'material_cost' => __('Material'),
            'labor_cost' => __('Labour'),
            'machine_cost' => __('Machine'),
            'outsourced_cost' => __('Outsourcing'),
            'overhead_cost' => __('Overhead'),
        ] as $field => $label)
            <div class="rounded-lg border border-erp-border px-3 py-2">
                <div class="text-xs text-slate-500">{{ $label }}</div>
                <div class="font-semibold tabular-nums">{{ number_format($sheet->{$field}, 2) }}</div>
            </div>
        @endforeach
    </div>

    <x-admin.card class="mb-4">
        <h3 class="text-sm font-semibold mb-3">{{ __('Estimated vs actual') }}</h3>
        <table class="erp-table erp-table--grid text-sm">
            <thead>
                <tr>
                    <th></th>
                    <th>{{ __('Estimated') }}</th>
                    <th>{{ __('Actual') }}</th>
                    <th>{{ __('Variance') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ([
                    ['Material', 'estimated_material_cost', 'material_cost'],
                    ['Labour', 'estimated_labor_cost', 'labor_cost'],
                    ['Machine', 'estimated_machine_cost', 'machine_cost'],
                    ['Total', 'estimated_total_cost', 'total_cost'],
                ] as [$label, $est, $act])
                    <tr>
                        <td>{{ __($label) }}</td>
                        <td>{{ number_format($sheet->{$est}, 2) }}</td>
                        <td>{{ number_format($sheet->{$act}, 2) }}</td>
                        <td class="{{ $tone === 'red' ? 'text-red-600' : ($tone === 'yellow' ? 'text-amber-600' : 'text-emerald-600') }}">
                            {{ number_format((float) $sheet->{$act} - (float) $sheet->{$est}, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="mt-2 text-xs text-slate-500">
            {{ __('Variance') }}: {{ number_format($sheet->variance_amount, 2) }} ({{ $sheet->variance_percent }}%)
            · <x-admin.enum-status-badge :status="$sheet->cost_review_status?->value ?? $sheet->cost_review_status" />
        </p>
    </x-admin.card>

    <div class="flex flex-wrap gap-2 mb-4">
        <a href="{{ $tabData['detail_url'] }}" class="erp-btn-secondary">{{ __('Full cost sheet') }}</a>
        @if ($tabData['can_calculate'] && ! $sheet->is_frozen)
            <form method="POST" action="{{ route('admin.production.job-cards.costing.calculate', $jobCard) }}">
                @csrf
                <button class="erp-btn-secondary">{{ __('Recalculate') }}</button>
            </form>
        @endif
        @if ($tabData['can_approve'] && ($sheet->cost_review_status?->value ?? $sheet->cost_review_status) === 'requires_review')
            <form method="POST" action="{{ route('admin.production.job-cards.costing.approve', $jobCard) }}">
                @csrf
                <button class="erp-btn-primary">{{ __('Approve cost review') }}</button>
            </form>
        @endif
    </div>
@endif
