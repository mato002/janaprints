@php
    use App\Enums\ProductionJobCardStatus;

    $jobCard = $jobCard ?? null;
    $completion = $completion ?? ['eligible' => false, 'blockers' => []];
    $hasPostedOutput = (bool) ($hasPostedOutput ?? false);
    $checklist = collect($readinessChecklist ?? []);
    $dispatchSummary = $dispatchSummary ?? null;
    $materialReadiness = is_array($materialReadiness ?? null) ? $materialReadiness : null;

    $materialsPassed = $checklist->firstWhere('key', 'materials')['state'] ?? null;
    $materialsConsumed = in_array($materialsPassed, ['passed', 'warning'], true)
        || ($jobCard?->material_consumptions_count ?? 0) > 0;

    if ($materialReadiness !== null) {
        if ($materialReadiness['ready'] ?? false) {
            $materialsState = 'completed';
        } else {
            $materialsState = ($materialReadiness['has_requirements'] ?? false) ? 'blocked' : 'blocked';
        }
    } else {
        $materialsState = $materialsConsumed ? 'completed' : 'future';
    }

    $operationsItem = $checklist->firstWhere('key', 'operations');
    $productionDone = in_array($operationsItem['state'] ?? null, ['passed', 'warning'], true);
    $productionCurrent = ! $productionDone && in_array($jobCard?->status, [
        ProductionJobCardStatus::InProduction,
        ProductionJobCardStatus::QualityCheck,
        ProductionJobCardStatus::Completed,
        ProductionJobCardStatus::ReadyForDispatch,
    ], true);

    $qcItem = $checklist->firstWhere('key', 'qc');
    $qcDone = ($qcItem['state'] ?? null) === 'passed';

    $fgDone = $hasPostedOutput;
    $fgCurrent = ! $fgDone && in_array($jobCard?->status, [ProductionJobCardStatus::Completed, ProductionJobCardStatus::ReadyForDispatch], true);

    $hasDeliveryNote = (bool) ($dispatchSummary['has_delivery_note'] ?? false);
    $dispatchPhase = $dispatchSummary['workflow_phase'] ?? null;
    $canCreateDeliveryNote = (bool) ($workflowPresentation['can_create_delivery_note'] ?? false);

    $fgState = 'future';
    if ($fgDone) {
        $fgState = 'completed';
    } elseif ($fgCurrent) {
        $fgState = ! empty($completion['blockers'] ?? []) ? 'blocked' : 'current';
    }

    $dispatchState = match (true) {
        in_array($dispatchPhase, ['delivered', 'closed'], true) => 'completed',
        in_array($dispatchPhase, ['dispatched', 'dispatch_created'], true) => 'current',
        $jobCard?->status === ProductionJobCardStatus::ReadyForDispatch && $hasPostedOutput && ! $hasDeliveryNote && $canCreateDeliveryNote => 'current',
        $jobCard?->status === ProductionJobCardStatus::ReadyForDispatch && $hasPostedOutput && ! $hasDeliveryNote && ! $canCreateDeliveryNote => 'blocked',
        $hasDeliveryNote => 'completed',
        default => 'future',
    };

    $stages = [
        ['label' => __('MAT'), 'full' => __('Materials'), 'state' => $materialsState, 'theme' => 'materials'],
        ['label' => __('PROD'), 'full' => __('Production'), 'state' => $productionDone ? 'completed' : ($productionCurrent ? 'current' : 'future'), 'theme' => 'production'],
        ['label' => __('QC'), 'full' => __('QC'), 'state' => $qcDone ? 'completed' : ($jobCard?->status === ProductionJobCardStatus::QualityCheck ? 'current' : 'future'), 'theme' => 'qc'],
        ['label' => __('FG'), 'full' => __('Finished goods'), 'state' => $fgState, 'theme' => 'slate'],
        ['label' => __('DISP'), 'full' => __('Dispatch'), 'state' => $dispatchState, 'theme' => 'dispatch'],
    ];

    $compact = (bool) ($compact ?? false);
@endphp

@if ($compact)
    <div class="job-360-workflow mes-workflow" aria-label="{{ __('Production stage progression') }}">
        <ol class="job-360-pipeline job-360-pipeline--compact">
            @foreach ($stages as $stage)
                <li @class(['job-360-pipeline__step', 'job-360-pipeline__step--'.$stage['state'], 'job-360-pipeline__step--'.$stage['theme']]) title="{{ $stage['full'] }}">
                    <div class="job-360-pipeline__node" aria-hidden="true"></div>
                    <span class="job-360-pipeline__label">{{ $stage['label'] }}</span>
                </li>
            @endforeach
        </ol>
    </div>
@else
    <x-admin.job-module-card theme="slate" :title="__('Workflow')" icon="switch-horizontal" compact aria-label="{{ __('Production stage progression') }}">
        <ol class="job-360-pipeline">
            @foreach ($stages as $stage)
                <li @class(['job-360-pipeline__step', 'job-360-pipeline__step--'.$stage['state'], 'job-360-pipeline__step--'.$stage['theme']])>
                    <div class="job-360-pipeline__node" aria-hidden="true"></div>
                    <span class="job-360-pipeline__label">{{ $stage['full'] }}</span>
                </li>
            @endforeach
        </ol>
    </x-admin.job-module-card>
@endif
