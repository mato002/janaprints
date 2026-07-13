<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title', 'description' => null]));

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

foreach (array_filter((['title', 'description' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>


<div <?php echo e($attributes->merge(['class' => 'workspace-page-header mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between'])); ?>>
    <div class="min-w-0">
        <h1 class="text-dashboard-title text-erp-primary truncate"><?php echo e($title); ?></h1>
        <?php if($description): ?>
            <p class="mt-1 text-sm text-slate-500"><?php echo e($description); ?></p>
        <?php endif; ?>
    </div>
    <?php if(isset($secondary) || isset($export) || isset($actions) || ! $slot->isEmpty()): ?>
        <div class="workspace-page-header__actions flex shrink-0 flex-wrap items-center justify-end gap-2">
            <?php if(isset($secondary)): ?>
                <?php echo e($secondary); ?>

            <?php endif; ?>
            <?php if(isset($export)): ?>
                <?php echo e($export); ?>

            <?php endif; ?>
            <?php if(isset($actions)): ?>
                <?php echo e($actions); ?>

            <?php elseif(! $slot->isEmpty()): ?>
                <?php echo e($slot); ?>

            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/admin/page-header.blade.php ENDPATH**/ ?>