@props(['tab_data', 'active_tab', 'tabs', 'filters'])

@if (($tab_data['type'] ?? '') === 'placeholder')
    <x-admin.card>
        <x-admin.empty-state icon="user-circle" :title="__('Customer Reports')" :description="$tab_data['message'] ?? __('No data available.')" />
    </x-admin.card>
@elseif (($tab_data['type'] ?? '') === 'summary')
    @include('admin.commercial.reports.partials.summary-tables', ['tables' => $tab_data['tables'] ?? []])
@elseif (($tab_data['type'] ?? '') === 'top_customers')
    <x-admin.card class="mb-6">
        <form method="GET" action="{{ route('admin.commercial.reports.customers.index') }}" x-data="erpIndexFilterForm()" @change="onFieldChange($event)" class="mb-4 flex flex-wrap items-center gap-2" data-turbo-frame="{{ \App\Support\Navigation\WorkspaceEmbed::turboFrame() }}">
            @foreach (collect($filters)->except(['top_limit', 'page']) as $key => $value)
                @if ($value !== null && $value !== '')
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <input type="hidden" name="tab" value="top">
            <select id="top_limit" name="top_limit" class="erp-toolbar-select" aria-label="{{ __('Show top') }}">
                @foreach ([10, 25, 50] as $limit)
                    <option value="{{ $limit }}" @selected(($filters['top_limit'] ?? 10) == $limit)>{{ $limit }}</option>
                @endforeach
            </select>
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
