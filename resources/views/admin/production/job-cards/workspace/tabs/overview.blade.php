@php
    $summary = $tabData['summary'] ?? [];
    $customer = $tabData['customer'] ?? null;
    $salesOrder = $tabData['sales_order'] ?? null;
    $quotation = $tabData['quotation'] ?? null;
    $artwork = $tabData['artwork'] ?? null;
@endphp

@include('admin.production.job-cards.workspace.partials.control-alerts', ['alerts' => $tabData['control_alerts'] ?? []])

@include('admin.production.job-cards.workspace.partials.workflow', ['jobCard' => $jobCard])

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <x-admin.card class="lg:col-span-1">
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Job summary') }}</h3>
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Type') }}</dt><dd>{{ str_replace('_', ' ', $summary['production_type'] ?? '—') }}</dd></div>
            <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Priority') }}</dt><dd>{{ str_replace('_', ' ', $summary['priority'] ?? '—') }}</dd></div>
            <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Planned') }}</dt><dd>{{ ($summary['planned']['start'] ?? '—') }} → {{ ($summary['planned']['end'] ?? '—') }}</dd></div>
            <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Actual') }}</dt><dd>{{ ($summary['actual']['start'] ?? '—') }} → {{ ($summary['actual']['end'] ?? '—') }}</dd></div>
        </dl>
    </x-admin.card>

    <div class="lg:col-span-2 grid grid-cols-1 gap-4 md:grid-cols-2">
        <x-admin.card>
            <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Customer') }}</h3>
            @if ($customer)
                <p class="text-sm font-medium">{{ $customer['name'] }}</p>
                <p class="text-xs text-slate-500">{{ $customer['code'] }}</p>
            @else
                <p class="text-sm text-slate-500">{{ __('No customer linked.') }}</p>
            @endif
        </x-admin.card>

        <x-admin.card>
            <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Sales order') }}</h3>
            @if ($salesOrder)
                <p class="text-sm font-medium">{{ $salesOrder['number'] }}</p>
                <p class="text-xs text-slate-500">{{ str_replace('_', ' ', $salesOrder['status']) }}</p>
            @else
                <p class="text-sm text-slate-500">{{ __('No sales order linked.') }}</p>
            @endif
        </x-admin.card>

        <x-admin.card>
            <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Quotation') }}</h3>
            @if ($quotation)
                <p class="text-sm font-medium">{{ $quotation['number'] }}</p>
                <p class="text-xs text-slate-500">{{ str_replace('_', ' ', $quotation['status']) }}</p>
            @else
                <p class="text-sm text-slate-500">{{ __('No quotation linked.') }}</p>
            @endif
        </x-admin.card>

        <x-admin.card>
            <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Artwork') }}</h3>
            @if ($artwork)
                <p class="text-sm font-medium">{{ $artwork['number'] }}</p>
                <p class="text-xs text-slate-500">{{ str_replace('_', ' ', $artwork['status']) }}</p>
            @else
                <p class="text-sm text-slate-500">{{ __('No artwork linked.') }}</p>
            @endif
        </x-admin.card>
    </div>
</div>

<x-admin.card class="mt-6">
    <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Current status') }}</h3>
    <p class="text-sm text-slate-700">{{ $tabData['status_explanation'] ?? '' }}</p>
    <p class="mt-3 text-sm"><span class="font-medium text-erp-primary">{{ __('Next action') }}:</span> {{ $tabData['next_action'] ?? '' }}</p>
</x-admin.card>
