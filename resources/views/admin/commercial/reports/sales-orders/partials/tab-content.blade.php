@props(['tab_data', 'active_tab', 'tabs'])

@if (($tab_data['type'] ?? '') === 'placeholder')
    <x-admin.card>
        <x-admin.empty-state icon="clipboard-list" :title="__('Sales Order Reports')" :description="$tab_data['message'] ?? __('No data available.')" />
    </x-admin.card>
@elseif (($tab_data['type'] ?? '') === 'summary')
    <x-admin.card class="mb-6">
        <h2 class="mb-4 text-sm font-semibold text-erp-primary">{{ __('Sales Order Summary') }}</h2>
        <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-5">
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Total Orders') }}</p>
                <p class="text-xl font-bold tabular-nums text-erp-primary">{{ number_format($tab_data['metrics']['total_orders'] ?? 0) }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Open Orders') }}</p>
                <p class="text-xl font-bold tabular-nums text-erp-primary">{{ number_format($tab_data['metrics']['open_orders'] ?? 0) }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Completed Orders') }}</p>
                <p class="text-xl font-bold tabular-nums text-erp-primary">{{ number_format($tab_data['metrics']['completed_orders'] ?? 0) }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Total Value') }}</p>
                <p class="text-xl font-bold tabular-nums text-erp-primary">{{ 'KES '.number_format($tab_data['metrics']['total_value'] ?? 0, 0) }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Completion Rate') }}</p>
                <p class="text-xl font-bold tabular-nums text-erp-primary">{{ ($tab_data['metrics']['completion_rate'] ?? 0).'%' }}</p>
            </div>
        </div>

        @include('admin.commercial.reports.sales-orders.partials.simple-table', [
            'title' => __('Status Breakdown'),
            'columns' => [__('Status'), __('Orders'), __('Value'), __('Average')],
            'rows' => collect($tab_data['status_breakdown'] ?? [])->map(fn ($row) => array_values($row))->all(),
        ])
    </x-admin.card>
@elseif (($tab_data['type'] ?? '') === 'table')
    <x-admin.card>
        @php
            $rows = $tab_data['rows'] ?? [];
            $tableRows = $rows instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
                ? collect($rows->items())->map(fn ($row) => array_values((array) $row))->all()
                : collect($rows)->map(fn ($row) => array_values((array) $row))->all();
        @endphp

        @include('admin.commercial.reports.sales-orders.partials.simple-table', [
            'title' => collect($tabs)->firstWhere('key', $active_tab)['label'] ?? __('Report'),
            'columns' => $tab_data['columns'] ?? [],
            'rows' => $tableRows,
        ])

        @if ($rows instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
            <div class="mt-4 border-t border-erp-border pt-4">
                {{ $rows->withQueryString()->links() }}
            </div>
        @endif
    </x-admin.card>
@endif
