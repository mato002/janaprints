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
    <div class="mb-4 flex items-center justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-erp-primary"><?php echo e(__('Approval workflow')); ?></h3>
            <p class="text-sm text-slate-600"><?php echo e(__('Current status: :status', ['status' => $approvals['status']?->label()])); ?></p>
        </div>
        <span class="erp-badge erp-badge--<?php echo e($approvals['status']?->badgeClass()); ?>"><?php echo e($approvals['status']?->label()); ?></span>
    </div>

    <ol class="space-y-3">
        <?php $__currentLoopData = $approvals['timeline']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li class="flex items-start gap-3 rounded-lg border px-4 py-3 <?php echo e($step['done'] ? 'border-emerald-100 bg-emerald-50/40' : 'border-slate-100 bg-slate-50/40'); ?>">
                <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-xs font-bold <?php echo e($step['done'] ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600'); ?>">
                    <?php echo e($step['done'] ? '✓' : '·'); ?>

                </span>
                <div class="min-w-0">
                    <p class="font-medium text-slate-900"><?php echo e($step['label']); ?></p>
                    <p class="text-sm text-slate-600">
                        <?php if($step['done']): ?>
                            <?php echo e($step['user'] ?? __('System')); ?> · <?php echo e($step['at']?->format('M j, Y H:i')); ?>

                        <?php else: ?>
                            <?php echo e(__('Pending')); ?>

                        <?php endif; ?>
                    </p>
                </div>
            </li>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\payroll\360\tabs\approvals.blade.php ENDPATH**/ ?>