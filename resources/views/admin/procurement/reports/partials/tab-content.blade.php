@props(['tab_data', 'active_tab', 'tabs', 'filters'])

@if (($tab_data['type'] ?? '') === 'placeholder')
    <x-admin.card>
        <x-admin.empty-state icon="truck" :title="__('Procurement Reports')" :description="$tab_data['message'] ?? __('No data available.')" />
    </x-admin.card>
@elseif (($tab_data['type'] ?? '') === 'summary')
    <x-admin.card class="mb-6">
        <h2 class="mb-4 text-sm font-semibold text-erp-primary">{{ __('Purchase Summary') }}</h2>
        <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Orders') }}</p>
                <p class="text-xl font-bold tabular-nums text-erp-primary">{{ number_format($tab_data['metrics']['orders'] ?? 0) }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Spend') }}</p>
                <p class="text-xl font-bold tabular-nums text-erp-primary">{{ 'KES '.number_format($tab_data['metrics']['spend'] ?? 0, 0) }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Average Order Value') }}</p>
                <p class="text-xl font-bold tabular-nums text-erp-primary">{{ 'KES '.number_format($tab_data['metrics']['average_order_value'] ?? 0, 0) }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Active Suppliers') }}</p>
                <p class="text-xl font-bold tabular-nums text-erp-primary">{{ number_format($tab_data['metrics']['suppliers'] ?? 0) }}</p>
            </div>
        </div>

        @include('admin.procurement.reports.partials.simple-table', [
            'title' => __('Branch Breakdown'),
            'columns' => [__('Branch'), __('Orders'), __('Spend'), __('Average Order')],
            'rows' => $tab_data['branch_breakdown'] ?? [],
        ])
    </x-admin.card>
@elseif (($tab_data['type'] ?? '') === 'trends')
    @include('admin.procurement.reports.partials.trends-charts', ['series' => $tab_data['series'] ?? []])
@elseif (($tab_data['type'] ?? '') === 'top_suppliers')
    <x-admin.card class="mb-6">
        <form method="GET" action="{{ route('admin.procurement.reports.index') }}" x-data="erpIndexFilterForm()" @change="onFieldChange($event)" class="mb-4 flex flex-wrap items-center gap-2" data-turbo-frame="erp-main">
            @foreach (collect($filters)->except(['top_limit', 'page']) as $key => $value)
                @if ($value !== null && $value !== '')
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <input type="hidden" name="tab" value="top_suppliers">
            <select id="top_limit" name="top_limit" class="erp-toolbar-select" aria-label="{{ __('Show top') }}">
                @foreach ([10, 25, 50] as $limit)
                    <option value="{{ $limit }}" @selected(($filters['top_limit'] ?? 10) == $limit)>{{ $limit }}</option>
                @endforeach
            </select>
        </form>

        @include('admin.procurement.reports.partials.simple-table', [
            'title' => __('Top Suppliers'),
            'columns' => $tab_data['columns'] ?? [],
            'rows' => collect($tab_data['rows'] ?? [])->map(fn ($row) => array_values($row))->all(),
        ])
    </x-admin.card>
@elseif (($tab_data['type'] ?? '') === 'scorecard')
    <x-admin.card>
        <h2 class="mb-4 text-sm font-semibold text-erp-primary">{{ __('Supplier Scorecard') }}</h2>
        @include('admin.procurement.reports.partials.simple-table', [
            'title' => __('Supplier Performance'),
            'columns' => $tab_data['columns'] ?? [],
            'rows' => collect(($tab_data['rows'] ?? collect())->items() ?? [])->map(fn ($row) => array_values((array) $row))->all(),
        ])

        @if (($tab_data['rows'] ?? null) instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
            <div class="mt-4 border-t border-erp-border pt-4">
                {{ $tab_data['rows']->withQueryString()->links() }}
            </div>
        @endif
    </x-admin.card>
@elseif (($tab_data['type'] ?? '') === 'table')
    <x-admin.card>
        @include('admin.procurement.reports.partials.simple-table', [
            'title' => collect($tabs)->firstWhere('key', $active_tab)['label'] ?? __('Report'),
            'columns' => $tab_data['columns'] ?? [],
            'rows' => collect(($tab_data['rows'] ?? collect())->items() ?? [])->map(fn ($row) => array_values((array) $row))->all(),
        ])

        @if (($tab_data['rows'] ?? null) instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
            <div class="mt-4 border-t border-erp-border pt-4">
                {{ $tab_data['rows']->withQueryString()->links() }}
            </div>
        @endif
    </x-admin.card>
@endif
