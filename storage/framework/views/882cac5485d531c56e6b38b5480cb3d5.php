<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'paginator',
]));

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

foreach (array_filter(([
    'paginator',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if($paginator instanceof \Illuminate\Contracts\Pagination\Paginator && $paginator->total() > 0): ?>
    <div class="erp-table-footer flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-slate-600">
            <?php echo e(__('Showing :from–:to of :total', [
                'from' => number_format($paginator->firstItem() ?? 0),
                'to' => number_format($paginator->lastItem() ?? 0),
                'total' => number_format($paginator->total()),
            ])); ?>

        </p>
        <div class="erp-table-pagination">
            <?php echo e($paginator->withQueryString()->links()); ?>

        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\janaprints\resources\views/components/admin/table-pagination.blade.php ENDPATH**/ ?>