<?php
    use App\Enums\ProductionJobCardStatus;

    $workflowPresentation = $workflowPresentation ?? null;
    $status = $status ?? null;
    $hasPostedOutput = (bool) ($hasPostedOutput ?? false);
    $completion = $completion ?? ['eligible' => false, 'blockers' => []];
    $dispatchSummary = $dispatchSummary ?? null;

    if (! empty($workflowPresentation['badges'])) {
        $badges = $workflowPresentation['badges'];
        $currentStage = $workflowPresentation['current_stage_label'] ?? ($status?->label() ?? '—');
    } else {
        $badges = [];
        $currentStage = $status?->label() ?? '—';

        if ($dispatchSummary['has_delivery_note'] ?? false) {
            $badges[] = ['label' => $dispatchSummary['workflow_label'], 'variant' => match ($dispatchSummary['workflow_phase'] ?? '') {
                'delivered', 'closed' => 'success',
                'dispatched' => 'in_production',
                default => 'neutral',
            }];
            $currentStage = $dispatchSummary['workflow_label'];
        } elseif ($status === ProductionJobCardStatus::ReadyForDispatch && ! $hasPostedOutput) {
            $badges[] = ['label' => __('Production complete'), 'variant' => 'success'];
            $badges[] = ['label' => __('Finished goods pending'), 'variant' => 'warning'];
            $currentStage = __('Finished goods pending');
        } elseif ($status === ProductionJobCardStatus::Completed) {
            $badges[] = ['label' => __('Production complete'), 'variant' => 'success'];
            if (! $hasPostedOutput) {
                $badges[] = ['label' => __('Finished goods pending'), 'variant' => 'warning'];
                $currentStage = __('Finished goods pending');
            } else {
                $currentStage = __('Production complete');
            }
        } elseif ($status === ProductionJobCardStatus::ReadyForDispatch && $hasPostedOutput) {
            $badges[] = ['label' => __('Ready for dispatch'), 'variant' => 'success'];
            $currentStage = __('Ready for dispatch');
        } else {
            $badges[] = ['label' => $status?->label() ?? '—', 'variant' => match ($status) {
                ProductionJobCardStatus::InProduction, ProductionJobCardStatus::QualityCheck => 'in_production',
                ProductionJobCardStatus::Cancelled => 'danger',
                default => 'neutral',
            }];
            $currentStage = $status?->label() ?? '—';
        }
    }
?>

<?php $__currentLoopData = $badges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $badge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => $badge['variant']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($badge['variant'])]); ?><?php echo e($badge['label']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $attributes = $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $component = $__componentOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php if($currentStage): ?>
    <span class="text-xs text-slate-500">
        <span class="uppercase tracking-wide"><?php echo e(__('Stage')); ?></span>
        <span class="font-semibold text-slate-800"><?php echo e($currentStage); ?></span>
    </span>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/workspace/partials/display-status-badges.blade.php ENDPATH**/ ?>