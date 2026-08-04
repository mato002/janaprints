<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['tabs', 'active_tab', 'filters', 'index_route' => null]));

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

foreach (array_filter((['tabs', 'active_tab', 'filters', 'index_route' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $indexRoute = $index_route ?? 'admin.commercial.reports.customers.index';
?>

<div class="mb-4 overflow-x-auto">
    <nav class="flex gap-1 border-b border-erp-border" aria-label="<?php echo e(__('Customer report tabs')); ?>">
        <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $query = array_merge($filters, ['tab' => $tab['key'], 'page' => 1]);
            ?>
            <a
                href="<?php echo e(\App\Support\Navigation\WorkspaceEmbed::url(route($indexRoute, $query))); ?>"
                data-turbo-frame="<?php echo e(\App\Support\Navigation\WorkspaceEmbed::turboFrame()); ?>" data-turbo-action="advance"
                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'whitespace-nowrap border-b-2 px-3 py-2 text-sm font-medium transition-colors',
                    'border-erp-accent text-erp-accent' => $active_tab === $tab['key'],
                    'border-transparent text-slate-500 hover:border-slate-300 hover:text-erp-primary' => $active_tab !== $tab['key'],
                ]); ?>"
            >
                <?php echo e($tab['label']); ?>

            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </nav>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\commercial\reports\customers\partials\tabs.blade.php ENDPATH**/ ?>