<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['items']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['items']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    use App\Support\Navigation\WorkspaceEmbed;
    $turboFrame = WorkspaceEmbed::turboFrame();
?>

<section id="hr-action-center" class="mb-3" aria-label="<?php echo e(__('HR Action Center')); ?>">
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['padding' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
        <div class="border-b border-erp-border px-4 py-2.5">
            <h2 class="text-xs font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('HR Action Center')); ?></h2>
            <p class="text-[11px] text-slate-500"><?php echo e(__('Pending approvals and items requiring HR attention.')); ?></p>
        </div>
        <div class="grid grid-cols-1 gap-px bg-erp-border sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $severityClass = match ($item['severity']) {
                        'high' => 'border-l-red-500 bg-red-50/60',
                        'medium' => 'border-l-amber-500 bg-amber-50/60',
                        'low' => 'border-l-emerald-500 bg-emerald-50/40',
                        default => 'border-l-slate-200 bg-white',
                    };
                ?>
                <?php if($item['clickable'] ?? false): ?>
                    <a
                        href="<?php echo e(\App\Support\Navigation\WorkspaceEmbed::url($item['url'])); ?>"
                        class="flex min-h-[5.5rem] flex-col justify-between border-l-4 px-3 py-3 transition-colors hover:bg-slate-50 <?php echo e($severityClass); ?>"
                        data-turbo-frame="<?php echo e($turboFrame); ?>"
                    >
                        <span class="text-[11px] font-medium leading-tight text-slate-600"><?php echo e($item['label']); ?></span>
                        <span class="text-2xl font-bold tabular-nums text-erp-primary"><?php echo e($item['count']); ?></span>
                    </a>
                <?php else: ?>
                    <div class="flex min-h-[5.5rem] flex-col justify-between border-l-4 px-3 py-3 <?php echo e($severityClass); ?>">
                        <span class="text-[11px] font-medium leading-tight text-slate-600"><?php echo e($item['label']); ?></span>
                        <span class="text-2xl font-bold tabular-nums text-erp-primary"><?php echo e($item['count']); ?></span>
                    </div>
                <?php endif; ?>
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
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/hr/dashboard/partials/action-center.blade.php ENDPATH**/ ?>