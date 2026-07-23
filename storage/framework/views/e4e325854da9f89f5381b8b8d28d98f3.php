<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['items' => []]));

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

foreach (array_filter((['items' => []]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="workspace-action-bar flex flex-wrap gap-2">
    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(! empty($item['route']) && \Illuminate\Support\Facades\Route::has($item['route'])): ?>
            <?php if(empty($item['permission']) || auth()->user()?->can($item['permission'])): ?>
                <a href="<?php echo e(route($item['route'], $item['route_params'] ?? [])); ?>" class="erp-btn-secondary"><?php echo e($item['label']); ?></a>
            <?php endif; ?>
        <?php elseif(! empty($item['url'])): ?>
            <a href="<?php echo e($item['url']); ?>" class="erp-btn-secondary"><?php echo e($item['label']); ?></a>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php echo e($slot); ?>

</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/admin/quick-actions.blade.php ENDPATH**/ ?>