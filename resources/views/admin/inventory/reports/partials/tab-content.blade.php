@props(['tab_data', 'active_tab', 'tabs', 'filters'])

@if (($tab_data['type'] ?? '') === 'placeholder')
    <x-admin.card>
        <x-admin.empty-state icon="cube" :title="__('Inventory Reports')" :description="$tab_data['message'] ?? __('No data available.')" />
    </x-admin.card>
@elseif (($tab_data['type'] ?? '') === 'aging')
    <x-admin.card class="mb-6">
        <h2 class="mb-4 text-sm font-semibold text-erp-primary">{{ __('Stock Aging Buckets') }}</h2>
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($tab_data['buckets'] ?? [] as $bucket)
                <div class="rounded-xl border border-erp-border bg-erp-page p-4">
                    <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ $bucket['label'] }}</p>
                    <p class="mt-1 text-lg font-bold tabular-nums text-erp-primary">{{ $bucket['value'] }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ __(':items items · :qty qty', ['items' => $bucket['items'], 'qty' => $bucket['qty']]) }}</p>
                </div>
            @endforeach
        </div>

        @include('admin.inventory.reports.partials.simple-table', [
            'title' => __('Stock Aging Detail'),
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
        @include('admin.inventory.reports.partials.simple-table', [
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
