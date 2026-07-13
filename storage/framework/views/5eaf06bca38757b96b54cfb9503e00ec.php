<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['snapshot']));

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

foreach (array_filter((['snapshot']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
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
    <div class="mb-3 flex items-start justify-between gap-2">
        <h2 class="text-xs font-semibold uppercase tracking-wide text-erp-primary"><?php echo e($snapshot['title']); ?></h2>
        <?php if($snapshot['open_url'] ?? null): ?>
            <a href="<?php echo e(\App\Support\Navigation\WorkspaceEmbed::url($snapshot['open_url'])); ?>" class="shrink-0 text-[11px] font-medium text-erp-accent hover:underline" data-turbo-frame="<?php echo e($turboFrame); ?>">
                <?php echo e($snapshot['open_label']); ?>

            </a>
        <?php endif; ?>
    </div>

    <dl class="grid grid-cols-2 gap-x-3 gap-y-2 text-xs">
        <?php $__currentLoopData = $snapshot['metrics']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div>
                <dt class="text-[10px] uppercase tracking-wide text-slate-400"><?php echo e($metric['label']); ?></dt>
                <dd class="mt-0.5 font-semibold tabular-nums text-erp-primary"><?php echo e($metric['value']); ?></dd>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </dl>

    <?php if(! empty($snapshot['categories'])): ?>
        <div class="mt-3 border-t border-erp-border pt-3">
            <p class="mb-2 text-[10px] uppercase tracking-wide text-slate-400"><?php echo e(__('By Category')); ?></p>
            <ul class="space-y-1 text-xs">
                <?php $__currentLoopData = $snapshot['categories']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex items-center justify-between gap-2 rounded border border-erp-border/60 px-2 py-1.5">
                        <span class="text-slate-600"><?php echo e($category['label']); ?></span>
                        <span class="tabular-nums text-slate-500">
                            <?php if($category['expiring'] > 0): ?>
                                <span class="text-amber-700"><?php echo e($category['expiring']); ?> <?php echo e(__('expiring')); ?></span>
                            <?php endif; ?>
                            <?php if($category['expired'] > 0): ?>
                                <span class="text-red-700"><?php echo e($category['expired']); ?> <?php echo e(__('expired')); ?></span>
                            <?php endif; ?>
                        </span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/hr/dashboard/partials/document-compliance.blade.php ENDPATH**/ ?>