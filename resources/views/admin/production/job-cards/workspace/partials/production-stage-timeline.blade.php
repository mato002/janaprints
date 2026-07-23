@php
    use App\Enums\ProductionJobCardStatus;

    $jobCard = $jobCard ?? null;
    $completion = $completion ?? ['eligible' => false, 'blockers' => []];
    $hasPostedOutput = (bool) ($hasPostedOutput ?? false);
    $checklist = collect($readinessChecklist ?? []);
    $dispatchSummary = $dispatchSummary ?? null;

    $materialsPassed = $checklist->firstWhere('key', 'materials')['state'] ?? null;
    $materialsDone = in_array($materialsPassed, ['passed', 'warning'], true)
        || ($jobCard?->material_consumptions_count ?? 0) > 0;

    $operationsItem = $checklist->firstWhere('key', 'operations');
    $productionDone = ($operationsItem['state'] ?? null) === 'passed'
        || in_array($jobCard?->status, [ProductionJobCardStatus::Completed, ProductionJobCardStatus::ReadyForDispatch, ProductionJobCardStatus::QualityCheck], true);

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
        ['label' => __('Materials'), 'state' => $materialsDone ? 'completed' : 'future', 'icon' => $materialsDone ? '✓' : '○'],
        ['label' => __('Production'), 'state' => $productionDone ? 'completed' : (in_array($jobCard?->status, [ProductionJobCardStatus::InProduction], true) ? 'current' : 'future'), 'icon' => $productionDone ? '✓' : ($jobCard?->status === ProductionJobCardStatus::InProduction ? '●' : '○')],
        ['label' => __('QC'), 'state' => $qcDone ? 'completed' : ($jobCard?->status === ProductionJobCardStatus::QualityCheck ? 'current' : 'future'), 'icon' => $qcDone ? '✓' : ($jobCard?->status === ProductionJobCardStatus::QualityCheck ? '●' : '○')],
        ['label' => __('Finished goods'), 'state' => $fgState, 'icon' => $fgDone ? '✓' : ($fgState === 'current' ? '●' : ($fgState === 'blocked' ? '!' : '○'))],
        ['label' => __('Dispatch'), 'state' => $dispatchState, 'icon' => in_array($dispatchState, ['completed'], true) ? '✓' : (in_array($dispatchState, ['current', 'blocked'], true) ? '●' : '○')],
    ];
@endphp

<section class="job-360-workflow mb-4" aria-label="{{ __('Workflow') }}">
    <h2 class="job-360-workflow__title">{{ __('Workflow') }}</h2>
    <nav class="job-360-stage-timeline" aria-label="{{ __('Production stage progression') }}">
        <ol class="job-360-stage-timeline__track">
            @foreach ($stages as $stage)
                <li @class([
                    'job-360-stage-timeline__step',
                    'job-360-stage-timeline__step--'.$stage['state'],
                ])>
                    <span class="job-360-stage-timeline__icon" aria-hidden="true">{{ $stage['icon'] }}</span>
                    <span class="job-360-stage-timeline__label">{{ $stage['label'] }}</span>
                    @unless ($loop->last)
                        <span @class([
                            'job-360-stage-timeline__connector',
                            'job-360-stage-timeline__connector--'.($stage['state'] === 'completed' ? 'completed' : 'future'),
                        ]) aria-hidden="true"></span>
                    @endunless
                </li>
            @endforeach
        </ol>
    </nav>
</section>
