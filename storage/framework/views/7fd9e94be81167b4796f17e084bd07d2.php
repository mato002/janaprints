<?php
    $currentSession = $sessionWidget['session'] ?? null;
    $sessionMetrics = $sessionWidget['metrics'] ?? null;
?>

<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6']); ?>
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h3 class="font-semibold"><?php echo e(__('Current session')); ?></h3>
            <?php if($currentSession): ?>
                <p class="mt-1 text-sm text-slate-500">
                    <?php echo e(__('Session :number — opened :time', [
                        'number' => $currentSession->session_number,
                        'time' => $currentSession->opened_at?->format('Y-m-d H:i'),
                    ])); ?>

                </p>
            <?php else: ?>
                <p class="mt-1 text-sm text-slate-500"><?php echo e(__('No active cashier session.')); ?></p>
            <?php endif; ?>
        </div>
        <?php if($currentSession): ?>
            <div class="flex flex-wrap gap-2">
                <a href="<?php echo e(route('admin.commercial.pos.sessions.show', $currentSession)); ?>" class="erp-btn-secondary"><?php echo e(__('View session')); ?></a>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('close', $currentSession)): ?>
                    <a href="<?php echo e(route('admin.commercial.pos.sessions.close', $currentSession)); ?>" class="erp-btn-secondary"><?php echo e(__('Close session')); ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if($currentSession && $sessionMetrics): ?>
        <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-5">
            <div class="rounded-lg border border-erp-border bg-slate-50 px-3 py-2">
                <p class="text-xs text-slate-500"><?php echo e(__('Cashier')); ?></p>
                <p class="font-medium"><?php echo e($currentSession->cashier?->name); ?></p>
            </div>
            <div class="rounded-lg border border-erp-border bg-slate-50 px-3 py-2">
                <p class="text-xs text-slate-500"><?php echo e(__('Opening float')); ?></p>
                <p class="font-medium tabular-nums"><?php echo e(number_format($currentSession->opening_float, 2)); ?></p>
            </div>
            <div class="rounded-lg border border-erp-border bg-slate-50 px-3 py-2">
                <p class="text-xs text-slate-500"><?php echo e(__('Sales count')); ?></p>
                <p class="font-medium tabular-nums"><?php echo e($sessionMetrics['sales_count']); ?></p>
            </div>
            <div class="rounded-lg border border-erp-border bg-slate-50 px-3 py-2">
                <p class="text-xs text-slate-500"><?php echo e(__('Current sales value')); ?></p>
                <p class="font-medium tabular-nums"><?php echo e(number_format($sessionMetrics['total_sales_value'], 2)); ?></p>
            </div>
            <div class="rounded-lg border border-erp-border bg-slate-50 px-3 py-2">
                <p class="text-xs text-slate-500"><?php echo e(__('Terminal')); ?></p>
                <p class="font-medium"><?php echo e($currentSession->terminal ?? '—'); ?></p>
            </div>
        </div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\commercial\pos\partials\session-widget.blade.php ENDPATH**/ ?>