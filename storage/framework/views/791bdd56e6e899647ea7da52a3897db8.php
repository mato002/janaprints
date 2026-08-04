<?php
    $manufacturingSummary = $tabData['manufacturing_summary'] ?? [];
    $printSource = $tabData['print_specification_source'] ?? null;
    $machine = $tabData['machine'] ?? [];
    $hasSpecs = $printSource || ! empty($manufacturingSummary) || ! empty($machine['machine_name']);
?>

<div class="job-360-overview">
    <div class="space-y-3">
        <?php if (isset($component)) { $__componentOriginalf57220fba53d148717b4781691527db9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf57220fba53d148717b4781691527db9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.job-module-card','data' => ['theme' => 'production','title' => __('Production'),'icon' => 'cog','compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.job-module-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['theme' => 'production','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Production')),'icon' => 'cog','compact' => true]); ?>
            <?php echo $__env->make('admin.production.job-cards.workspace.partials.operations-zone', [
                'jobCard' => $jobCard,
                'executionState' => $executionState ?? [],
                'assignableMachines' => $assignableMachines ?? collect(),
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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

        <?php echo $__env->make('admin.production.job-cards.workspace.partials.history-zone', ['jobCard' => $jobCard], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php if($hasSpecs): ?>
            <?php if (isset($component)) { $__componentOriginalf57220fba53d148717b4781691527db9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf57220fba53d148717b4781691527db9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.job-module-card','data' => ['theme' => 'materials','title' => __('Job specifications'),'icon' => 'document-text','compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.job-module-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['theme' => 'materials','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Job specifications')),'icon' => 'document-text','compact' => true]); ?>
                 <?php $__env->slot('actions', null, []); ?> 
                    <?php if(! empty($manufacturingSummary['manufacturing_url'])): ?>
                        <a href="<?php echo e($manufacturingSummary['manufacturing_url']); ?>" class="text-xs font-medium text-emerald-700 hover:underline" data-turbo-frame="erp-main"><?php echo e(__('Manufacturing tab')); ?> →</a>
                    <?php endif; ?>
                 <?php $__env->endSlot(); ?>

                <?php if($printSource): ?>
                    <dl class="job-360-zone__compact-grid mb-2">
                        <div><dt><?php echo e(__('Source')); ?></dt><dd><?php echo e($printSource['order_source_label'] ?? __('—')); ?></dd></div>
                        <div><dt><?php echo e(__('Product')); ?></dt><dd><?php echo e($printSource['product_name'] ?? __('—')); ?></dd></div>
                        <div class="sm:col-span-2"><dt><?php echo e(__('Specification')); ?></dt><dd><?php echo e($printSource['specification_label'] ?? __('—')); ?></dd></div>
                    </dl>
                <?php endif; ?>

                <?php if(! empty($manufacturingSummary) && ($manufacturingSummary['has_specification'] ?? false)): ?>
                    <dl class="job-360-zone__compact-grid">
                        <div><dt><?php echo e(__('Product')); ?></dt><dd><?php echo e($manufacturingSummary['product'] ?? '—'); ?></dd></div>
                        <div><dt><?php echo e(__('Quantity')); ?></dt><dd><?php echo e($manufacturingSummary['quantity'] ?? '—'); ?></dd></div>
                        <div><dt><?php echo e(__('Type')); ?></dt><dd><?php echo e($manufacturingSummary['production_type'] ?? '—'); ?></dd></div>
                        <div><dt><?php echo e(__('Sheets')); ?></dt><dd><?php echo e($manufacturingSummary['estimated_sheets'] ?? '—'); ?></dd></div>
                    </dl>
                <?php endif; ?>

                <?php if(! empty($machine['machine_name'])): ?>
                    <dl class="job-360-zone__compact-grid mt-2 border-t border-erp-border pt-2">
                        <div><dt><?php echo e(__('Machine')); ?></dt><dd><?php echo e($machine['machine_name']); ?></dd></div>
                        <div><dt><?php echo e(__('Status')); ?></dt><dd><?php echo e($machine['machine_status']); ?></dd></div>
                    </dl>
                <?php endif; ?>
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

        <?php echo $__env->make('admin.production.job-cards.workspace.partials.outsource', ['jobCard' => $jobCard, 'tabData' => $tabData], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/workspace/tabs/overview.blade.php ENDPATH**/ ?>