@if (! empty($tab_data['summary']))
    <div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
        @foreach ($tab_data['summary'] as $item)
            <div class="rounded-md border border-erp-border bg-white px-3 py-2">
                <p class="text-[10px] font-medium uppercase tracking-wide text-slate-500">{{ $item['label'] }}</p>
                <p class="mt-0.5 text-lg font-semibold tabular-nums text-erp-primary">{{ $item['value'] }}</p>
            </div>
        @endforeach
    </div>
@endif

@include('admin.reports.operational-registers.partials.register-table', [
    'table' => $tab_data['table'] ?? [],
])
