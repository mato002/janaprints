@php
    $salesOrder = $tabData['sales_order'] ?? null;
    $costSummary = $tabData['cost_summary'] ?? null;
    $manufacturingHint = $tabData['manufacturing_cost_hint'] ?? null;
    $outsource = $tabData['outsource'] ?? [];
@endphp

<div class="space-y-4">
    @if ($salesOrder)
        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Sales order') }}</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Order') }}</dt>
                    <dd class="font-medium">
                        @if ($jobCard->salesOrder)
                            <a
                                href="{{ route('admin.sales-orders.show', $jobCard->salesOrder) }}"
                                class="font-mono text-erp-accent underline decoration-erp-accent/40 underline-offset-2 hover:decoration-erp-accent"
                                data-turbo-frame="erp-main"
                                data-turbo-action="advance"
                            >{{ $salesOrder['number'] ?? $jobCard->salesOrder->order_number }}</a>
                        @else
                            <span class="font-mono">{{ $salesOrder['number'] }}</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Status') }}</dt><dd>{{ str_replace('_', ' ', $salesOrder['status']) }}</dd></div>
                @if ($salesOrder['total'] !== null)
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Order total') }}</dt><dd class="font-medium tabular-nums">{{ number_format((float) $salesOrder['total'], 2) }}</dd></div>
                @endif
            </dl>
        </x-admin.card>
    @endif

    @if ($costSummary)
        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Job cost summary') }}</h3>
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-6 text-sm">
                @foreach ([
                    'material' => __('Material'),
                    'labor' => __('Labour'),
                    'outsource' => __('Outsource'),
                    'total' => __('Total'),
                    'revenue' => __('Revenue'),
                    'gross_profit' => __('Gross profit'),
                ] as $field => $label)
                    @if (isset($costSummary[$field]))
                        <div class="rounded-lg border border-erp-border px-3 py-2">
                            <div class="text-xs text-slate-500">{{ $label }}</div>
                            <div class="font-semibold tabular-nums">{{ number_format((float) $costSummary[$field], 2) }}</div>
                        </div>
                    @endif
                @endforeach
            </div>
            <p class="mt-3 text-xs text-slate-500">{{ __('Read-only snapshot from the job cost sheet.') }}</p>
            @if (! empty($tabData['cost_detail_url']))
                <a href="{{ $tabData['cost_detail_url'] }}" class="erp-btn-secondary mt-3 inline-flex text-sm">{{ __('Full cost sheet') }}</a>
            @endif
        </x-admin.card>
    @elseif ($manufacturingHint)
        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Estimated costs') }}</h3>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 text-sm">
                <div><div class="text-xs text-slate-500">{{ __('Material') }}</div><div class="font-semibold">{{ number_format($manufacturingHint['material'], 2) }}</div></div>
                <div><div class="text-xs text-slate-500">{{ __('Labour') }}</div><div class="font-semibold">{{ number_format($manufacturingHint['labor'], 2) }}</div></div>
                <div><div class="text-xs text-slate-500">{{ __('Outsource') }}</div><div class="font-semibold">{{ number_format($manufacturingHint['outsource'], 2) }}</div></div>
                <div><div class="text-xs text-slate-500">{{ __('Total') }}</div><div class="font-semibold">{{ number_format($manufacturingHint['total'], 2) }}</div></div>
            </div>
        </x-admin.card>
    @elseif (! ($tabData['can_view_costing'] ?? false))
        <x-admin.empty-state icon="lock-closed" :title="__('Costing access required')" />
    @else
        <x-admin.card>
            <p class="text-sm text-slate-600">{{ __('No cost sheet recorded for this job yet.') }}</p>
        </x-admin.card>
    @endif

    @if (! empty($outsource['vendor']) || ! empty($outsource['quoted_cost']))
        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Outsource') }}</h3>
            <dl class="space-y-2 text-sm">
                @if ($outsource['vendor'] ?? null)
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Vendor') }}</dt><dd>{{ $outsource['vendor']->vendor_name }}</dd></div>
                @endif
                @if ($outsource['quoted_cost'] ?? null)
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Quoted cost') }}</dt><dd class="tabular-nums">{{ number_format((float) $outsource['quoted_cost'], 2) }}</dd></div>
                @endif
            </dl>
        </x-admin.card>
    @endif
</div>
