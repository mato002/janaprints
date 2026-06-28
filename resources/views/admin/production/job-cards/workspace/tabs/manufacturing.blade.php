@php
    $hasSpec = $tabData['has_specification'] ?? false;
    $sections = $tabData['sections'] ?? [];
    $sectionLabels = [
        'general' => __('General'),
        'material' => __('Material'),
        'printing' => __('Printing'),
        'finishing' => __('Finishing'),
        'production' => __('Production'),
        'artwork' => __('Artwork'),
        'delivery' => __('Delivery'),
        'notes' => __('Production notes'),
    ];
    $operators = $tabData['operators'] ?? [];
    $recommendations = $tabData['recommendations'] ?? [];
    $materialPlan = $tabData['material_plan'] ?? [];
    $costSummary = $tabData['cost_summary'] ?? null;
    $qcHints = $tabData['qc_hints'] ?? [];
    $pipeline = $tabData['timeline_pipeline'] ?? [];
    $artwork = $tabData['artwork'] ?? [];
@endphp

<div class="manufacturing-tab space-y-4">
    <div class="sticky top-0 z-10 -mx-1 flex flex-wrap items-center gap-2 border-b border-erp-border bg-white/95 px-1 py-2 backdrop-blur sm:static sm:border-0 sm:bg-transparent sm:p-0">
        @if ($hasSpec && ! empty($tabData['edit_url']))
            <a href="{{ $tabData['edit_url'] }}" class="erp-btn-secondary text-sm">{{ __('Edit specification') }}</a>
        @endif
        <a href="{{ route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'materials']) }}" class="erp-btn-secondary text-sm">{{ __('Materials') }}</a>
        <a href="{{ route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'quality']) }}" class="erp-btn-secondary text-sm">{{ __('QC') }}</a>
    </div>

    @if (! $hasSpec)
        <x-admin.card>
            <p class="text-sm text-slate-600">{{ $tabData['empty_message'] ?? __('No structured Production Specification available.') }}</p>
            @if (! empty($tabData['legacy']))
                <dl class="mt-4 space-y-2 border-t border-erp-border pt-4 text-sm">
                    @foreach ($tabData['legacy'] as $label => $value)
                        @if ($value)
                            <div class="flex justify-between gap-3">
                                <dt class="text-slate-500">{{ ucfirst(str_replace('_', ' ', $label)) }}</dt>
                                <dd class="font-medium">{{ $value }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            @endif
        </x-admin.card>
    @else
        @if (! empty($tabData['template_name']))
            <p class="text-xs text-slate-500">{{ __('Template') }}: {{ $tabData['template_name'] }}</p>
        @endif

        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Manufacturing timeline') }}</h3>
            <ol class="flex flex-wrap gap-2">
                @foreach ($pipeline as $stage)
                    @php
                        $tone = match ($stage['state']) {
                            'complete' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'current' => 'bg-erp-primary/10 text-erp-primary border-erp-primary/30 ring-2 ring-erp-primary/20',
                            default => 'bg-slate-50 text-slate-500 border-slate-200',
                        };
                    @endphp
                    <li class="rounded-full border px-3 py-1 text-xs font-medium {{ $tone }}">{{ $stage['label'] }}</li>
                @endforeach
            </ol>
        </x-admin.card>

        @foreach ($sectionLabels as $key => $label)
            @if (! empty($sections[$key]))
                <details class="rounded-lg border border-erp-border bg-white" @if($loop->first) open @endif>
                    <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-erp-primary">{{ $label }}</summary>
                    <dl class="space-y-2 border-t border-erp-border px-4 py-3 text-sm">
                        @foreach ($sections[$key] as $field)
                            <div class="flex justify-between gap-3">
                                <dt class="text-slate-500 shrink-0">{{ $field['label'] }}</dt>
                                <dd class="text-right font-medium">{{ $field['value'] ?? '—' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </details>
            @endif
        @endforeach

        @if (! empty($materialPlan['paper']) || ! empty($materialPlan['estimated_sheets']))
            <details class="rounded-lg border border-erp-border bg-white" open>
                <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-erp-primary">{{ __('Material summary') }}</summary>
                <div class="border-t border-erp-border px-4 py-3 text-sm">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div>
                            <div class="text-xs text-slate-500">{{ __('Paper') }}</div>
                            <div class="font-medium">{{ $materialPlan['paper'] ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">{{ __('Estimated sheets') }}</div>
                            <div class="font-medium tabular-nums">{{ $materialPlan['estimated_sheets'] ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">{{ __('Waste') }}</div>
                            <div class="font-medium tabular-nums">
                                @if ($materialPlan['waste_percent'] !== null)
                                    {{ number_format((float) $materialPlan['waste_percent'], 1) }}%
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">{{ __('Planning view only — stock is not reserved from this panel.') }}</p>
                </div>
            </details>
        @endif

        <details class="rounded-lg border border-erp-border bg-white">
            <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-erp-primary">{{ __('Machine recommendation') }}</summary>
            <dl class="space-y-2 border-t border-erp-border px-4 py-3 text-sm">
                <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Recommended work centre') }}</dt><dd>{{ $recommendations['work_center'] ?? '—' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Recommended machine') }}</dt><dd>{{ $recommendations['machine'] ?? '—' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Recommended department') }}</dt><dd>{{ $recommendations['department'] ?? '—' }}</dd></div>
            </dl>
            <p class="border-t border-erp-border px-4 py-2 text-xs text-slate-500">{{ __('Recommendations only — operators may override assignments.') }}</p>
        </details>

        <details class="rounded-lg border border-erp-border bg-white">
            <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-erp-primary">{{ __('Operator information') }}</summary>
            <dl class="space-y-2 border-t border-erp-border px-4 py-3 text-sm">
                <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Assigned operator') }}</dt><dd>{{ $operators['operator'] ?? '—' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Assigned supervisor') }}</dt><dd>{{ $operators['supervisor'] ?? '—' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Assigned machine') }}</dt><dd>{{ $operators['machine'] ?? '—' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Assigned department') }}</dt><dd>{{ $operators['department'] ?? '—' }}</dd></div>
            </dl>
        </details>

        @if (! empty($artwork) && empty($artwork['empty']))
            <details class="rounded-lg border border-erp-border bg-white">
                <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-erp-primary">{{ __('Artwork') }}</summary>
                <div class="border-t border-erp-border px-4 py-3 text-sm">
                    @if ($artwork['request'] ?? null)
                        <p class="font-medium">{{ $artwork['request']->request_number }} · v{{ $artwork['request']->current_version }}</p>
                        <p class="text-xs text-slate-500">{{ str_replace('_', ' ', $artwork['approval_status'] ?? '') }}</p>
                        <a href="{{ route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'artwork']) }}" class="mt-2 inline-block text-xs text-erp-primary">{{ __('Open artwork tab') }}</a>
                    @else
                        <p class="text-slate-500">{{ __('No artwork linked.') }}</p>
                    @endif
                </div>
            </details>
        @endif

        <details class="rounded-lg border border-erp-border bg-white">
            <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-erp-primary">{{ __('QC requirements') }}</summary>
            <ul class="list-inside list-disc space-y-1 border-t border-erp-border px-4 py-3 text-sm text-slate-700">
                @foreach ($qcHints as $hint)
                    <li>{{ $hint }}</li>
                @endforeach
            </ul>
            <div class="border-t border-erp-border px-4 py-2">
                <a href="{{ route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'quality']) }}" class="text-xs text-erp-primary">{{ __('Open QC tab') }}</a>
            </div>
        </details>

        @if ($costSummary)
            <details class="rounded-lg border border-erp-border bg-white">
                <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-erp-primary">{{ __('Cost summary') }}</summary>
                <div class="grid grid-cols-2 gap-3 border-t border-erp-border px-4 py-3 text-sm sm:grid-cols-4">
                    <div><div class="text-xs text-slate-500">{{ __('Material') }}</div><div class="font-semibold tabular-nums">{{ number_format($costSummary['material'], 2) }}</div></div>
                    <div><div class="text-xs text-slate-500">{{ __('Labour') }}</div><div class="font-semibold tabular-nums">{{ number_format($costSummary['labor'], 2) }}</div></div>
                    <div><div class="text-xs text-slate-500">{{ __('Outsource') }}</div><div class="font-semibold tabular-nums">{{ number_format($costSummary['outsource'], 2) }}</div></div>
                    <div><div class="text-xs text-slate-500">{{ __('Total') }}</div><div class="font-semibold tabular-nums">{{ number_format($costSummary['total'], 2) }}</div></div>
                </div>
                <p class="border-t border-erp-border px-4 py-2 text-xs text-slate-500">{{ __('Read-only — use Commercial tab or costing workspace for full detail.') }}</p>
            </details>
        @endif
    @endif
</div>
