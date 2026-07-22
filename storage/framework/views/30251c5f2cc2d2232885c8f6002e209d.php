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
    <div class="border-b border-erp-border px-4 py-3">
        <h2 class="text-sm font-semibold text-slate-900"><?php echo e(__('Recent stock movements')); ?></h2>
        <p class="mt-0.5 text-xs text-slate-500"><?php echo e(__('Live feed of posted movements today and earlier.')); ?></p>
    </div>
    <?php if(count($movementFeed ?? []) === 0): ?>
        <div class="px-4 py-6 text-center text-sm text-slate-500"><?php echo e(__('No stock movements recorded yet.')); ?></div>
    <?php else: ?>
        <ul class="divide-y divide-slate-100">
            <?php $__currentLoopData = $movementFeed; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="flex items-center gap-4 px-4 py-3 text-sm">
                    <span class="w-12 shrink-0 font-mono text-xs tabular-nums text-slate-500"><?php echo e($movement['time']); ?></span>
                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'inline-flex shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide',
                        'border-emerald-200 bg-emerald-50 text-emerald-700' => $movement['inbound'],
                        'border-rose-200 bg-rose-50 text-rose-700' => ! $movement['inbound'],
                    ]); ?>"><?php echo e($movement['type']); ?></span>
                    <span class="min-w-0 flex-1 truncate font-medium text-slate-900"><?php echo e($movement['item']); ?></span>
                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'shrink-0 font-mono text-xs font-semibold tabular-nums',
                        'text-emerald-700' => $movement['inbound'],
                        'text-rose-700' => ! $movement['inbound'],
                    ]); ?>"><?php echo e($movement['quantity']); ?></span>
                </li>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/store/desk/partials/movement-feed.blade.php ENDPATH**/ ?>