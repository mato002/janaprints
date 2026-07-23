<?php
    $checklist = $tabData['checklist'] ?? [];
    $eligibility = $tabData['dispatch_eligibility'] ?? ['eligible' => false, 'blockers' => [], 'warnings' => []];
    $dnEligibility = $tabData['delivery_note_eligibility'] ?? ['eligible' => false, 'blockers' => []];
    $presentation = $tabData['dispatch_presentation'] ?? [];
    $hasDeliveryNote = (bool) ($presentation['has_delivery_note'] ?? false);
    $jobCard = $jobCard ?? null;
?>

<?php if($hasDeliveryNote): ?>
    <?php echo $__env->make('admin.production.job-cards.workspace.partials.dispatch-summary-dashboard', [
        'tabData' => $tabData,
        'dispatchPresentation' => $presentation,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php else: ?>
    <?php if(! empty($eligibility['blockers'])): ?>
        <?php echo $__env->make('admin.production.job-cards.workspace.partials.control-alerts', [
            'alerts' => collect($eligibility['blockers'])->map(fn ($m) => ['type' => 'error', 'message' => $m])->all(),
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'lg:col-span-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'lg:col-span-1']); ?>
            <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Readiness score')); ?></h3>
            <p class="text-3xl font-bold tabular-nums text-erp-primary"><?php echo e($tabData['readiness_score'] ?? 0); ?>%</p>
            <p class="mt-2 text-sm text-slate-600">
                <?php if($eligibility['eligible'] ?? false): ?>
                    <?php echo e(__('Eligible to mark ready for dispatch')); ?>

                <?php else: ?>
                    <?php echo e(__('Dispatch blocked until checklist items pass')); ?>

                <?php endif; ?>
            </p>
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

        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'lg:col-span-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'lg:col-span-2']); ?>
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Dispatch readiness checklist')); ?></h3>
            <ul class="divide-y divide-erp-border">
                <?php $__currentLoopData = $checklist; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $stateBadge = match ($item['state']) {
                            'passed' => 'bg-emerald-100 text-emerald-800',
                            'failed' => 'bg-red-100 text-red-800',
                            'warning' => 'bg-amber-100 text-amber-800',
                            default => 'bg-slate-100 text-slate-600',
                        };
                    ?>
                    <li class="flex items-center justify-between gap-4 py-2.5 text-sm">
                        <span class="font-medium text-erp-primary"><?php echo e($item['label']); ?></span>
                        <span class="text-slate-500"><?php echo e($item['detail']); ?></span>
                        <span class="erp-badge shrink-0 <?php echo e($stateBadge); ?>"><?php echo e(ucfirst($item['state'])); ?></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
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
    </div>

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
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Create delivery note')); ?></h3>
        <p class="mb-4 text-sm text-slate-600"><?php echo e(__('Dispatch is managed through delivery notes. Create a delivery note to begin dispatch for this job.')); ?></p>

        <?php if($dnEligibility['eligible'] ?? false): ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Dispatch\DeliveryNote::class)): ?>
                <form method="POST" action="<?php echo e(route('admin.dispatch.delivery-notes.store-from-job', $jobCard)); ?>" class="mt-2">
                    <?php echo csrf_field(); ?>
                    <?php if (isset($component)) { $__componentOriginald411d1792bd6cc877d687758b753742c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald411d1792bd6cc877d687758b753742c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.primary-button','data' => ['type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('primary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit']); ?><?php echo e(__('Create delivery note')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $attributes = $__attributesOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__attributesOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $component = $__componentOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__componentOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
                </form>
            <?php else: ?>
                <p class="text-sm text-slate-500"><?php echo e(__('You do not have permission to create delivery notes.')); ?></p>
            <?php endif; ?>
        <?php else: ?>
            <ul class="list-disc ps-5 text-sm text-red-700">
                <?php $__currentLoopData = $dnEligibility['blockers'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $blocker): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($blocker); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
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
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/workspace/tabs/dispatch.blade.php ENDPATH**/ ?>