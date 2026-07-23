<?php
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

    // Stock readiness (planning gate) takes precedence over consumption completeness on the timeline.
    if ($materialReadiness !== null) {
        if ($materialReadiness['ready'] ?? false) {
            $materialsState = 'completed';
            $materialsIcon = '✓';
        } else {
            $materialsState = 'blocked';
            $materialsIcon = ($materialReadiness['has_requirements'] ?? false) ? '⚠' : '!';
        }
    } else {
        $materialsState = $materialsConsumed ? 'completed' : 'future';
        $materialsIcon = $materialsConsumed ? '✓' : '○';
    }

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
        ['label' => __('Materials'), 'state' => $materialsState, 'icon' => $materialsIcon],
        ['label' => __('Production'), 'state' => $productionDone ? 'completed' : (in_array($jobCard?->status, [ProductionJobCardStatus::InProduction], true) ? 'current' : 'future'), 'icon' => $productionDone ? '✓' : ($jobCard?->status === ProductionJobCardStatus::InProduction ? '●' : '○')],
        ['label' => __('QC'), 'state' => $qcDone ? 'completed' : ($jobCard?->status === ProductionJobCardStatus::QualityCheck ? 'current' : 'future'), 'icon' => $qcDone ? '✓' : ($jobCard?->status === ProductionJobCardStatus::QualityCheck ? '●' : '○')],
        ['label' => __('Finished goods'), 'state' => $fgState, 'icon' => $fgDone ? '✓' : ($fgState === 'current' ? '●' : ($fgState === 'blocked' ? '!' : '○'))],
        ['label' => __('Dispatch'), 'state' => $dispatchState, 'icon' => in_array($dispatchState, ['completed'], true) ? '✓' : (in_array($dispatchState, ['current', 'blocked'], true) ? '●' : '○')],
    ];
?>

<section class="job-360-workflow mb-4" aria-label="<?php echo e(__('Workflow')); ?>">
    <h2 class="job-360-workflow__title"><?php echo e(__('Workflow')); ?></h2>
    <nav class="job-360-stage-timeline" aria-label="<?php echo e(__('Production stage progression')); ?>">
        <ol class="job-360-stage-timeline__track">
            <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'job-360-stage-timeline__step',
                    'job-360-stage-timeline__step--'.$stage['state'],
                ]); ?>">
                    <span class="job-360-stage-timeline__icon" aria-hidden="true"><?php echo e($stage['icon']); ?></span>
                    <span class="job-360-stage-timeline__label"><?php echo e($stage['label']); ?></span>
                    <?php if (! ($loop->last)): ?>
                        <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'job-360-stage-timeline__connector',
                            'job-360-stage-timeline__connector--'.($stage['state'] === 'completed' ? 'completed' : 'future'),
                        ]); ?>" aria-hidden="true"></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ol>
    </nav>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\partials\production-stage-timeline.blade.php ENDPATH**/ ?>