@props(['tab_data', 'active_tab', 'tabs', 'filters'])

@if (($tab_data['type'] ?? '') === 'placeholder')
    <x-admin.card>
        <x-admin.empty-state icon="chart-bar" :title="__('Supplier Performance')" :description="$tab_data['message'] ?? __('No data available.')" />
    </x-admin.card>
@elseif (($tab_data['type'] ?? '') === 'scorecard')
    <x-admin.card>
        <h2 class="mb-4 text-sm font-semibold text-erp-primary">{{ __('Supplier Scorecard') }}</h2>
        @include('admin.procurement.supplier-performance.partials.simple-table', [
            'title' => __('Calculated from historical procurement performance'),
            'columns' => $tab_data['columns'] ?? [],
            'rows' => collect(($tab_data['rows'] ?? collect())->items() ?? [])->map(fn ($row) => array_values((array) $row))->all(),
        ])

        @if (($tab_data['rows'] ?? null) instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
            <div class="mt-4 border-t border-erp-border pt-4">
                {{ $tab_data['rows']->withQueryString()->links() }}
            </div>
        @endif
    </x-admin.card>
@elseif (($tab_data['type'] ?? '') === 'trends')
    @include('admin.procurement.supplier-performance.partials.trends-charts', ['series' => $tab_data['series'] ?? []])
@elseif (($tab_data['type'] ?? '') === 'rankings')
    <div class="grid gap-6 xl:grid-cols-2">
        @foreach ([
            'top_suppliers' => [__('Top Suppliers'), __('Score'), __('Grade')],
            'most_reliable' => [__('Most Reliable'), __('On-Time %')],
            'fastest_delivery' => [__('Fastest Delivery'), __('Avg Delivery')],
            'best_price' => [__('Best Price'), __('Price Score')],
            'highest_spend' => [__('Highest Spend'), __('Spend')],
        ] as $key => $meta)
            <x-admin.card>
                <h3 class="mb-4 text-sm font-semibold text-erp-primary">{{ $meta[0] }}</h3>
                @php $rows = $tab_data['rankings'][$key] ?? []; @endphp
                @if ($rows === [])
                    <p class="py-6 text-center text-sm text-slate-500">{{ __('No ranking data for selected filters.') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-erp-border text-left text-[11px] uppercase tracking-wide text-slate-500">
                                    <th class="px-3 py-2 font-semibold">{{ __('Supplier') }}</th>
                                    @foreach (array_slice($meta, 1) as $heading)
                                        <th class="px-3 py-2 font-semibold">{{ $heading }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rows as $row)
                                    <tr class="border-b border-erp-border/60">
                                        <td class="px-3 py-2 text-slate-700">{{ $row['supplier'] }}</td>
                                        @if ($key === 'top_suppliers')
                                            <td class="px-3 py-2 tabular-nums">{{ $row['score'] }}</td>
                                            <td class="px-3 py-2 font-semibold">{{ $row['grade'] }}</td>
                                        @elseif ($key === 'most_reliable')
                                            <td class="px-3 py-2 tabular-nums">{{ $row['on_time_percent'] }}</td>
                                        @elseif ($key === 'fastest_delivery')
                                            <td class="px-3 py-2 tabular-nums">{{ $row['average_delivery_time'] }}</td>
                                        @elseif ($key === 'best_price')
                                            <td class="px-3 py-2 tabular-nums">{{ $row['price_score'] }}</td>
                                        @else
                                            <td class="px-3 py-2 tabular-nums">{{ $row['spend'] }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-admin.card>
        @endforeach
    </div>
@elseif (($tab_data['type'] ?? '') === 'table')
    <x-admin.card>
        @include('admin.procurement.supplier-performance.partials.simple-table', [
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
