@props(['tab_data', 'active_tab', 'tabs'])

@if (($tab_data['type'] ?? '') === 'placeholder')
    <x-admin.card>
        <x-admin.empty-state icon="clipboard-list" :title="__('Sales Order Reports')" :description="$tab_data['message'] ?? __('No data available.')" />
    </x-admin.card>
@elseif (($tab_data['type'] ?? '') === 'summary')
    @include('admin.commercial.reports.partials.summary-tables', ['tables' => $tab_data['tables'] ?? []])
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
