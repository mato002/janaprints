<?php $outsource = $tabData['outsource'] ?? []; ?>

<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mt-6','id' => 'outsource']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-6','id' => 'outsource']); ?>
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Outsourced Production')); ?></h3>

    <?php if($outsource['vendor'] ?? null): ?>
        <dl class="grid grid-cols-1 gap-3 text-sm md:grid-cols-2 mb-4">
            <div><dt class="text-slate-500"><?php echo e(__('Vendor')); ?></dt><dd class="font-medium"><?php echo e($outsource['vendor']->vendor_name); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Issue date')); ?></dt><dd><?php echo e($outsource['issue_date']?->format('Y-m-d') ?? '—'); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Expected return')); ?></dt><dd><?php echo e($outsource['expected_return']?->format('Y-m-d') ?? '—'); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Quoted cost')); ?></dt><dd><?php echo e($outsource['quoted_cost'] !== null ? number_format($outsource['quoted_cost'], 2) : '—'); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Actual cost')); ?></dt><dd><?php echo e($outsource['actual_cost'] !== null ? number_format($outsource['actual_cost'], 2) : '—'); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Margin exposure')); ?></dt><dd><?php echo e(isset($outsource['cost_exposure']['margin_impact']) && $outsource['cost_exposure']['margin_impact'] !== null ? number_format($outsource['cost_exposure']['margin_impact'], 2) : '—'); ?></dd></div>
        </dl>
        <?php if($outsource['notes']): ?>
            <p class="text-sm text-slate-600 mb-4"><?php echo e($outsource['notes']); ?></p>
        <?php endif; ?>
    <?php endif; ?>

    <?php if($outsource['can_outsource'] ?? false): ?>
        <form method="POST" action="<?php echo e(route('admin.production.job-cards.outsource', $jobCard)); ?>" class="grid grid-cols-1 gap-3 md:grid-cols-2 max-w-2xl">
            <?php echo csrf_field(); ?>
            <div class="md:col-span-2">
                <label class="erp-label"><?php echo e(__('Production vendor')); ?></label>
                <select name="outsource_vendor_id" class="erp-input w-full" required>
                    <?php $__currentLoopData = $outsource['production_vendors'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($vendor->id); ?>"><?php echo e($vendor->vendor_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div><label class="erp-label"><?php echo e(__('Issue date')); ?></label><input type="date" name="outsource_issue_date" class="erp-input w-full" required value="<?php echo e(now()->format('Y-m-d')); ?>"></div>
            <div><label class="erp-label"><?php echo e(__('Expected return')); ?></label><input type="date" name="outsource_expected_return" class="erp-input w-full"></div>
            <div><label class="erp-label"><?php echo e(__('Quoted cost')); ?></label><input type="number" step="0.01" name="outsource_quoted_cost" class="erp-input w-full"></div>
            <div class="md:col-span-2"><label class="erp-label"><?php echo e(__('Notes')); ?></label><textarea name="outsource_notes" class="erp-input w-full" rows="2"></textarea></div>
            <div><button type="submit" class="erp-btn-primary"><?php echo e(__('Outsource production')); ?></button></div>
        </form>
    <?php elseif($outsource['can_return'] ?? false): ?>
        <form method="POST" action="<?php echo e(route('admin.production.job-cards.outsource.return', $jobCard)); ?>" class="flex flex-wrap items-end gap-3 max-w-md">
            <?php echo csrf_field(); ?>
            <div class="flex-1"><label class="erp-label"><?php echo e(__('Actual cost')); ?></label><input type="number" step="0.01" name="outsource_actual_cost" class="erp-input w-full"></div>
            <button type="submit" class="erp-btn-primary"><?php echo e(__('Mark returned')); ?></button>
        </form>
    <?php elseif(! ($outsource['vendor'] ?? null)): ?>
        <p class="text-sm text-slate-500"><?php echo e(__('This job has not been outsourced.')); ?></p>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/workspace/partials/outsource.blade.php ENDPATH**/ ?>