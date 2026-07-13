<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'formId' => null,
    'checkboxClass' => 'erp-bulk-checkbox',
    'selectAllId' => null,
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
    'formId' => null,
    'checkboxClass' => 'erp-bulk-checkbox',
    'selectAllId' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div
    x-data="{
        selectedCount: 0,
        refresh() {
            this.selectedCount = document.querySelectorAll('.<?php echo e($checkboxClass); ?>:checked').length;
        },
    }"
    x-init="
        refresh();
        document.addEventListener('change', (event) => {
            if (event.target.matches('.<?php echo e($checkboxClass); ?>') || event.target.id === '<?php echo e($selectAllId); ?>') {
                refresh();
            }
        });
    "
    x-show="selectedCount > 0"
    x-cloak
    <?php echo e($attributes->merge(['class' => 'erp-bulk-action-bar mb-3 flex flex-wrap items-center gap-2 rounded-lg border border-erp-border bg-slate-50 px-3 py-2'])); ?>

>
    <span class="text-xs font-medium text-slate-600" x-text="selectedCount + ' <?php echo e(__('selected')); ?>'"></span>
    <?php echo e($slot); ?>

</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/admin/bulk-action-bar.blade.php ENDPATH**/ ?>