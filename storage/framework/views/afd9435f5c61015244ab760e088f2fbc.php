<?php if(count($reorderRecommendations ?? []) > 0): ?>
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
                <h2 class="text-sm font-semibold text-slate-900"><?php echo e(__('Recommended purchases')); ?></h2>
                <p class="mt-0.5 text-xs text-slate-500"><?php echo e(__('Replenishment suggestions from reorder intelligence.')); ?></p>
            </div>
            <a href="<?php echo e($reorderAlertsUrl ?? route('admin.store.desk.reorder-alerts')); ?>" class="text-xs font-medium text-erp-accent hover:underline" data-erp-modal-open><?php echo e(__('Alerts')); ?></a>
        </div>
        <ul class="divide-y divide-slate-100">
            <?php $__currentLoopData = $reorderRecommendations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="flex items-center justify-between gap-3 px-4 py-3 text-sm">
                    <span class="font-medium text-slate-900"><?php echo e($rec['name']); ?></span>
                    <span class="inline-flex shrink-0 items-center gap-2">
                        <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide',
                            'border-rose-200 bg-rose-50 text-rose-800' => $rec['urgency'] === __('Order today'),
                            'border-amber-200 bg-amber-50 text-amber-800' => $rec['urgency'] === __('Order tomorrow'),
                            'border-slate-200 bg-slate-50 text-slate-700' => $rec['urgency'] === __('Monitor'),
                        ]); ?>"><?php echo e($rec['urgency']); ?></span>
                        <span class="text-xs text-slate-500"><?php echo e($rec['action']); ?></span>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/store/desk/partials/reorder-suggestions.blade.php ENDPATH**/ ?>