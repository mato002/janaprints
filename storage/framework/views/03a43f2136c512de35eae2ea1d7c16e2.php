<?php
    $hasSpec = $tabData['has_specification'] ?? false;
    $pipeline = $tabData['timeline_pipeline'] ?? [];
?>

<div class="manufacturing-tab space-y-4">
    <?php if($hasSpec && ! empty($tabData['edit_url'])): ?>
        <div class="flex flex-wrap items-center gap-2">
            <a href="<?php echo e($tabData['edit_url']); ?>" class="erp-btn-secondary text-sm"><?php echo e(__('Edit specification')); ?></a>
            <a href="<?php echo e(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'materials'])); ?>" class="erp-btn-secondary text-sm"><?php echo e(__('Materials')); ?></a>
            <a href="<?php echo e(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'quality'])); ?>" class="erp-btn-secondary text-sm"><?php echo e(__('QC')); ?></a>
        </div>
    <?php endif; ?>

    <?php if(! $hasSpec): ?>
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
            <p class="text-sm text-slate-600"><?php echo e($tabData['empty_message'] ?? __('No structured Production Specification available.')); ?></p>
            <?php if(! empty($tabData['legacy'])): ?>
                <dl class="mt-4 divide-y divide-erp-border rounded-lg border border-erp-border text-sm">
                    <?php $__currentLoopData = $tabData['legacy']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($value): ?>
                            <div class="flex justify-between gap-3 px-3 py-2">
                                <dt class="text-slate-500"><?php echo e(ucfirst(str_replace('_', ' ', $label))); ?></dt>
                                <dd class="font-medium"><?php echo e($value); ?></dd>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </dl>
            <?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
    <?php else: ?>
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Manufacturing timeline')); ?></h3>
            <ol class="flex flex-wrap gap-2">
                <?php $__currentLoopData = $pipeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $tone = match ($stage['state']) {
                            'complete' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'current' => 'bg-erp-primary/10 text-erp-primary border-erp-primary/30 ring-2 ring-erp-primary/20',
                            default => 'bg-slate-50 text-slate-500 border-slate-200',
                        };
                    ?>
                    <li class="rounded-full border px-3 py-1 text-xs font-medium <?php echo e($tone); ?>"><?php echo e($stage['label']); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ol>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>

        <?php echo $__env->make('admin.production.job-cards.workspace.partials.manufacturing-dashboard', [
            'jobCard' => $jobCard,
            'tabData' => $tabData,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/workspace/tabs/manufacturing.blade.php ENDPATH**/ ?>