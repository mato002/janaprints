@php
    $summary = $tabData['summary'] ?? [];
    $manufacturingSummary = $tabData['manufacturing_summary'] ?? [];
    $printSource = $tabData['print_specification_source'] ?? null;
    $machine = $tabData['machine'] ?? [];
@endphp

<div class="job-360-overview">
    <div class="job-360-overview__zones">
        @include('admin.production.job-cards.workspace.partials.operations-zone', [
            'jobCard' => $jobCard,
            'executionState' => $executionState ?? [],
            'assignableMachines' => $assignableMachines ?? collect(),
        ])

        @include('admin.production.job-cards.workspace.partials.commercial-zone', [
            'jobCard' => $jobCard,
            'tabData' => $tabData,
            'dispatchSummary' => $dispatchSummary ?? null,
        ])
    </div>

    @include('admin.production.job-cards.workspace.partials.history-zone', ['jobCard' => $jobCard])

    @if ($printSource || ! empty($manufacturingSummary) || ! empty($machine['machine_name']))
        <details class="job-360-overview__details">
            <summary>{{ __('Job details & specifications') }}</summary>
            <div class="job-360-overview__details-body">
                @if ($printSource)
                    <x-admin.card class="mb-4">
                        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Print specification') }}</h3>
                        <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <dt class="text-slate-500">{{ __('Source') }}</dt>
                                <dd class="font-medium">{{ $printSource['order_source_label'] ?? __('—') }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-slate-500">{{ __('Specification') }}</dt>
                                <dd class="font-medium">{{ $printSource['specification_label'] ?? __('—') }}</dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">{{ __('Product') }}</dt>
                                <dd class="font-medium">{{ $printSource['product_name'] ?? __('—') }}</dd>
                            </div>
                        </dl>
                        @if (! empty($printSource['production_notes']) || ! empty($printSource['commercial_notes']) || ! empty($printSource['customer_instructions']))
                            <dl class="mt-4 grid grid-cols-1 gap-3 border-t border-erp-border pt-4 text-sm lg:grid-cols-3">
                                @if (! empty($printSource['production_notes']))
                                    <div>
                                        <dt class="text-slate-500">{{ __('Production notes') }}</dt>
                                        <dd class="mt-1 whitespace-pre-wrap text-slate-700">{{ $printSource['production_notes'] }}</dd>
                                    </div>
                                @endif
                                @if (! empty($printSource['commercial_notes']))
                                    <div>
                                        <dt class="text-slate-500">{{ __('Commercial notes') }}</dt>
                                        <dd class="mt-1 whitespace-pre-wrap text-slate-700">{{ $printSource['commercial_notes'] }}</dd>
                                    </div>
                                @endif
                                @if (! empty($printSource['customer_instructions']))
                                    <div>
                                        <dt class="text-slate-500">{{ __('Customer instructions') }}</dt>
                                        <dd class="mt-1 whitespace-pre-wrap text-slate-700">{{ $printSource['customer_instructions'] }}</dd>
                                    </div>
                                @endif
                            </dl>
                        @endif
                    </x-admin.card>
                @endif

                @if (! empty($manufacturingSummary))
                    <x-admin.card class="mb-4">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Manufacturing instructions') }}</h3>
                            <a href="{{ $manufacturingSummary['manufacturing_url'] }}" class="text-xs font-medium text-erp-primary" data-turbo-frame="erp-main">{{ __('Open manufacturing tab') }}</a>
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

                @if (! empty($machine['machine_name']))
                    <x-admin.card>
                        <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Machine profile') }}</h3>
                        <dl class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                            <div><dt class="text-slate-500">{{ __('Machine') }}</dt><dd class="font-medium">{{ $machine['machine_name'] }}</dd></div>
                            <div><dt class="text-slate-500">{{ __('Status') }}</dt><dd>{{ $machine['machine_status'] }}</dd></div>
                            <div><dt class="text-slate-500">{{ __('Expected throughput') }}</dt><dd>{{ number_format($machine['expected_throughput'] ?? 0, 2) }} / hr</dd></div>
                            <div><dt class="text-slate-500">{{ __('Availability') }}</dt><dd>{{ $machine['availability']['label'] ?? '—' }}</dd></div>
                        </dl>
                    </x-admin.card>
                @endif
            </div>
        </details>
    @endif

    @include('admin.production.job-cards.workspace.partials.outsource', ['jobCard' => $jobCard, 'tabData' => $tabData])
</div>
