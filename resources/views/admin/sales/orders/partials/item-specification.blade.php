@php
    $specEntry = $itemSpecifications[$item->id] ?? null;
    $specModel = $specEntry['model'] ?? null;
    $specSummary = $specEntry['summary'] ?? null;
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

    @if ($specSummary && ($specSummary['has_specification'] ?? false))
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
            <a href="{{ route('admin.sales-orders.items.specification.edit', [$salesOrder, $item, $specModel]) }}" class="mt-2 inline-block text-xs text-erp-accent hover:underline">
                {{ __('Edit specification') }}
            </a>
        @endcan
    @else
        <p class="mt-2 text-xs text-slate-500">{{ __('No production specification yet.') }}</p>
        @can('create', [App\Models\Production\ProductionSpecification::class, $salesOrder])
            <a href="{{ route('admin.sales-orders.items.specification.create', [$salesOrder, $item]) }}" class="mt-1 inline-block text-xs text-erp-accent hover:underline">
                {{ __('Add specification') }}
            </a>
        @endcan
    @endif
</div>
