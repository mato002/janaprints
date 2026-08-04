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
        ['label' => __('MAT'), 'full' => __('Materials'), 'state' => $materialsState, 'theme' => 'materials'],
        ['label' => __('PROD'), 'full' => __('Production'), 'state' => $productionDone ? 'completed' : (in_array($jobCard?->status, [ProductionJobCardStatus::InProduction], true) ? 'current' : 'future'), 'theme' => 'production'],
        ['label' => __('QC'), 'full' => __('QC'), 'state' => $qcDone ? 'completed' : ($jobCard?->status === ProductionJobCardStatus::QualityCheck ? 'current' : 'future'), 'theme' => 'qc'],
        ['label' => __('FG'), 'full' => __('Finished goods'), 'state' => $fgState, 'theme' => 'slate'],
        ['label' => __('DISP'), 'full' => __('Dispatch'), 'state' => $dispatchState, 'theme' => 'dispatch'],
    ];

    $compact = (bool) ($compact ?? false);
?>

<?php if($compact): ?>
    <div class="job-360-workflow mes-workflow" aria-label="<?php echo e(__('Production stage progression')); ?>">
        <ol class="job-360-pipeline job-360-pipeline--compact">
            <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="<?php echo \Illuminate\Support\Arr::toCssClasses(['job-360-pipeline__step', 'job-360-pipeline__step--'.$stage['state'], 'job-360-pipeline__step--'.$stage['theme']]); ?>" title="<?php echo e($stage['full']); ?>">
                    <div class="job-360-pipeline__node" aria-hidden="true"></div>
                    <span class="job-360-pipeline__label"><?php echo e($stage['label']); ?></span>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ol>
    </div>
<?php else: ?>
    <?php if (isset($component)) { $__componentOriginalf57220fba53d148717b4781691527db9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf57220fba53d148717b4781691527db9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.job-module-card','data' => ['theme' => 'slate','title' => __('Workflow'),'icon' => 'switch-horizontal','compact' => true,'ariaLabel' => ''.e(__('Production stage progression')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.job-module-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['theme' => 'slate','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Workflow')),'icon' => 'switch-horizontal','compact' => true,'aria-label' => ''.e(__('Production stage progression')).'']); ?>
        <ol class="job-360-pipeline">
            <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="<?php echo \Illuminate\Support\Arr::toCssClasses(['job-360-pipeline__step', 'job-360-pipeline__step--'.$stage['state'], 'job-360-pipeline__step--'.$stage['theme']]); ?>">
                    <div class="job-360-pipeline__node" aria-hidden="true"></div>
                    <span class="job-360-pipeline__label"><?php echo e($stage['full']); ?></span>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ol>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf57220fba53d148717b4781691527db9)): ?>
<?php $attributes = $__attributesOriginalf57220fba53d148717b4781691527db9; ?>
<?php unset($__attributesOriginalf57220fba53d148717b4781691527db9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf57220fba53d148717b4781691527db9)): ?>
<?php $component = $__componentOriginalf57220fba53d148717b4781691527db9; ?>
<?php unset($__componentOriginalf57220fba53d148717b4781691527db9); ?>
<?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/workspace/partials/production-stage-timeline.blade.php ENDPATH**/ ?>