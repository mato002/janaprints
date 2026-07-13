@props(['tab_data', 'report_label', 'filters'])

@if (($tab_data['type'] ?? '') === 'placeholder')
    <x-admin.card>
        <x-admin.empty-state icon="cash" :title="__('POS Intelligence')" :description="$tab_data['message'] ?? __('No data available.')" />
    </x-admin.card>
@elseif (($tab_data['type'] ?? '') === 'table')
    <x-admin.card :padding="false">
        <div class="flex items-center justify-between gap-3 border-b border-erp-border px-4 py-3">
            <h3 class="text-sm font-semibold text-erp-primary">{{ $report_label }}</h3>
            <span class="text-xs text-slate-500">{{ __('Use filters above to refine this report.') }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        @foreach ($tab_data['columns'] ?? [] as $column)
                            <th scope="col">{{ $column }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                        $rows = collect(($tab_data['rows'] ?? collect())->items() ?? [])->map(fn ($row) => array_values((array) $row))->all();
                    @endphp
                    @forelse ($rows as $row)
                        <tr>
                            @foreach ((array) $row as $cell)
                                <td class="tabular-nums">{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($tab_data['columns'] ?? []) }}" class="py-8 text-center text-slate-500">
                                {{ __('No data for selected filters.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (($tab_data['rows'] ?? null) instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
            <div class="border-t border-erp-border px-4 py-3">
                {{ $tab_data['rows']->withQueryString()->links() }}
            </div>
        @endif
    </x-admin.card>
@endif
