@php
    $summary = $tabData['summary'] ?? [];
    $customer = $tabData['customer'] ?? null;
    $salesOrder = $tabData['sales_order'] ?? null;
    $quotation = $tabData['quotation'] ?? null;
    $artwork = $tabData['artwork'] ?? null;
    $queue = $tabData['queue'] ?? [];
    $manufacturingSummary = $tabData['manufacturing_summary'] ?? [];
@endphp

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <x-admin.card class="lg:col-span-1">
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Queue status') }}</h3>
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Current queue') }}</dt><dd>{{ $queue['status_label'] ?? __('—') }}</dd></div>
            <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Work center') }}</dt><dd>{{ $queue['work_center'] ?? __('—') }}</dd></div>
            <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Queue position') }}</dt><dd>{{ $queue['position'] ?? __('—') }}</dd></div>
            <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Priority') }}</dt><dd>{{ str_replace('_', ' ', $queue['priority'] ?? '—') }}</dd></div>
            <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Required date') }}</dt><dd>{{ $queue['required_date'] ?? __('—') }}</dd></div>
        </dl>
    </x-admin.card>

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

@if (! empty($manufacturingSummary))
    <x-admin.card class="mt-6">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Manufacturing instructions') }}</h3>
            <a href="{{ $manufacturingSummary['manufacturing_url'] }}" class="text-xs font-medium text-erp-primary">{{ __('Open manufacturing tab') }}</a>
        </div>
        @if ($manufacturingSummary['has_specification'] ?? false)
            <dl class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-2 lg:grid-cols-4">
                <div><dt class="text-slate-500">{{ __('Product') }}</dt><dd class="font-medium">{{ $manufacturingSummary['product'] ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Quantity') }}</dt><dd class="font-medium">{{ $manufacturingSummary['quantity'] ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Production type') }}</dt><dd class="font-medium">{{ $manufacturingSummary['production_type'] ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Estimated sheets') }}</dt><dd class="font-medium">{{ $manufacturingSummary['estimated_sheets'] ?? '—' }}</dd></div>
            </dl>
        @else
            <p class="text-sm text-slate-600">{{ $manufacturingSummary['empty_message'] ?? __('No structured Production Specification available.') }}</p>
        @endif
    </x-admin.card>
@endif

@php $machine = $tabData['machine'] ?? []; @endphp
<x-admin.card class="mt-6">
    <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Assigned Machine') }}</h3>
    @if (! empty($machine['machine_name']))
        <dl class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500">{{ __('Machine') }}</dt><dd class="font-medium">{{ $machine['machine_name'] }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Status') }}</dt><dd>{{ $machine['machine_status'] }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Expected Throughput') }}</dt><dd>{{ number_format($machine['expected_throughput'] ?? 0, 2) }} / hr</dd></div>
            <div><dt class="text-slate-500">{{ __('Availability') }}</dt><dd>{{ $machine['availability']['label'] ?? '—' }}</dd></div>
        </dl>
        @if (($machine['assignment_history'] ?? collect())->isNotEmpty())
            <div class="mt-4">
                <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Assignment History') }}</h4>
                <ul class="space-y-1 text-sm text-slate-600">
                    @foreach ($machine['assignment_history'] as $history)
                        <li>{{ $history->assigned_at?->format('Y-m-d H:i') }} — {{ $history->assigner?->name }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    @else
        <p class="text-sm text-slate-500">{{ __('No machine assigned.') }}</p>
    @endif

    @can('machines.assign')
        @if (($tabData['machine_options'] ?? collect())->isNotEmpty())
            <form method="POST" action="{{ route('admin.production.job-cards.assign-machine', $jobCard) }}" class="mt-4 flex flex-wrap items-end gap-2">
                @csrf
                <div class="min-w-[14rem] flex-1">
                    <label class="erp-label">{{ __('Assign Machine') }}</label>
                    <select name="assigned_machine_asset_id" class="erp-select w-full" required>
                        <option value="">{{ __('Select machine…') }}</option>
                        @foreach ($tabData['machine_options'] as $option)
                            <option value="{{ $option->fixed_asset_id }}">{{ $option->asset?->asset_name }} ({{ $option->machine_code }})</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="erp-btn-primary">{{ __('Assign') }}</button>
            </form>
        @endif
    @endcan
</x-admin.card>

<x-admin.card class="mt-6">
    <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Current status') }}</h3>
    <p class="text-sm text-slate-700">{{ $tabData['status_explanation'] ?? '' }}</p>
    <p class="mt-3 text-sm"><span class="font-medium text-erp-primary">{{ __('Next action') }}:</span> {{ $tabData['next_action'] ?? '' }}</p>
</x-admin.card>

@include('admin.production.job-cards.workspace.partials.outsource', ['jobCard' => $jobCard, 'tabData' => $tabData])
