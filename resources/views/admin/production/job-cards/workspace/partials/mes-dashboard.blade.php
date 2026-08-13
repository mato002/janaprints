{{-- Fixed MES control panel — dense widgets, minimal vertical stacking --}}
<div class="mes-dashboard" aria-label="{{ __('Job control panel') }}">
    @include('admin.production.job-cards.workspace.header', [
        'jobCard' => $jobCard,
        'header' => $header,
        'completion' => $completion,
        'hasPostedOutput' => $hasPostedOutput,
        'dispatchSummary' => $dispatchSummary,
        'workflowPresentation' => $workflowPresentation,
        'executionState' => $executionState,
        'primaryAction' => $primaryAction,
        'secondaryActions' => $secondaryActions,
    ])

    <div class="mes-dashboard__row mes-dashboard__row--split">
        @include('admin.production.job-cards.workspace.partials.blockers-panel', [
            'jobCard' => $jobCard,
            'workflowPresentation' => $workflowPresentation,
            'controlAlerts' => $controlAlerts,
            'completion' => $completion,
            'hasPostedOutput' => $hasPostedOutput,
            'materialReadiness' => $materialReadiness,
            'executionState' => $executionState,
            'compact' => true,
        ])

        @include('admin.production.job-cards.workspace.partials.production-stage-timeline', [
            'jobCard' => $jobCard,
            'completion' => $completion,
            'hasPostedOutput' => $hasPostedOutput,
            'readinessChecklist' => $readinessChecklist,
            'dispatchSummary' => $dispatchSummary,
            'workflowPresentation' => $workflowPresentation,
            'materialReadiness' => $materialReadiness,
            'compact' => true,
        ])
    </div>

    <div class="mes-dashboard__row mes-dashboard__row--widgets">
        @php
            $materialReadiness = is_array($materialReadiness ?? null) ? $materialReadiness : null;
        @endphp

        @if ($materialReadiness)
            <a
                href="{{ $materialReadiness['materials_url'] ?? route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'materials']) }}"
                class="mes-kpi mes-kpi--materials"
                data-turbo-frame="erp-main"
                data-turbo-action="advance"
                title="{{ $materialReadiness['detail'] ?? '' }}"
            >
                <span class="mes-kpi__label">{{ __('Materials') }}</span>
                <span class="mes-kpi__value">
                    @if ($materialReadiness['has_requirements'] ?? false)
                        <span class="mes-kpi__stat mes-kpi__stat--ok">✓ {{ $materialReadiness['ready_count'] ?? 0 }}</span>
                        @if (($materialReadiness['short_count'] ?? 0) > 0)
                            <span class="mes-kpi__stat mes-kpi__stat--warn">⚠ {{ $materialReadiness['short_count'] }}</span>
                        @endif
                    @else
                        <span class="mes-kpi__stat mes-kpi__stat--warn">{{ $materialReadiness['label'] ?? __('Setup') }}</span>
                    @endif
                </span>
            </a>
        @endif

        @include('admin.production.job-cards.workspace.partials.performance-section', [
            'kpis' => $kpis,
            'compact' => true,
        ])

        <div class="mes-widget mes-widget--commercial">
            @include('admin.production.job-cards.workspace.partials.commercial-chips', [
                'jobCard' => $jobCard,
                'tabData' => $tabData ?? [],
                'dispatchSummary' => $dispatchSummary,
            ])
        </div>
    </div>
</div>
