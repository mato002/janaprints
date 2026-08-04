<?php if(! empty($orderPresentation['production'])): ?>
    <?php $production = $orderPresentation['production']; ?>
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'border-emerald-200 bg-emerald-50/50']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'border-emerald-200 bg-emerald-50/50']); ?>
        <h3 class="mb-2 text-sm font-semibold text-emerald-900"><?php echo e(__('Production handoff')); ?></h3>
        <dl class="grid grid-cols-2 gap-x-3 gap-y-2 text-sm">
            <div>
                <dt class="text-xs text-emerald-700"><?php echo e(__('Job card')); ?></dt>
                <dd class="font-mono font-medium"><?php echo e($production['job_card_number']); ?></dd>
            </div>
            <div>
                <dt class="text-xs text-emerald-700"><?php echo e(__('Department')); ?></dt>
                <dd><?php echo e($production['department_label'] ?? '—'); ?></dd>
            </div>
            <div>
                <dt class="text-xs text-emerald-700"><?php echo e(__('Queue status')); ?></dt>
                <dd><?php echo e($production['queue_status']); ?></dd>
            </div>
            <div>
                <dt class="text-xs text-emerald-700"><?php echo e(__('Job status')); ?></dt>
                <dd><?php echo e($production['job_status']); ?></dd>
            </div>
        </dl>
        <?php if($production['department_queue_url'] ?? null): ?>
            <a href="<?php echo e($production['department_queue_url']); ?>" class="mt-3 inline-flex text-xs text-erp-primary hover:underline" data-turbo-frame="erp-main"><?php echo e(__('Open department register')); ?></a>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/sales/desk/partials/production-handoff.blade.php ENDPATH**/ ?>