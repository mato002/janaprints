<?php if(count($lowStockItems ?? []) > 0): ?>
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['padding' => false,'class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'class' => 'mb-4']); ?>
        <div class="flex items-center justify-between gap-2 border-b border-erp-border px-4 py-3">
            <div>
                <h2 class="text-sm font-semibold text-slate-900"><?php echo e(__('Low stock')); ?></h2>
                <p class="mt-0.5 text-xs text-slate-500"><?php echo e(__('Most urgent items right now.')); ?></p>
            </div>
            <a href="<?php echo e($reorderAlertsUrl ?? route('admin.store.desk.reorder-alerts')); ?>" class="text-xs font-medium text-erp-accent hover:underline" data-erp-modal-open><?php echo e(__('View all')); ?></a>
        </div>
        <ul class="divide-y divide-slate-100">
            <?php $__currentLoopData = $lowStockItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="flex items-center justify-between gap-3 px-4 py-3 text-sm">
                    <span class="min-w-0">
                        <span class="block font-medium text-slate-900"><?php echo e($item['name']); ?></span>
                        <?php if(! empty($item['warehouse'])): ?>
                            <span class="block text-xs text-slate-500"><?php echo e($item['warehouse']); ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="inline-flex shrink-0 items-center gap-1.5">
                        <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'font-semibold tabular-nums',
                            'text-rose-700' => $item['urgent'],
                            'text-amber-700' => ! $item['urgent'],
                        ]); ?>"><?php echo e($item['remaining_label']); ?></span>
                        <?php if($item['urgent']): ?>
                            <span aria-hidden="true" class="text-amber-500">⚠</span>
                        <?php endif; ?>
                    </span>
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
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\store\desk\partials\low-stock.blade.php ENDPATH**/ ?>