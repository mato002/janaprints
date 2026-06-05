@props(['tab_data', 'active_tab', 'tabs', 'filters'])

@if (($tab_data['type'] ?? '') === 'placeholder')
    <x-admin.card>
        <x-admin.empty-state icon="document-text" :title="__('Quotation Reports')" :description="$tab_data['message'] ?? __('No data available.')" />
    </x-admin.card>
@elseif (($tab_data['type'] ?? '') === 'summary')
    <x-admin.card class="mb-6">
        <h2 class="mb-4 text-sm font-semibold text-erp-primary">{{ __('Quotation Summary') }}</h2>
        @php $m = $tab_data['metrics'] ?? []; @endphp
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Quotes Issued') }}</p>
                <p class="text-xl font-bold tabular-nums text-erp-primary">{{ number_format($m['issued'] ?? 0) }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Quotes Accepted') }}</p>
                <p class="text-xl font-bold tabular-nums text-erp-primary">{{ number_format($m['accepted'] ?? 0) }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Total Quote Value') }}</p>
                <p class="text-xl font-bold tabular-nums text-erp-primary">{{ 'KES '.number_format($m['total_value'] ?? 0, 0) }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Conversion %') }}</p>
                <p class="text-xl font-bold tabular-nums text-erp-primary">{{ ($m['conversion'] ?? 0).'%' }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Open Quotes') }}</p>
                <p class="text-xl font-bold tabular-nums text-erp-primary">{{ number_format($m['open'] ?? 0) }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Quotes Rejected') }}</p>
                <p class="text-xl font-bold tabular-nums text-erp-primary">{{ number_format($m['rejected'] ?? 0) }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Quotes Expired') }}</p>
                <p class="text-xl font-bold tabular-nums text-erp-primary">{{ number_format($m['expired'] ?? 0) }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Avg Approval Time') }}</p>
                <p class="text-xl font-bold tabular-nums text-erp-primary">
                    {{ isset($m['avg_approval_hours']) && $m['avg_approval_hours'] !== null ? $m['avg_approval_hours'].' '.__('hrs') : '—' }}
                </p>
            </div>
        </div>
    </x-admin.card>
@elseif (($tab_data['type'] ?? '') === 'win_rate')
    @php $win = $tab_data['data'] ?? []; @endphp
    <x-admin.card class="mb-6">
        <h2 class="mb-4 text-sm font-semibold text-erp-primary">{{ __('Quote Win Rate') }}</h2>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Issued') }}</p>
                <p class="text-2xl font-bold tabular-nums text-erp-primary">{{ number_format($win['issued'] ?? 0) }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Won') }}</p>
                <p class="text-2xl font-bold tabular-nums text-emerald-600">{{ number_format($win['won'] ?? 0) }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Win Rate') }}</p>
                <p class="text-2xl font-bold tabular-nums text-erp-primary">{{ ($win['win_rate'] ?? 0).'%' }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Loss Rate') }}</p>
                <p class="text-2xl font-bold tabular-nums text-rose-600">{{ ($win['loss_rate'] ?? 0).'%' }}</p>
            </div>
        </div>
        <div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="rounded-lg border border-erp-border bg-erp-page px-4 py-3">
                <p class="text-xs text-slate-500">{{ __('Accepted') }}</p>
                <p class="text-lg font-semibold tabular-nums">{{ number_format($win['accepted'] ?? 0) }}</p>
            </div>
            <div class="rounded-lg border border-erp-border bg-erp-page px-4 py-3">
                <p class="text-xs text-slate-500">{{ __('Converted') }}</p>
                <p class="text-lg font-semibold tabular-nums">{{ number_format($win['converted'] ?? 0) }}</p>
            </div>
            <div class="rounded-lg border border-erp-border bg-erp-page px-4 py-3">
                <p class="text-xs text-slate-500">{{ __('Rejected') }}</p>
                <p class="text-lg font-semibold tabular-nums">{{ number_format($win['rejected'] ?? 0) }}</p>
            </div>
            <div class="rounded-lg border border-erp-border bg-erp-page px-4 py-3">
                <p class="text-xs text-slate-500">{{ __('Expired') }}</p>
                <p class="text-lg font-semibold tabular-nums">{{ number_format($win['expired'] ?? 0) }}</p>
            </div>
        </div>
    </x-admin.card>
@elseif (($tab_data['type'] ?? '') === 'table')
    <x-admin.card>
        @php
            $rows = $tab_data['rows'] ?? [];
            $tableRows = $rows instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
                ? collect($rows->items())->map(fn ($row) => array_values((array) $row))->all()
                : collect($rows)->map(fn ($row) => array_values((array) $row))->all();
        @endphp
        @include('admin.commercial.reports.sales.partials.simple-table', [
            'title' => $tab_data['title'] ?? collect($tabs)->firstWhere('key', $active_tab)['label'] ?? __('Report'),
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
