<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['tabs', 'active_tab', 'filters']));

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

foreach (array_filter((['tabs', 'active_tab', 'filters']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="mb-4 flex flex-wrap gap-2 border-b border-erp-border pb-3">
    <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a
            href="<?php echo e(route('admin.reports.hr', array_merge($filters, ['tab' => $tab['key']]))); ?>"
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'rounded-md px-3 py-1.5 text-sm font-medium transition',
                'bg-erp-primary text-white' => $active_tab === $tab['key'],
                'text-slate-600 hover:bg-slate-100' => $active_tab !== $tab['key'],
            ]); ?>"
            data-turbo-frame="erp-main"
        >
            <?php echo e($tab['label']); ?>

        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\reports\hr\partials\tabs.blade.php ENDPATH**/ ?>