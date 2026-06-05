@props(['tab_data', 'active_tab', 'tabs', 'filters'])

@if (($tab_data['type'] ?? '') === 'placeholder')
    <x-admin.card>
        <x-admin.empty-state icon="user-circle" :title="__('Customer Reports')" :description="$tab_data['message'] ?? __('No data available.')" />
    </x-admin.card>
@elseif (($tab_data['type'] ?? '') === 'summary')
    <x-admin.card class="mb-6">
        <h2 class="mb-4 text-sm font-semibold text-erp-primary">{{ __('Customer Summary') }}</h2>
        <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Total Customers') }}</p>
                <p class="text-xl font-bold tabular-nums text-erp-primary">{{ number_format($tab_data['metrics']['total'] ?? 0) }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('New Customers') }}</p>
                <p class="text-xl font-bold tabular-nums text-erp-primary">{{ number_format($tab_data['metrics']['new'] ?? 0) }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Repeat Customers') }}</p>
                <p class="text-xl font-bold tabular-nums text-erp-primary">{{ number_format($tab_data['metrics']['repeat'] ?? 0) }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Average Customer Value') }}</p>
                <p class="text-xl font-bold tabular-nums text-erp-primary">{{ 'KES '.number_format($tab_data['metrics']['average_value'] ?? 0, 0) }}</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            @include('admin.commercial.reports.sales.partials.simple-table', [
                'title' => __('Customer By Branch'),
                'columns' => [__('Branch'), __('Customers'), __('Active'), __('Inactive'), __('Revenue')],
                'rows' => $tab_data['branch_breakdown'] ?? [],
            ])
            @include('admin.commercial.reports.sales.partials.simple-table', [
                'title' => __('Customer By Salesperson'),
                'columns' => [__('Salesperson'), __('Customers'), __('Orders'), __('Revenue'), __('Average Value')],
                'rows' => $tab_data['salesperson_breakdown'] ?? [],
            ])
        </div>
    </x-admin.card>
@elseif (($tab_data['type'] ?? '') === 'top_customers')
    <x-admin.card class="mb-6">
        <form method="GET" action="{{ route('commercial.reports.customers.index') }}" class="mb-4 flex flex-wrap items-end gap-3" data-turbo-frame="erp-main">
            @foreach (collect($filters)->except(['top_limit', 'page']) as $key => $value)
                @if ($value !== null && $value !== '')
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <input type="hidden" name="tab" value="top">
            <div>
                <label class="text-[11px] text-slate-500" for="top_limit">{{ __('Show top') }}</label>
                <select id="top_limit" name="top_limit" class="erp-input mt-1">
                    @foreach ([10, 25, 50] as $limit)
                        <option value="{{ $limit }}" @selected(($filters['top_limit'] ?? 10) == $limit)>{{ $limit }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="erp-btn-primary">{{ __('Apply') }}</button>
        </form>

        @include('admin.commercial.reports.sales.partials.simple-table', [
            'title' => __('Top Customers'),
            'columns' => $tab_data['columns'] ?? [],
            'rows' => collect($tab_data['rows'] ?? [])->map(fn ($row) => array_values($row))->all(),
        ])
    </x-admin.card>
@elseif (($tab_data['type'] ?? '') === 'table')
    <x-admin.card>
        @include('admin.commercial.reports.sales.partials.simple-table', [
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
