<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['dimensions', 'active_dimension', 'filters']));

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

foreach (array_filter((['dimensions', 'active_dimension', 'filters']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="mb-4 flex flex-wrap gap-2 border-b border-erp-border pb-3">
    <?php $__currentLoopData = $dimensions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dimension): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a
            href="<?php echo e(route('admin.hr.kpi', array_merge($filters, ['dimension' => $dimension['key']]))); ?>"
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'rounded-md px-3 py-1.5 text-sm font-medium transition',
                'bg-erp-primary text-white' => $active_dimension === $dimension['key'],
                'text-slate-600 hover:bg-slate-100' => $active_dimension !== $dimension['key'],
            ]); ?>"
            data-turbo-frame="erp-main"
        >
            <?php echo e($dimension['label']); ?>

        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\kpi\partials\dimension-tabs.blade.php ENDPATH**/ ?>