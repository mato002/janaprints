<?php if(count($warehouseSnapshot ?? []) > 0): ?>
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4']); ?>
        <h2 class="mb-2 text-sm font-semibold text-slate-900"><?php echo e(__('Warehouse snapshot')); ?></h2>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <?php $__currentLoopData = $warehouseSnapshot; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warehouse): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e($warehouse['url']); ?>" class="block rounded-lg border border-erp-border bg-white p-3 transition hover:border-erp-accent/40 hover:bg-slate-50" data-turbo-frame="erp-main">
                    <div class="mb-2 flex items-center justify-between gap-2">
                        <span class="font-medium text-slate-900"><?php echo e($warehouse['name']); ?></span>
                        <span class="text-xs font-semibold tabular-nums text-erp-primary"><?php echo e($warehouse['fill_percent']); ?>%</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                        <div
                            class="h-full rounded-full bg-erp-accent transition-all"
                            style="width: <?php echo e(min(100, max(0, $warehouse['fill_percent']))); ?>%"
                        ></div>
                    </div>
                    <p class="mt-1 text-[10px] uppercase tracking-wide text-slate-500"><?php echo e(__('Stores')); ?></p>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\store\desk\partials\warehouse-snapshot.blade.php ENDPATH**/ ?>