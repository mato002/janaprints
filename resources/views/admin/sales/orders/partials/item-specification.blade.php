@php
    $specEntry = $itemSpecifications[$item->id] ?? null;
    $specModel = $specEntry['model'] ?? null;
    $specSummary = $specEntry['summary'] ?? null;
    $hasSpec = $specSummary && ($specSummary['has_specification'] ?? false);
@endphp
<div class="border-b border-erp-border py-3 last:border-b-0">
    <div class="flex flex-wrap items-start justify-between gap-2">
        <div class="text-sm">
            <p class="font-medium">{{ $item->item_name }} × {{ $item->quantity }}</p>
            @if ($item->description)
                <p class="text-slate-500">{{ $item->description }}</p>
            @endif
        </div>
        <div class="text-sm font-mono">{{ number_format($item->line_total, 2) }}</div>
    </div>

    @if ($hasSpec)
        <dl class="mt-2 grid grid-cols-2 gap-x-4 gap-y-1 text-xs text-slate-600 sm:grid-cols-4">
            @if ($specSummary['production_type_label'] ?? null)
                <div><dt class="text-slate-400">{{ __('Type') }}</dt><dd>{{ $specSummary['production_type_label'] }}</dd></div>
            @endif
            @if ($specSummary['size'] ?? null)
                <div><dt class="text-slate-400">{{ __('Size') }}</dt><dd>{{ $specSummary['size'] }}</dd></div>
            @endif
            @if ($specSummary['paper'] ?? null)
                <div><dt class="text-slate-400">{{ __('Paper') }}</dt><dd>{{ $specSummary['paper'] }}</dd></div>
            @endif
            @if ($specSummary['ups'] ?? null)
                <div><dt class="text-slate-400">{{ __('Ups') }}</dt><dd>{{ $specSummary['ups'] }}</dd></div>
            @endif
        </dl>
        @can('update', $specModel)
            <a href="{{ route('admin.sales-orders.items.specification.edit', [$salesOrder, $item, $specModel]) }}" class="erp-btn-secondary mt-2 inline-flex text-xs">
                {{ __('Edit specification') }}
            </a>
        @endcan
    @else
        <div class="mt-3 rounded-md border border-amber-200 bg-amber-50 px-3 py-2">
            <p class="text-sm font-medium text-amber-950">{{ __('How should production make this item?') }}</p>
            <p class="mt-0.5 text-xs text-amber-900/80">{{ __('Size, paper, colours, and finishing for the job card. Sales price is already set above.') }}</p>
            @can('create', [App\Models\Production\ProductionSpecification::class, $salesOrder])
                <a href="{{ route('admin.sales-orders.items.specification.create', [$salesOrder, $item]) }}" class="erp-btn-primary mt-2 inline-flex text-xs">
                    {{ __('Add production specification') }}
                </a>
            @endcan
        </div>
    @endif
</div>
