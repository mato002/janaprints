@php
    $manufacturingSummary = $tabData['manufacturing_summary'] ?? [];
    $printSource = $tabData['print_specification_source'] ?? null;
    $machine = $tabData['machine'] ?? [];
    $hasSpecs = $printSource || ! empty($manufacturingSummary) || ! empty($machine['machine_name']);
@endphp

<div class="job-360-overview">
    <div class="grid grid-cols-1 gap-3 lg:grid-cols-2 lg:items-stretch">
        <x-admin.job-module-card class="h-full" theme="production" :title="__('Production')" icon="cog" compact>
            @include('admin.production.job-cards.workspace.partials.operations-zone', [
                'jobCard' => $jobCard,
                'executionState' => $executionState ?? [],
                'assignableMachines' => $assignableMachines ?? collect(),
            ])
        </x-admin.job-module-card>

        @if ($hasSpecs)
            <x-admin.job-module-card class="h-full" theme="materials" :title="__('Job specifications')" icon="document-text" compact>
                <x-slot:actions>
                    @if (! empty($manufacturingSummary['manufacturing_url']))
                        <a href="{{ $manufacturingSummary['manufacturing_url'] }}" class="text-xs font-medium text-emerald-700 hover:underline" data-turbo-frame="erp-main">{{ __('Manufacturing tab') }} →</a>
                    @endif
                </x-slot:actions>

                @if ($printSource)
                    <dl class="job-360-zone__compact-grid mb-2">
                        <div><dt>{{ __('Source') }}</dt><dd>{{ $printSource['order_source_label'] ?? __('—') }}</dd></div>
                        <div><dt>{{ __('Product') }}</dt><dd>{{ $printSource['product_name'] ?? __('—') }}</dd></div>
                        <div class="sm:col-span-2"><dt>{{ __('Specification') }}</dt><dd>{{ $printSource['specification_label'] ?? __('—') }}</dd></div>
                    </dl>
                @endif

                @if (! empty($manufacturingSummary) && ($manufacturingSummary['has_specification'] ?? false))
                    <dl class="job-360-zone__compact-grid">
                        <div><dt>{{ __('Product') }}</dt><dd>{{ $manufacturingSummary['product'] ?? '—' }}</dd></div>
                        <div><dt>{{ __('Quantity') }}</dt><dd>{{ $manufacturingSummary['quantity'] ?? '—' }}</dd></div>
                        <div><dt>{{ __('Type') }}</dt><dd>{{ $manufacturingSummary['production_type'] ?? '—' }}</dd></div>
                        <div><dt>{{ __('Sheets') }}</dt><dd>{{ $manufacturingSummary['estimated_sheets'] ?? '—' }}</dd></div>
                    </dl>
                @endif

                @if (! empty($machine['machine_name']))
                    <dl class="job-360-zone__compact-grid mt-2 border-t border-erp-border pt-2">
                        <div><dt>{{ __('Machine') }}</dt><dd>{{ $machine['machine_name'] }}</dd></div>
                        <div><dt>{{ __('Status') }}</dt><dd>{{ $machine['machine_status'] }}</dd></div>
                    </dl>
                @endif
            </x-admin.job-module-card>
        @endif

        @include('admin.production.job-cards.workspace.partials.history-zone', ['jobCard' => $jobCard])

        @include('admin.production.job-cards.workspace.partials.outsource', ['jobCard' => $jobCard, 'tabData' => $tabData])
    </div>
</div>
