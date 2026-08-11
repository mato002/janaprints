<div class="artwork-detail-card">
    <h2 class="artwork-detail-card__title"><?php echo e(__('Details')); ?></h2>
    <dl class="artwork-detail-meta-grid">
        <div class="artwork-detail-meta-grid__item">
            <dt class="artwork-detail-meta-grid__label"><?php echo e(__('Priority')); ?></dt>
            <dd class="artwork-detail-meta-grid__value">
                <?php if (isset($component)) { $__componentOriginal3961274b5c8f86cd4e1074ec5f54b0f9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3961274b5c8f86cd4e1074ec5f54b0f9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.artwork-priority-badge','data' => ['priority' => $request->priority]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.artwork-priority-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['priority' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($request->priority)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3961274b5c8f86cd4e1074ec5f54b0f9)): ?>
<?php $attributes = $__attributesOriginal3961274b5c8f86cd4e1074ec5f54b0f9; ?>
<?php unset($__attributesOriginal3961274b5c8f86cd4e1074ec5f54b0f9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3961274b5c8f86cd4e1074ec5f54b0f9)): ?>
<?php $component = $__componentOriginal3961274b5c8f86cd4e1074ec5f54b0f9; ?>
<?php unset($__componentOriginal3961274b5c8f86cd4e1074ec5f54b0f9); ?>
<?php endif; ?>
            </dd>
        </div>
        <div class="artwork-detail-meta-grid__item">
            <dt class="artwork-detail-meta-grid__label"><?php echo e(__('Due')); ?></dt>
            <dd class="artwork-detail-meta-grid__value"><?php echo e($request->due_date?->format('d M Y') ?? '—'); ?></dd>
        </div>
        <div class="artwork-detail-meta-grid__item">
            <dt class="artwork-detail-meta-grid__label"><?php echo e(__('Designer')); ?></dt>
            <dd class="artwork-detail-meta-grid__value"><?php echo e($request->assignedDesigner?->name ?? '—'); ?></dd>
        </div>
    </dl>
    <?php if($request->description): ?>
        <div class="mt-4 border-t border-slate-100 pt-4">
            <p class="text-xs font-medium text-slate-500"><?php echo e(__('Description')); ?></p>
            <p class="mt-1 text-sm text-slate-700"><?php echo e($request->description); ?></p>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\artwork\requests\partials\details-grid.blade.php ENDPATH**/ ?>