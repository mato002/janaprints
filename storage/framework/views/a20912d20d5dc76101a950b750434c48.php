<div class="job-360__header mb-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-xl font-semibold text-slate-900"><?php echo e($header['job_number']); ?></h1>
                <?php if (isset($component)) { $__componentOriginal6b1e38fda99422b6943f33aba545ca7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6b1e38fda99422b6943f33aba545ca7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.enum-status-badge','data' => ['status' => $header['status']->value]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.enum-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($header['status']->value)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6b1e38fda99422b6943f33aba545ca7b)): ?>
<?php $attributes = $__attributesOriginal6b1e38fda99422b6943f33aba545ca7b; ?>
<?php unset($__attributesOriginal6b1e38fda99422b6943f33aba545ca7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6b1e38fda99422b6943f33aba545ca7b)): ?>
<?php $component = $__componentOriginal6b1e38fda99422b6943f33aba545ca7b; ?>
<?php unset($__componentOriginal6b1e38fda99422b6943f33aba545ca7b); ?>
<?php endif; ?>
                <span class="erp-badge erp-badge--draft text-xs"><?php echo e(str_replace('_', ' ', $header['priority']->value)); ?></span>
                <?php if($header['is_delayed']): ?>
                    <span class="text-xs font-medium text-red-600"><?php echo e(__('Delayed')); ?></span>
                <?php endif; ?>
            </div>
            <p class="mt-1 text-sm text-slate-600">
                <?php echo e($header['customer_name'] ?? __('No customer')); ?>

                <?php if($header['sales_order_number']): ?>
                    <span class="text-slate-400">·</span>
                    <span class="font-mono text-xs"><?php echo e($header['sales_order_number']); ?></span>
                <?php endif; ?>
                <?php if($header['product_name']): ?>
                    <span class="text-slate-400">·</span>
                    <?php echo e($header['product_name']); ?>

                <?php endif; ?>
            </p>
            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                <span><?php echo e(__('Due')); ?>: <strong class="font-medium text-slate-700"><?php echo e($header['due_date']?->format('Y-m-d') ?? '—'); ?></strong></span>
                <span><?php echo e(__('Type')); ?>: <strong class="font-medium text-slate-700"><?php echo e(str_replace('_', ' ', $header['production_type']->value)); ?></strong></span>
                <span><?php echo e(__('Work center')); ?>: <strong class="font-medium text-slate-700"><?php echo e($header['work_center'] ?? '—'); ?></strong></span>
                <span><?php echo e(__('Progress')); ?>: <strong class="font-medium text-slate-700 tabular-nums"><?php echo e($header['progress_percent']); ?>%</strong></span>
            </div>
        </div>
        <div class="flex shrink-0 items-center gap-2">
            <a href="<?php echo e(route('admin.production.floor')); ?>" class="erp-btn-secondary text-sm" data-turbo-frame="erp-main"><?php echo e(__('Back to floor')); ?></a>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $jobCard)): ?>
                <a href="<?php echo e(route('admin.production.job-cards.edit', $jobCard)); ?>" class="erp-btn-secondary text-sm"><?php echo e(__('Edit')); ?></a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/workspace/header.blade.php ENDPATH**/ ?>